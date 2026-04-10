<?php
include '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit();
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid JSON or missing action']);
    exit();
}

$action = $data['action'];
$allowedActions = ['Add', 'Edit', 'Delete'];

if (!in_array($action, $allowedActions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit();
}

if (!isset($data['row'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing row data']);
    exit();
}

// AppSheet table name
$tableName = 'Patients';

// Set Date format for Date/Time columns to match MM/DD/YYYY if they are included
if ($action === 'Add') {
    if (!isset($data['row']['PatientID'])) {
         // Auto generate an ID if not provided. Or we can let AppSheet do it?
         // Usually we provide one if it's the key. AppSheet expects unique ID.
         $data['row']['PatientID'] = 'P-' . date('ymd-His') . rand(10,99);
    }
}

// Ensure PatientID exists for Edit/Delete
if (($action === 'Edit' || $action === 'Delete') && empty($data['row']['PatientID'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'PatientID is required for Edit/Delete']);
    exit();
}

// Send request
$result = executeAppSheetAction($tableName, $action, sizeof($data['row']) > 0 ? [$data['row']] : [], $appId, $accessKey);

echo json_encode($result);
?>
