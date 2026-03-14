<?php

return [
    'abilities' => [
        'access_users' => ['super_admin', 'admin'],
        'access_audit_logs' => ['super_admin', 'admin'],
        'access_settings' => ['super_admin'],
        'manage_facility_master' => ['super_admin', 'admin'],
        'approve_facility_meters' => ['super_admin', 'admin', 'engineer'],
        'manage_energy_profile' => ['super_admin', 'admin', 'energy_officer'],
        'delete_energy_profile' => ['super_admin', 'admin'],
        'approve_energy_profile' => ['super_admin', 'admin', 'engineer'],
        'maintenance_actions' => ['super_admin', 'admin', 'energy_officer'],
        'maintenance_complete' => ['super_admin', 'admin'],
        'delete_maintenance_history' => ['super_admin', 'admin'],
        'access_reports' => ['super_admin', 'admin', 'energy_officer', 'staff'],
        'encode_submeter_readings' => ['super_admin', 'admin', 'energy_officer', 'staff'],
        'approve_submeter_readings' => ['super_admin', 'admin', 'energy_officer', 'engineer'],
        'view_submeter_alerts' => ['super_admin', 'admin', 'energy_officer', 'staff', 'engineer'],
        'encode_main_meter_readings' => ['super_admin', 'admin', 'energy_officer', 'staff'],
        'approve_main_meter_readings' => ['super_admin', 'admin', 'energy_officer', 'engineer'],
        'view_main_meter_alerts' => ['super_admin', 'admin', 'energy_officer', 'staff', 'engineer'],
        'view_load_tracking' => ['super_admin', 'admin', 'energy_officer', 'staff', 'engineer'],
        'manage_load_tracking' => ['super_admin', 'admin', 'energy_officer', 'staff'],
    ],
];
