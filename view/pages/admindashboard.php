<?php
// Dashboard Data Fetching (Specific to admin dashboard)
if (isset($appId) && isset($accessKey)) {
    $dashboardRequests = [
        'sell' => ['tableName' => 'Sell'],
        'payment' => ['tableName' => 'Payment'],
        'incomeexpenses' => ['tableName' => 'IncomeExpenses'],
        'laborderdetails' => ['tableName' => 'LabOrderDetails'],
        'prescriptions' => ['tableName' => 'Prescriptions']
        // We exclude employees, mainstocks, patients, etc!
    ];
    // This function will block until these tables are downloaded
    $multiData = getAppSheetDataMulti($dashboardRequests, $appId, $accessKey);
    $sell = $multiData['sell'] ?? [];
    $payment = $multiData['payment'] ?? [];
    $incomeexpenses = $multiData['incomeexpenses'] ?? [];
    $laborderdetails = $multiData['laborderdetails'] ?? [];
    $prescriptions = $multiData['prescriptions'] ?? [];
}

// Ensure the variables exist so api.php works even if fetch fails
$sell = $sell ?? [];
$payment = $payment ?? [];
$incomeexpenses = $incomeexpenses ?? [];
$laborderdetails = $laborderdetails ?? [];
$prescriptions = $prescriptions ?? [];

