<?php
require_once '../utils/networking.php';
require_once '../utils/request_page.php';
global $token;

$id_user = $_GET['id'] ?? 0;
$id_user = intval($id_user);

$response = serverQuery($token, [
    'endpoint' => 'Users',
    'action' => 'getUserInfo',
    'id' => $id_user
]);

$username = $response['data']['username'] ?? '';
$fullname = $response['data']['fullname'] ?? '';

$response = serverQuery($token, [
    'endpoint' => 'Following',
    'action' => 'doIFollow',
    'id_user' => $id_user
]);

$ImFollowingThisUser = $response['exists'] ?? false;

$response = serverQuery($token, [
    'endpoint' => 'Following',
    'action' => 'peopleFollow',
    'op' => 'count',
    'target' => 'myFollowers',
    'id_user' => $id_user
]);
$myFollowers = $response['data'][0]['cantidad'] ?? 0;
$response = serverQuery($token, [
    'endpoint' => 'Following',
    'action' => 'peopleFollow',
    'op' => 'count',
    'target' => 'IFollow',
    'id_user' => $id_user
]);
$iFollow = $response['data'][0]['cantidad'];

$response = serverQuery($token, [
    'endpoint' => 'Tweets',
    'action' => 'getUserTweets',
    'userId' => $id_user
]);
$tweets = $response['data'] ?? [];
?>
<div class="container-fluid">
    <div class="text-left mt-4">
        <h3><?= $fullname ?></h3>
        <h4>@<?= $username ?></h4>
    </div>
    <hr>
    <div class="row mt-5">
        <div class="col-2 text-center">
            <div>Users Following this profile</div>
            <h1><?= $myFollowers ?></h1>
            <small><a href="#profileFollowers=<?= $id_user ?>">View People</a></small>
            <br>
            <br>
            <div>This Profile is following</div>
            <h1><?= $iFollow ?></h1>
            <small><a href="#profileFollowings=<?= $id_user ?>">View People</a></small>
            <br>
            <br>
            <?php
            if ($ImFollowingThisUser) {
                ?>
                <button data-id="<?= $id_user ?>" id="unfollow" class="btn btn-danger">UnFollow</button>
                <?php
            } else {
                ?>
                <button data-id="<?= $id_user ?>" id="follow" class="btn btn-primary">Follow</button>
                <?php
            }
            ?>
        </div>
        <div class="col-10">
            <?php
            foreach ($tweets as $tweet) {
                ?>
                <div class="border p-2 mt-3">
                    <h4><?= $fullname ?>, @<?= $username ?> - <?= $tweet['registered_at'] ?></h4>
                    <div><?= $tweet['description'] ?></div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<script>
    $('#follow').click((source) => {
        const user_id = source.target.getAttribute('data-id');
        const request = {endpoint: 'Following', action: 'follow', toFollowId: parseInt(user_id)};
        showLoading('Wait');
        serverQuery(request, (json) => showMessageRedir('Now you follow this user', 'success', 'home'), (json) => showMessage(json.message, 'error'));
    });


    $('#unfollow').click((source) => {
        const user_id = source.target.getAttribute('data-id');
        const request = {endpoint: 'Following', action: 'unFollow', id_user: parseInt(user_id)};
        showLoading('Wait');
        serverQuery(request, (json) => showMessageRedir(json.message, 'success', 'home'), (json) => showMessage(json.message, 'error'));
    });

</script>