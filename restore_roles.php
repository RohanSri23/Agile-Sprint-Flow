<?php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

// Ensure Default role exists
$role = Role::firstOrCreate(['name' => 'Default role', 'guard_name' => 'web']);

// Give it ALL permissions that exist in the system
$permissions = Permission::all()->pluck('name')->toArray();
$role->syncPermissions($permissions);

// Force all users to have this role
foreach (User::all() as $user) {
    $user->assignRole('Default role');
    echo "Restored permissions for: " . $user->email . "\n";
}

echo "Fixed! All users are now super-admins again.\n";
