<?php
// dashboards/api/webhook.php
header('Content-Type: application/json');

// Get the raw POST data from AppSheet
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

// Prepare the notification message
$table = $data['table'] ?? 'Unknown Table';
$user = $data['user'] ?? 'Someone';
$time = date('H:i');
$message = "[$time] $user has added a new record to $table";

// Load existing notifications
$file = __DIR__ . '/../data/notifications.json';
$notifications = [];
if (file_exists($file)) {
    $notifications = json_decode(file_get_contents($file), true) ?: [];
}

// Add new notification to the beginning
array_unshift($notifications, [
    'id' => uniqid(),
    'message' => $message,
    'timestamp' => time(),
    'read' => false
]);

// Keep only the last 20 notifications
$notifications = array_slice($notifications, 0, 20);

// Save back to file
file_put_contents($file, json_encode($notifications));

echo json_encode(['status' => 'success', 'message' => 'Notification recorded']);
