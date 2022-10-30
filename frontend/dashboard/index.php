<?php
require_once '../utils/networking.php';
require_once '../utils/request_page.php';
global $token;
$data = serverQuery($token, ['endpoint' => 'Session', 'action' => 'decodeToken', 'token' => $token]);
$fullname = $data['data']['user_data']['fullname'];
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
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#"><?= $fullname ?></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="#home">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#tweet">Tweet</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#follow_user">Follow User</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#logout">Logout</a>
            </li>
        </ul>
    </div>
</nav>
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
        setPage(window.location.hash);
    });
    window.addEventListener('hashchange', function (event) {
        if (event.newURL === event.oldURL) {
            return;
        }
        setPage(window.location.hash);
    }, false);

    function setPage(current_hash) {
        let urlParams = new URL(window.location.href.replace(/#/g, "?"));
        current_hash = current_hash.split('=')[0];
        let hash = current_hash.replace('#', '');
        let hashValue = urlParams.searchParams.get(hash);
        switch (current_hash) {
            case "#home":
                loadPage('home.php');
                break;
            case '#tweet':
                loadPage('tweet.php')
                break;
            case '#follow_user':
                loadPage('followuser.php');
                break;
            case '#logout':
                delete_cookie('token');
                window.location.href = '../login';
                break;
            case '#myFollowers':
                loadPage('myFollowers.php');
                break;
            case '#peopleIFollow':
                loadPage('peopleIFollow.php');
                break;
            case '#profile':
                if (hashValue) {
                    loadPage(`profile.php?id=${hashValue}`);
                } else {
                    loadPage('home');
                }
                break;
            case '#profileFollowers':
                if (hashValue) {
                    loadPage(`profileFollowers.php?id=${hashValue}`);
                } else {
                    loadPage('home');
                }
                break;
            case '#profileFollowings':
                if (hashValue) {
                    loadPage(`profileFollowings.php?id=${hashValue}`);
                } else {
                    loadPage('home');
                }
                break;
            default:
                window.location.hash = "home";
                break;
        }
    }
</script>
</body>
<div id="page-loader" class="show"></div>
</html>
