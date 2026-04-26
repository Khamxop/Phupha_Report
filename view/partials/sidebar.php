<?php
// Ensure this file has access to the session variable, which is started in index.php
$sidebarRole = isset($_SESSION['user']['Role']) ? $_SESSION['user']['Role'] : 'User';
$currentPage = $_GET['page'] ?? 'admindashboard';
?>


<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <img src="../img/Phupha.png" alt="logo" width="60px" height="60px"
                style="border-radius: 50%; border: 1px solid #fff;">
            <span>ໂຮງງານ ນ້ຳດື່ມພູຜາ</span>
        </div>
        <button class="close-sidebar-btn" id="closeSidebarBtn">
            <i class="fa-solid fa-xmark"></i>
        </button>

    </div>

    <div class="sidebar-menu">
        <div class="menu-title">Main Menu</div>
        <ul>
            <li
                class="<?php echo in_array($currentPage, ['admindashboard', 'Customize_Report', 'patient-dashboard']) ? 'active' : ''; ?>">
                <a href="#"><i class="fa-solid fa-border-all"></i> Dashboard <i
                        class="fa-solid fa-chevron-right arrow"></i></a>
                <ul class="submenu">
                    <?php if (strtolower($sidebarRole) == 'admin' || strtolower($sidebarRole) == 'owner'): ?>
                        <li class="<?php echo $currentPage === 'admindashboard' ? 'active' : ''; ?>"><a
                        href="index.php?page=admindashboard">ລາຍງານລາຍຮັບຂາຍນ້ຳດື່ມ</a></li>
                    <?php endif; ?>
                    <li class="<?php echo $currentPage === 'Customize_Report' ? 'active' : ''; ?>"><a
                            href="index.php?page=Customize_Report">ລາຍງານລາຍຮັບ Customize</a></li>
                </ul>
            </li>
            <li><a href="index.php?page=Warehouse_Report"><i class="fa-solid fa-cubes"></i> ລາຍງານສາງ <i
                        class="fa-solid fa-chevron-right arrow"></i></a></li>
        </ul>

        <!-- <div class="menu-title">Clinic</div>
        <ul>
            <li class="<?php echo $currentPage === 'employees' ? 'active' : ''; ?>"><a
                    href="index.php?page=employees"><i class="fa-solid fa-user-doctor"></i> Employees</a>
            </li>
            <li class="<?php echo $currentPage === 'Customer' ? 'active' : ''; ?>"><a href="index.php?page=Customer"><i
                class="fa-solid fa-bed-pulse"></i> Customer</a></li>
        </ul> -->

        <div class="menu-title">Account Settings</div>
        <ul>
            <li class="<?php echo $currentPage === 'account' ? 'active' : ''; ?>"><a href="index.php?page=account"><i class="fa-solid fa-user"></i> My Profile</a></li>
        
            <li><a href="../api/logout.php" style="color: #ff6b6b;"><i class="fa-solid fa-arrow-right-from-bracket"></i>
                    Logout</a></li>
        </ul>
    </div>
</aside>