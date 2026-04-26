<?php
// Dashboard Data Fetching (Specific to admin dashboard)
if (isset($appId) && isset($accessKey)) {
    $dashboardRequests = [
        'Selldetails' => ['tableName' => 'Sell_Details'],
        'Production_Log' => ['tableName' => 'Production_Log'],
        'Production_Warehouse' => ['tableName' => 'Production_Warehouse'],
        'Stock_Transfer_Detail' => ['tableName' => 'Stock_Transfer_Detail']
        // We exclude employees, mainstocks, Customer, etc!
    ];
    // This function will block until these tables are downloaded
    $multiData = getAppSheetDataMulti($dashboardRequests, $appId, $accessKey);
    $Production_Warehouse = $multiData['Production_Warehouse'] ?? [];
    $incomeexpenses = $multiData['Production_Log'] ?? [];
    $prescriptions = $multiData['Stock_Transfer_Detail'] ?? [];
}

// Ensure the variables exist so api.php works even if fetch fails
$sell = $sell ?? [];
$payment = $payment ?? [];
$incomeexpenses = $incomeexpenses ?? [];
$selldetails = $selldetails ?? [];
$prescriptions = $prescriptions ?? [];

// Then process the financial logic
include '../src/routes/api.php';
?>
<link rel="stylesheet" href="../assets/css/admindashboard.css">
<!-- Dashboard Content -->
<div class="dashboard-content">


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
                    ລາຍຮັບປຽບທຽບເດືອນກ່ອນ</h4>
                <div class="summary-table-container">
                <?php
                // Get current and previous month names
                $currentMonth = date('n');
                $currentYear = date('Y');
                $prevMonth = ($currentMonth == 1) ? 12 : $currentMonth - 1;
                $prevYear = ($currentMonth == 1) ? $currentYear - 1 : $currentYear;

                $currentMonthName = date('M-y', strtotime(date('Y-m-01')));
                $prevMonthName = date('M-y', strtotime($prevYear . '-' . str_pad($prevMonth, 2, '0', STR_PAD_LEFT) . '-01'));

                $sizes = ['250ml', '350ml', '600ml', '1500ml'];
                $currentMonthSales = array_fill_keys($sizes, 0);
                $prevMonthSales = array_fill_keys($sizes, 0);

                foreach ($incomeexpenses as $detail) {
                    $productName = $detail['Product_Name'] ?? '';
                    $size = trim($detail['Size'] ?? '');
                    $qtyIn = (int)($detail['Qty_In'] ?? 0);
                    $logDate = $detail['Log_Date'] ?? '';

                    if (!empty($productName) && in_array($size, $sizes, true) && !empty($logDate)) {
                        $itemTimestamp = strtotime($logDate);
                        $itemMonth = date('n', $itemTimestamp);
                        $itemYear = date('Y', $itemTimestamp);

                        if ($itemMonth === $currentMonth && $itemYear === $currentYear) {
                            $currentMonthSales[$size] += $qtyIn;
                        } elseif ($itemMonth === $prevMonth && $itemYear === $prevYear) {
                            $prevMonthSales[$size] += $qtyIn;
                        }
                    }
                }

                $getTrendBadge = function ($trend) {
                    $trendVal = round($trend, 2);
                    if ($trendVal > 0) {
                        return '<span style="color: #28a745;">▲ +' . $trendVal . '%</span>';
                    }
                    if ($trendVal < 0) {
                        return '<span style="color: #dc3545;">▼ ' . $trendVal . '%</span>';
                    }
                    return '<span style="color: #6c757d;">● 0%</span>';
                };
                ?>
                <table class="summary-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="text-align: left;">ຂະໜາດ</th>
                            <th style="text-align: right;"><?php echo $prevMonthName; ?></th>
                            <th style="text-align: right;"><?php echo $currentMonthName; ?></th>
                            <th style="text-align: center;">ປຽບທຽບ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sumPrev = 0;
                        $sumCurrent = 0;
                        foreach ($sizes as $size):
                            $previous = $prevMonthSales[$size];
                            $current = $currentMonthSales[$size];
                            $sumPrev += $previous;
                            $sumCurrent += $current;
                            $trend = 0;
                            if ($previous > 0) {
                                $trend = (($current - $previous) / $previous) * 100;
                            } elseif ($current > 0) {
                                $trend = 100;
                            }
                        ?>
                            <tr>
                                <td style="text-align: left;"><?php echo $productName . ' ' . $size; ?></td>
                                <td style="text-align: right; padding-right: 15px;">
                                    <?php echo number_format($previous); ?>
                                </td>
                                <td style="text-align: right; padding-right: 15px;">
                                    <?php echo number_format($current); ?>
                                </td>
                                <td style="text-align: center; padding-left: 15px;">
                                    <?php echo $getTrendBadge($trend); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <!-- Total for Product -->
                        <?php
                        $trendTotal = 0;
                        if ($sumPrev > 0) {
                            $trendTotal = (($sumCurrent - $sumPrev) / $sumPrev) * 100;
                        } elseif ($sumCurrent > 0) {
                            $trendTotal = 100;
                        }
                        ?>
                        <tr style="font-weight:bold; background:#eafbea;">
                            <td style="text-align:left;">ລວມຈຳນວນ</td>
                            <td style="text-align:right; padding-right:15px;"><?php echo number_format($sumPrev); ?></td>
                            <td style="text-align:right; padding-right:15px;"><?php echo number_format($sumCurrent); ?></td>
                            <td style="text-align:center; padding-left:15px; color:#28a745;"><?php echo $getTrendBadge($trendTotal); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            </div>
            </div>
        </div>

        <!-- Right Main Content (Table) -->
        <div class="report-main">
            <div
                style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 15px; flex-shrink: 0;">
                <h4 style="margin: 0; font-size: 16px; color: #333;">ລາຍລະອຽດການຊຳລະ</h4>
            </div>

            <div style="flex: 1; overflow-y: auto; overflow-x: auto; padding-right: 5px;" class="custom-scroll">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>ວັນທີ</th>
                            <th>ລູກຄ້າ</th>
                            <th>ສະກຸນເງິນ</th>
                            <th>ວິທີຊຳລະ</th>
                            <th>ຈຳນວນ</th>
                            <th>ສ່ວນຫຼຸດ</th>
                            <th>ຈ່າຍ</th>
                            <th>ເລດເງິນ</th>
                            <th style="width: 30px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (['income', 'expense'] as $key): ?>
                            <?php $group = $financialGroups[$key]; ?>
                            <?php if (!empty($group['items'])): ?>
                                <tr class="group-header">
                                    <td colspan="9">
                                        <strong class="<?php echo $key === 'expense' ? 'text-danger' : 'text-success'; ?>"
                                            style="font-size: 14px;">
                                            <?php echo $group['title']; ?>
                                        </strong>
                                    </td>
                                </tr>
                                <?php foreach ($group['items'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['date'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['customerName'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['currency'] ?? ''); ?></td>
                                        <td><?php echo htmlspecialchars($item['paymentType'] ?: 'ບໍ່ລະບຸ'); ?></td>
                                        <td class="<?php echo $key === 'expense' ? 'text-danger' : 'text-success'; ?>">
                                            <?php echo htmlspecialchars($item['Total_Product'] ?? ''); ?>
                                        </td>
                                        <td><?php echo $item['discount'] > 0 ? number_format($item['discount']) : ''; ?></td>
                                        <td class="<?php echo $key === 'expense' ? 'text-danger' : 'text-success'; ?>">
                                            <?php echo $item['payAmount'] > 0 ? number_format($item['payAmount']) : ''; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($item['MoneyRate'] ?: ''); ?></td>
                                        <td style="text-align: right;"><i class="fa-solid fa-chevron-right"
                                                style="color:#ccc; font-size: 11px;"></i></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>

                        <?php if (empty($financialGroups['income']['items']) && empty($financialGroups['expense']['items'])): ?>
                            <tr>
                                <td colspan="9" style="text-align: center; color: #999; padding: 20px;">
                                    ຍັງບໍ່ມີຂໍ້ມູນໃນຊ່ວງວັນທີນີ້</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    </div>
</div>
</div>