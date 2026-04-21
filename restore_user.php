<?php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

// Find or create the Default role
$role = Role::firstOrCreate(['name' => 'Default role']);
echo "Syncing all permissions to Default role...\n";
$role->syncPermissions(Permission::all());

// Find the user and assign the role
$user = User::where('email', 'rohanddude23@gmail.com')->first();
if ($user) {
    if (!$user->hasRole('Default role')) {
        $user->assignRole($role);
    }
    echo "Successfully restored permissions for " . $user->email . "\n";
} else {
    echo "User rohanddude23@gmail.com not found!\n";
}
