<?php
require_once '../utils/networking.php';
require_once '../utils/request_page.php';
global $token;
$response = serverQuery($token, [
    'endpoint' => 'Following',
    'action' => 'peopleFollow',
    'op' => 'count',
    'target' => 'myFollowers'
]);
$myFollowers = $response['data'][0]['cantidad'] ?? 0;
$response = serverQuery($token, [
    'endpoint' => 'Following',
    'action' => 'peopleFollow',
    'op' => 'count',
    'target' => 'IFollow'
]);
$iFollow = $response['data'][0]['cantidad'];

$response = serverQuery($token, [
    'endpoint' => 'Tweets',
    'action' => 'feed'
]);
$tweets = $response['data'] ?? [];
?>
<div class="container-fluid">
    <div class="row mt-5">
        <div class="col-2 text-center">
            <div>Users Following Me</div>
            <h1><?= $myFollowers ?></h1>
            <small><a href="#myFollowers">View People</a></small>
            <br>
            <br>
            <div>Users I Follow</div>
            <h1><?= $iFollow ?></h1>
            <small><a href="#peopleIFollow">View People</a></small>
        </div>
        <div class="col-10">
            <h3>Feed Stack</h3>
            <?php
            foreach ($tweets as $tweet) {
                ?>
                <div class="border p-4 mt-3 rounded">
                    <h4><a href="#profile=<?=$tweet['ui']?>"><?= $tweet['fullname'] ?></a>, @<?= $tweet['username'] ?> - <?= $tweet['registered_at'] ?></h4>
                    <div><?= $tweet['description'] ?></div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

</div>