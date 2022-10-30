<?php
require_once '../utils/networking.php';
require_once '../utils/request_page.php';
global $token;

$data = serverQuery($token, ['endpoint' => 'Session', 'action' => 'decodeToken', 'token' => $token]);
$current_user_id = $data['data']['user_data']['id'] ?? 0;

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
    'action' => 'peopleFollow',
    'op' => 'list',
    'target' => 'IFollow',
    'last_record' => 0,
    'id_user' => $id_user
]);
$data = $response['data'] ?? [];
?>
<div class="container-fluid">
    <div class="row mt-5">
        <div class="col-4"></div>
        <div class="col-4">
            <h2>People <?= $fullname ?> (@<?= $username ?>) Follows</h2>
            <div class="list-group">
                <?php
                foreach ($data as $item) {
                    ?>
                    <div class="row">
                        <div class="col-10">
                            <a href="#profile=<?= $item['id'] ?>"
                               class="list-group-item list-group-item-action"><?= $item['fullname'] ?>
                                (@<?= $item['username'] ?>)</a>
                        </div>
                        <div class="col-2">
                            <?php
                            if (!$item['iFollow'] && $current_user_id!==$item['id']) {
                                ?>
                                <button data-id="<?= $item['id'] ?>" class="btn btn-primary followButton">Follow
                                </button>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="col-4"></div>
    </div>
</div>
<script>
    $('.followButton').click((source) => {
        const user_id = source.target.getAttribute('data-id');
        const request = {endpoint: 'Following', action: 'follow', toFollowId: parseInt(user_id)};
        showLoading('Wait');
        serverQuery(request, (json) => showMessageRedir('Now you follow this user', 'success', `profile=${user_id}`), (json) => showMessage(json.message, 'error'));
    });
</script>