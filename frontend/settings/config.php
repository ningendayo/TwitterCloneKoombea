<?php
$host = $_SERVER['HTTP_HOST'];
$protocol = isset($_SERVER["HTTPS"]) ? 'https' : 'http';
$folderStructure = ($protocol == 'https') ? 'TwitterCloneKoombea' : "TwitterCloneKoombea";
$domain = "$protocol://$host/$folderStructure";
$config = [
    'serverApi' => "$domain/backend/api.php",
    'login' => "$domain/frontend/login"
];