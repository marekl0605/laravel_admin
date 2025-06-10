<?php

namespace App\Http\Services;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Collection;

class RolePermissionService
{
    /**
     * Assign a role to a user
     */
    public function assignRole(User $user, string $roleName): bool
    {
        try {
            $user->assignRole($roleName);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Remove a role from a user
     */
    public function removeRole(User $user, string $roleName): bool
    {
        try {
            $user->removeRole($roleName);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all available roles
     */
    public function getAllRoles(): Collection
    {
        return Role::all();
    }

    /**
     * Get all available permissions
     */
    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }

    /**
     * Check if user can access student data
     */
    public function canAccessStudentData(User $user, User $student): bool
    {
        // Administrator can access all student data
        if ($user->isAdministrator()) {
            return true;
        }

        // Students can access their own data
        if ($user->id === $student->id && $user->isStudent()) {
            return true;
        }

        // Secretary can access student data
        if ($user->isSecretary() && $student->isStudent()) {
            return true;
        }

        // Legal representative can access represented student data
        if ($user->isLegalRepresentative() && $user->representsStudent($student)) {
            return true;
        }

        // Instructors can access their students' data (you'll need to implement course enrollment logic)
        if ($user->isInstructor() && $this->isInstructorOfStudent($user, $student)) {
            return true;
        }

        return false;
    }

    /**
     * Check if instructor teaches a specific student
     */
    private function isInstructorOfStudent(User $instructor, User $student): bool
    {
        // This would need to be implemented based on your course enrollment system
        // For now, returning false as placeholder
        return false;
    }

    /**
     * Get users with specific role
     */
    public function getUsersByRole(string $roleName): Collection
    {
        return User::role($roleName)->get();
    }

    /**
     * Get role hierarchy for authorization
     */
    public function getRoleHierarchy(): array
    {
        return [
            'administrator' => ['secretary', 'instructor', 'student', 'legal_representative'],
            'secretary' => ['student'],
            'instructor' => [],
            'student' => [],
            'legal_representative' => [],
        ];
    }

    /**
     * Check if user can manage another user based on role hierarchy
     */
    public function canManageUser(User $manager, User $user): bool
    {
        $hierarchy = $this->getRoleHierarchy();
        $managerRole = $manager->getPrimaryRole();
        $userRole = $user->getPrimaryRole();

        if (!$managerRole || !$userRole) {
            return false;
        }

        return in_array($userRole, $hierarchy[$managerRole] ?? []);
    }

    /**
     * Get permissions for a specific role
     */
    public function getPermissionsForRole(string $roleName): Collection
    {
        $role = Role::findByName($roleName);
        return $role ? $role->permissions : collect();
    }

    /**
     * Sync permissions for a role
     */
    public function syncRolePermissions(string $roleName, array $permissions): bool
    {
        try {
            $role = Role::findByName($roleName);
            if ($role) {
                $role->syncPermissions($permissions);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create a new user with role
     */
    public function createUserWithRole(array $userData, string $roleName): ?User
    {
        try {
            $user = User::create($userData);
            $user->assignRole($roleName);
            return $user;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get dashboard permissions based on user role
     */
    public function getDashboardPermissions(User $user): array
    {
        $permissions = [];

        if ($user->isAdministrator()) {
            $permissions = [
                'view_admin_dashboard',
                'manage_users',
                'system_settings',
                'view_all_reports',
                'impersonate_users',
            ];
        } elseif ($user->isSecretary()) {
            $permissions = [
                'view_secretary_dashboard',
                'manage_student_records',
                'process_enrollments',
                'generate_reports',
                'manage_schedules',
            ];
        } elseif ($user->isInstructor()) {
            $permissions = [
                'view_instructor_dashboard',
                'manage_courses',
                'grade_assignments',
                'view_student_progress',
                'generate_course_reports',
            ];
        } elseif ($user->isStudent()) {
            $permissions = [
                'view_student_dashboard',
                'view_grades',
                'submit_assignments',
                'enroll_courses',
                'view_schedule',
            ];
        } elseif ($user->isLegalRepresentative()) {
            $permissions = [
                'view_representative_dashboard',
                'view_student_records',
                'communicate_with_staff',
                'authorize_actions',
            ];
        }

        return $permissions;
    }
}
