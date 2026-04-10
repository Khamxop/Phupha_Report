<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Verify it's not empty
    if (empty($username) || empty($password)) {
        header("Location: ../view/login.php?error=empty");
        exit();
    }

    include '../config/config.php';

    // Explicitly fetch Employees data because config no longer fetches globally
    if (isset($appId) && isset($accessKey)) {
        $employees = getAppSheetData('Employee', $appId, $accessKey);
    } else {
        $employees = [];
    }

    $authenticated = false;
    $loggedInUser = null;

    // Iterate over employees array looking for a match
    if (isset($employees) && is_array($employees)) {
        foreach ($employees as $employee) {
            // Adjust the keys exactly as they appear in AppSheet. 
            // Often they are either capitalized "Username", "Password" or exactly the column name.
            $empUsername = $employee['Username'] ?? $employee['Username'] ?? '';
            $empPassword = $employee['Password'] ?? $employee['Password'] ?? '';

            if ($empUsername === $username && $empPassword === $password) {
                $authenticated = true;
                $loggedInUser = $employee;
                break;
            }
        }
    }

    if ($authenticated && $loggedInUser) {
        // Find the Role
        $role = $loggedInUser['Role'] ?? $loggedInUser['role'] ?? 'User';

        $_SESSION['user'] = [
            'username' => $username,
            'Role' => $role,
            'data' => $loggedInUser
        ];

        header("Location: ../view/index.php");
        exit();
    } else {
        // Authentication failed
        header("Location: ../view/login.php?error=invalid");
        exit();
    }
} else {
    header("Location: ../view/login.php");
    exit();
}
