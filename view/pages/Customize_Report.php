<?php
// Dashboard Data Fetching (Specific to admin dashboard)
if (isset($appId) && isset($accessKey)) {
    $dashboardRequests = [
        'sell' => ['tableName' => 'Sell'],
        'payment' => ['tableName' => 'Payment'],
        'incomeexpenses' => ['tableName' => 'Income_Expense'],
        'Selldetails' => ['tableName' => 'Sell_Details'],
        'prescriptions' => ['tableName' => 'Prescriptions']
        // We exclude employees, mainstocks, Customer, etc!
    ];
    // This function will block until these tables are downloaded
    $multiData = getAppSheetDataMulti($dashboardRequests, $appId, $accessKey);
    $sell = $multiData['sell'] ?? [];
    $payment = $multiData['payment'] ?? [];
    $incomeexpenses = $multiData['incomeexpenses'] ?? [];
    $selldetails = $multiData['Selldetails'] ?? [];
    $prescriptions = $multiData['prescriptions'] ?? [];
}

// Ensure the variables exist so api.php works even if fetch fails
$sell = $sell ?? [];
$payment = $payment ?? [];
$incomeexpenses = $incomeexpenses ?? [];
$selldetails = $selldetails ?? [];
$prescriptions = $prescriptions ?? [];

