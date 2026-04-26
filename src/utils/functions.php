<?php

/**
 * Format currency with appropriate symbols and styling
 * ຟັງຊັນນີ້ບໍ່ເຂື່ອມກັບຖານຂໍ້ມູນໂດຍກົງ, ແຕ່ເປັນ utility ສໍາລັບການຈັດຮູບສະກຸນເງິນ
 */
function formatCurrency($amount, $currency = 'LAK')
{
    $prefix = '';
    $suffix = '';

    switch (strtoupper($currency)) {
        case 'LAK':
        case 'KIP':
            $prefix = '₭';
            break;
        case 'THB':
        case 'BAHT':
            $prefix = '฿';
            break;
        case 'USD':
        case 'DOLLAR':
            $prefix = '$';
            break;
    }

    return $prefix . number_format($amount, 0) . $suffix;
}

/**
 * Generate HTML for trend badges
 * ຟັງຊັນນີ້ບໍ່ເຂື່ອມກັບຖານຂໍ້ມູນໂດຍກົງ, ແຕ່ເປັນ utility ສໍາລັບການສ້າງ HTML ສໍາລັບ trend
 */
function getTrendHtml($current, $previous, $chartColor)
{
    if ($previous > 0) {
        $trend = (($current - $previous) / $previous) * 100;
        $trend = round($trend);
    } elseif ($current > 0) {
        $trend = 100;
    } else {
        $trend = 0;
    }

    $class = $trend >= 0 ? 'trend-up' : 'trend-down';
    $sign = $trend > 0 ? '+' : '';

    return '<div class="kpi-trend ' . $class . '">
                <span class="badge">' . $sign . $trend . '%</span>
                <small>in last 7 Days</small>
                <div class="trend-chart-stub ' . $chartColor . '"></div>
            </div>';
}

/**
 * Helper to calculate top items dynamically from data array
 * ຟັງຊັນນີ້ບໍ່ເຂື່ອມກັບຖານຂໍ້ມູນໂດຍກົງ, ແຕ່ປະມວນຜົນຂໍ້ມູນຈາກອາເຣທີ່ໄດ້ຮັບມາເພື່ອຄົ້ນຫາລາຍການທີ່ນິຍົມສຸດ
 */
function getTopItems($dataArray, $nameCol, $qtyCol = null)
{
    if (!is_array($dataArray) || empty($dataArray))
        return [];

    $counts = [];

    foreach ($dataArray as $row) {
        $itemName = $row[$nameCol] ?? 'Unknown';
        if (trim($itemName) === '')
            continue;

        $qty = 1;
        if ($qtyCol && isset($row[$qtyCol])) {
            $qty = floatval($row[$qtyCol]);
        }

        if (!isset($counts[$itemName])) {
            $counts[$itemName] = 0;
        }
        $counts[$itemName] += $qty;
    }

    arsort($counts);
    return array_slice($counts, 0, 15, true);
}

/**
 * Process sales data by product size and month comparison
 * ປະມວນຜົນຂໍ້ມູນການຂາຍໂດຍຊອກຫາສະກຸນນ້ຳດື່ມກາພູຜາ ແຍກຕາມຂະໜາດ ແລະປຽບທຽບເດືອນ
 */
