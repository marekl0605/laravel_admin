<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Services\RolePermissionService;
use App\Models\User;
use App\Models\LegalRepresentative;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Carbon\Carbon;

class AdminController extends Controller
{
    protected $rolePermissionService;

    public function __construct(RolePermissionService $rolePermissionService)
    {
        $this->rolePermissionService = $rolePermissionService;
    }

    /**
     * Get admin dashboard statistics
     */
    public function dashboard(): JsonResponse
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'total_students' => User::role('student')->count(),
                'total_instructors' => User::role('instructor')->count(),
                'total_secretaries' => User::role('secretary')->count(),
                'total_legal_representatives' => User::role('legal_representative')->count(),
                'active_users' => User::where('status', 'active')->count(),
                'inactive_users' => User::where('status', 'inactive')->count(),
                'suspended_users' => User::where('status', 'suspended')->count(),
            ];

            $recent_users = User::with('roles')->latest()->take(10)->get();
            $role_distribution = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select('roles.name', DB::raw('count(*) as count'))
                ->groupBy('roles.name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'stats' => $stats,
                    'recent_users' => $recent_users,
                    'role_distribution' => $role_distribution
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard data fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data'
            ], 500);
        }
    }

    /**
     * Get paginated users list with filters
     */
    public function users(Request $request): JsonResponse
    {
        try {
            $query = User::with('roles');

            // Filter by role
            if ($request->filled('role')) {
                $query->role($request->role);
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Search functionality
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('student_id', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            }

            $users = $query->paginate($request->get('per_page', 20));
            $roles = Role::all();

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'roles' => $roles
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Users fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users'
            ], 500);
        }
    }

    /**
     * Get roles for user creation
     */
    public function getRoles(): JsonResponse
    {
        try {
            $roles = Role::all();
            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            Log::error('Roles fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch roles'
            ], 500);
        }
    }

    /**
     * Store new user
     */
    public function storeUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before:today',
        ]);

        try {
            DB::beginTransaction();

            $userData = $request->only([
                'name',
                'email',
                'phone',
                'address',
                'date_of_birth',
            ]);
            $userData['password'] = Hash::make($request->password);

            $user = $this->rolePermissionService->createUserWithRole($userData, $request->role);

            if (!$user) {
                throw new \Exception('Failed to create user');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user->load('roles')
            ], 201);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('User creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create user. Please try again.'
            ], 500);
        }
    }

    /**
     * Get user details for editing
     */
    public function showUser(User $user): JsonResponse
    {
        try {
            $user->load(['roles', 'permissions']);

            // Get legal representative relationships
            $representedStudents = [];
            $legalRepresentatives = [];

            if ($user->isLegalRepresentative()) {
                $representedStudents = $user->representedStudents;
            }

            if ($user->isStudent()) {
                $legalRepresentatives = $user->legalRepresentatives;
            }

            $roles = Role::all();
            $userRole = $user->roles->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'roles' => $roles,
                    'user_role' => $userRole,
                    'represented_students' => $representedStudents,
                    'legal_representatives' => $legalRepresentatives
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('User show failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user details'
            ], 500);
        }
    }

    /**
     * Update user
     */
    public function updateUser(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date|before:today',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        try {
            DB::beginTransaction();

            $userData = $request->only([
                'name',
                'email',
                'phone',
                'address',
                'date_of_birth',
                'status'
            ]);

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Update role if changed
            $currentRole = $user->roles->first()?->name;
            if ($currentRole !== $request->role) {
                $user->syncRoles([$request->role]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user->load('roles')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('User update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update user. Please try again.'
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user): JsonResponse
    {
        try {
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account.'
                ], 403);
            }

            DB::beginTransaction();

            // Remove legal representative relationships
            LegalRepresentative::where('representative_id', $user->id)
                ->orWhere('student_id', $user->id)
                ->delete();

            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('User deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user. Please try again.'
            ], 500);
        }
    }

    /**
     * Impersonate user
     */
    public function impersonateUser(User $user): JsonResponse
    {
        try {
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot impersonate yourself.'
                ], 403);
            }

            session(['impersonator' => auth()->id()]);
            auth()->login($user);

            return response()->json([
                'success' => true,
                'message' => "You are now impersonating {$user->name}",
                'data' => [
                    'user' => $user,
                    'impersonating' => true
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Impersonation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Impersonation failed'
            ], 500);
        }
    }

    /**
     * Stop impersonation
     */
    public function stopImpersonation(): JsonResponse
    {
        try {
            if (!session('impersonator')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active impersonation session'
                ], 400);
            }

            $impersonatorId = session('impersonator');
            session()->forget('impersonator');

            $impersonator = User::find($impersonatorId);
            if ($impersonator) {
                auth()->login($impersonator);
            }

            return response()->json([
                'success' => true,
                'message' => 'Impersonation stopped',
                'data' => [
                    'user' => $impersonator,
                    'impersonating' => false
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Stop impersonation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to stop impersonation'
            ], 500);
        }
    }

    /**
     * Get all roles with permissions
     */
    public function roles(): JsonResponse
    {
        try {
            $roles = Role::with('permissions')->get();
            return response()->json([
                'success' => true,
                'data' => $roles
            ]);
        } catch (\Exception $e) {
            Log::error('Roles fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch roles'
            ], 500);
        }
    }

    /**
     * Get all permissions grouped
     */
    public function permissions(): JsonResponse
    {
        try {
            $permissions = Permission::all()->groupBy(function ($permission) {
                return explode('-', $permission->name)[0];
            });

            return response()->json([
                'success' => true,
                'data' => $permissions
            ]);
        } catch (\Exception $e) {
            Log::error('Permissions fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch permissions'
            ], 500);
        }
    }

    /**
     * Get system settings
     */
    public function settings(): JsonResponse
    {
        try {
            // This would typically load system settings from a config table
            $settings = [
                'app_name' => config('app.name'),
                'app_env' => config('app.env'),
                'timezone' => config('app.timezone'),
                // Add more settings as needed
            ];

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);
        } catch (\Exception $e) {
            Log::error('Settings fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch settings'
            ], 500);
        }
    }

    /**
     * Get security logs
     */
    public function securityLogs(Request $request): JsonResponse
    {
        try {
            $query = User::query();

            if ($request->filled('date_from')) {
                $query->where('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('created_at', '<=', $request->date_to);
            }

            $logs = $query->with('roles')->latest()->paginate($request->get('per_page', 50));

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            Log::error('Security logs fetch failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch security logs'
            ], 500);
        }
    }

    /**
     * Get reports data
     */
    public function reports(): JsonResponse
    {
        try {
            // Generate basic reports data
            $reports = [
                'user_registration_trends' => User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                    ->groupBy('date')
                    ->orderBy('date', 'desc')
                    ->take(30)
                    ->get(),
                'role_statistics' => User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                    ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->select('roles.name', DB::raw('count(*) as count'))
                    ->groupBy('roles.name')
                    ->get(),
                'status_distribution' => User::select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $reports
            ]);
        } catch (\Exception $e) {
            Log::error('Reports generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate reports'
            ], 500);
        }
    }

    /**
     * Export user data as CSV
     */
    public function exportUsers(Request $request): JsonResponse
    {
        try {
            $query = User::with('roles');

            if ($request->filled('role')) {
                $query->role($request->role);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $users = $query->get();

            $filename = 'users_export_' . Carbon::now()->format('Y-m-d_H-i-s') . '.csv';

            $csvData = [];
            $csvData[] = ['ID', 'Name', 'Email', 'Role', 'Status', 'Student ID', 'Employee ID', 'Phone', 'Created At'];

            foreach ($users as $user) {
                $csvData[] = [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->roles->first()?->name ?? 'No Role',
                    $user->status,
                    $user->student_id,
                    $user->employee_id,
                    $user->phone,
                    $user->created_at->format('Y-m-d H:i:s'),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'filename' => $filename,
                    'csv_data' => $csvData,
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export users'
            ], 500);
        }
    }

    /**
     * Bulk actions on users
     */
    public function bulkActions(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,suspend,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $userIds = $request->user_ids;

            // Prevent action on current user
            $userIds = array_filter($userIds, function ($id) {
                return $id != auth()->id();
            });

            if (empty($userIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid users selected for bulk action'
                ], 400);
            }

            switch ($request->action) {
                case 'activate':
                    User::whereIn('id', $userIds)->update(['status' => 'active']);
                    $message = 'Users activated successfully.';
                    break;

                case 'deactivate':
                    User::whereIn('id', $userIds)->update(['status' => 'inactive']);
                    $message = 'Users deactivated successfully.';
                    break;

                case 'suspend':
                    User::whereIn('id', $userIds)->update(['status' => 'suspended']);
                    $message = 'Users suspended successfully.';
                    break;

                case 'delete':
                    // Remove legal representative relationships
                    LegalRepresentative::whereIn('representative_id', $userIds)
                        ->orWhereIn('student_id', $userIds)
                        ->delete();

                    User::whereIn('id', $userIds)->delete();
                    $message = 'Users deleted successfully.';
                    break;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'affected_count' => count($userIds)
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk action failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bulk action failed. Please try again.'
            ], 500);
        }
    }
}
