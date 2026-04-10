<?php
// Mock Data for Roles (Since AppSheet tables are pending)
$roles = [
    ['id' => '1', 'name' => 'Admin', 'created_on' => '30 Apr 2025', 'status' => 'Active'],
    ['id' => '2', 'name' => 'Nurse', 'created_on' => '12 Mar 2025', 'status' => 'Active'],
    ['id' => '3', 'name' => 'Receptionist', 'created_on' => '27 Mar 2025', 'status' => 'Active'],
    ['id' => '4', 'name' => 'Lab Technician', 'created_on' => '05 Mar 2025', 'status' => 'Inactive'],
    ['id' => '5', 'name' => 'Pharmacist', 'created_on' => '24 Feb 2025', 'status' => 'Active'],
    ['id' => '6', 'name' => 'Accountant', 'created_on' => '16 Feb 2025', 'status' => 'Active'],
];
?>

<link rel="stylesheet" href="../assets/css/roles.css">

<div class="content">
    <div class="page-header" style="margin-bottom: 0;">
        <h1 class="page-title">Roles</h1>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="openRoleModal()"><i class="fa-solid fa-plus"></i> New Role</button>
        </div>
    </div>

    <div class="patients-container" style="margin-top: 20px;">
        <div class="table-responsive">
            <table class="report-table roles-table">
                <thead>
                    <tr>
                        <th style="text-align: left; padding-left: 20px;">Role</th>
                        <th>Created On</th>
                        <th>Status</th>
                        <th style="text-align: center;"></th>
                        <th style="text-align: right; padding-right: 20px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($roles) > 0): ?>
                        <?php foreach ($roles as $r): ?>
                            <tr>
                                <td style="text-align: left; padding-left: 20px; color: #64748b; font-weight: 500;">
                                    <?php echo htmlspecialchars($r['name']); ?>
                                </td>
                                <td style="color: #64748b;">
                                    <?php echo htmlspecialchars($r['created_on']); ?>
                                </td>
                                <td>
                                    <?php if ($r['status'] == 'Active'): ?>
                                        <span class="status-badge border-green">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge border-red">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <button class="btn-permissions" onclick="window.location.href='index.php?page=permissions&role_id=<?php echo urlencode($r['id']); ?>&role_name=<?php echo urlencode($r['name']); ?>'">
                                        <i class="fa-solid fa-shield-halved"></i> Permissions
                                    </button>
                                </td>
                                <td style="text-align: right; padding-right: 20px;">
                                    <button class="btn-options"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #999; padding: 20px;">No Roles Found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal For Add / Edit Category -->
<div class="modal-overlay" id="roleModal">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 id="roleModalTitle">Add New Role</h3>
            <button class="btn-close" onclick="closeRoleModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <form id="roleForm">
                <input type="hidden" id="roleActionType" value="Add">
                
                <div class="form-group">
                    <label>Role Name <span style="color:red">*</span></label>
                    <input type="text" id="roleNameInput" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Status</label>
                    <select id="roleStatusSelect" class="form-control">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" type="button" onclick="closeRoleModal()">Cancel</button>
            <button class="btn btn-primary" type="button" onclick="submitRole()"><i class="fa-solid fa-save"></i> Save</button>
        </div>
    </div>
</div>

<script>
    function openRoleModal() {
        document.getElementById('roleModal').classList.add('active');
    }
    function closeRoleModal() {
        document.getElementById('roleModal').classList.remove('active');
    }
    function submitRole() {
        Swal.fire({
            icon: 'info',
            title: 'Mock Mode',
            text: 'This is a mock UI. Database synchronization is pending.',
            confirmButtonColor: '#293092'
        });
        closeRoleModal();
    }
</script>
