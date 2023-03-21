<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= base_url('public/assets/styles/style.css') ?>">
    <link rel="shortcut icon" href="<?= base_url('public/assets/images/icon.png') ?>" type="image/x-icon">
</head>

<body style="background-color: #dddddd;" class="font-ubuntu">

    <div class="container d-flex justify-content-center align-items-center" style="height: 100vh">
        <div class="card col-md-6 col-lg-4 col-xl-3 mx-auto rounded-1 shadow">
            <div class="card-body">
                <div class="text-center">
                    <img src="<?= base_url('/public/assets/images/apanel.png') ?>" style="width:80%">
                </div>
                <form class="mt-5">
                    <div class="mb-3">
                        <label for="usernameInput" class="form-label">Username</label>
                        <input type="text" class="form-control rounded-1" id="usernameInput">
                    </div>
                    <div class="mb-4">
                        <label for="passwordInput" class="form-label">Password</label>
                        <input type="password" class="form-control rounded-1" id="passwordInput">
                    </div>
                    <div class="mb-3 d-grid">
                        <button type="button" role="button" class="btn btn-primary rounded-1" onclick="submitLogin()">Login</button>
                    </div>
                </form>
                <small class="text-center font-nunito-sans text-muted">&copy; 2023 aPanel by AkuOnline</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <script src="<?= base_url('public/assets/scripts/notiflix-aio-3.2.6.min.js') ?>"></script>

    <script>
        Notiflix.Notify.init({
            position: 'center-top',
            cssAnimationStyle: 'from-top',
            showOnlyTheLastOne: true
        });

        function submitLogin() {
            const username = $('#usernameInput').val();
            const password = $('#passwordInput').val();

            $.post("login", {
                    username: username,
                    password: password
                })
                .done(function(data) {
                    if (data === "200 OK") {
                        window.location.href = "<?= base_url('apanel/attendance') ?>";
                    } else if (data === "404 Not Found") {
                        Notiflix.Notify.failure('Invalid administrator username');
                    } else if (data === "400 Bad Request") {
                        Notiflix.Notify.failure('Username and password cannot be empty');
                    } else if (data === "401 Unauthorized") {
                        Notiflix.Notify.failure('Password incorrect');
                    }
                });
        }

        $(document).ready(function() {
            $('.form-control').keydown(function(e) {
                if (e.keyCode == 13) {
                    e.preventDefault();
                    submitLogin();
                }
            });
        });
    </script>
</body>

</html>