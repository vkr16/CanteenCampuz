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
                <h2 class="fw-semibold">Summary</h2>
                <hr class="mt-05" style="max-width: 200px;border: 2px solid; opacity: 1 ">
                <div class="d-flex flex-wrap">
                    <div class="mb-3">
                        From Date
                        <input type="date" id="fromDate" class="form-control rounded-0">
                    </div>
                    &emsp;
                    <div class="mb-3">
                        To Date
                        <input type="date" id="toDate" class="form-control rounded-0">
                    </div>
                </div>
                <button class="btn btn-outline-primary rounded-0" onclick="getSummary()"><i class="fa-solid fa-file-excel"></i> &nbsp; Download Summary</button>
                <div class="table-responsive p-1" id="abc">

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

        $("#sidebar_summary").addClass("active text-white");

        Notiflix.Notify.init({
            position: 'center-top',
            cssAnimationStyle: 'from-top',
            showOnlyTheLastOne: true
        });

        $(document).ready(function() {
            resetDateInput();
        });

        function resetDateInput() {
            let now = new Date();
            const date = now.getDate() < 10 ? '0' + now.getDate() : now.getDate();
            let fixmonth = now.getMonth() + 1;
            const month = fixmonth < 10 ? '0' + fixmonth : fixmonth;
            const year = now.getFullYear();
            $("#fromDate").val(`${year}-${month}-${date}`);
            $("#toDate").val(`${year}-${month}-${date}`);
        }

        function getSummary() {
            const from = $('#fromDate').val();
            const to = $('#toDate').val();
            let endpoint = '<?= base_url('apanel/summary/get') ?>' + "?fromDate=" + from + "&toDate=" + to;

            window.open(endpoint, '_blank');
        }
    </script>
</body>

</html>