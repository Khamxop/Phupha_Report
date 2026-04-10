<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
include '../config/config.php';
$loggedInUser = $_SESSION['user'];

// Handle Topbar Date Filters globally
$reportFilterStart = $_GET['start'] ?? date('Y-m-d', strtotime('-30 days'));
$reportFilterEnd = $_GET['end'] ?? date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Phetsarath:wght@400;700&display=swap"
        rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div id="loading-overlay" class="hidden">
        <div class="loader-spinner"></div>
        <div class="loader-text">ກຳລັງໂຫລດຂໍ້ມູນ...</div>
    </div>