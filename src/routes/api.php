<?php
include_once __DIR__ . '/../utils/functions.php';

$sevenDaysAgo = strtotime('-7 days');
$fourteenDaysAgo = strtotime('-14 days');
$now = time();


// Calculate Payments by Currency
// ຄຳນວນການຈ່າຍເງິນໂດຍອີງໃສ່ສະກຸນເງິນໂດຍອີງໃສ່ຂໍ້ມູນຈາກ $payment
// ເຂື່ອມກັບຖານຂໍ້ມູນໂດຍຜ່ານການໃຊ້ຂໍ້ມູນຈາກ AppSheet API
$totalLAK = 0;
$last7LAK = 0;
$prev7LAK = 0;
$totalTHB = 0;
$last7THB = 0;
$prev7THB = 0;
$totalUSD = 0;
$last7USD = 0;
$prev7USD = 0;

if (isset($payment) && is_array($payment)) {
    foreach ($payment as $payRow) {
        $currency = strtoupper(trim($payRow['Currency'] ?? ''));
        $baseCurrency = strtoupper(trim($payRow['Base_Currency'] ?? ''));

        // --- ສ່ວນທີ່ເພີ່ມ/ແກ້ໄຂໃໝ່ ---
        // ຖ້າ Base_Currency ເປັນ THB ແມ່ນໃຫ້ຂ້າມໄປເລີຍ (ບໍ່ເອົາມາໄລ່)
        if ($baseCurrency === 'THB') {
            continue;
        }

        // ຖ້າຕ້ອງການບັງຄັບວ່າ "ຕ້ອງເປັນ LAK ເທົ່ານັ້ນ" ຈຶ່ງຈະເອົາ 
        // ສາມາດປ່ຽນໄປໃຊ້: if ($baseCurrency !== 'LAK') { continue; }
        // -------------------------

        $payAmount = floatval($payRow['Total_Pay'] ?? 0);

        $paymentStr = $payRow['DateTime'] ?? $payRow['Date'] ?? '';
        $paymentTime = strtotime($paymentStr);
        if (!$paymentTime)
            $paymentTime = $now;

        $isCurrent = $paymentTime >= $sevenDaysAgo;
        $isPrevious = $paymentTime >= $fourteenDaysAgo && $paymentTime < $sevenDaysAgo;

        // ກວດສອບສະກຸນເງິນເພື່ອແຍກບວກເຂົ້າຕົວປ່ຽນ
        if ($currency === 'LAK' || $currency === 'KIP' || strpos($currency, 'LAK') !== false) {
            $totalLAK += $payAmount;
            if ($isCurrent) $last7LAK += $payAmount;
            if ($isPrevious) $prev7LAK += $payAmount;
            
        } elseif ($currency === 'THB' || $currency === 'BAHT' || strpos($currency, 'THB') !== false) {
            $totalTHB += $payAmount;
            if ($isCurrent) $last7THB += $payAmount;
            if ($isPrevious) $prev7THB += $payAmount;
            
        } elseif ($currency === 'USD' || $currency === 'DOLLAR' || strpos($currency, 'USD') !== false) {
            $totalUSD += $payAmount;
            if ($isCurrent) $last7USD += $payAmount;
            if ($isPrevious) $prev7USD += $payAmount;
        }
    }
}

// Calculate Total_Product from Payment table
$totalProduct = 0;
$last7Product = 0;
$prev7Product = 0;

