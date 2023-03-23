<table id="attendance_table" class="table">
    <thead>
        <tr>
            <th>Tag's UID</th>
            <th>Name</th>
            <th>Position</th>
            <th>Attendance Time</th>
            <th class="col-action">Action</th>
            <th>Overtime</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($attendants as $key => $attendant) {
            if ($attendant['name'] != 'n/a' && $attendant['position'] != 'n/a') {
        ?>
                <tr>
                    <td><tt><?= $attendant['uid'] ?></tt></td>
                    <td><?= $attendant['name'] ?></td>
                    <td><?= $attendant['position'] ?></td>
                    <td><?= $attendant['attendance_time'] ?></td>
                    <td class="col-action">
                        <?= $attendant['attendance_time'] == "Absent" ? '<button class="btn btn-success btn-sm" onclick="setPresent(\'' . $attendant['uid'] . '\',\'' . $attendant['name'] . '\')"><i class="fa-solid fa-square-check"></i> &nbsp; Present</button>' : '<button class="btn btn-danger btn-sm" onclick="setAbsent(\'' . $attendant['attendance_id'] . '\',\'' . $attendant['uid'] . '\',\'' . $attendant['name'] . '\')"><i class="fa-solid fa-square-xmark"></i> &nbsp; Absent</button>' ?>
                    </td>
                    <td>
                        <?php
                        if ($attendant['attendance_time'] != "Absent") {
                        ?>
                            <button class="btn btn-sm btn-outline-primary actions-btn p-0 rounded-1" onclick="minusOvertime('<?= $attendant['attendance_id'] ?>','att-<?= $attendant['attendance_id'] ?>')"><i class="fa-solid fa-minus fa-fw"></i></button>
                            &nbsp;<span id="att-<?= $attendant['attendance_id'] ?>"><?= $attendant['overtime'] ?></span>&nbsp;
                            <button class="btn btn-sm btn-outline-primary actions-btn p-0 rounded-1" onclick="plusOvertime('<?= $attendant['attendance_id'] ?>','att-<?= $attendant['attendance_id'] ?>')"><i class="fa-solid fa-plus fa-fw"></i></button>
                        <?php
                        } else {
                        ?>
                            Unavailable
                        <?php
                        }
                        ?>
                    </td>
                </tr>

        <?php
            }
        }
        ?>
    </tbody>
</table>
<script>
    $(document).ready(function() {
        $('#attendance_table').DataTable();

        let now = new Date();
        const date = now.getDate() < 10 ? '0' + now.getDate() : now.getDate();
        let fixmonth = now.getMonth() + 1;
        const month = fixmonth < 10 ? '0' + fixmonth : fixmonth;
        const year = now.getFullYear();
        if ($('#dateInput').val() != `${year}-${month}-${date}`) {
            $('.col-action').css('display', 'none');
            $('.actions-btn').css('display', 'none');
        }
    });


    function plusOvertime(attendance_id, attid) {
        Notiflix.Loading.standard();
        $.post('<?= base_url('apanel/attendance/plusovertime') ?>', {
                attendance_id: attendance_id,
                current_ot: $("#" + attid).html(),
            })
            .done((data) => {
                if (data == "200") {
                    Notiflix.Loading.remove(500);
                    setTimeout(() => {
                        Notiflix.Notify.success('Updated!');
                        $("#" + attid).html(parseInt($("#" + attid).html()) + 1);
                    }, 500);
                } else {
                    Notiflix.Loading.remove();
                    Notiflix.Notify.failure('Error' + data + "!!");
                    window.location.reload();
                }
            })
    }

    function minusOvertime(attendance_id, attid) {
        Notiflix.Loading.standard();
        $.post('<?= base_url('apanel/attendance/minusovertime') ?>', {
                attendance_id: attendance_id,
                current_ot: $("#" + attid).html(),
            })
            .done((data) => {
                if (data == "200") {
                    Notiflix.Loading.remove(500);
                    setTimeout(() => {
                        Notiflix.Notify.success('Updated!');
                        $("#" + attid).html(parseInt($("#" + attid).html()) - 1);
                    }, 500);
                } else if (data == "400") {
                    Notiflix.Loading.remove(500);
                    setTimeout(() => {
                        Notiflix.Notify.failure("Overtime cannot less than zero (0)");
                    }, 500);
                } else {
                    Notiflix.Loading.remove(500);
                    setTimeout(() => {
                        Notiflix.Notify.failure('Error ' + data + "!!");
                    }, 500);
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                }
            })


    }
</script>