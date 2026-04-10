<?php
include_once __DIR__ . '/../utils/functions.php';

$sevenDaysAgo = strtotime('-7 days');
$fourteenDaysAgo = strtotime('-14 days');
$now = time();

// Calculate Claims
// ຄຳນວນຈຳນວນການຊຳລະປະກັນໄພໂດຍອີງໃສ່ຂໍ້ມູນຈາກ $sell
// ເຂື່ອມກັບຖານຂໍ້ມູນໂດຍຜ່ານການໃຊ້ຂໍ້ມູນຈາກ AppSheet API ທີ່ຖືກດຶງໄດ້ກ່ອນໜ້າ
$claimsTotal = 0;
$last7Claims = 0;
$prev7Claims = 0;

if (isset($sell) && is_array($sell)) {
    foreach ($sell as $op) {
        $insuranceId = $op['InsuranceID'] ?? '';
        $claimStatus = $op['ClaimStatus'] ?? '';

        if (!empty($insuranceId) && $claimStatus === 'ຊຳລະແລ້ວ') {
            $labTotal = floatval($op['LabOrderTotalPrice'] ?? 0);
            $medTotal = floatval($op['MedicineTotalPrice'] ?? 0);
            $amount = $labTotal + $medTotal;
            $claimsTotal += $amount;

            $opTimeStr = $op['DateTime'] ?? $op['Date'] ?? '';
            $opTime = strtotime($opTimeStr);
            if (!$opTime)
                $opTime = $now;

            if ($opTime >= $sevenDaysAgo) {
                $last7Claims += $amount;
            } elseif ($opTime >= $fourteenDaysAgo && $opTime < $sevenDaysAgo) {
                $prev7Claims += $amount;
            }
        }
    }
}
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

        $payAmount = floatval($payRow['Pay'] ?? 0);

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

// Helper to calculate top 15 items dynamically
// ຄຳນວນລາຍການທີ່ນິຍົມສຸດໂດຍອີງໃສ່ຂໍ້ມູນຈາກ $laborderdetails ແລະ $prescriptions
// ເຂື່ອມກັບຖານຂໍ້ມູນໂດຍຜ່ານການໃຊ້ຂໍ້ມູນຈາກ AppSheet API

$laborderdetails = isset($laborderdetails) ? $laborderdetails : [];
$prescriptions = isset($prescriptions) ? $prescriptions : [];

// ສຳລັບ Lab Orders: ນັບຈຳນວນແຕ່ລະລາຍການ (count rows), ຊື່ຖັນແມ່ນ LabOrderName
$topLabs = getTopItems($laborderdetails, 'LabOrderName', null);

// ສຳລັບ Prescriptions: ບວກລວມຈຳນວນໃນຖັນ Quantity, ຊື່ຖັນແມ່ນ MedicineID
$topMeds = getTopItems($prescriptions, 'MedicineID', 'Quantity');

$monthlyCases = array_fill(1, 12, 0);
$statTotal = 0;
$statWalkin = 0;
$statBooking = 0;
$statCancel = 0;
$statCompleted = 0;

// ຄຳນວນສະຖິຕິການປະຕິບັດໂດຍອີງໃສ່ຂໍ້ມູນຈາກ $sell
// ເຂື່ອມກັບຖານຂໍ້ມູນໂດຍຜ່ານການໃຊ້ຂໍ້ມູນຈາກ AppSheet API
$totalPatients = isset($patients) && is_array($patients) ? count($patients) : 0;

