<?php
$project = App\Models\Project::first();
if ($project) {
    echo "Attaching users to project: " . $project->name . "\n";
    $users = App\Models\User::all();
    foreach ($users as $u) {
        $project->users()->syncWithoutDetaching([$u->id => ['role' => 'Administrator']]);
        echo "Attached user: " . $u->email . "\n";
    }
} else {
    echo "No projects found.\n";
}
