<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summary - aPanel</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= base_url('public/assets/styles/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/assets/library/datatables-1.12.1/datatables.min.css') ?>" />
    <link rel="shortcut icon" href="<?= base_url('public/assets/images/icon.png') ?>" type="image/x-icon">

</head>

<body>
    <div class="d-flex font-nunito-sans bg-light">
        <section id="sidebar-section">
            <?= $this->include("apanel/components/sidebar") ?>
        </section>
        <section class="vh-100 w-100 scrollable-y" id="topbar-section">
            <!-- Topbar -->
            <?= $this->include("apanel/components/topbar") ?>

            <div class="mx-2 mx-lg-5 my-4 px-3 py-2">
                <h2 class="fw-semibold">Configuration</h2>
                <hr class="mt-05" style="max-width: 200px;border: 2px solid; opacity: 1 ">

                <div class="row">
                    <div class="col-md-4 px-2 my-2 mx-0">
                        <div class="card rounded-0">
                            <div class="card-header rounded-0 bg-primary text-white">
                                <h5 class="mb-0">Firmware Update</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="firmwareUrl">Update source url</label>
                                    <input type="url" name="firmwareUrl" id="firmwareUrl" class="form-control mt-2 rounded-0 mb-3" placeholder="https://server.com/update.bin" value="<?= $update_url ?>">

                                    <div class="d-flex flex-wrap justify-content-between gap-3">
                                        <button class="btn btn-primary rounded-0 " onclick="setSource()"><i class="fa-solid fa-link"></i>&nbsp; Set Source</button>

                                        <span id="updateMode">
                                            <button id="updateModeButton" class="btn btn-outline-danger rounded-0 " onclick="enableUpdate()"><i class="fa-solid fa-ban"></i> &nbsp; Update Mode : OFF</button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 px-2 my-2">
                        <div class="card rounded-0">
                            <div class="card-header rounded-0 bg-primary text-white">
                                <h5 class="mb-0">Master Tag</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="masterTag" class="form-label">Master Tag UID</label>
                                    <input type="text" class="form-control rounded-0 mb-3" id="masterTag">
                                    <span>
                                        <button class="btn btn-primary rounded-0 " onclick="submitMasterTag()"><i class="fa-brands fa-nfc-symbol"></i>&nbsp; Set Master Tag</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 px-2 my-2">
                        <div class="card rounded-0">
                            <div class="card-header rounded-0 bg-primary text-white">
                                <h5 class="mb-0">Register Mode</h5>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <div class="mb-3">
                                    <button class="btn btn-outline-danger rounded-0" id="registerModeButton"><i class="fa-solid fa-ban"></i>&nbsp; Register Mode : OFF</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>


    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
    <script src="<?= base_url('public/assets/scripts/notiflix-aio-3.2.6.min.js') ?>"></script>
    <script src="https://kit.fontawesome.com/e4b7aab4db.js" crossorigin="anonymous"></script>
    <script src="<?= base_url('public/assets/library/datatables-1.12.1/datatables.min.js') ?>"></script>

    <script>
        $(document).ready(() => {
            getMasterTag();
            getUpdateMode();
            getRegisterMode();
            setInterval(() => {
                getUpdateMode();
                getRegisterMode();
            }, 2000);
        })

        $("#sidebar_config").addClass("active text-white");

        Notiflix.Notify.init({
            position: 'center-top',
            cssAnimationStyle: 'from-top',
            showOnlyTheLastOne: true
        });

        function getUpdateMode() {
            $.post("<?= base_url('api/v1/update/mode/get') ?>", {
                    "api_key": "<?= $_ENV['API_KEY'] ?>"
                })
                .done((data) => {
                    const update_mode = data['data']['update_mode'];
                    if (update_mode == "0") {
                        $("#updateModeButton").addClass("btn-outline-danger").removeClass("btn-outline-primary").removeAttr("onclick").attr("onclick", "enableUpdate()").html('<i class="fa-solid fa-ban"></i> &nbsp; Update Mode : OFF');
                    } else if (update_mode == "1") {
                        $("#updateModeButton").addClass("btn-outline-primary").removeClass("btn-outline-danger").removeAttr("onclick").attr("onclick", "disableUpdate()").html('<i class="fa-solid fa-wifi fa-beat-fade"></i> &nbsp; Update Mode : ON');
                    }
                })
        }

        function disableUpdate() {
            $.post("<?= base_url('api/v1/update/mode/disable') ?>", {
                    "api_key": '<?= $_ENV['API_KEY'] ?>'
                })
                .done((data) => {
                    Notiflix.Notify.success("Remote firmware update disabled successfully!")
                    getUpdateMode();
                    getUpdateUrl();
                })
        }

        function enableUpdate() {
            $.post("<?= base_url('api/v1/update/mode/enable') ?>", {
                    "api_key": '<?= $_ENV['API_KEY'] ?>'
                })
                .done((data) => {
                    Notiflix.Notify.success("Remote firmware update enabled successfully!")
                    getUpdateMode();
                    getUpdateUrl();
                })
        }

        function setSource() {
            $.post("<?= base_url('api/v1/update/url/set') ?>", {
                    "api_key": '<?= $_ENV['API_KEY'] ?>',
                    "update_url": $("#firmwareUrl").val(),
                })
                .done((data) => {
                    if (data["status"] == "200 OK") {
                        Notiflix.Notify.success("Firmware url updated successfully")
                        getUpdateUrl();
                    } else {
                        getUpdateUrl();
                    }
                })
        }

        function getUpdateUrl() {
            $.post("<?= base_url('api/v1/update/url/get') ?>", {
                    "api_key": '<?= $_ENV['API_KEY'] ?>',
                })
                .done((data) => {
                    const update_url = data['data']['update_url'];
                    $("#firmwareUrl").val(update_url);
                })

        }

        function getMasterTag() {
            $.post("<?= base_url('api/v1/mastertag') ?>", {
                    api_key: '<?= $_ENV['API_KEY'] ?>'
                })
                .done((data) => {
                    $('#masterTag').val(data["data"]["uid"]);
                })
        }

        function getRegisterMode() {
            $.post("<?= base_url('api/v1/register/mode/get') ?>", {
                    api_key: '<?= $_ENV['API_KEY'] ?>'
                })
                .done((data) => {
                    const registerMode = data['data']['register_mode'];
                    if (registerMode == "0") {
                        $("#registerModeButton").addClass("btn-outline-danger").removeClass("btn-outline-primary").removeAttr("onclick").attr("onclick", "enableRegister()").html('<i class="fa-solid fa-ban"></i> &nbsp; Register Mode : OFF');
                    } else if (registerMode == "1") {
                        $("#registerModeButton").addClass("btn-outline-primary").removeClass("btn-outline-danger").removeAttr("onclick").attr("onclick", "disableRegister()").html('<i class="fa-solid fa-user-plus fa-beat-fade"></i> &nbsp; Register Mode : ON');
                    }
                })
        }

        function enableRegister() {
            $.post("<?= base_url('api/v1/register/mode/enable') ?>", {
                    "api_key": '<?= $_ENV['API_KEY'] ?>'
                })
                .done((data) => {
                    Notiflix.Notify.success("Register mode enabled successfully!")
                    getRegisterMode();
                })
        }

        function disableRegister() {
            $.post("<?= base_url('api/v1/register/mode/disable') ?>", {
                    "api_key": '<?= $_ENV['API_KEY'] ?>'
                })
                .done((data) => {
                    Notiflix.Notify.success("Register mode disabled successfully!")
                    getRegisterMode();
                })
        }

        function submitMasterTag() {
            const newTag = $("#masterTag").val();

            if (newTag == "") {
                Notiflix.Notify.failure("Master tag uid is required");
            } else {
                $.post("<?= base_url('apanel/changemaster') ?>", {
                        uid: newTag,
                    })
                    .done((data) => {
                        if (data == "200") {
                            $('#changeMasterTag').modal('hide');

                            Notiflix.Notify.success("Master tag changed successfully");
                        } else if (data == "404") {
                            Notiflix.Notify.failure("Admin account not found");
                        } else if (data == "500") {
                            Notiflix.Notify.failure("Internal Server Error");
                        }
                    })
            }
        }
    </script>
</body>

</html>