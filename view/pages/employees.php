<?php
// Employees Data Fetching
if (isset($appId) && isset($accessKey)) {
    $employees = getAppSheetData('Employee', $appId, $accessKey);
} else {
    $employees = [];
}
?>

<link rel="stylesheet" href="../assets/css/employees.css">

<!-- Employees Content -->
<div class="content">
    <div class="page-header" style="margin-bottom: 0;">
        <h1 class="page-title">ລາຍຊື່ພະນັກງານ (Employees)</h1>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="openEmployeeModal()"><i class="fa-solid fa-plus"></i>
                ເພີ່ມພະນັກງານໃໝ່</button>
        </div>
    </div>

    <div class="Customer-container">
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>ລະຫັດ (ID)</th>
                        <th>ຊື່ພະນັກງານ (Name)</th>
                        <th>ຕຳແໜ່ງ (Position)</th>
                        <th>ພະແນກ (Department)</th>
                        <th>ບົດບາດ (Role)</th>
                        <th>ເບີໂທ (Phone)</th>
                        <th>ຊື່ຜູ້ໃຊ້ (Username)</th>
                        <th style="text-align: center;">ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($employees) && is_array($employees) && count($employees) > 0): ?>
                        <?php foreach ($employees as $e): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($e['Emp_ID'] ?? ''); ?></strong></td>
                                <td><?php echo htmlspecialchars($e['Name'] ?? 'ບໍ່ລະບຸ'); ?></td>
                                <td><?php echo htmlspecialchars($e['Position'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($e['Department'] ?? ''); ?></td>
                                <td>
                                    <span class="badge"
                                        style="background:#0d6efd; color:#fff; padding:3px 8px; border-radius:5px; font-size:12px;">
                                        <?php echo htmlspecialchars($e['Role'] ?? 'User'); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($e['Phone'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($e['Username'] ?? ''); ?></td>
                                <td>
                                    <div class="action-btns" style="justify-content: center;">
                                        <button class="btn-sm btn-edit"
                                            onclick='openEmployeeModal(<?php echo json_encode($e); ?>)'>
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn-sm btn-delete"
                                            onclick="confirmDeleteEmployee('<?php echo htmlspecialchars($e['EmployeeID'] ?? ''); ?>')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #999; padding: 20px;">ຍັງບໍ່ມີຂໍ້ມູນພະນັກງານ
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal For Add / Edit -->
<div class="modal-overlay" id="employeeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="empModalTitle">ເພີ່ມພະນັກງານໃໝ່ (Add Employee)</h3>
            <button class="btn-close" onclick="closeEmployeeModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <form id="employeeForm">
                <input type="hidden" id="empActionType" value="Add">
                <!-- If editing, preserve the original ID -->
                <input type="hidden" id="employeeIdHidden">

                <div class="form-group">
                    <label>ລະຫັດພະນັກງານ (Employee ID) <span style="color:red">*</span></label>
                    <input type="text" id="employeeId" class="form-control" placeholder="E-00000" required>
                    <small style="color: #888;">ສຳລັບການເພີ່ມໃໝ່, ຫາກປະວ່າງລະບົບອາດຈະສ້າງໃຫ້ອັດຕະໂນມັດ.</small>
                </div>

                <div class="form-group">
                    <label>ຊື່ ແລະ ນາມສະກຸນ (Full Name) <span style="color:red">*</span></label>
                    <input type="text" id="employeeName" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>ຕຳແໜ່ງ (Position)</label>
                            <input type="text" id="empPosition" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label>ພະແນກ (Department)</label>
                            <input type="text" id="empDepartment" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>ເບີໂທ (Phone)</label>
                            <input type="text" id="empPhone" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label>ອີເມລ (Email)</label>
                            <input type="email" id="empEmail" class="form-control">
                        </div>
                    </div>
                </div>

                <hr style="margin:20px 0; border:0; border-top:1px solid #eee;">
                <h4 style="margin-bottom:10px; font-size:14px;">ຂໍ້ມູນເຂົ້າສູ່ລະບົບ (Login Credentials)</h4>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>ຊື່ຜູ້ໃຊ້ (Username)</label>
                            <input type="text" id="empUsername" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label>ລະຫັດຜ່ານ (Password)</label>
                            <input type="text" id="empPassword" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>ບົດບາດ (Role)</label>
                    <select id="empRole" class="form-control">
                        <option value="User">User (ເບິ່ງຂໍ້ມູນພື້ນຖານ)</option>
                        <option value="Admin">Admin (ຈັດການລະບົບ)</option>
                    </select>
                </div>

            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" type="button" onclick="closeEmployeeModal()">ຍົກເລີກ</button>
            <button class="btn btn-primary" type="button" onclick="submitEmployee()"><i class="fa-solid fa-save"></i>
                ບັນທຶກສະຖານະ</button>
        </div>
    </div>
</div>
<script src="../assets/js/employees.js"></script>