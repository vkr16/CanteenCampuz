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
        </ul>
        <hr>
        <div class="px-3">
            <span class="d-flex align-items-center link-dark text-decoration-none">
                <i class="fa-solid fa-user-tie me-3"></i>
                <p class="mb-0"><?= $_SESSION['apanel_session'] ?></p>
            </span>
        </div>
    </div>
</div>