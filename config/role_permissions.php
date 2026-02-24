<?php

return [
    'abilities' => [
        'access_users' => ['super_admin', 'admin'],
        'access_settings' => ['super_admin'],
        'manage_facility_master' => ['super_admin', 'admin'],
        'manage_energy_profile' => ['super_admin', 'admin', 'energy_officer'],
        'delete_energy_profile' => ['super_admin', 'admin'],
        'maintenance_actions' => ['super_admin', 'admin', 'energy_officer'],
        'maintenance_complete' => ['super_admin', 'admin'],
        'delete_maintenance_history' => ['super_admin', 'admin'],
        'access_reports' => ['super_admin', 'admin', 'energy_officer', 'staff'],
    ],
];
