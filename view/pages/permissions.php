<?php
$roleName = $_GET['role_name'] ?? 'Admin';

$clinicModules = [
    'Doctors', 'Patients', 'Appointments', 'Locations', 'Visits', 
    'Services', 'Designations', 'Departments', 'Activities'
];
$hrmModules = ['Staffs'];

// Helper to check random values for mock display
function isMockChecked($mod, $act) {
    if ($mod === 'Patients' && $act === 'EDIT') return true;
    if ($mod === 'Appointments' && $act === 'DELETE') return true;
    if ($mod === 'Locations' && $act === 'VIEW') return true;
    if ($mod === 'Services' && $act === 'EDIT') return true;
    if ($mod === 'Designations' && $act === 'DELETE') return true;
    if ($mod === 'Departments' && $act === 'VIEW') return true;
    if (in_array($mod, ['Doctors', 'Visits', 'Activities', 'Staffs']) && $act === 'CREATE') return true;
    return false;
}
?>

<link rel="stylesheet" href="../assets/css/permissions.css">

<div class="content permissions-layout">
    <div class="page-header" style="justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div class="header-left" style="display: flex; align-items: center; gap: 10px;">
            <button class="btn-back" onclick="window.location.href='index.php?page=roles'"><i class="fa-solid fa-chevron-left"></i></button>
            <h1 class="page-title" style="margin:0;">Permissions</h1>
        </div>
        <div class="header-right">
            <span class="role-indicator">Role : <strong><?php echo htmlspecialchars($roleName); ?></strong></span>
        </div>
    </div>

    <!-- Clinic Category -->
    <div class="permissions-card">
        <div class="card-header">
            <h3>Clinic</h3>
            <label class="allow-all-wrapper"><input type="checkbox"> Allow All</label>
        </div>
        <div class="table-responsive">
            <table class="report-table perm-table">
                <thead>
                    <tr>
                        <th style="text-align: left; padding-left: 20px;">Module</th>
                        <th style="text-align: center;">CREATE</th>
                        <th style="text-align: center;">EDIT</th>
                        <th style="text-align: center;">DELETE</th>
                        <th style="text-align: center;">VIEW</th>
                        <th style="text-align: center;">ALLOW ALL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clinicModules as $module): ?>
                        <tr>
                            <td style="text-align: left; padding-left: 20px; font-weight: 500; color: #1e293b;">
                                <?php echo $module; ?>
                            </td>
                            <td class="checkbox-cell"><input type="checkbox" <?php echo isMockChecked($module, 'CREATE') ? 'checked' : ''; ?>></td>
                            <td class="checkbox-cell"><input type="checkbox" <?php echo isMockChecked($module, 'EDIT') ? 'checked' : ''; ?>></td>
                            <td class="checkbox-cell"><input type="checkbox" <?php echo isMockChecked($module, 'DELETE') ? 'checked' : ''; ?>></td>
                            <td class="checkbox-cell"><input type="checkbox" <?php echo isMockChecked($module, 'VIEW') ? 'checked' : ''; ?>></td>
                            <td class="checkbox-cell"><input type="checkbox" class="allow-row"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- HRM Category -->
    <div class="permissions-card">
        <div class="card-header">
            <h3>Hrm</h3>
            <label class="allow-all-wrapper"><input type="checkbox"> Allow All</label>
        </div>
        <div class="table-responsive">
            <table class="report-table perm-table">
                <thead>
                    <tr>
                        <th style="text-align: left; padding-left: 20px;">Module</th>
                        <th style="text-align: center;">CREATE</th>
                        <th style="text-align: center;">EDIT</th>
                        <th style="text-align: center;">DELETE</th>
                        <th style="text-align: center;">VIEW</th>
                        <th style="text-align: center;">ALLOW ALL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hrmModules as $module): ?>
                        <tr>
                            <td style="text-align: left; padding-left: 20px; font-weight: 500; color: #1e293b;">
                                <?php echo $module; ?>
                            </td>
                            <td class="checkbox-cell"><input type="checkbox" <?php echo isMockChecked($module, 'CREATE') ? 'checked' : ''; ?>></td>
                            <td class="checkbox-cell"><input type="checkbox"></td>
                            <td class="checkbox-cell"><input type="checkbox"></td>
                            <td class="checkbox-cell"><input type="checkbox"></td>
                            <td class="checkbox-cell"><input type="checkbox" class="allow-row"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Floating Action Save -->
    <button class="floating-save-btn" onclick="savePermissions()">
        <i class="fa-solid fa-gear"></i>
    </button>
</div>

<script>
    function savePermissions() {
        Swal.fire({
            icon: 'info',
            title: 'Mock Mode',
            text: 'Permissions mapping UI generated. Pending AppSheet backend tables.',
            confirmButtonColor: '#293092'
        });
    }
</script>
