<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - aPanel</title>

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
                <h2 class="fw-semibold">Attendance List</h2>
                <hr class="mt-05" style="max-width: 200px;border: 2px solid; opacity: 1 ">
                <div class="d-flex">
                    <div class="mb-3">
                        <input type="date" id="dateInput" class="form-control" onchange="get_table_data()">
                    </div>
                </div>
                <div class="table-responsive p-1" id="attendance_table_container">

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
        })
        
        $("#sidebar_attendance").addClass("active text-white");

        Notiflix.Notify.init({
            position: 'center-top',
            cssAnimationStyle: 'from-top',
            showOnlyTheLastOne: true
        });

        $(document).ready(function() {
            resetDateInput();

            get_table_data();
        });

        function get_table_data() {
            var date = new Date($('#dateInput').val() + ' 00:00:00');
            var today = new Date()

            if (date > today) {
                Notiflix.Notify.warning("Can not select date later than today");
                resetDateInput();
                $("#dateInput").change();
            } else {
                $.post("get_attendants", {
                        date: $('#dateInput').val(),
                    })
                    .done(function(data) {
                        $("#attendance_table_container").html(data);
                    });
            }
        }

        function resetDateInput() {
            let now = new Date();
            const date = now.getDate() < 10 ? '0' + now.getDate() : now.getDate();
            let fixmonth = now.getMonth() + 1;
            const month = fixmonth < 10 ? '0' + fixmonth : fixmonth;
            const year = now.getFullYear();
            $("#dateInput").val(`${year}-${month}-${date}`);
        }

        function setPresent(uid, name) {
            Notiflix.Confirm.show(
                'Record Attendance',
                `Are you sure you want to mark ${name} as present?`,
                'Yes',
                'No',
                () => {
                    $.post("<?= base_url('apanel/attendance/present') ?>", {
                            uid: uid,
                        })
                        .done(function(data) {
                            if (data === "201 Created" || data === "200 OK") {
                                Notiflix.Notify.success("Attendance recorded successfully");
                            } else if (data === "500 Internal Server Error") {
                                Notiflix.Notify.failure("Internal Server Error, please try again or contact admin if error persists");
                            } else if (data === "404 Not Found") {
                                Notiflix.Notify.failure("Employee not found");
                            } else if (data === "400 Bad Request") {
                                Notiflix.Notify.failure("Parameter can not be empty");
                            }
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        });
                },
                () => {}, {
                    titleColor: '#198754',
                    okButtonBackground: '#198754',
                    borderRadius: '7px'
                }
            );
        }

        function setAbsent(id, uid, name) {
            Notiflix.Confirm.show(
                'Void Attendance Record',
                `Are you sure you want to mark ${name} as absent?`,
                'Yes',
                'No',
                () => {
                    $.post("<?= base_url('apanel/attendance/absent') ?>", {
                            id: id,
                            uid: uid
                        })
                        .done(function(data) {
                            if (data === "204 No Content") {
                                Notiflix.Notify.success("Attendance record invalidated successfully");
                            } else if (data === "500 Internal Server Error") {
                                Notiflix.Notify.failure("Internal Server Error, please try again or contact admin if error persists");
                            } else if (data === "404 Not Found") {
                                Notiflix.Notify.failure("Employee not found");
                            } else if (data === "400 Bad Request") {
                                Notiflix.Notify.failure("Parameter can not be empty");
                            }
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        });
                },
                () => {}, {
                    titleColor: '#dc3545',
                    okButtonBackground: '#dc3545',
                    borderRadius: '7px'
                }
            );
        }
    </script>
</body>

</html>