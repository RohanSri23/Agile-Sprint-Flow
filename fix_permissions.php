<?php
use Illuminate\Support\Facades\Artisan;
use App\Models\Role;
use App\Models\User;

Artisan::call('db:seed', ['--class' => 'PermissionsSeeder']);
echo "Permissions seeded.\n";

$role = Role::where('name', 'Default role')->first();
if ($role) {
    foreach(User::all() as $u) {
        $u->assignRole($role);
        echo "Assigned Default role to " . $u->email . "\n";
    }
}
