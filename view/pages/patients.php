<?php
// Customer Data Fetching
if (isset($appId) && isset($accessKey)) {
    $patients = getAppSheetData('Patients', $appId, $accessKey);
} else {
    $patients = [];
}
?>

<link rel="stylesheet" href="../assets/css/patients.css">

<!-- Customers Content -->
<div class="content">
    <div class="page-header" style="margin-bottom: 0;">
        <h1 class="page-title">ລາຍຊື່ລູກຄ້າ (Customer)</h1>
        <div class="page-actions">
            <button class="btn btn-primary" onclick="openPatientModal()"><i class="fa-solid fa-plus"></i>
                ເພີ່ມລູກຄ້າໃໝ່</button>
        </div>
    </div>

    <div class="patients-container">
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>ລະຫັດ (ID)</th>
                        <th>ຊື່ ແລະ ນາມສະກຸນ</th>
                        <th>ເພດ</th>
                        <th>ອາຍຸ</th>
                        <th>ບ້ານ</th>
                        <th>ອາຊີບ</th>
                        <th>ວັນທີລົງທະບຽນ</th>
                        <th style="text-align: center;">ຈັດການ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($patients) && is_array($patients) && count($patients) > 0): ?>
                        <?php foreach ($patients as $p): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($p['PatientID'] ?? ''); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['FullName'] ?? 'ບໍ່ລະບຸ'); ?></td>
                                <td><?php echo htmlspecialchars($p['Gender'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($p['Age'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($p['Village'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($p['Occupation'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($p['DateTime'] ?? ''); ?></td>
                                <td>
                                    <div class="action-btns" style="justify-content: center;">
                                        <button class="btn-sm btn-edit"
                                            onclick='openPatientModal(<?php echo json_encode($p); ?>)'>
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button class="btn-sm btn-delete"
                                            onclick="confirmDelete('<?php echo htmlspecialchars($p['PatientID'] ?? ''); ?>')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; color: #999; padding: 20px;">ຍັງບໍ່ມີຂໍ້ມູນລູກຄ້າໃດໆ
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal For Add / Edit -->
<div class="modal-overlay" id="patientModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">ເພີ່ມລູກຄ້າໃໝ່ (Add Customer)</h3>
            <button class="btn-close" onclick="closePatientModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <form id="patientForm">
                <input type="hidden" id="actionType" value="Add">
                <!-- If editing, preserve the original ID -->
                <input type="hidden" id="patientIdHidden">

                <div class="form-group">
                    <label>ລະຫັດລູກຄ້າ (Customer ID) <span style="color:red">*</span></label>
                    <input type="text" id="patientId" class="form-control" placeholder="P-00000" required>
                    <small style="color: #888;">ສຳລັບການເພີ່ມໃໝ່, ຫາກປະວ່າງລະບົບອາດຈະສ້າງໃຫ້ອັດຕະໂນມັດ.</small>
                </div>

                <div class="form-group">
                    <label>ຊື່ ແລະ ນາມສະກຸນ (Full Name) <span style="color:red">*</span></label>
                    <input type="text" id="fullName" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>ເພດ (Gender)</label>
                            <select id="gender" class="form-control">
                                <option value="">- ເລືອກ -</option>
                                <option value="ຊາຍ">ຊາຍ (Male)</option>
                                <option value="ຍິງ">ຍິງ (Female)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label>ອາຍຸ (Age)</label>
                            <input type="number" id="age" class="form-control" min="0">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>ອາຊີບ (Occupation)</label>
                            <input type="text" id="occupation" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label>ໝວດເລືອດ (Blood Group)</label>
                            <input type="text" id="bloodGroup" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label>ບ້ານ (Village)</label>
                            <input type="text" id="village" class="form-control">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label>ເມືອງ (District)</label>
                            <input type="text" id="district" class="form-control">
                        </div>
                    </div>
                </div>

            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-cancel" type="button" onclick="closePatientModal()">ຍົກເລີກ</button>
            <button class="btn btn-primary" type="button" onclick="submitPatient()"><i class="fa-solid fa-save"></i>
                ບັນທຶກສະຖານະ</button>
        </div>
    </div>
</div>
<script src="../assets/js/patients.js"></script>