function getSalesDataByProductSize($sellData, $productName = 'ກາພູຜາ')
{
    if (!is_array($sellData) || empty($sellData))
        return [];

    $currentMonth = date('n');
    $currentYear = date('Y');
    $prevMonth = ($currentMonth == 1) ? 12 : $currentMonth - 1;
    $prevYear = ($currentMonth == 1) ? $currentYear - 1 : $currentYear;

    $sizeData = [];

    foreach ($sellData as $row) {
        $product = trim($row['Product_Name'] ?? '');
        $size = trim($row['Size'] ?? '');
        $qty = floatval($row['Qty'] ?? 0);

        // ກວດສອບວ່າເປັນສະກຸນນ້ຳດື່ມກາພູຜາ
        if (strpos($product, $productName) === false && strpos($productName, $product) === false) {
            continue;
        }

        // ຖ້າບໍ່ມີ Size ໃຫ້ຂ້າມ
        if (empty($size))
            continue;

        if (!isset($sizeData[$size])) {
            $sizeData[$size] = [
                'name' => $size,
                'currentMonth' => 0,
                'prevMonth' => 0,
                'count' => 0
            ];
        }

        // ກວດສອບວັນທີ
        $dateStr = $row['Date'] ?? $row['DateTime'] ?? '';
        $timestamp = strtotime($dateStr);

        if ($timestamp) {
            $month = date('n', $timestamp);
            $year = date('Y', $timestamp);

            if ($month == $currentMonth && $year == $currentYear) {
                $sizeData[$size]['currentMonth'] += $qty;
            } elseif ($month == $prevMonth && $year == $prevYear) {
                $sizeData[$size]['prevMonth'] += $qty;
            }
        }

        $sizeData[$size]['count']++;
    }

    // ຄຳນວນ trend ສໍາລັບແຕ່ລະ size
    foreach ($sizeData as $size => &$data) {
        if ($data['prevMonth'] > 0) {
            $data['trend'] = (($data['currentMonth'] - $data['prevMonth']) / $data['prevMonth']) * 100;
        } elseif ($data['currentMonth'] > 0) {
            $data['trend'] = 100;
        } else {
            $data['trend'] = 0;
        }
    }

    return $sizeData;
}


/**
 * Process sales data by product size and month comparison
 * ປະມວນຜົນຂໍ້ມູນການຂາຍໂດຍຊອກຫາສະກຸນນ້ຳດື່ມກາພູຜາ ແຍກຕາມຂະໜາດ ແລະປຽບທຽບເດືອນ
 */
function getSumPriceByProductSize($sellData, $productName = 'ກາພູຜາ')
{
    if (!is_array($sellData) || empty($sellData))
        return [];

    $currentMonth = date('n');
    $currentYear = date('Y');
    $prevMonth = ($currentMonth == 1) ? 12 : $currentMonth - 1;
    $prevYear = ($currentMonth == 1) ? $currentYear - 1 : $currentYear;

    $sizeData = [];

    foreach ($sellData as $row) {
        $product = trim($row['Product_Name'] ?? '');
        $size = trim($row['Size'] ?? '');
        $SubTotal_Detail = ($row['SubTotal_Detail'] ?? 0);

        // ກວດສອບວ່າເປັນສະກຸນນ້ຳດື່ມກາພູຜາ
        if (strpos($product, $productName) === false && strpos($productName, $product) === false) {
            continue;
        }

        // ຖ້າບໍ່ມີ Size ໃຫ້ຂ້າມ
        if (empty($size))
            continue;

        if (!isset($sizeData[$size])) {
            $sizeData[$size] = [
                'name' => $size,
                'currentMonth' => 0,
                'prevMonth' => 0,
                'count' => 0
            ];
        }

        // ກວດສອບວັນທີ
        $dateStr = $row['Date'] ?? $row['DateTime'] ?? '';
        $timestamp = strtotime($dateStr);

        if ($timestamp) {
            $month = date('n', $timestamp);
            $year = date('Y', $timestamp);

            if ($month == $currentMonth && $year == $currentYear) {
                $sizeData[$size]['currentMonth'] += $SubTotal_Detail;
            } elseif ($month == $prevMonth && $year == $prevYear) {
                $sizeData[$size]['prevMonth'] += $SubTotal_Detail;
            }
        }

        $sizeData[$size]['count']++;
    }

    // ຄຳນວນ trend ສໍາລັບແຕ່ລະ size
    foreach ($sizeData as $size => &$data) {
        if ($data['prevMonth'] > 0) {
            $data['trend'] = (($data['currentMonth'] - $data['prevMonth']) / $data['prevMonth']) * 100;
        } elseif ($data['currentMonth'] > 0) {
            $data['trend'] = 100;
        } else {
            $data['trend'] = 0;
        }
    }

    return $sizeData;
    
}

