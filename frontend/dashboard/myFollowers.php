<?php
require_once '../utils/networking.php';
require_once '../utils/request_page.php';
global $token;
$response = serverQuery($token, [
    'endpoint' => 'Following',
    'action' => 'peopleFollow',
    'op' => 'list',
    'target' => 'myFollowers',
    'last_record' => 0
]);
$data = $response['data'] ?? [];
?>
<div class="container-fluid">
    <div class="row mt-5">
        <div class="col-4"></div>
        <div class="col-4">
            <h2>Users Following Me</h2>
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
                            if (!$item['iFollow']) {
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