if (isset($sell) && is_array($sell)) {
    foreach ($sell as $op) {
        $statTotal++;
        $type = strtolower(trim($op['Type'] ?? ''));
        if (strpos($type, 'walk') !== false) {
            $statWalkin++;
        } elseif (strpos($type, 'book') !== false) {
            $statBooking++;
        }

        $status = strtolower(trim($op['Status'] ?? ''));
        if ($status === 'cancel' || $status === 'ຍົກເລີກ') {
            $statCancel++;
        }

        $appointment = trim($op['Appointment'] ?? '');
        if ($appointment === 'ສຳເລັດ') {
            $statCompleted++;
        }

        // Month parsing
        $dateStr = $op['DateTime'] ?? $op['Date'] ?? '';
        if ($dateStr) {
            $timestamp = strtotime($dateStr);
            if ($timestamp) {
                $month = (int) date('n', $timestamp);
                if ($month >= 1 && $month <= 12) {
                    $monthlyCases[$month]++;
                }
            }
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

$rptIncomeCheckup = 0;
$rptIncomeMeds = 0;
$rptIncomeGeneral = 0;

$rptExpenseBuyMeds = 0;
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
$rptIncomeCheckupByCur = array_fill_keys($rptCurrencies, 0);
$rptIncomeMedsByCur = array_fill_keys($rptCurrencies, 0);
$rptExpenseBuyMedsByCur = array_fill_keys($rptCurrencies, 0);
$rptExpenseSalaryByCur = array_fill_keys($rptCurrencies, 0);
$rptExpenseGeneralByCur = array_fill_keys($rptCurrencies, 0);

$financialGroups = [
    'income' => ['title' => 'ລາຍຮັບ (Income)', 'totalamount' => 0, 'items' => []],
    'expense' => ['title' => 'ລາຍຈ່າຍ (Expense)', 'totalamount' => 0, 'items' => []]
];

// 1. ດຶງຂໍ້ມູນຈາກ Payment
if (isset($payment) && is_array($payment)) {
    foreach ($payment as $pay) {
        $dateStr = $pay['DateTime'] ?? $pay['Date'] ?? '';
        $ts = strtotime($dateStr);
        if (!$ts)
            continue;
        if ($ts < $startTs || $ts > $endTs)
            continue;

        $amount = floatval($pay['Pay'] ?? $pay['Amount'] ?? 0);
        $method = trim($pay['PaymentMethod'] ?? '');
        $discount = floatval($pay['Discount'] ?? 0);

        $labAmount = floatval($pay['LabOrderAmount'] ?? 0);
        $medAmount = floatval($pay['MedicineAmount'] ?? 0);

        // Summaries
        $rptIncomeTotal += $amount;
        $rptDiscount += $discount;
        $rptIncomeCheckup += $labAmount;
        $rptIncomeMeds += $medAmount;

        $cur = strtoupper(trim($pay['Currency'] ?? 'LAK'));
        if ($cur === 'KIP')
            $cur = 'LAK';
        if ($cur === 'BAHT')
            $cur = 'THB';
        if ($cur === 'DOLLAR')
            $cur = 'USD';

        if (array_key_exists($cur, $rptIncomeByCur)) {
            $rptIncomeByCur[$cur] += $amount;
            $rptDiscountByCur[$cur] += $discount;
            $rptIncomeCheckupByCur[$cur] += $labAmount;
            $rptIncomeMedsByCur[$cur] += $medAmount;
            if (strpos($method, 'ສົດ') !== false || stripos($method, 'cash') !== false) {
                $rptIncomeCash += $amount;
                $rptCashByCur[$cur] += $amount;
            } elseif (strpos($method, 'ໂອນ') !== false || stripos($method, 'transfer') !== false) {
                $rptIncomeTransfer += $amount;
                $rptTransferByCur[$cur] += $amount;
            } else {
                $rptIncomeCash += $amount;
                $rptCashByCur[$cur] += $amount;
            }
        } else {
            // Default logic if unknown currency
            if (strpos($method, 'ສົດ') !== false || stripos($method, 'cash') !== false) {
                $rptIncomeCash += $amount;
            } elseif (strpos($method, 'ໂອນ') !== false || stripos($method, 'transfer') !== false) {
                $rptIncomeTransfer += $amount;
            } else {
                $rptIncomeCash += $amount;
            }
        }
    }
}

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
                if (strpos($method, 'ເງິນສົດ') !== false || stripos($method, 'cash') !== false) {
                    $rptIncomeCash += $amount;
                    $rptCashByCur[$cur] += $amount;
                } elseif (strpos($method, 'ເງິນໂອນ') !== false || stripos($method, 'transfer') !== false) {
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
            if (strpos($category, 'ຢາ') !== false || strpos($category, 'med') !== false) {
                $rptExpenseBuyMeds += $totalamount;
                if (array_key_exists($cur, $rptExpenseBuyMedsByCur))
                    $rptExpenseBuyMedsByCur[$cur] += $totalamount;
            } elseif (strpos($category, 'ເງິນເດືອນ') !== false || strpos($category, 'salary') !== false) {
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

        $financialGroups[$groupKey]['totalamount'] += $totalamount;
        $financialGroups[$groupKey]['items'][] = [
            'date' => date('n/j/Y', $ts),
            'category' => $inex['Category'] ?? '',
            'description' => $inex['Description'] ?? '',
            'currency' => $inex['Currency'] ?? 'LAK',
            'method' => $method,
            'cashAmount' => (strpos($method, 'ເງິນສົດ') !== false || stripos($method, 'cash') !== false) ? $amount : 0,
            'transferAmount' => (strpos($method, 'ເງິນໂອນ') !== false || stripos($method, 'transfer') !== false) ? $pay : 0,
            'payee' => $inex['Payee'] ?? '',
            'amount' => $amount,
            'totalamount' => $totalamount
        ];
    }
}

$rptExpenseTotal = $rptExpenseBuyMeds + $rptExpenseGeneral + $rptExpenseSalary;
$rptProfit = $rptIncomeTotal - $rptExpenseTotal;

$rptProfitByCur = [];
foreach ($rptCurrencies as $c) {
    $rptProfitByCur[$c] = $rptIncomeByCur[$c] - $rptExpenseByCur[$c];
}

// Sort financial groups descending by date
krsort($financialGroups);

// ---------------------------------------------------------
// ລະບົບລາຍງານປະກັນໄພ (Insurance Report Calculations)
// ຄຳນວນລາຍງານປະກັນໄພໂດຍອີງໃສ່ຂໍ້ມູນຈາກ $sell ແລະ $payment
// ເຂື່ອມກັບຖານຂໍ້ມູນໂດຍຜ່ານການໃຊ້ຂໍ້ມູນຈາກ AppSheet API
// ---------------------------------------------------------
// 1. Process Sell for Insurance Totals (LAK is default)
if (isset($sell) && is_array($sell)) {
    foreach ($sell as $op) {
        $InsuranceLevel = trim($op['InsuranceLevel'] ?? '');
        $providerName = trim($op['ProviderName'] ?? '');
        if (empty($providerName))
            continue; // Allow null ID if info exists? User wants company summary.

        $dateStr = $op['DateTime'] ?? $op['Date'] ?? '';
        $ts = strtotime($dateStr);
        if (!$ts || $ts < $startTs || $ts > $endTs)
            continue;

        $labTotal = floatval($op['LabOrderTotalPrice'] ?? 0);
        $medTotal = floatval($op['MedicineTotalPrice'] ?? 0);
        $totalAmt = $labTotal + $medTotal;

        $key = $providerName;
        if (!isset($rptInsuranceData[$key])) {
            $rptInsuranceData[$key] = [
                'name' => $providerName,
                'levels' => [], // Collect levels as well
                'total' => 0,
                'paid' => 0
            ];
        }
        $rptInsuranceData[$key]['total'] += $totalAmt;
        if (!empty($insuranceId) && !in_array($insuranceId, $rptInsuranceData[$key]['levels'])) {
            $rptInsuranceData[$key]['levels'][] = $insuranceId;
        }
    }
}

// 2. Process Payment for Insurance Paid Totals
if (isset($payment) && is_array($payment)) {
    foreach ($payment as $pay) {
        $providerName = trim($pay['ProviderName'] ?? '');
        $InsuranceLevel = trim($pay['InsuranceLevel'] ?? '');
        if (empty($providerName))
            continue;

        $dateStr = $pay['DateTime'] ?? '';
        $ts = strtotime($dateStr);
        if (!$ts || $ts < $startTs || $ts > $endTs)
            continue;

        $paidAmt = floatval($pay['Amount'] ?? 0);
        $cur = strtoupper(trim($pay['Currency'] ?? 'LAK'));
        if ($cur === 'KIP')
            $cur = 'LAK';
        if ($cur === 'BAHT')
            $cur = 'THB';
        if ($cur === 'DOLLAR')
            $cur = 'USD';

        $key = $providerName;
        if (!isset($rptInsuranceData[$key])) {
            $rptInsuranceData[$key] = [
                'name' => $providerName,
                'levels' => [],
                'total' => 0,
                'paid' => 0
            ];
        }
        $rptInsuranceData[$key]['paid'] += $paidAmt;
        if (!empty($InsuranceLevel) && !in_array($InsuranceLevel, $rptInsuranceData[$key]['levels'])) {
            $rptInsuranceData[$key]['levels'][] = $InsuranceLevel;
        }

        // Add to insurance details table
        if (!isset($insuranceIncomeGroups[$key])) {
            $insuranceIncomeGroups[$key] = [
                'title' => $providerName,
                'items' => []
            ];
        }

        $insuranceIncomeGroups[$key]['items'][] = [
            'date' => date('n/j/Y', $ts),
            'category' => $InsuranceLevel,
            'currency' => $cur,
            'method' => $pay['PaymentMethod'] ?? 'ບໍ່ລະບຸ',
            'totalamount' => $paidAmt,
            'description' => $InsuranceLevel,
            'ts' => $ts // keep timestamp for sorting
        ];
    }
}

// Calculate total insurance summary in LAK
$totalInsuranceAll = 0;
$totalPaidAll = 0;
foreach ($rptInsuranceData as $data) {
    $totalInsuranceAll += $data['total'];
    $totalPaidAll += $data['paid'];
}

// Sort details by date descending for each provider
foreach ($insuranceIncomeGroups as &$group) {
    usort($group['items'], function ($a, $b) {
        return $b['ts'] - $a['ts'];
    });
}
unset($group);

?>