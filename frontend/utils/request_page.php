<?php

function OnTokenFail($refreshTimeOut=5,$httpCode=0,$errorMessage='')
{
    global $config;
    global $authHandler;
    if($httpCode !== 0){
        if($httpCode===401){
            if($errorMessage){
                $authHandler->printErrorJson(["status"=>false,"message"=>$errorMessage]);
            }
            header("Refresh:$refreshTimeOut; url={$config['login']}");
        }
    }else{
        if($errorMessage){
            $authHandler->printErrorJson(["status"=>false,"message"=>$errorMessage]);
        }
        header("Refresh:$refreshTimeOut; url={$config['login']}");
    }
    die();
}
$token = '';
if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];
    $response = serverQuery($token, array(
        'endpoint' => 'Session',
        'action' => 'decodeToken',
        'token' => $token
    ));
    $responseHttpCode = $response['httpCode'] ?? 0;
    $responseMessage = $response['message'] ?? '';
    if (!isset($response['status'])) {
        OnTokenFail(null,$responseHttpCode,$responseMessage);
    } else {
        if (!$response['status']) {
            OnTokenFail(null,$responseHttpCode,$responseMessage);
        }
    }
} else {
    OnTokenFail(0);
}