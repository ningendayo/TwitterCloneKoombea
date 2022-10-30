<?php
require_once '../utils/networking.php';
require_once '../utils/request_page.php';
?>
<div class="container-fluid">
    <div class="row mt-5">
        <div class="col-4"></div>
        <div class="col-4">
            <div class="form-group">
                <label for="description">username</label>
                <input type="text" class="form-control" id="username">
            </div>
            <div class="form-group text-right">
                <div class="row">
                    <div class="col-4 text-left" id="condition" style="color: green"></div>
                    <div class="col-4"></div>
                    <div class="col-4">
                        <button id="follow" class="btn btn-primary">Follow</button>
                    </div>
                </div>
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
        serverQuery(request, (json) => showMessageRedir(json.message, 'success', `profile=${json.id}`), (json) => showMessage(json.message, 'error'));
    });
</script>