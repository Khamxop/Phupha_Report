<?php

/**
 * Format currency with appropriate symbols and styling
 * ຟັງຊັນນີ້ບໍ່ເຂື່ອມກັບຖານຂໍ້ມູນໂດຍກົງ, ແຕ່ເປັນ utility ສໍາລັບການຈັດຮູບສະກຸນເງິນ
 */
function formatCurrency($amount, $currency = 'LAK')
{
    $prefix = '';
    $suffix = '';
    $class = '';

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
