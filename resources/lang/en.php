<?php

declare(strict_types=1);

/**
 * English Language File
 *
 * All user-facing strings. Referenced via __('key') helper.
 * No hardcoded text in views or controllers.
 */

return [
    // ── General ────────────────────────────────────────────────────────────────
    'app_name'            => 'GCIRMS',
    'app_tagline'         => 'Government Civic Incident Reporting & Management System',
    'welcome'             => 'Welcome',
    'loading'             => 'Loading...',
    'save'                => 'Save',
    'cancel'              => 'Cancel',
    'delete'              => 'Delete',
    'edit'                => 'Edit',
    'view'                => 'View',
    'submit'              => 'Submit',
    'search'              => 'Search',
    'filter'              => 'Filter',
    'export'              => 'Export',
    'print'               => 'Print',
    'back'                => 'Back',
    'next'                => 'Next',
    'previous'            => 'Previous',
    'confirm'             => 'Confirm',
    'yes'                 => 'Yes',
    'no'                  => 'No',
    'status'              => 'Status',
    'actions'             => 'Actions',
    'created_at'          => 'Created',
    'updated_at'          => 'Updated',

    // ── Auth ───────────────────────────────────────────────────────────────────
    'auth.login'              => 'Login',
    'auth.logout'             => 'Logout',
    'auth.register'           => 'Register',
    'auth.email'              => 'Email Address',
    'auth.password'           => 'Password',
    'auth.confirm_password'   => 'Confirm Password',
    'auth.forgot_password'    => 'Forgot Password?',
    'auth.reset_password'     => 'Reset Password',
    'auth.remember_me'        => 'Remember Me',
    'auth.login_required'     => 'Please log in to continue.',
    'auth.session_expired'    => 'Your session has expired. Please log in again.',
    'auth.login_failed'       => 'Invalid email or password.',
    'auth.account_locked'     => 'Your account has been locked due to too many failed attempts.',
    'auth.account_inactive'   => 'Your account is not active. Contact the administrator.',
    'auth.logout_success'     => 'You have been logged out successfully.',
    'auth.password_reset_sent'=> 'Password reset link has been sent to your email.',
    'auth.password_changed'   => 'Your password has been changed successfully.',

    // ── Incidents ──────────────────────────────────────────────────────────────
    'incident.report'         => 'Report an Incident',
    'incident.title'          => 'Incident Title',
    'incident.description'    => 'Description',
    'incident.category'       => 'Category',
    'incident.priority'       => 'Priority',
    'incident.status'         => 'Status',
    'incident.location'       => 'Location',
    'incident.submitted'      => 'Incident submitted successfully. Your reference number is :number.',
    'incident.not_found'      => 'Incident not found.',
    'incident.draft_saved'    => 'Draft saved.',

    // ── Priorities ─────────────────────────────────────────────────────────────
    'priority.low'            => 'Low',
    'priority.medium'         => 'Medium',
    'priority.high'           => 'High',
    'priority.critical'       => 'Critical',
    'priority.emergency'      => 'Emergency',

    // ── Statuses ───────────────────────────────────────────────────────────────
    'status.draft'            => 'Draft',
    'status.submitted'        => 'Submitted',
    'status.received'         => 'Received',
    'status.pending_verification' => 'Pending Verification',
    'status.verified'         => 'Verified',
    'status.rejected'         => 'Rejected',
    'status.assigned'         => 'Assigned',
    'status.in_progress'      => 'In Progress',
    'status.resolved'         => 'Resolved',
    'status.closed'           => 'Closed',
    'status.archived'         => 'Archived',

    // ── Navigation ─────────────────────────────────────────────────────────────
    'nav.dashboard'           => 'Dashboard',
    'nav.incidents'           => 'Incidents',
    'nav.my_reports'          => 'My Reports',
    'nav.notifications'       => 'Notifications',
    'nav.reports'             => 'Reports',
    'nav.analytics'           => 'Analytics',
    'nav.users'               => 'Users',
    'nav.settings'            => 'Settings',
    'nav.audit_logs'          => 'Audit Logs',
    'nav.profile'             => 'My Profile',
    'nav.logout'              => 'Logout',

    // ── Validation ─────────────────────────────────────────────────────────────
    'validation.required'     => 'The :field field is required.',
    'validation.email'        => 'Please enter a valid email address.',
    'validation.min_length'   => 'The :field must be at least :min characters.',
    'validation.max_length'   => 'The :field must not exceed :max characters.',
    'validation.unique'       => 'This :field is already in use.',
    'validation.password'     => 'Password must be at least 8 characters with uppercase, lowercase, number, and special character.',

    // ── Flash Messages ─────────────────────────────────────────────────────────
    'flash.success'           => 'Success',
    'flash.error'             => 'Error',
    'flash.warning'           => 'Warning',
    'flash.info'              => 'Information',

    // ── Errors ─────────────────────────────────────────────────────────────────
    'error.404'               => 'Page Not Found',
    'error.403'               => 'Access Denied',
    'error.500'               => 'Internal Server Error',
    'error.404_message'       => 'The page you requested could not be found.',
    'error.403_message'       => 'You do not have permission to access this resource.',
    'error.500_message'       => 'An unexpected error occurred. Please try again later.',
];
