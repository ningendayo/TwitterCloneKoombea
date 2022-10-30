<div class="container-fluid">
    <div class="row mt-5">
        <div class="col-4"></div>
        <div class="col-4">
            <div class="form-group">
                <label for="fullanme">Fullname</label>
                <input id="fullanme" type="text" class="form-control">
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input id="username" type="text" class="form-control">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="text" class="form-control">
            </div>

            <div class="form-group">
                <label for="Ingrese su contraseña">Password</label>
                <input id="password" type="password" class="form-control">
            </div>
            <div class="form-group text-right">
                <button id="login" class="btn btn-primary">Sign in</button>
                <br>
                <small> <a href="#login">already Have an account?</a></small>
            </div>
        </div>
        <div class="col-4"></div>
    </div>
</div>
<script>
    $(document).ready(async function () {
        document.querySelector('#login').addEventListener('click', () => {
            const fullname = $('#fullanme').val();
            const email = $('#email').val();
            const username = $('#username').val();
            const password = $('#password').val();
            if (username === '' || password === '' || fullname === '' || email === '') {
                return;
            }
            const request = {
                endpoint: "Session",
                action: "register",
                username: username,
                pass: password,
                fullname: fullname,
                email: email
            };
            showLoading('Wait');
            serverQuery(request, (json) => {
                showMessageRedir(json.message,'success','login');
            }, (json) => {
                showMessage(json.message, 'error');
            });
        });
    });
</script>