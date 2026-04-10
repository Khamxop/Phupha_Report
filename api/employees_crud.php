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
$tableName = 'Employee';

if ($action === 'Add') {
    if (!isset($data['row']['Emp_ID'])) {
         // Auto generate an ID if not provided.
         $data['row']['Emp_ID'] = 'E-' . date('ymd-His') . rand(10,99);
    }
}

// Ensure Emp_ID exists for Edit/Delete
if (($action === 'Edit' || $action === 'Delete') && empty($data['row']['Emp_ID'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Emp_ID is required for Edit/Delete']);
    exit();
}

// Send request
$result = executeAppSheetAction($tableName, $action, sizeof($data['row']) > 0 ? [$data['row']] : [], $appId, $accessKey);

echo json_encode($result);
?>
