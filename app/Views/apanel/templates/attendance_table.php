<table id="attendance_table" class="table">
    <thead>
        <tr>
            <th>Tag's UID</th>
            <th>Name</th>
            <th>Position</th>
            <th>Attendance Time</th>
            <th class="col-action">Action</th>
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
        }
    });
</script>