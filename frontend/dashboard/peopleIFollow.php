<?php
require_once '../utils/networking.php';
require_once '../utils/request_page.php';
global $token;
$response = serverQuery($token, [
    'endpoint' => 'Following',
    'action' => 'peopleFollow',
    'op' => 'list',
    'target' => 'IFollow',
    'last_record' => 0
]);
$data = $response['data'] ?? [];
?>
<div class="container-fluid">
    <div class="row mt-5">
        <div class="col-4"></div>
        <div class="col-4">
            <h2>People I Follow</h2>
            <div class="list-group">
                <?php
                foreach ($data as $item) {
                    ?>
                    <a href="#profile=<?= $item['id'] ?>" class="list-group-item list-group-item-action"><?=$item['fullname']?> (@<?=$item['username']?>)</a>
                    <?php
                }
                ?>
            </div>
        </div>
        <div class="col-4"></div>
    </div>
</div>
<script>
    document.querySelector('#follow').addEventListener('click', () => {
        const username = $('#username').val();
        if (username === '') {
            return;
        }
        const request = {endpoint: 'Following', action: 'follow', toFollowUserName: username};
        showLoading('Wait');
        serverQuery(request, (json) => showMessageRedir(json.message, 'success', 'home'), (json) => showMessage(json.message, 'error'));
    });
</script>