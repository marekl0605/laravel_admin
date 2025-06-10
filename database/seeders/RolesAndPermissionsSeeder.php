<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'manage-users',
            'create-users',
            'edit-users',
            'delete-users',
            'view-users',
            'assign-roles',
            'impersonate-users',

            // Role Management
            'manage-roles',
            'assign-permissions',

            // System Management
            'manage-system-settings',
            'access-all-modules',
            'view-security-logs',
            'manage-system-configuration',

            // Academic Records
            'view-all-academic-records',
            'edit-all-academic-records',
            'view-own-academic-records',
            'edit-own-academic-records',

            // Student Profile Management
            'view-student-profiles',
            'edit-student-profiles',
            'view-own-profile',
            'edit-own-profile',
            'update-contact-info',

            // Course Management
            'manage-course-catalog',
            'create-courses',
            'edit-courses',
            'delete-courses',
            'view-courses',
            'enroll-courses',
            'withdraw-courses',
            'process-enrollments',

            // Course Content
            'create-course-content',
            'manage-course-content',
            'view-course-content',
            'access-learning-materials',

            // Assignments and Exams
            'create-assignments',
            'edit-assignments',
            'delete-assignments',
            'submit-assignments',
            'grade-assignments',
            'view-assignments',

            // Grades and Feedback
            'view-all-grades',
            'edit-grades',
            'view-own-grades',
            'provide-feedback',
            'export-grades',

            // Attendance
            'record-attendance',
            'view-attendance',
            'maintain-attendance-records',

            // Communication
            'communicate-with-students',
            'communicate-with-instructors',
            'communicate-with-staff',
            'communicate-with-parents',

            // Scheduling
            'manage-schedules',
            'schedule-classes',
            'assign-rooms',
            'schedule-office-hours',
            'view-schedules',

            // Reports
            'generate-all-reports',
            'export-all-reports',
            'generate-academic-reports',
            'generate-administrative-reports',
            'generate-course-reports',

            // Documents
            'manage-institutional-documents',
            'request-documents',
            'download-official-documents',
            'generate-transcripts',
            'generate-certificates',

            // Legal Representative Specific
            'authorize-student-actions',
            'access-represented-student-records',
            'view-represented-student-progress',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $this->createAdministratorRole();
        $this->createStudentRole();
        $this->createSecretaryRole();
        $this->createInstructorRole();
        $this->createLegalRepresentativeRole();
    }

    private function createAdministratorRole()
    {
        $role = Role::create(['name' => 'administrator']);
        
        $permissions = [
            'manage-users',
            'create-users',
            'edit-users',
            'delete-users',
            'view-users',
            'assign-roles',
            'impersonate-users',
            'manage-roles',
            'assign-permissions',
            'manage-system-settings',
            'access-all-modules',
            'view-security-logs',
            'manage-system-configuration',
            'view-all-academic-records',
            'edit-all-academic-records',
            'manage-course-catalog',
            'create-courses',
            'edit-courses',
            'delete-courses',
            'view-courses',
            'manage-schedules',
            'schedule-classes',
            'assign-rooms',
            'generate-all-reports',
            'export-all-reports',
            'view-all-grades',
            'edit-grades',
            'export-grades',
        ];

        $role->givePermissionTo($permissions);
    }

    private function createStudentRole()
    {
        $role = Role::create(['name' => 'student']);
        
        $permissions = [
            'view-own-academic-records',
            'view-own-profile',
            'edit-own-profile',
            'update-contact-info',
            'enroll-courses',
            'view-courses',
            'access-learning-materials',
            'submit-assignments',
            'view-assignments',
            'view-own-grades',
            'communicate-with-instructors',
            'communicate-with-staff',
            'view-schedules',
            'request-documents',
            'view-course-content',
        ];

        $role->givePermissionTo($permissions);
    }

    private function createSecretaryRole()
    {
        $role = Role::create(['name' => 'secretary']);
        
        $permissions = [
            'view-student-profiles',
            'edit-student-profiles',
            'process-enrollments',
            'enroll-courses',
            'withdraw-courses',
            'schedule-classes',
            'assign-rooms',
            'generate-academic-reports',
            'generate-administrative-reports',
            'manage-institutional-documents',
            'communicate-with-students',
            'communicate-with-parents',
            'maintain-attendance-records',
            'view-attendance',
            'view-courses',
            'create-courses',
            'edit-courses',
        ];

        $role->givePermissionTo($permissions);
    }

    private function createInstructorRole()
    {
        $role = Role::create(['name' => 'instructor']);
        
        $permissions = [
            'create-course-content',
            'manage-course-content',
            'view-course-content',
            'create-assignments',
            'edit-assignments',
            'delete-assignments',
            'view-assignments',
            'grade-assignments',
            'provide-feedback',
            'record-attendance',
            'view-attendance',
            'communicate-with-students',
            'view-student-profiles',
            'view-own-grades',
            'edit-grades',
            'export-grades',
            'schedule-office-hours',
            'generate-course-reports',
            'view-schedules',
        ];

        $role->givePermissionTo($permissions);
    }

    private function createLegalRepresentativeRole()
    {
        $role = Role::create(['name' => 'legal_representative']);
        
        $permissions = [
            'access-represented-student-records',
            'view-represented-student-progress',
            'view-schedules',
            'view-attendance',
            'communicate-with-instructors',
            'communicate-with-staff',
            'authorize-student-actions',
            'download-official-documents',
            'update-contact-info',
        ];

        $role->givePermissionTo($permissions);
    }
}