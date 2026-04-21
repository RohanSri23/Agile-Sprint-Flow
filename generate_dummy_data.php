<?php
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\TicketPriority;
use App\Models\Activity;
use App\Models\Sprint;
use App\Models\Ticket;
use App\Models\TicketHour;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// Disable event listener temporarily for notification if it fails, but we'll try with it on.
// Get the 3 existing users
$rohan = User::where('email', 'rohanddude23@gmail.com')->first();
$manaswi = User::where('email', 'manaswi59@example.com')->first();
$somrita = User::where('email', 'somrita60@example.com')->first();

if (!$rohan || !$manaswi || !$somrita) {
    echo "Could not find all 3 users. Ensure Rohan, Manaswi, and Somrita exist.\n";
    return;
}
$users = collect([$rohan, $manaswi, $somrita]);

// Referential data
$projectStatus = ProjectStatus::firstOrCreate(['name' => 'In Progress', 'color' => '#10b981', 'is_default' => true]);
$bugType = TicketType::firstOrCreate(['name' => 'Bug', 'icon' => 'heroicon-o-bug-ant', 'color' => '#ef4444']);
$featureType = TicketType::firstOrCreate(['name' => 'Feature', 'icon' => 'heroicon-o-document-text', 'color' => '#3b82f6']);
$highPriority = TicketPriority::firstOrCreate(['name' => 'High', 'color' => '#ef4444']);
$lowPriority = TicketPriority::firstOrCreate(['name' => 'Low', 'color' => '#10b981']);
$backlog = TicketStatus::firstOrCreate(['name' => 'Backlog', 'order' => 1]);
$inProgress = TicketStatus::firstOrCreate(['name' => 'In Progress', 'order' => 2]);
$done = TicketStatus::firstOrCreate(['name' => 'Done', 'order' => 3]);
$activities = Activity::all();
if ($activities->isEmpty()) {
    $activities->push(Activity::create(['name' => 'Programming', 'description' => 'Programming related activities']));
    $activities->push(Activity::create(['name' => 'Testing', 'description' => 'Testing related activities']));
    $activities->push(Activity::create(['name' => 'Design', 'description' => 'Design related activities']));
}

echo "Generating E-Commerce Redesign Project data...\n";

// 1. Create Project
$project = Project::create([
    'name' => 'E-Commerce Redesign',
    'owner_id' => $rohan->id, // Rohan as owner
    'status_id' => $projectStatus->id,
    'ticket_prefix' => 'ECO',
    'status_type' => 'default',
    'description' => 'Overhaul of the main e-commerce storefront including UI/UX redesign and payment gateway integration.',
    'type' => 'scrum', // Needs to be 'scrum' or 'kanban'
]);

// Add all 3 as Administrators to project
$syncData = [];
foreach ($users as $u) {
    if ($u->id !== $rohan->id) {
       $syncData[$u->id] = ['role' => 'Administrator'];
    }
}
$project->users()->sync($syncData);

// 2. Create Sprints (which auto-creates Epics)
// Sprint 1 (Completed Week 1)
$sprint1 = Sprint::create([
    'name' => 'Design & Prototyping',
    'project_id' => $project->id,
    'starts_at' => Carbon::now()->subDays(14),
    'ends_at' => Carbon::now()->subDays(7),
    'started_at' => Carbon::now()->subDays(14),
    'ended_at' => Carbon::now()->subDays(7),
    'description' => 'Figma designs for the new storefront.',
]);

// Sprint 2 (Active Week 2)
$sprint2 = Sprint::create([
    'name' => 'Frontend Implementation',
    'project_id' => $project->id,
    'starts_at' => Carbon::now()->subDays(6),
    'ends_at' => Carbon::now()->addDays(5),
    'started_at' => Carbon::now()->subDays(6),
    'description' => 'Translating Figma designs into frontend components.',
]);

// Sprint 3 (Future)
$sprint3 = Sprint::create([
    'name' => 'Backend & API Integration',
    'project_id' => $project->id,
    'starts_at' => Carbon::now()->addDays(6),
    'ends_at' => Carbon::now()->addDays(20),
    'description' => 'Stripe integration and cart logic.',
]);

echo "Created Sprints and Epics...\n";

// 3. Create Tickets
$tasks = [
     ['name' => 'Homepage UI Design', 'sprint' => $sprint1, 'status' => $done, 'type' => $featureType],
     ['name' => 'Product Catalog Mockup', 'sprint' => $sprint1, 'status' => $done, 'type' => $featureType],
     ['name' => 'Cart Slide-out Design', 'sprint' => $sprint1, 'status' => $done, 'type' => $featureType],
     
     ['name' => 'Setup React/Vue Storefront', 'sprint' => $sprint2, 'status' => $done, 'type' => $featureType],
     ['name' => 'Implement Hero Banner slider', 'sprint' => $sprint2, 'status' => $inProgress, 'type' => $featureType],
     ['name' => 'Fix mobile navigation overflow bug', 'sprint' => $sprint2, 'status' => $inProgress, 'type' => $bugType],
     ['name' => 'Product Grid component', 'sprint' => $sprint2, 'status' => $backlog, 'type' => $featureType],
     
     ['name' => 'Stripe Webhook Listener', 'sprint' => $sprint3, 'status' => $backlog, 'type' => $featureType],
     ['name' => 'Database schema update for cart', 'sprint' => $sprint3, 'status' => $backlog, 'type' => $featureType],
     ['name' => 'Checkout Validation Error', 'sprint' => $sprint3, 'status' => $backlog, 'type' => $bugType],
];

foreach ($tasks as $i => $task) {
    $assignedUser = collect([$rohan, $manaswi, $somrita])->random();
    $owner = collect([$rohan, $manaswi, $somrita])->random();
    $ticket = Ticket::create([
        'name' => $task['name'],
        'content' => "Detailed specification for: " . $task['name'] . "\n\nThis task was automatically generated. Please ensure code quality metrics are met.",
        'owner_id' => $owner->id,
        'responsible_id' => $assignedUser->id,
        'project_id' => $project->id,
        'status_id' => $task['status']->id,
        'type_id' => $task['type']->id,
        'priority_id' => collect([$highPriority, $lowPriority])->random()->id,
        'sprint_id' => $task['sprint']->id,
        'estimation' => rand(2, 10), // Hours
    ]);
    
    // Log Hours on tickets that are Not Backlog
    if ($task['status']->id === $done->id || $task['status']->id === $inProgress->id) {
        $numLogs = rand(1, 3);
        for ($j=0; $j<$numLogs; $j++) {
            $hours = rand(1, 3);
            $date = Carbon::now()->subDays(rand(1, 10));
            TicketHour::create([
                'user_id' => collect([$rohan, $manaswi, $somrita])->random()->id,
                'ticket_id' => $ticket->id,
                'value' => $hours,
                'comment' => "Worked on " . strtolower($task['name']),
                'activity_id' => $activities->random()->id,
                'created_at' => $date,
                'updated_at' => $date
            ]);
        }
    }
}

echo "Successfully injected 'E-Commerce Redesign' Project with Sprints, Tickets, and Timesheets!\n";
