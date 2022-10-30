<div class="container-fluid">
    <div class="row mt-5">
        <div class="col-4"></div>
        <div class="col-4">
            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" type="text" class="form-control">
            </div>
            <div class="form-group">
                <label for="Ingrese su contraseña">Password</label>
                <input id="password" type="password" class="form-control">
            </div>
            <div class="form-group text-right">
                <button id="login" class="btn btn-primary">Log in</button>
                <br>
                <small><a href="#register">Register here</a></small>
            </div>
        </div>
        <div class="col-4"></div>
    </div>
</div>
<script>
    $(document).ready(async function () {
        document.querySelector('#login').addEventListener('click', () => {
            const username = $('#username').val();
            const password = $('#password').val();
            if (username === '' || password === '') {
                return;
            }
            const request = {endpoint: "Session", action: "login", username: username, password: password};
            showLoading('Wait');
            serverQuery(request, (json) => {
                Swal.close();
                setCookie('token', json.token, 0);
                window.location.href = '../dashboard';
            }, (json) => {
                showMessage(json.message, 'error');
            });
        });
    });
</script>