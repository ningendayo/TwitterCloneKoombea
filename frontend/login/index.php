<?php
require_once '../utils/networking.php';
$token = $_COOKIE['token'] ?? '';
$data = serverQuery($token, ['endpoint' => 'Session', 'action' => 'decodeToken', 'token' => $token]);
$status = $data['status'] ?? false;
if ($status) {
    header('Location: ../dashboard');
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title>Twitter</title>
    <meta name="description"
          content="Twitter Clone For Koombea">
    <meta name="author" content="Brandon Zambrano">
    <meta property="og:title" content="Twitter">
    <meta property="og:site_name" content="Twitter">
    <meta property="og:description"
          content="Twitter Clone For Koombea">
    <meta property="og:type" content="website">
    <meta property="og:url" content="">
    <meta property="og:image" content="">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" id="css-main" href="../assets/css/sweetalert2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
          integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

</head>
<body>
<div id="page-container">
    <main id="main-container">

    </main>
</div>

<script src="../assets/js/core/jquery.min.js"></script>
<script src="../assets/js/oneui.app.min.js"></script>
<script src="../assets/js/config.js"></script>
<script src="../assets/js/empiric_frontend_framework.js"></script>
<script src="../assets/js/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"
        integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN"
        crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"
        integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+"
        crossorigin="anonymous"></script>
<script>
    window.addEventListener('load', function (event) {
        const token = getCookie('token');
        if (token) {
            const request = {endpoint: 'Session', 'action': 'decodeToken', token: token};
            showLoading('Double checking session...');
            serverQuery(request, (json) => window.location.href = '../dashboard', (json) => showMessage(json.message, 'error'));
        }
        setPage(window.location.hash);
    });
    window.addEventListener('hashchange', function (event) {
        if (event.newURL === event.oldURL) {
            return;
        }
        setPage(window.location.hash);
    }, false);

    function setPage(current_hash) {
        switch (current_hash) {
            case "#login":
                loadPage('login.php');
                break;
            case '#register':
                loadPage('register.php');
                break;
            default:
                window.location.hash = "login";
                break;
        }
    }
</script>
</body>
<div id="page-loader" class="show"></div>
</html>
