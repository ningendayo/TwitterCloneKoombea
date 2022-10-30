<?php
require_once '../utils/networking.php';
require_once '../utils/request_page.php';
?>
<div class="container-fluid">
    <div class="row mt-5">
        <div class="col-4"></div>
        <div class="col-4">
            <div class="form-group">
                <label for="description">Write a Tweet</label>
                <textarea id="description" cols="30" rows="10" class="form-control"></textarea>
            </div>
            <div class="form-group text-right">
                <div class="row">
                    <div class="col-4 text-left" id="condition" style="color: green">0/280</div>
                    <div class="col-4"></div>
                    <div class="col-4">
                        <button id="publish" class="btn btn-primary">Publish</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-4"></div>
    </div>
</div>
<script>
    document.querySelector('#description').addEventListener('keyup', () => {
        const size = $('#description').val().length;
        const condition = $('#condition');
        condition.html(`${size}/280`);
        if (size >= 280) {
            condition.css('color', 'red');
        }
    });
    document.querySelector('#publish').addEventListener('click', () => {
        const size = $('#description').val();
        const request = {endpoint: 'Tweets', action: 'publish', description: size};
        showLoading('Wait');
        serverQuery(request, (json) => showMessageRedir(json.message, 'success', 'home'), (json) => showMessage(json.message, 'error'));
    });
</script>