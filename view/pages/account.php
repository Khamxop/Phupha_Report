<?php
// Extracted from session in index.php
$userData = $_SESSION['user']['data'] ?? [];

$id = $userData['Emp_ID'] ?? 'N/A';
$name = $userData['Name'] ?? 'No Name Set';
$role = $userData['Role'] ?? 'User';
$department = $userData['Department'] ?? 'Not Specified';
$position = $userData['Position'] ?? 'Not Specified';
$email = $userData['Email'] ?? 'Not Specified';
$phone = $userData['Phone'] ?? 'Not Specified';
$location = ($userData['Village'] ?? '') . ', ' . ($userData['District'] ?? '') . ', ' . ($userData['Province'] ?? '');
// Clean up location if everything was empty
if (trim($location, ', ') == '') $location = 'Not Specified';
?>

<link rel="stylesheet" href="../assets/css/account.css">

<div class="content account-layout">
    <div class="page-header">
        <!-- <h1 class="page-title">ໂປຣໄຟລ໌ຂອງຂ້ອຍ</h1> -->
    </div>

    <div class="profile-container">
        <!-- Cover Photo & Avatar -->
        <div class="profile-header-card">
            <div class="profile-cover"></div>
            <div class="profile-avatar-wrapper">
                <div class="profile-avatar">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
            </div>
            <div class="profile-main-info">
                <h2 class="profile-name"><?php echo htmlspecialchars($name); ?></h2>
                <div class="profile-badges">
                    <span class="badge role-badge"><i class="fa-solid fa-shield"></i> <?php echo htmlspecialchars($role); ?></span>
                    <span class="badge id-badge"><i class="fa-solid fa-id-card"></i> ID: <?php echo htmlspecialchars($id); ?></span>
                </div>
            </div>
        </div>

        <div class="profile-details-grid">
            <!-- Professional Info -->
            <div class="info-card">
                <h3 class="card-title"><i class="fa-solid fa-briefcase"></i> ຂໍ້ມູນໜ້າທີ່ວຽກງານ</h3>
                <ul class="info-list">
                    <li>
                        <span class="info-label">ຕຳແໜ່ງ (Position):</span>
                        <span class="info-value"><?php echo htmlspecialchars($position); ?></span>
                    </li>
                    <li>
                        <span class="info-label">ພະແນກ (Department):</span>
                        <span class="info-value"><?php echo htmlspecialchars($department); ?></span>
                    </li>
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="info-card">
                <h3 class="card-title"><i class="fa-solid fa-address-book"></i> ຂໍ້ມູນຕິດຕໍ່</h3>
                <ul class="info-list">
                    <li>
                        <span class="info-label">ເບີໂທ (Phone):</span>
                        <span class="info-value"><?php echo htmlspecialchars($phone); ?></span>
                    </li>
                    <li>
                        <span class="info-label">ອີເມລ (Email):</span>
                        <span class="info-value"><?php echo htmlspecialchars($email); ?></span>
                    </li>
                    <li>
                        <span class="info-label">ທີ່ຢູ່ (Location):</span>
                        <span class="info-value"><?php echo htmlspecialchars($location); ?></span>
                    </li>
                </ul>
            </div>
        </div>

    </div>
</div>