if (isset($payment) && is_array($payment)) {
    foreach ($payment as $payRow) {
        $currency = strtoupper(trim($payRow['Currency'] ?? ''));
        $baseCurrency = strtoupper(trim($payRow['Base_Currency'] ?? ''));

        // --- ສ່ວນທີ່ເພີ່ມ/ແກ້ໄຂໃໝ່ ---
        // ຖ້າ Base_Currency ເປັນ THB ແມ່ນໃຫ້ຂ້າມໄປເລີຍ (ບໍ່ເອົາມາໄລ່)
        if ($baseCurrency === 'THB') {
            continue;
        }

        // ຖ້າຕ້ອງການບັງຄັບວ່າ "ຕ້ອງເປັນ LAK ເທົ່ານັ້ນ" ຈຶ່ງຈະເອົາ 
        // ສາມາດປ່ຽນໄປໃຊ້: if ($baseCurrency !== 'LAK') { continue; }
        // -------------------------

        $paidAmount = floatval($payRow['Paid'] ?? 0);

        $paymentStr = $payRow['DateTime'] ?? $payRow['Date'] ?? '';
        $paymentTime = strtotime($paymentStr);
        if (!$paymentTime)
            $paymentTime = $now;

        $isCurrent = $paymentTime >= $sevenDaysAgo;
        $isPrevious = $paymentTime >= $fourteenDaysAgo && $paymentTime < $sevenDaysAgo;

        // ກວດສອບສະກຸນເງິນເພື່ອແຍກບວກເຂົ້າຕົວປ່ຽນ
        if ($currency === 'LAK' || $currency === 'KIP' || strpos($currency, 'LAK') !== false) {
            $totalProduct += $paidAmount;
            if ($isCurrent) $last7Product += $paidAmount;
            if ($isPrevious) $prev7Product += $paidAmount;
            
        } elseif ($currency === 'THB' || $currency === 'BAHT' || strpos($currency, 'THB') !== false) {
            $totalProduct += $paidAmount;
            if ($isCurrent) $last7Product += $paidAmount;
            if ($isPrevious) $prev7Product += $paidAmount;
            
        } elseif ($currency === 'USD' || $currency === 'DOLLAR' || strpos($currency, 'USD') !== false) {
            $totalProduct += $paidAmount;
            if ($isCurrent) $last7Product += $paidAmount;
            if ($isPrevious) $prev7Product += $paidAmount;
        }
    }
}



// ---------------------------------------------------------
// ລະບົບລາຍງານລວມ (Income & Expenses Calculations)
// ຄຳນວນລາຍຮັບແລະລາຍຈ່າຍໂດຍອີງໃສ່ຂໍ້ມູນຈາກ $payment ແລະ $incomeexpenses
// ເຂື່ອມກັບຖານຂໍ້ມູນໂດຍຜ່ານການໃຊ້ຂໍ້ມູນຈາກ AppSheet API
// ---------------------------------------------------------
$reportFilterStart = $_GET['start'] ?? date('Y-m-01');
$reportFilterEnd = $_GET['end'] ?? date('Y-m-d');
$startTs = strtotime($reportFilterStart . ' 00:00:00');
$endTs = strtotime($reportFilterEnd . ' 23:59:59');

$rptIncomeTotal = 0;
$rptIncomeCash = 0;
$rptIncomeTransfer = 0;
$rptDiscount = 0;

$rptIncomeGeneral = 0;

$rptExpenseGeneral = 0;
$rptExpenseSalary = 0;

// Currency-specific totals
$rptCurrencies = ['LAK', 'THB', 'USD'];
$rptIncomeByCur = array_fill_keys($rptCurrencies, 0);
$rptCashByCur = array_fill_keys($rptCurrencies, 0);
$rptTransferByCur = array_fill_keys($rptCurrencies, 0);
$rptDiscountByCur = array_fill_keys($rptCurrencies, 0);
$rptExpenseByCur = array_fill_keys($rptCurrencies, 0);

// Specific Category Breakdowns by Currency
$rptExpenseSalaryByCur = array_fill_keys($rptCurrencies, 0);
$rptExpenseGeneralByCur = array_fill_keys($rptCurrencies, 0);

$financialGroups = [
    'income' => ['title' => 'ລາຍຮັບ (Income)', 'totalamount' => 0, 'items' => []],
    'expense' => ['title' => 'ລາຍຈ່າຍ (Expense)', 'totalamount' => 0, 'items' => []]
];

