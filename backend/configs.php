<?php
$host = $_SERVER['HTTP_HOST'];
$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
$file_system_home = 'C:/xampp/htdocs/TwitterCloneKoombea';
$folder = "TwitterCloneKoombea";
$domain = "$protocol://$host/$folder";
$configs = [
    'app' => [
        'app_name'=>'Twitter Clone',
        'company' => 'Koombea',
        'defaultTimeZone' => 'America/Guayaquil',
        'provider' => 'Koombea',
        'url_provider' => 'koombea.com',
        'environment' => '[PRODUCCIÓN]'
    ],
    'system' => [
        'tokenPath' => $domain,
    ],
    'database' => [
        'engine' => 'mysql',
        'host' => ['host', '127.0.0.1'],
        'database' => ['dbname', 'twitter'],
        'username' => 'root',
        'password' => '',
    ],
    'mongodb' => [
        'host' => '',
        'port' => 0,
        'user' => '',
        'password' => '',
        'adminDatabase' => '',
        'sysDatabase' => ''
    ],
    'smtp' => [
        'host' => '',
        'port' => 0,
        'account_name' => '',
        'address' => '',
        'password' => ''
    ],
    'paths' => [
        'log' => '',
        'resources' => "$file_system_home/backend/resources"
    ]
];