// Then process the financial logic
include '../src/routes/api.php';
?>
<link rel="stylesheet" href="../assets/css/admindashboard.css">
<!-- Dashboard Content -->
<div class="dashboard-content">
    <div class="page-header">
        <h2>ລາຍງານລາຍຮັບສິນຄ້ານ້ຳດື່ມ</h2>
    </div>
    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon icon-blue"><i class="fa-brands fa-amazon-pay"></i></div>
            <div class="kpi-info">
                <p>ປະກັນ/Claims</p>
                <h3><?php echo formatCurrency($claimsTotal, 'LAK'); ?></h3>
            </div>
            <?php echo getTrendHtml($last7Claims, $prev7Claims, 'chart-blue'); ?>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon icon-red"><i class="fa-solid fa-kip-sign"></i></div>
            <div class="kpi-info">
                <p>ກີບ/LAK</p>
                <h3><?php echo formatCurrency($totalLAK, 'LAK'); ?></h3>
            </div>
            <?php echo getTrendHtml($last7LAK, $prev7LAK, 'chart-red'); ?>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon icon-blue-light"><i class="fa-solid fa-baht-sign"></i></div>
            <div class="kpi-info">
                <p>ບາດ/THB</p>
                <h3><?php echo formatCurrency($totalTHB, 'THB'); ?></h3>
            </div>
            <?php echo getTrendHtml($last7THB, $prev7THB, 'chart-blue-light'); ?>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon icon-green"><i class="fa-solid fa-dollar-sign"></i></div>
            <div class="kpi-info">
                <p>ໂດລາ/USD</p>
                <h3><?php echo formatCurrency($totalUSD, 'USD'); ?></h3>
            </div>
            <?php echo getTrendHtml($last7USD, $prev7USD, 'chart-green'); ?>
        </div>
    </div>
    <hr style="margin: 20px 0;">


    <!-- Income & Expenses -->
    <div class="page-header">
        <h2>ລາຍງານລວມ (Income & Expenses)</h2>
    </div>
    <div class="report-container">
        <div class="report-sidebar">
            <div class="report-card" style="padding: 15px; overflow-y: auto; flex: 1;">
                <h4 class="report-title text-warning" style="margin-top: 5px; margin-bottom: 15px;">
                    ລາຍຮັບລວມ (Summary)</h4>
                <div class="summary-table-container">
                    <?php
                    $summaries = [
                        ['label' => 'ລາຍຮັບລວມ', 'data' => $rptIncomeByCur, 'class' => 'text-success'],
                        ['label' => 'ເງິນສົດ', 'data' => $rptCashByCur, 'class' => ''],
                        ['label' => 'ເງິນໂອນ', 'data' => $rptTransferByCur, 'class' => ''],
                        ['label' => 'ສ່ວນຫຼຸດ', 'data' => $rptDiscountByCur, 'class' => 'text-danger'],
                    ];
                    ?>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>ລາຍການ</th>
                                <th>₭ (LAK)</th>
                                <th>฿ (THB)</th>
                                <th>$ (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($summaries as $s): ?>
                                <tr>
                                    <td><?php echo $s['label']; ?></td>
                                    <td class="<?php echo $s['class']; ?>">
                                        <?php echo number_format($s['data']['LAK'] ?? 0); ?>
                                    </td>
                                    <td class="<?php echo $s['class']; ?>">
                                        <?php echo number_format($s['data']['THB'] ?? 0); ?>
                                    </td>
                                    <td class="<?php echo $s['class']; ?>">
                                        <?php echo number_format($s['data']['USD'] ?? 0); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="profit-row">
                                <td>ກຳໄລ (Profit)</td>
                                <td><?php echo number_format($rptProfitByCur['LAK'] ?? 0); ?></td>
                                <td><?php echo number_format($rptProfitByCur['THB'] ?? 0); ?></td>
                                <td><?php echo number_format($rptProfitByCur['USD'] ?? 0); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h4 class="report-title text-warning" style="margin-top: 30px; margin-bottom: 15px;">
                    ລາຍລະອຽດແຍກຕາມໝວດໝູ່</h4>
                <div class="summary-table-container">
                    <?php
                    $details = [
                        ['label' => 'ລາຍຮັບຈາກການກວດ', 'data' => $rptIncomeCheckupByCur, 'class' => 'text-success'],
                        ['label' => 'ລາຍຮັບຈາກການຂາຍຢາ', 'data' => $rptIncomeMedsByCur, 'class' => 'text-success'],
                        ['label' => 'ຊື້ຢາເຂົ້າສາງ', 'data' => $rptExpenseBuyMedsByCur, 'class' => 'text-danger'],
                        ['label' => 'ເງິນເດືອນພະນັກງານ', 'data' => $rptExpenseSalaryByCur, 'class' => 'text-danger'],
                        ['label' => 'ລາຍຈ່າຍທົ່ວໄປ', 'data' => $rptExpenseGeneralByCur, 'class' => 'text-danger'],
                    ];
                    ?>
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>ໝວດໝູ່</th>
                                <th>₭ (LAK)</th>
                                <th>฿ (THB)</th>
                                <th>$ (USD)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details as $d): ?>
                                <tr>
                                    <td><?php echo $d['label']; ?></td>
                                    <td class="<?php echo $d['class']; ?>">
                                        <?php echo number_format($d['data']['LAK'] ?? 0); ?>
                                    </td>
                                    <td class="<?php echo $d['class']; ?>">
                                        <?php echo number_format($d['data']['THB'] ?? 0); ?>
                                    </td>
                                    <td class="<?php echo $d['class']; ?>">
                                        <?php echo number_format($d['data']['USD'] ?? 0); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Main Content (Table) -->
        <div class="report-main">
            <div
                style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 15px; flex-shrink: 0;">
                <h4 style="margin: 0; font-size: 16px; color: #333;">ຜົນລວມການຊຳລະ</h4>
            </div>

            <div style="flex: 1; overflow-y: auto; overflow-x: auto; padding-right: 5px;" class="custom-scroll">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>ວັນທີ</th>
                            <th>ໝວດໝູ່</th>
                            <th>ສະກຸນເງິນ</th>
                            <th>ວິທີຊຳລະ</th>
                            <th>ຈຳນວນເງິນ</th>
                            <th>ລາຍລະອຽດ</th>
                            <th style="width: 30px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (['income', 'expense'] as $key): ?>
                            <?php $group = $financialGroups[$key]; ?>
                            <?php if (!empty($group['items'])):
                                // รวมยอดตามสกุลเงิน
                                $totalsByCurrency = [
                                    'LAK' => 0,
                                    'THB' => 0,
                                    'USD' => 0
                                ];

                                foreach ($group['items'] as $item) {
                                    $currency = $item['currency'] ?? 'LAK';
                                    if (isset($totalsByCurrency[$currency])) {
                                        $totalsByCurrency[$currency] += $item['totalamount'];
                                    }
                                } ?>
                                <tr class="group-header">
                                    <td colspan="8">
                                        <strong class="<?php echo $key === 'expense' ? 'text-danger' : 'text-success'; ?>"
                                            style="font-size: 14px;">
                                            <?php echo $group['title']; ?>
                                        </strong>
                                        <?php foreach ($totalsByCurrency as $currency => $total): ?>
                                            <?php if ($total > 0): // แสดงเฉพาะยอดที่ > 0 ?>
                                                <span class="group-total" style="color: inherit;">
                                                    (<?php echo $currency . ': ' . number_format($total); ?>)
                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                                <?php foreach ($group['items'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['date'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['category'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['currency'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['method'] ?: 'ບໍ່ລະບຸ'); ?></td>
                                        <td class="<?php echo $key === 'expense' ? 'text-danger' : 'text-success'; ?>">
                                            <?php echo $item['totalamount'] > 0 ? number_format($item['totalamount']) : ''; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['description'] ?? ''); ?></td>
                                        <td style="text-align: right;"><i class="fa-solid fa-chevron-right"
                                                style="color:#ccc; font-size: 11px;"></i></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php if (empty($financialGroups['income']['items']) && empty($financialGroups['expense']['items'])): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: #999; padding: 20px;">
                                    ຍັງບໍ່ມີຂໍ້ມູນໃນຊ່ວງວັນທີນີ້</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <hr style="margin: 20px 0;">

    <!-- insurance -->
    <div class="page-header">
        <h2>ລາຍງານລວມ (Insurance)</h2>
    </div>
    <div class="report-container-insurance">
        <div class="report-sidebar-insurance">
            <div class="report-card"
                style="padding: 15px; overflow-y: auto; flex: 1; display: flex; flex-direction: column;">
                <h4 class="report-title text-warning" style="margin-top: 5px; margin-bottom: 15px;">
                    ລາຍຮັບລວມ (Summary)</h4>
                <div class="summary-table-container" style="flex: 1;">
                    <table class="summary-table">
                        <thead>
                            <tr>
                                <th>ບໍລິສັດປະກັນໄພ</th>
                                <th>ລວມ ₭ (LAK)</th>
                                <th>ຊຳລະແລ້ວ ₭ (LAK)</th>
                                <th>ຄ້າງຊຳລະ ₭ (LAK)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rptInsuranceData as $data): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600;">
                                            <?php echo htmlspecialchars($data['name']); ?>
                                        </div>
                                    </td>
                                    <td style="font-weight: 500;"><?php echo number_format($data['total']); ?>
                                    </td>
                                    <td class="text-success" style="font-weight: 500;">
                                        <?php echo number_format($data['paid']); ?>
                                    </td>
                                    <td class="text-danger" style="font-weight: 600;">
                                        <?php echo number_format($data['total'] - $data['paid']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="profit-row" style="background: rgba(var(--success-rgb), 0.05);">
                                <td><strong>ລວມທັງໝົດ</strong></td>
                                <td><strong><?php echo number_format($totalInsuranceAll); ?></strong></td>
                                <td class="text-success">
                                    <strong><?php echo number_format($totalPaidAll); ?></strong>
                                </td>
                                <td class="text-danger">
                                    <strong><?php echo number_format($totalInsuranceAll - $totalPaidAll); ?></strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Main Content (Table) -->
        <div class="report-main-insurance">
            <div
                style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 15px; flex-shrink: 0;">
                <h4 style="margin: 0; font-size: 16px; color: #333;">ການຊຳລະ</h4>
            </div>

            <div style="flex: 1; overflow-y: auto; overflow-x: auto; padding-right: 5px;" class="custom-scroll">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>ວັນທີ</th>
                            <th>ລາຍລະອຽດ</th>
                            <th>ສະກຸນເງິນ</th>
                            <th>ວິທີຊຳລະ</th>
                            <th>ຈຳນວນເງິນ</th>
                            <th style="width: 30px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($insuranceIncomeGroups)): ?>
                            <?php foreach ($insuranceIncomeGroups as $group): ?>
                                <?php if (!empty($group['items'])): ?>
                                    <?php
                                    $groupTotal = array_reduce($group['items'], function ($carry, $item) {
                                        return $carry + $item['totalamount'];
                                    }, 0);
                                    ?>
                                    <tr class="group-header">
                                        <td colspan="7">
                                            <strong class="text-success" style="font-size: 14px;">
                                                <?php echo htmlspecialchars($group['title']); ?>
                                            </strong>
                                            <span class="text-success" style="font-size: 14px;">
                                                (<?php echo number_format($groupTotal); ?>₭)
                                            </span>
                                        </td>
                                    </tr>
                                    <?php foreach ($group['items'] as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['date'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($item['description'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($item['currency'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($item['method'] ?? ''); ?></td>
                                            <td class="text-success">
                                                <?php echo number_format($item['totalamount'] ?? 0); ?>
                                            </td>
                                            <td style="text-align: right;"><i class="fa-solid fa-chevron-right"
                                                    style="color:#ccc; font-size: 11px;"></i></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; color: #999; padding: 20px;">
                                    ຍັງບໍ່ມີຂໍ້ມູນໃນຊ່ວງວັນທີນີ້</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <hr style="margin: 20px 0;">

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon icon-blue"><i class="fa-solid fa-user-doctor"></i></div>
            <div class="kpi-info">
                <p>Doctors</p>
                <h3>50</h3>
            </div>
            <?php echo getTrendHtml($last7LAK, $prev7LAK, 'chart-red'); ?>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon icon-red"><i class="fa-solid fa-bed-pulse"></i></div>
            <div class="kpi-info">
                <p>Patients</p>
                <h3>
                    <?php echo number_format($totalPatients, 0); ?>
                </h3>
            </div>
            <?php echo getTrendHtml($last7THB, $prev7THB, 'chart-blue-light'); ?>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon icon-blue-light"><i class="fa-regular fa-calendar-check"></i></div>
            <div class="kpi-info">
                <p>Appointment</p>
                <h3>
                    <?php echo number_format($statTotal, 0); ?>
                </h3>
            </div>
            <?php echo getTrendHtml($last7USD, $prev7USD, 'chart-green'); ?>
        </div>
        <!-- <div class="kpi-card">
                    <div class="kpi-icon icon-blue"><i class="fa-solid fa-dollar-sign"></i></div>
                    <div class="kpi-info">
                        <p>Revenue</p>
                        <h3><?php echo number_format($claimsTotal, 0); ?></h3>
                    </div>
                    <?php echo getTrendHtml($last7Claims, $prev7Claims, 'chart-blue'); ?>
                </div> -->
    </div>

    <!-- Charts Area -->
    <div class="charts-grid">
        <div class="chart-card main-chart">
            <div class="card-header">
                <h3>Appointment Statistics</h3>
            </div>
            <div class="chart-summary" style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center;">
                <div class="summary-item"><span class="dot dot-all"></span> ໝົດທຸກລາຍການ
                    <strong>
                        <?php echo number_format($statTotal ?? 0); ?>
                    </strong>
                </div>
                <div class="summary-item"><span class="dot" style="background:#0d6efd;"></span>
                    Walk-in
                    <strong>
                        <?php echo number_format($statWalkin ?? 0); ?>
                    </strong>
                </div>
                <div class="summary-item"><span class="dot" style="background:#0dcaf0;"></span>
                    Booking
                    <strong>
                        <?php echo number_format($statBooking ?? 0); ?>
                    </strong>
                </div>
                <div class="summary-item"><span class="dot dot-cancelled"></span> Cancelled
                    <strong>
                        <?php echo number_format($statCancel ?? 0); ?>
                    </strong>
                </div>
                <div class="summary-item"><span class="dot dot-completed"></span> Completed
                    <strong>
                        <?php echo number_format($statCompleted ?? 0); ?>
                    </strong>
                </div>
            </div>
            <div class="chart-placeholder" style="height: auto; padding: 20px 0 0 0; background: none; border: none;">
                <div class="simple-bar-chart"
                    style="display: flex; align-items: flex-end; justify-content: space-between; height: 230px; width: 100%;">
                    <?php
                    $maxCases = max($monthlyCases ?? [0]) ?: 1;
                    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    for ($i = 1; $i <= 12; $i++) {
                        $val = $monthlyCases[$i] ?? 0;
                        $pct = ($val / $maxCases) * 100;
                        ?>
                        <div
                            style="display: flex; flex-direction: column; align-items: center; width: calc(100% / 12 - 4px);">
                            <div style="font-size: 11px; margin-bottom: 5px; color: var(--text-color); font-weight: 600;">
                                <?php echo ($val > 0) ? $val : ''; ?>
                            </div>
                            <div
                                style="width: 100%; max-width: 24px; background: #eaedf2; height: 200px; border-radius: 4px; display: flex; align-items: flex-end; overflow: hidden;">
                                <div
                                    style="width: 100%; background: linear-gradient(180deg, #0d6efd, #293092); height: <?php echo $pct; ?>%; border-radius: 4px; transition: height 0.5s;">
                                </div>
                            </div>
                            <div style="font-size: 10px; margin-top: 8px; color: var(--text-muted);">
                                <?php echo $months[$i - 1]; ?>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="appointments-sidebar">
            <div class="card-header">
                <h3>Appointments</h3>
            </div>
            <!-- Calendar mock -->
            <div class="calendar-mock">
                <div class="cal-header">
                    <i class="fa-solid fa-chevron-left" style="cursor:pointer;"></i>
                    <span>March 2026</span>
                    <i class="fa-solid fa-chevron-right" style="cursor:pointer;"></i>
                </div>
                <div class="cal-days">
                    <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                    <span>1</span><span>2</span><span>3</span><span>4</span><span>5</span><span>6</span><span>7</span>
                </div>
            </div>
            <div class="appointment-list">
                <div class="appointment-item">
                    <div class="apt-info">
                        <h4>General Visit</h4>
                        <p><i class="fa-regular fa-clock"></i> Wed, 05 Apr 2026, 06:30 PM</p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=User1" class="apt-avatar">
                </div>
                <div class="appointment-item">
                    <div class="apt-info">
                        <h4>General Visit</h4>
                        <p><i class="fa-regular fa-clock"></i> Wed, 05 Apr 2026, 04:10 PM</p>
                    </div>
                    <img src="https://ui-avatars.com/api/?name=User2" class="apt-avatar">
                </div>
            </div>
            <a href="#" class="view-all-link">View All Appointments</a>
        </div>
    </div>
    <hr style="margin: 20px 0;">


    <div class="lab-prescription">
        <!-- Lab Order -->
        <div class="chart-card">
            <div class="card-header">
                <h3>ລາຍການກວດທີ່ມີຍອດຂາຍສູງສຸດ</h3>
            </div>
            <div class="chart-placeholder"
                style="height: auto; padding: 10px 0; background: none; border: none; display: block;">
                <?php if (empty($topLabs)): ?>
                    <p style="text-align: center; color: #888; padding: 20px;">ບໍ່ມີຂໍ້ມູນ (No Data)</p>
                <?php else: ?>
                    <div class="simple-bar-chart">
                        <?php
                        $maxLab = max($topLabs) ?: 1;
                        foreach ($topLabs as $name => $val):
                            $pct = ($val / $maxLab) * 100;
                            ?>
                            <div class="bar-row"
                                style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                                <div style="flex: 1; min-width: 120px; margin-right: 15px; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                    title="<?php echo htmlspecialchars($name); ?>">
                                    <?php echo htmlspecialchars($name); ?>
                                </div>
                                <div style="flex: 2; background: #eaedf2; height: 10px; border-radius: 6px; overflow: hidden;">
                                    <div
                                        style="width: <?php echo $pct; ?>%; background: linear-gradient(90deg, #293092, #0d6efd); height: 100%; border-radius: 6px;">
                                    </div>
                                </div>
                                <div
                                    style="min-width: 40px; text-align: right; font-size: 13px; font-weight: 600; margin-left: 15px; color: var(--text-color);">
                                    <?php echo number_format($val, 0); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>


        <!-- Prescription -->
        <div class="chart-card">
            <div class="card-header">
                <h3>ລາຍການຢາທີ່ມີຍອດຂາຍສູງສຸດ</h3>
            </div>

            <div class="chart-placeholder"
                style="height: auto; padding: 10px 0; background: none; border: none; display: block;">
                <?php if (empty($topMeds)): ?>
                    <p style="text-align: center; color: #888; padding: 20px;">ບໍ່ມີຂໍ້ມູນ (No Data)</p>
                <?php else: ?>
                    <div class="simple-bar-chart">
                        <?php
                        $maxMed = max($topMeds) ?: 1;
                        foreach ($topMeds as $name => $val):
                            $pct = ($val / $maxMed) * 100;
                            ?>
                            <div class="bar-row"
                                style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                                <div style="flex: 1; min-width: 120px; margin-right: 15px; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                    title="<?php echo htmlspecialchars($name); ?>">
                                    <?php echo htmlspecialchars($name); ?>
                                </div>
                                <div style="flex: 2; background: #eaedf2; height: 10px; border-radius: 6px; overflow: hidden;">
                                    <div
                                        style="width: <?php echo $pct; ?>%; background: linear-gradient(90deg, #198754, #20c997); height: 100%; border-radius: 6px;">
                                    </div>
                                </div>
                                <div
                                    style="min-width: 40px; text-align: right; font-size: 13px; font-weight: 600; margin-left: 15px; color: var(--text-color);">
                                    <?php echo number_format($val, 0); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>