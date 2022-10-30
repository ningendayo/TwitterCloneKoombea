<?php
chdir(__DIR__);
require_once '../settings/config.php';
chdir(__DIR__);
function serverQuery(string $token, array $request, bool $flag = false)
{
    global $config;
    $init = curl_init($config['serverApi']);
    curl_setopt($init, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded', 'Cookie: token=' . $token));
    curl_setopt($init, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($init, CURLOPT_POSTFIELDS, json_encode($request));
    curl_setopt($init, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($init, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($init);
    $http_status = curl_getinfo($init, CURLINFO_HTTP_CODE);
    $result = preg_replace("/\xEF\xBB\xBF/", "", $result);
    if ($flag) {
        echo $result;
    }
    curl_close($init);
    $jsonResponse = json_decode($result, true);
    $jsonResponse['httpCode'] = $http_status;
    return $jsonResponse;
}