// 1. ດຶງຂໍ້ມູນຈາກ Payment
$seenPaymentIds = [];
if (isset($payment) && is_array($payment)) {
    foreach ($payment as $pay) {
        $payId = $pay['Pay_ID'] ?? $pay['Pay ID'] ?? $pay['Payment_ID'] ?? $pay['Payment ID'] ?? $pay['ID'] ?? null;
        if ($payId !== null) {
            if (isset($seenPaymentIds[$payId])) {
                continue;
            }
            $seenPaymentIds[$payId] = true;
        }

        $dateStr = $pay['DateTime'] ?? $pay['Date'] ?? '';
        $ts = strtotime($dateStr);
        if (!$ts)
            continue;
        if ($ts < $startTs || $ts > $endTs)
            continue;

        $amount = floatval($pay['Pay'] ?? $pay['Amount'] ?? 0);
        $method = trim($pay['PaymentMethod'] ?? '');
        $discount = floatval($pay['Discount'] ?? 0);

        // --- ສ່ວນທີ່ເພີ່ມ/ແກ້ໄຂໃໝ່ ---
        // ຖ້າ Base_Currency ເປັນ THB ແມ່ນໃຫ້ຂ້າມໄປເລີຍ (ບໍ່ເອົາມາໄລ່)
        $baseCurrency = strtoupper(trim($pay['Base_Currency'] ?? ''));
        if ($baseCurrency === 'THB') {
            continue;
        }

        // Summaries
        $rptIncomeTotal += $amount;
        $rptDiscount += $discount;

        $cur = strtoupper(trim($pay['Currency'] ?? 'LAK'));
        if ($cur === 'KIP')
            $cur = 'LAK';
        if ($cur === 'BAHT')
            $cur = 'THB';
        if ($cur === 'DOLLAR')
            $cur = 'USD';

        $cashAmount = floatval($pay['Cash'] ?? 0);
        $transferAmount = floatval($pay['Transfer'] ?? 0);
        if ($cashAmount > 0) {
            $rptIncomeCash += $cashAmount;
            if (array_key_exists($cur, $rptCashByCur)) {
                $rptCashByCur[$cur] += $cashAmount;
            }
        }
        if ($transferAmount > 0) {
            $rptIncomeTransfer += $transferAmount;
            if (array_key_exists($cur, $rptTransferByCur)) {
                $rptTransferByCur[$cur] += $transferAmount;
            }
        }

        if (array_key_exists($cur, $rptIncomeByCur)) {
            $rptIncomeByCur[$cur] += $amount;
            $rptDiscountByCur[$cur] += $discount;
        }

        $paymentType = '';
        if ($cashAmount > 0) {
            $paymentType = 'Cash';
        } elseif ($transferAmount > 0) {
            $paymentType = 'Transfer';
        } elseif (strpos($method, 'ສົດ') !== false || stripos($method, 'cash') !== false) {
            $paymentType = 'Cash';
            $cashAmount = $amount;
        } elseif (strpos($method, 'ໂອນ') !== false || stripos($method, 'transfer') !== false) {
            $paymentType = 'Transfer';
            $transferAmount = $amount;
        } else {
            $paymentType = $method ?: 'Unknown';
        }

        $payAmount = $amount;
        if ($cashAmount > 0) {
            $payAmount = $cashAmount;
        } elseif ($transferAmount > 0) {
            $payAmount = $transferAmount;
        }

        $financialGroups['income']['totalamount'] += $payAmount;
        $financialGroups['income']['items'][] = [
            'date' => date('n/j/Y', $ts),
            'customerName' => $pay['Customer_Name'] ?? $pay['Customer'] ?? $pay['Customer Name'] ?? $pay['Payee'] ?? '',
            'category' => $pay['Category'] ?? '',
            'currency' => $cur,
            'paymentType' => $paymentType,
            'payAmount' => $payAmount,
            'discount' => $discount,
            'Total_Product' => $pay['Total_Product'] ?? '',
            'MoneyRate' => $pay['Money Rate'] ?? '',
            'cashAmount' => $cashAmount,
            'transferAmount' => $transferAmount,
            'payId' => $payId,
            'amount' => $amount,
            'totalamount' => $payAmount,
            'Paid' => $paidAmount,
        ];
    }
}

$customerPaymentStats = [];
foreach ($financialGroups['income']['items'] as $item) {
    $customerName = trim($item['customerName'] ?? '') ?: 'Unknown Customer';
    if (!isset($customerPaymentStats[$customerName])) {
        $customerPaymentStats[$customerName] = [
            'name' => $customerName,
            'count' => 0,
            'sum' => 0
        ];
    }
    $customerPaymentStats[$customerName]['count']++;
    $customerPaymentStats[$customerName]['sum'] += floatval($item['Paid'] ?? 0);
}

$topPaymentCustomers = array_values($customerPaymentStats);
usort($topPaymentCustomers, function ($a, $b) {
    if ($b['sum'] === $a['sum']) {
        return $b['count'] <=> $a['count'];
    }
    return $b['sum'] <=> $a['sum'];
});
$topPaymentCustomers = array_slice($topPaymentCustomers, 0, 10);

