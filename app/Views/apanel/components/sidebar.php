<div class="offcanvas-lg offcanvas-start custom-sidebar" data-bs-scroll="true" tabindex="-1" id="sidebarPanelOffCanvas" style="overflow-y: auto">
    <div class="d-flex flex-column flex-shrink-0 py-3 bg-white" style="width: auto; height: 100vh;">
        <a href="<?= base_url() ?>" class=" px-3 d-flex align-items-center mb-3 mb-md-0 me-md-auto link-dark text-decoration-none">
            <img src="<?= base_url('public/assets/images/apanel.png') ?>" style="width:50%">
        </a>
        <br>
        <ul class="nav nav-pills flex-column mb-auto border-top">
            <li>
                <a href="<?= base_url('apanel/attendance') ?>" class="nav-link rounded-0" id="sidebar_attendance">
                    <i class="fa-solid fa-list-check fa-fw"></i>&emsp;
                    Attendance
                </a>
            </li>
            <li>
                <a href="<?= base_url('apanel/employees') ?>" class="nav-link rounded-0" id="sidebar_employees">
                    <i class="fa-solid fa-people-group fa-fw"></i>&emsp;
                    Employees
                </a>
            </li>
            <li>
                <a href="<?= base_url('apanel/summary') ?>" class="nav-link rounded-0" id="sidebar_summary">
                    <i class="fa-solid fa-file-lines fa-fw"></i>&emsp;
                    Summary
                </a>
            </li>
            <li>
                <a href="<?= base_url('apanel/config') ?>" class="nav-link rounded-0" id="sidebar_config">
                    <i class="fa-solid fa-gears fa-fw"></i>&emsp;
                    Configuration
                </a>
            </li>
        </ul>
        <hr>
        <div class="px-3 dropup">
            <span class="d-flex align-items-center link-dark text-decoration-none" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-user-tie me-3"></i>
                <p class="mb-0"><?= $_SESSION['apanel_session'] ?></p>
            </span>
            <ul class="dropdown-menu">
                <li><a role="button" class="dropdown-item" onclick="changePassModal()">Change Password</a></li>
            </ul>
        </div>
    </div>
</div>



<!-- Modal Change Password -->
<div class="modal fade" id="changePassModal" tabindex="-1" aria-labelledby="changePassModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-0">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="changePassModalLabel">Change Password</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="oldPassword" class="form-label">Current Password</label>
                    <input type="password" class="form-control rounded-0" id="oldPassword" placeholder="Current Password">
                </div>
                <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control rounded-0" id="newPassword" placeholder="New Password">
                </div>
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control rounded-0" id="confirmPassword" placeholder="Confirm Password">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-0" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary rounded-0" onclick="submitPassword()">Save changes</button>
            </div>
        </div>
    </div>
</div>

<script>
    function changePassModal() {
        $('#changePassModal').modal('show');
    }

    function submitPassword() {
        const currentPass = $("#oldPassword").val();
        const newPass = $("#newPassword").val();
        const confirmPass = $("#confirmPassword").val();

        if (newPass !== confirmPass) {
            Notiflix.Notify.failure("New Password did not match");
        } else {
            $.post("<?= base_url('apanel/changepassword') ?>", {
                    currentPass: currentPass,
                    newPass: newPass,
                })
                .done((data) => {
                    if (data == "401") {
                        Notiflix.Notify.failure("Current password invalid");
                    } else if (data == "200") {
                        Notiflix.Notify.success("Password changed successfully");
                        $("#oldPassword").val("");
                        $("#newPassword").val("");
                        $("#confirmPassword").val("");
                        $("#changePassModal").modal("hide");
                    }
                })
        }
    }
</script>