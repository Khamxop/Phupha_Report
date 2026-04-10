<?php
// Main Front Controller
include 'partials/header.php';

// Determine which page to load
$page = $_GET['page'] ?? 'admindashboard';

// Validation: Only allow specific pages to be loaded securely
$validPages = [
    'admindashboard', 'patients', 'doctor-dashboard', 'patient-dashboard', 
    'employees', 'account', 'roles', 'permissions', 'delete_requests'
];

if (!in_array($page, $validPages)) {
    $page = 'admindashboard'; // Fallback to default
}
?>

<div class="app-container">
    <!-- Sidebar -->
    <?php include 'partials/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <main class="main-content" id="mainContent">
        <!-- Header / Topbar -->
        <?php include 'partials/topbar.php'; ?>

        <!-- ======================= -->
        <!--  Dynamic Page Content   -->
        <!-- ======================= -->
        <?php
        // Load the requested page content dynamically
        $pagePath = "pages/{$page}.php";
        if (file_exists($pagePath)) {
            include $pagePath;
        } else {
            echo "<div class='content'><h2>404 - Page Not Found</h2><p>ລະບົບບໍ່ພົບໜ້າທີ່ທ່ານຕ້ອງການ.</p></div>";
        }
        ?>

        <!-- <?php include '../partials/footer-bottom.php'; ?> -->
    </main>
</div>

<!-- Global Scripts -->
<script src="../assets/js/script.js"></script>
</body>

</html>