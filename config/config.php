<?php
// config/config.php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();

$appId = $_ENV['APPSHEET_APP_ID'];
$accessKey = $_ENV['APPSHEET_ACCESS_KEY'];
$appName = $_ENV['APPSHEET_APP_NAME'];
//ດຶງຂໍ້ມູນເທື່ອລະ 1 table
/**
 * ດຶງຂໍ້ມູນຈາກຕາຕະລາງດຽວໃນ AppSheet
 * ເຂື່ອມກັບຖານຂໍ້ມູນໂດຍຜ່ານ AppSheet API ໂດຍໃຊ້ cURL
 * @param string $tableName ຊື່ຕາຕະລາງ
 * @param string $appId AppSheet App ID
 * @param string $accessKey AppSheet Access Key
 * @param array $filters ເງື່ອນໄຂການກັ່ນຕອງ (optional)
 * @return array ຂໍ້ມູນທີ່ດຶງໄດ້ ຫຼື array ວ່າງຖ້າມີຂໍ້ຜິດພາດ
 */
function getAppSheetData($tableName, $appId, $accessKey, $filters = [])
{
    $url = "https://api.appsheet.com/api/v2/apps/$appId/tables/$tableName/Action";

    $data = [
        "Action" => "Find",
        "Properties" => [
            "Locale" => "en-US",
            "Timezone" => "UTC"
        ],
        "Rows" => $filters
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            'ApplicationAccessKey: ' . $accessKey,
            'Content-Type: application/json'
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($error) {
        error_log("cURL Error ($tableName): " . $error);
        return [];
    }

    if ($httpCode !== 200) {
        error_log("AppSheet API Error ($tableName): HTTP $httpCode | Response: $response");
        return [];
    }

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : [];
}

//ດຶງຂໍ້ມູນຫຼາຍ table
/**
 * ດຶງຂໍ້ມູນຈາກຫຼາຍຕາຕະລາງໃນ AppSheet ໂດຍໃຊ້ multi cURL
 * ເຂື່ອມກັບຖານຂໍ້ມູນໂດຍຜ່ານ AppSheet API ເພື່ອປະສິດທິພາບ
 * @param array $requests ອາເຣຂອງຄໍາຮ້ອງຂໍ, ແຕ່ລະອັນມີ 'tableName' ແລະ 'filters' (optional)
 * @param string $appId AppSheet App ID
 * @param string $accessKey AppSheet Access Key
 * @return array ຜົນຂອງການດຶງຂໍ້ມູນສໍາລັບແຕ່ລະຄໍາຮ້ອງຂໍ
 */
function getAppSheetDataMulti($requests, $appId, $accessKey)
{
    $mh = curl_multi_init();
    $curl_array = [];
    $results = [];

    foreach ($requests as $key => $req) {
        $tableName = $req['tableName'];
        $filters = $req['filters'] ?? [];

        $url = "https://api.appsheet.com/api/v2/apps/$appId/tables/$tableName/Action";

        $data = [
            "Action" => "Find",
            "Properties" => [
                "Locale" => "en-US",
                "Timezone" => "UTC"
            ],
            "Rows" => $filters
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => [
                'ApplicationAccessKey: ' . $accessKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30, // Updated timeout
            CURLOPT_CONNECTTIMEOUT => 5
        ]);

        $curl_array[$key] = $ch;
        curl_multi_add_handle($mh, $ch);
    }

    $running = null;
    do {
        curl_multi_exec($mh, $running);
        curl_multi_select($mh);
    } while ($running > 0);

    foreach ($curl_array as $key => $ch) {
        $response = curl_multi_getcontent($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        if ($error) {
            error_log("Multi cURL Error for $key: " . $error);
            $results[$key] = [];
        } elseif ($httpCode !== 200) {
            error_log("Multi AppSheet API Error: HTTP $httpCode | Response: $response");
            $results[$key] = [];
        } else {
            $decoded = json_decode($response, true);
            $results[$key] = is_array($decoded) ? $decoded : [];
        }

        curl_multi_remove_handle($mh, $ch);
    }

    curl_multi_close($mh);
    return $results;
}

// ບັນທຶກ (Add, Edit, Delete)
/**
 * ປະຕິບັດການປ່ຽນແປງຂໍ້ມູນໃນ AppSheet (Add, Edit, Delete)
 * ເຂື່ອມກັບຖານຂໍ້ມູນໂດຍຜ່ານ AppSheet API ໂດຍໃຊ້ cURL
 * @param string $tableName ຊື່ຕາຕະລາງ
 * @param string $action ການປະຕິບັດ ('Add', 'Edit', 'Delete')
 * @param array $rows ອາເຣຂອງແຖວຂໍ້ມູນເພື່ອປ່ຽນແປງ
 * @param string $appId AppSheet App ID
 * @param string $accessKey AppSheet Access Key
 * @return array ຜົນຂອງການປະຕິບັດ, ລວມທັງ 'success' ແລະ 'response' ຫຼື 'error'
 */
function executeAppSheetAction($tableName, $action, $rows, $appId, $accessKey)
{
    $url = "https://api.appsheet.com/api/v2/apps/$appId/tables/$tableName/Action";

    $data = [
        "Action" => $action,
        "Properties" => [
            "Locale" => "en-US",
            "Timezone" => "UTC"
        ],
        "Rows" => is_array($rows) && isset($rows[0]) ? $rows : [$rows]
    ];

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            "ApplicationAccessKey: " . $accessKey,
            "Content-Type: application/json"
        ],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($error) {
        error_log("cURL Error ($action $tableName): " . $error);
        return ['success' => false, 'error' => $error];
    }

    if ($httpCode !== 200 && $httpCode !== 201) {
        error_log("AppSheet API Error ($action $tableName): HTTP $httpCode | Response: $response");
        return ['success' => false, 'error' => "HTTP Code: $httpCode", 'details' => $response];
    }

    return ['success' => true, 'response' => json_decode($response, true)];
}
?>