// 2. ດຶງຂໍ້ມູນຈາກ IncomeExpenses
$incomeexpenses = isset($incomeexpenses) ? $incomeexpenses : [];
if (is_array($incomeexpenses)) {
    foreach ($incomeexpenses as $inex) {
        $dateStr = $inex['DateTime'] ?? $inex['Date'] ?? '';
        $ts = strtotime($dateStr);
        if (!$ts)
            continue;
        if ($ts < $startTs || $ts > $endTs)
            continue;

        $amount = floatval($inex['Amount'] ?? 0); //ເງິນສົດ
        $pay = floatval($inex['Pay'] ?? 0); //ເງິນໂອນ
        $totalamount = floatval($inex['TotalAmount'] ?? 0); // ເງິນສົດ+ເງິນໂອນ
        $type = strtolower(trim($inex['Type'] ?? ''));
        $category = trim($inex['Category'] ?? '');
        $method = trim($inex['PaymentMethod'] ?? '');

        $cur = strtoupper(trim($inex['Currency'] ?? 'LAK'));
        if ($cur === 'KIP')
            $cur = 'LAK';
        if ($cur === 'BAHT')
            $cur = 'THB';
        if ($cur === 'DOLLAR')
            $cur = 'USD';

        // Is Income?
        if (strpos($type, 'ລາຍຮັບ') !== false || stripos($type, 'income') !== false || $type === 'in') {
            $rptIncomeTotal += $totalamount;
            $rptIncomeGeneral += $totalamount;
            if (array_key_exists($cur, $rptIncomeByCur)) {
                $rptIncomeByCur[$cur] += $totalamount;
                if (strpos($method, 'ເງິນສົດ') !== false || stripos($method, 'Cash') !== false) {
                    $rptIncomeCash += $amount;
                    $rptCashByCur[$cur] += $amount;
                } elseif (strpos($method, 'ເງິນໂອນ') !== false || stripos($method, 'Transfer') !== false) {
                    $rptIncomeTransfer += $pay;
                    $rptTransferByCur[$cur] += $pay;
                } else {
                    $rptIncomeCash += $amount;
                    $rptCashByCur[$cur] += $amount;
                }
            }
        }
        // Is Expense?
        elseif (strpos($type, 'ລາຍຈ່າຍ') !== false || stripos($type, 'expense') !== false || $type === 'out') {
            if (strpos($category, 'ເງິນເດືອນ') !== false || strpos($category, 'salary') !== false) {
                $rptExpenseSalary += $totalamount;
                if (array_key_exists($cur, $rptExpenseSalaryByCur))
                    $rptExpenseSalaryByCur[$cur] += $totalamount;
            } else {
                $rptExpenseGeneral += $totalamount;
                if (array_key_exists($cur, $rptExpenseGeneralByCur))
                    $rptExpenseGeneralByCur[$cur] += $totalamount;
            }
            if (array_key_exists($cur, $rptExpenseByCur)) {
                $rptExpenseByCur[$cur] += $totalamount;
            }
        }

        // Add to grouped Data Table (Income / Expense)
        $isExpense = (strpos($type, 'ຈ່າຍ') !== false || stripos($type, 'expense') !== false || $type === 'out');
        $groupKey = $isExpense ? 'expense' : 'income';

        $cashAmount = floatval($inex['Cash'] ?? 0);
        $transferAmount = floatval($inex['Transfer'] ?? 0);
        $paymentType = '';
        if ($cashAmount > 0) {
            $paymentType = 'Cash';
        } elseif ($transferAmount > 0) {
            $paymentType = 'Transfer';
        } elseif (strpos($method, 'ເງິນສົດ') !== false || stripos($method, 'cash') !== false) {
            $paymentType = 'Cash';
            $cashAmount = $amount;
        } elseif (strpos($method, 'ເງິນໂອນ') !== false || stripos($method, 'transfer') !== false) {
            $paymentType = 'Transfer';
            $transferAmount = $pay;
        }
        $payAmount = $cashAmount > 0 ? $cashAmount : ($transferAmount > 0 ? $transferAmount : max($amount, $pay, $totalamount));

        $financialGroups[$groupKey]['totalamount'] += $totalamount;
        $financialGroups[$groupKey]['items'][] = [
            'date' => date('n/j/Y', $ts),
            'customerName' => $inex['Customer_Name'] ?? $inex['Customer'] ?? $inex['Payee'] ?? '',
            'category' => $inex['Category'] ?? '',
            'currency' => $cur,
            'paymentType' => $paymentType ?: ($method ?: 'Unknown'),
            'payAmount' => $payAmount,
            'discount' => floatval($inex['Discount'] ?? 0),
            'Total_Product' => $inex['Total_Product'] ?? '',
            'MoneyRate' => $inex['Money Rate'] ?? '',
            'cashAmount' => $cashAmount,
            'transferAmount' => $transferAmount,
            'payee' => $inex['Payee'] ?? '',
            'amount' => $amount,
            'totalamount' => $totalamount
        ];
    }
}

$rptExpenseTotal = $rptExpenseGeneral + $rptExpenseSalary;
$rptProfit = $rptIncomeTotal - $rptExpenseTotal;

$rptProfitByCur = [];
foreach ($rptCurrencies as $c) {
    $rptProfitByCur[$c] = $rptIncomeByCur[$c] - $rptExpenseByCur[$c];
}

