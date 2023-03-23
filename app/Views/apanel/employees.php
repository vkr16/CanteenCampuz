<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - aPanel </title>

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
                <h2 class="fw-semibold">Employees</h2>
                <hr class="mt-05" style="max-width: 200px;border: 2px solid; opacity: 1 ">

                <div class="table-responsive p-1" id="employees_table_container">
                    <table id="employees_table" class="table">
                        <thead>
                            <tr>
                                <th>UID</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($employees as $key => $employee) {
                            ?>

                                <tr>
                                    <td><tt><?= $employee['uid'] ?></tt></td>
                                    <td><?= $employee['name'] ?></td>
                                    <td><?= $employee['position'] ?></td>
                                    <td><button class="btn btn-success btn-sm" onclick="editEmployeeModal('<?= $employee['id'] ?>','<?= $employee['uid'] ?>','<?= $employee['name'] ?>','<?= $employee['position'] ?>')"><i class="fa-solid fa-pencil fa-fw"></i>&nbsp; Edit</button></td>
                                    <td><button class="btn btn-danger btn-sm" onclick="deleteEmployee('<?= $employee['id'] ?>','<?= $employee['name'] ?>')"><i class="fa-solid fa-trash-alt fa-fw"></i>&nbsp; Delete</button></td>
                                </tr>

                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>


    </div>

    <!-- Modal -->
    <div class="modal fade" id="editEmployee" tabindex="-1" aria-labelledby="editEmployeeLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="editEmployeeLabel">Edit Employee Details</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="employeeUid" class="form-label">UID</label>
                        <input readonly disabled type="email" class="form-control" id="employeeUid">
                    </div>
                    <div class="mb-3">
                        <label for="employeeNameInput" class="form-label">Employee Name</label>
                        <input type="text" class="form-control" id="employeeNameInput">
                    </div>
                    <div class="mb-3">
                        <label for="employeePositionInput" class="form-label">Position</label>
                        <input type="text" class="form-control" id="employeePositionInput">
                    </div>
                </div>
                <input type="text" id="hiddenEmployeeId" hidden>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="editEployeeBtn" onclick="editEmployee()">Save changes</button>
                </div>
            </div>
        </div>
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

        $("#sidebar_employees").addClass("active text-white");

        Notiflix.Notify.init({
            position: 'center-top',
            cssAnimationStyle: 'from-top',
            showOnlyTheLastOne: true
        });

        $(document).ready(function() {
            $('#employees_table').DataTable();
        });

        function deleteEmployee(id, name) {
            Notiflix.Confirm.show(
                'Delete Employee',
                `Are you sure you want to delete ${name}?`,
                'Yes',
                'No',
                () => {
                    $.post("<?= base_url('apanel/employees/delete') ?>", {
                            id: id,
                        })
                        .done(function(data) {
                            if (data === "204 No Content") {
                                Notiflix.Block.dots('body');
                                Notiflix.Notify.success('Employee data deleted successfully');
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1000);
                            } else if (data === "404 Not Found") {
                                Notiflix.Notify.failure('Employee data not found');
                            } else if (data === "500 Internal Server Error") {
                                Notiflix.Notify.failure('Internal server error, Please try again later or contact admin if error persists');
                            } else if (data === "400 Bad Request") {
                                Notiflix.Notify.failure('Parameter cannot be empty');
                            }
                        });
                },
                () => {}, {
                    titleColor: '#dc3545',
                    okButtonBackground: '#dc3545',
                    borderRadius: '7px'
                }
            );
        }

        function editEmployeeModal(id, uid, name, position) {
            $("#editEmployee").modal('show');
            $("#employeeUid").val(uid);
            $("#employeeNameInput").val(name);
            $("#employeePositionInput").val(position);
            $("#hiddenEmployeeId").val(id);
        }

        function editEmployee() {
            const id = $("#hiddenEmployeeId").val();
            const uid = $("#employeeUid").val();
            const name = $("#employeeNameInput").val();
            const position = $("#employeePositionInput").val();

            $.post("<?= base_url('apanel/employees/update') ?>", {
                    id: id,
                    uid: uid,
                    name: name,
                    position: position
                })
                .done(function(data) {
                    if (data === "200 OK") {
                        $("#editEmployee").modal('hide');
                        Notiflix.Block.dots('body');
                        Notiflix.Notify.success('Employee data updated successfully');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else if (data === "404 Not Found") {
                        Notiflix.Notify.failure('Employee data not found');
                    } else if (data === "500 Internal Server Error") {
                        Notiflix.Notify.failure('Internal server error, Please try again later or contact admin if error persists');
                    } else if (data === "400 Bad Request") {
                        Notiflix.Notify.failure('Fields cannot be empty');
                    }
                });
        }
    </script>
</body>

</html>