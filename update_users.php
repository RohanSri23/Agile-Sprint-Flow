<?php
use App\Models\User;
use App\Models\Ticket;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

$password = Hash::make('12345678');
$role = Role::where('name', 'Default role')->first();

// 1. Ensure the 3 users exist
$usersData = [
    ['name' => 'Rohan Srivastava', 'email' => 'rohanddude23@gmail.com'],
    ['name' => 'Manaswi Singh', 'email' => 'manaswi59@example.com'],
    ['name' => 'Somrita Ghosh', 'email' => 'somrita60@example.com']
];

$validUserIds = [];

foreach ($usersData as $data) {
    $user = User::withTrashed()->where('email', $data['email'])->first();
    if ($user) {
        $user->restore(); // just in case it was soft deleted
        $user->update([
            'name' => $data['name'], 
            'password' => $password, 
            'type' => 'db'
        ]);
    } else {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $password,
            'type' => 'db'
        ]);
    }
    
    if ($role && !$user->hasRole('Default role')) {
        $user->assignRole($role);
    }
    
    $validUserIds[] = $user->id;
}

// 2. Re-assign tickets to the valid users
$tickets = Ticket::all();
foreach ($tickets as $ticket) {
    $ticket->owner_id = $validUserIds[array_rand($validUserIds)];
    if ($ticket->responsible_id) {
        $ticket->responsible_id = $validUserIds[array_rand($validUserIds)];
    }
    $ticket->save();
}

// 3. Re-assign projects to the valid users
$projects = Project::all();
foreach ($projects as $project) {
    if (!in_array($project->owner_id, $validUserIds)) {
        $project->owner_id = $validUserIds[0]; 
        $project->save();
    }
    
    // Sync allowing only the 3 users as Admins for the projects
    $syncData = [];
    foreach ($validUserIds as $uid) {
        $syncData[$uid] = ['role' => 'Administrator'];
    }
    $project->users()->sync($syncData);
}

// 4. Delete all other old dummy users
User::whereNotIn('id', $validUserIds)->forceDelete();

echo "Successfully updated users, setup passwords, reassigned dummy resources, and removed the old AI users.\n";