// Sort financial groups descending by date
krsort($financialGroups);

// ---------------------------------------------------------
// ລາຍງານປຽບທຽບເດືອນ (Month-to-Month Comparison)
// ຄຳນວນລາຍຮັບສະເພາະໂດຍອີງໃສ່ PayDebt column ແລະ Sum ຂອງ Paid column
// ---------------------------------------------------------
$currentMonth = date('n');
$currentYear = date('Y');
$prevMonth = ($currentMonth == 1) ? 12 : $currentMonth - 1;
$prevYear = ($currentMonth == 1) ? $currentYear - 1 : $currentYear;

// Date ranges for current and previous month
$currentMonthStart = strtotime(date('Y-m-01'));
$currentMonthEnd = strtotime(date('Y-m-t 23:59:59'));
$prevMonthStart = strtotime($prevYear . '-' . str_pad($prevMonth, 2, '0', STR_PAD_LEFT) . '-01');
$prevMonthEnd = strtotime($prevYear . '-' . str_pad($prevMonth, 2, '0', STR_PAD_LEFT) . '-t 23:59:59');

// Initialize month comparison data
$monthComparison = [
    'sales' => ['current' => 0, 'previous' => 0, 'label' => 'ລາຍຮັບຈາກການຂາຍ'],
    'debt' => ['current' => 0, 'previous' => 0, 'label' => 'ຮັບຈາກການຈ່າຍໜີ້'],
    'total' => ['current' => 0, 'previous' => 0, 'label' => 'ລາຍຮັບທັ້ງໝົດ']
];

if (isset($payment) && is_array($payment)) {
    foreach ($payment as $pay) {
        $dateStr = $pay['DateTime'] ?? $pay['Date'] ?? '';
        $ts = strtotime($dateStr);
        if (!$ts) continue;

        $baseCurrency = strtoupper(trim($pay['Base_Currency'] ?? ''));
        if ($baseCurrency === 'THB') continue;

        $paid = floatval($pay['Paid'] ?? 0);
        $payDebt = trim($pay['PayDebt'] ?? '');

        // Determine if current or previous month
        $isCurrentMonth = ($ts >= $currentMonthStart && $ts <= $currentMonthEnd);
        $isPrevMonth = ($ts >= $prevMonthStart && $ts <= $prevMonthEnd);

        if (!$isCurrentMonth && !$isPrevMonth) continue;

        $period = $isCurrentMonth ? 'current' : 'previous';

        // Categorize based on PayDebt column
        if (!empty($payDebt)) {
            // Has PayDebt value = Debt payment
            $monthComparison['debt'][$period] += $paid;
        } else {
            // Empty PayDebt = Sales/Revenue
            $monthComparison['sales'][$period] += $paid;
        }

        // Add to total
        $monthComparison['total'][$period] += $paid;
    }
}

// Calculate percentage changes for month comparison
$monthComparisonWithTrend = [];
foreach ($monthComparison as $key => $data) {
    $current = $data['current'];
    $previous = $data['previous'];
    
    if ($previous > 0) {
        $trend = (($current - $previous) / $previous) * 100;
    } elseif ($current > 0) {
        $trend = 100;
    } else {
        $trend = 0;
    }
    
    $monthComparisonWithTrend[$key] = [
        'label' => $data['label'],
        'current' => $current,
        'previous' => $previous,
        'trend' => $trend
    ];
}

// ---------------------------------------------------------
// ລາຍງານການຂາຍສະເພາະສະກຸນນ້ຳດື່ມກາພູຜາຕາມຂະໜາດ
// Process Gaa Phu Pha water sales by size from Sell_Details
// ---------------------------------------------------------
$gaaPhuPhaBySize = [];
if (isset($selldetails) && is_array($selldetails)) {
    $gaaPhuPhaBySize = getSalesDataByProductSize($selldetails, 'ກາພູຜາ');
}

// Transform ກາພູຜາ by size data into chart format (use current month data)
$topLabs = [];
if (isset($gaaPhuPhaBySize) && is_array($gaaPhuPhaBySize)) {
    foreach ($gaaPhuPhaBySize as $size => $data) {
        // Use currentMonth as the main display value, or total if currentMonth is 0
        $displayQty = isset($data['currentMonth']) ? $data['currentMonth'] : 0;
        if ($displayQty == 0 && isset($data['prevMonth'])) {
            $displayQty = $data['prevMonth'];
        }
        $topLabs[$size] = $displayQty;
    }
    // Sort by value descending and limit to top 15
    arsort($topLabs);
    $topLabs = array_slice($topLabs, 0, 15, true);
}



?>