// Then process the financial logic
include '../src/routes/api_Customize_Report.php';
?>
<link rel="stylesheet" href="../assets/css/admindashboard.css">
<!-- Dashboard Content -->
<div class="dashboard-content">
    <div class="page-header">
        <h2>ລາຍງານລາຍຮັບຂາຍນ້ຳດື່ມ</h2>
    </div>
    <!-- KPI Cards -->
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-icon icon-blue"><i class="fa-brands fa-amazon-pay"></i></div>
            <div class="kpi-info">
                <p>ລວມເປັນເງິນບາດ</p>
                <h3><?php echo number_format($totalProduct, 0); ?></h3>
            </div>
            <?php echo getTrendHtml($last7Product, $prev7Product, 'chart-blue'); ?>
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
                    
                    // Helper function to get trend HTML
                    $getTrendBadge = function($trend, $current, $previous) {
                        $trendVal = round($trend, 2);
                        $symbol = '';
                        $color = '';
                        $icon = '';
                        
                        if ($trendVal > 0) {
                            $symbol = '+';
                            $color = 'color: #28a745;'; // Green
                            $icon = '▲';
                        } elseif ($trendVal < 0) {
                            $symbol = '';
                            $color = 'color: #dc3545;'; // Red
                            $icon = '▼';
                        } else {
                            $color = 'color: #6c757d;'; // Gray
                            $icon = '●';
                        }
                        
                        return '<span style="' . $color . '">' . $icon . $symbol . $trendVal . '%</span>';
                    };
                    ?>
                    <table class="summary-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="text-align: left;">ລາຍຮັບ/ເດືອນ</th>
                                <th style="text-align: right;"><?php echo $prevMonthName; ?></th>
                                <th style="text-align: right;"><?php echo $currentMonthName; ?></th>
                                <th style="text-align: center;">ປຽບທຽບ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthComparisonWithTrend as $key => $item): ?>
                                <tr>
                                    <td style="text-align: left;"><?php echo $item['label']; ?></td>
                                    <td style="text-align: right; padding-right: 15px;">
                                        <?php echo number_format((int)$item['previous']); ?>
                                    </td>
                                    <td style="text-align: right; padding-right: 15px;">
                                        <?php echo number_format((int)$item['current']); ?>
                                    </td>
                                    <td style="text-align: center; padding-left: 15px;">
                                        <?php echo $getTrendBadge($item['trend'], $item['current'], $item['previous']); ?>
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
                                            <?php echo htmlspecialchars($item['Total_Customize'] ?? ''); ?>
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

   <div class="lab-prescription">
                <!-- ແມ່ພິມ by Size -->
        <div class="chart-card">
            <div class="card-header">
                <h3>ລາຍຮັບປຽບທຽບເດືອນນີ້ vs ເດືອນກ່ອນ</h3>
            </div>
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

                foreach ($selldetails as $detail) {
                    $productName = $detail['Product_Name'] ?? '';
                    $size = trim($detail['Size'] ?? '');
                    $SubTotal_Detail = ($detail['SubTotal_Detail'] ?? 0);
                    $saleDate = $detail['Date'] ?? '';

                    if ($productName === 'ແມ່ພິມ' && in_array($size, $sizes, true) && !empty($saleDate)) {
                        $itemTimestamp = strtotime($saleDate);
                        $itemMonth = date('n', $itemTimestamp);
                        $itemYear = date('Y', $itemTimestamp);

                        if ($itemMonth === $currentMonth && $itemYear === $currentYear) {
                            $currentMonthSales[$size] += $SubTotal_Detail;
                        } elseif ($itemMonth === $prevMonth && $itemYear === $prevYear) {
                            $prevMonthSales[$size] += $SubTotal_Detail;
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
                                <td style="text-align: left;">ແມ່ພິມ <?php echo $size; ?></td>
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
                        <!-- Total for ແມ່ພິມ -->
                        <?php
                        $trendTotal = 0;
                        if ($sumPrev > 0) {
                            $trendTotal = (($sumCurrent - $sumPrev) / $sumPrev) * 100;
                        } elseif ($sumCurrent > 0) {
                            $trendTotal = 100;
                        }
                        ?>
                        <tr style="font-weight:bold; background:#eafbea;">
                            <td style="text-align:left;">ລວມຍອດແມ່ພິມ</td>
                            <td style="text-align:right; padding-right:15px;"><?php echo number_format($sumPrev); ?></td>
                            <td style="text-align:right; padding-right:15px;"><?php echo number_format($sumCurrent); ?></td>
                            <td style="text-align:center; padding-left:15px; color:#28a745;"><?php echo $getTrendBadge($trendTotal); ?></td>
                        </tr>
                        <!-- OEM by Size (ยอดขาย) -->
                        <!-- <tr>
                            <td colspan="4" style="font-weight:bold; background:#f5f5f5;">OEM</td>
                        </tr> -->
                        <?php
                        $oemMonthSales = array_fill_keys($sizes, 0);
                        $oemPrevMonthSales = array_fill_keys($sizes, 0);
                        foreach ($selldetails as $detail) {
                            $productName = $detail['Product_Name'] ?? '';
                            $size = trim($detail['Size'] ?? '');
                            $SubTotal_Detail = ($detail['SubTotal_Detail'] ?? 0);
                            $saleDate = $detail['Date'] ?? '';
                            if ($productName === 'OEM' && in_array($size, $sizes, true) && !empty($saleDate)) {
                                $itemTimestamp = strtotime($saleDate);
                                $itemMonth = date('n', $itemTimestamp);
                                $itemYear = date('Y', $itemTimestamp);
                                if ($itemMonth === $currentMonth && $itemYear === $currentYear) {
                                    $oemMonthSales[$size] += $SubTotal_Detail;
                                } elseif ($itemMonth === $prevMonth && $itemYear === $prevYear) {
                                    $oemPrevMonthSales[$size] += $SubTotal_Detail;
                                }
                            }
                        }
                        $sumOEMPrev = 0;
                        $sumOEMCurrent = 0;
                        foreach ($sizes as $size):
                            $previous = $oemPrevMonthSales[$size];
                            $current = $oemMonthSales[$size];
                            $sumOEMPrev += $previous;
                            $sumOEMCurrent += $current;
                            $trend = 0;
                            if ($previous > 0) {
                                $trend = (($current - $previous) / $previous) * 100;
                            } elseif ($current > 0) {
                                $trend = 100;
                            }
                        ?>
                        <tr>
                            <td style="text-align: left;">OEM <?php echo $size; ?></td>
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
                        <!-- Total for OEM -->
                        <?php
                        $trendOEMTotal = 0;
                        if ($sumOEMPrev > 0) {
                            $trendOEMTotal = (($sumOEMCurrent - $sumOEMPrev) / $sumOEMPrev) * 100;
                        } elseif ($sumOEMCurrent > 0) {
                            $trendOEMTotal = 100;
                        }
                        ?>
                        <tr style="font-weight:bold; background:#eafbea;">
                            <td style="text-align:left;">ລວມຍອດ OEM</td>
                            <td style="text-align:right; padding-right:15px;"><?php echo number_format($sumOEMPrev); ?></td>
                            <td style="text-align:right; padding-right:15px;"><?php echo number_format($sumOEMCurrent); ?></td>
                            <td style="text-align:center; padding-left:15px; color:#28a745;"><?php echo $getTrendBadge($trendOEMTotal); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ແມ່ພິມ by Size -->
        <div class="chart-card">
            <div class="card-header">
                <h3>ລາຍຮັບປຽບທຽບເດືອນນີ້ vs ເດືອນກ່ອນ</h3>
            </div>
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

                foreach ($selldetails as $detail) {
                    $productName = $detail['Product_Name'] ?? '';
                    $size = trim($detail['Size'] ?? '');
                    $qty = (int)($detail['Qty'] ?? 0);
                    $saleDate = $detail['Date'] ?? '';

                    if ($productName === 'ແມ່ພິມ' && in_array($size, $sizes, true) && !empty($saleDate)) {
                        $itemTimestamp = strtotime($saleDate);
                        $itemMonth = date('n', $itemTimestamp);
                        $itemYear = date('Y', $itemTimestamp);

                        if ($itemMonth === $currentMonth && $itemYear === $currentYear) {
                            $currentMonthSales[$size] += $qty;
                        } elseif ($itemMonth === $prevMonth && $itemYear === $prevYear) {
                            $prevMonthSales[$size] += $qty;
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
                                <td style="text-align: left;">ແມ່ພິມ <?php echo $size; ?></td>
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
                        <!-- Total for ແມ່ພິມ -->
                        <?php
                        $trendTotal = 0;
                        if ($sumPrev > 0) {
                            $trendTotal = (($sumCurrent - $sumPrev) / $sumPrev) * 100;
                        } elseif ($sumCurrent > 0) {
                            $trendTotal = 100;
                        }
                        ?>
                        <tr style="font-weight:bold; background:#eafbea;">
                            <td style="text-align:left;">ລວມຈຳນວນແມ່ພິມ</td>
                            <td style="text-align:right; padding-right:15px;"><?php echo number_format($sumPrev); ?></td>
                            <td style="text-align:right; padding-right:15px;"><?php echo number_format($sumCurrent); ?></td>
                            <td style="text-align:center; padding-left:15px; color:#28a745;"><?php echo $getTrendBadge($trendTotal); ?></td>
                        </tr>
                        <!-- OEM by Size (จำนวน) -->
                        <?php
                        $oemMonthSales = array_fill_keys($sizes, 0);
                        $oemPrevMonthSales = array_fill_keys($sizes, 0);
                        foreach ($selldetails as $detail) {
                            $productName = $detail['Product_Name'] ?? '';
                            $size = trim($detail['Size'] ?? '');
                            $qty = (int)($detail['Qty'] ?? 0);
                            $saleDate = $detail['Date'] ?? '';
                            if ($productName === 'OEM' && in_array($size, $sizes, true) && !empty($saleDate)) {
                                $itemTimestamp = strtotime($saleDate);
                                $itemMonth = date('n', $itemTimestamp);
                                $itemYear = date('Y', $itemTimestamp);
                                if ($itemMonth === $currentMonth && $itemYear === $currentYear) {
                                    $oemMonthSales[$size] += $qty;
                                } elseif ($itemMonth === $prevMonth && $itemYear === $prevYear) {
                                    $oemPrevMonthSales[$size] += $qty;
                                }
                            }
                        }
                        $sumOEMPrev = 0;
                        $sumOEMCurrent = 0;
                        foreach ($sizes as $size):
                            $previous = $oemPrevMonthSales[$size];
                            $current = $oemMonthSales[$size];
                            $sumOEMPrev += $previous;
                            $sumOEMCurrent += $current;
                            $trend = 0;
                            if ($previous > 0) {
                                $trend = (($current - $previous) / $previous) * 100;
                            } elseif ($current > 0) {
                                $trend = 100;
                            }
                        ?>
                        <tr>
                            <td style="text-align: left;">OEM <?php echo $size; ?></td>
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
                        <!-- Total for OEM -->
                        <?php
                        $trendOEMTotal = 0;
                        if ($sumOEMPrev > 0) {
                            $trendOEMTotal = (($sumOEMCurrent - $sumOEMPrev) / $sumOEMPrev) * 100;
                        } elseif ($sumOEMCurrent > 0) {
                            $trendOEMTotal = 100;
                        }
                        ?>
                        <tr style="font-weight:bold; background:#eafbea;">
                            <td style="text-align:left;">ລວມຈຳນວນ OEM</td>
                            <td style="text-align:right; padding-right:15px;"><?php echo number_format($sumOEMPrev); ?></td>
                            <td style="text-align:right; padding-right:15px;"><?php echo number_format($sumOEMCurrent); ?></td>
                            <td style="text-align:center; padding-left:15px; color:#28a745;"><?php echo $getTrendBadge($trendOEMTotal); ?></td>
                        </tr>
                        
                    </tbody>
                </table>
            </div>
        </div>



    </div>
</div>
</div>