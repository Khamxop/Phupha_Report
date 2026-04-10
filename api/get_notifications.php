<?php
// dashboards/api/get_notifications.php
header('Content-Type: application/json');

$file = __DIR__ . '/../data/notifications.json';
if (!file_exists($file)) {
    echo json_encode([]);
    exit;
}

$notifications = json_decode(file_get_contents($file), true) ?: [];

// If 'mark_read' is passed, set all to read
if (isset($_GET['mark_read'])) {
    foreach ($notifications as &$n) {
        $n['read'] = true;
    }
    file_put_contents($file, json_encode($notifications));
}

echo json_encode($notifications);
