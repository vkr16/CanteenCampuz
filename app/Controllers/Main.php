<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Shuchkin\SimpleXLSXGen;

class Main extends BaseController
{
    protected $session;
    protected $adminModel;
    protected $employeeModel;
    protected $attendanceModel;
    protected $db;

    public function __construct()
    {
        $this->session = \Config\Services::session();
        $this->adminModel = model('AdminModel', true, $db);
        $this->employeeModel = model('EmployeeModel', true, $db);
        $this->attendanceModel = model('AttendanceModel', true, $db);
        $this->db = \Config\Database::connect();
    }

    public function index()
    {
        if ($this->session->has("apanel_session") && $this->adminModel->find($this->session->get("apanel_session"))) {
            return redirect()->to(base_url("apanel/attendance"));
        } else {
            return redirect()->to(base_url("logout"));
        }
    }

    public function login()
    {
        if ($this->session->has("apanel_session") && $this->adminModel->find($this->session->get("apanel_session"))) {
            return redirect()->to(base_url('apanel/attendance'));
        }

        return view("login");
    }

    public function auth()
    {
        if (isset($_POST['username']) && $_POST['username'] != '' && isset($_POST['password']) && $_POST['password'] != '') {
            $username = $_POST['username'];
            $password = $_POST['password'];
            if ($admin = $this->adminModel->find($username)) {
                if (password_verify($password, $admin["password"])) {
                    $this->session->set("apanel_session", $admin["username"]);
                    return "200 OK";
                } else {
                    return "401 Unauthorized";
                }
            } else {
                return "404 Not Found";
            }
        } else {
            return "400 Bad Request";
        }
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to(base_url("login"));
    }

    public function attendance()
    {
        return view("apanel/attendance");
    }

    public function getAttendants()
    {
        if (isset($_POST['date'])) {

            $date = $_POST['date'];

            $timestamp = strtotime($date);
            $timestampplus = strtotime('+1 day', $timestamp);

            $employees = $this->employeeModel->where("created_at < '$timestampplus' AND (deleted_at >= '$timestampplus' OR deleted_at IS NULL)")->findAll();

            $attendants = [];
            foreach ($employees as $key => $employee) {
                $employee_uid = $employee['uid'];

                $query = $this->db->query("SELECT * FROM attendance WHERE employee_uid = '$employee_uid' AND (created_at >= '$timestamp' AND created_at < '$timestampplus')");

                if ($attendance = $query->getRowArray()) {
                    $attendanceData = [
                        "employee_id" => $employee['id'],
                        "attendance_id" => $attendance['id'],
                        "uid" => $employee['uid'],
                        "name" => $employee['name'],
                        "position" => $employee['position'],
                        "attendance_time" => date("H : i : s A", $attendance['created_at']),
                        "overtime" => $attendance['overtime'],
                    ];
                } else {
                    $attendanceData = [
                        "employee_id" => $employee['id'],
                        "uid" => $employee['uid'],
                        "name" => $employee['name'],
                        "position" => $employee['position'],
                        "attendance_time" => "Absent",
                    ];
                }
                array_push($attendants, $attendanceData);
            }

            $data = [
                "attendants" => $attendants,
            ];

            return view("apanel/templates/attendance_table", $data);
        }
    }

    public function employees()
    {
        $employees = $this->employeeModel->findAll();
        $data = [
            "employees" => $employees,
        ];
        return view("apanel/employees", $data);
    }

    public function employeesDelete()
    {
        if (isset($_POST['id'])) {
            $id = $_POST['id'];
            if ($this->employeeModel->where("id", $id)->find()) {
                if ($this->employeeModel->where("id", $id)->delete()) {
                    return "204 No Content";
                } else {
                    return "500 Internal Server Error";
                }
            } else {
                return "404 Not Found";
            }
        } else {
            return "400 Bad Request";
        }
    }

    public function employeesUpdate()
    {

        if (
            isset($_POST['id']) && isset($_POST['uid']) && isset($_POST['name']) && isset($_POST['position']) && $_POST['id'] != '' && $_POST['uid'] != '' && $_POST['name'] != '' && $_POST['position'] != ''
        ) {
            $id = $_POST['id'];
            $uid = $_POST['uid'];
            $name = $_POST['name'];
            $position = $_POST['position'];

            if ($this->employeeModel->where("id = '$id' AND uid = '$uid'")->find()) {
                $employeeData = [
                    "name" => $name,
                    "position" => $position
                ];
                if ($this->employeeModel->where("id = '$id'")->set($employeeData)->update()) {

                    return "200 OK";
                } else {
                    return "500 Internal Server Error";
                }
            } else {
                return "404 Not Found";
            }
        } else {
            return "400 Bad Request";
        }
    }

    public function attendantPresent()
    {
        if (isset($_POST['uid'])) {
            $uid = $_POST['uid'];
            if ($this->employeeModel->where("uid", $uid)->find()) {
                $attendanceRecord = [
                    "employee_uid" => $uid
                ];
                $today = strtotime(date('Y-m-d'));
                $query = $this->db->query("SELECT * FROM attendance WHERE employee_uid = '$uid' AND created_at >= '$today'");

                if ($query->getNumRows() < 1) {
                    if ($this->attendanceModel->insert($attendanceRecord)) {
                        return "201 Created";
                    } else {
                        return "500 Internal Server Error";
                    }
                } else {
                    return "200 OK";
                }
            } else {
                return "404 Not Found";
            }
        } else {
            return "400 Bad Request";
        }
    }

    public function attendantAbsent()
    {
        if (isset($_POST['uid']) && isset($_POST['id'])) {
            $uid = $_POST['uid'];
            $id = $_POST['id'];

            if ($this->attendanceModel->where("id = '$id' AND employee_uid = '$uid'")->find()) {
                if ($this->attendanceModel->where("id = '$id' AND employee_uid = '$uid'")->delete()) {
                    return "204 No Content";
                } else {
                    return "500 Internal Server Error";
                }
            } else {
                return "404 Not Found";
            }
        } else {
            return "400 Bad Request";
        }
    }

    public function summary()
    {
        return view('apanel/summary');
    }

    public function getSummary()
    {
        $from = strtotime($_GET['fromDate']);
        $to = strtotime($_GET['toDate']);

        if ($to < $from) {
            return "404 Bad Request";
        } elseif ($to >= $from) {
            $to = strtotime('+1 days', $to);
        }
        $employees = $this->employeeModel->findAll();
        $records = [];
        $dayCount = ($to - $from) / 86400;
        $datesArray = ["UID", "Name", "Position"];
        for ($day = 0; $day < $dayCount; $day++) {
            $dateA = $from + (86400 * $day);
            $dateB = $from + (86400 * ($day + 1));

            $thisDay = date("d/M/y", $dateA);
            array_push($datesArray, $thisDay);
        }
        foreach ($employees as $key => $employee) {
            $employee_uid = $employee['uid'];
            $employee_name = $employee['name'];
            $employee_position = $employee['position'];
            $employee_created_at = $employee['created_at'];

            $oneEmployeeRecord = [];
            array_push($oneEmployeeRecord, $employee_uid);
            array_push($oneEmployeeRecord, $employee_name);
            array_push($oneEmployeeRecord, $employee_position);
            for ($day = 0; $day < $dayCount; $day++) {
                $dateA = $from + (86400 * $day);
                $dateB = $from + (86400 * ($day + 1));

                if ($attendance = $this->attendanceModel->where("employee_uid = '$employee_uid' AND created_at >= '$dateA' AND created_at < '$dateB' AND overtime = 0")->find()) {
                    $record = '<center><style border="#000" bgcolor="#C6E0B4">1</style></center>';
                } else if ($attendance = $this->attendanceModel->where("employee_uid = '$employee_uid' AND created_at >= '$dateA' AND created_at < '$dateB' AND overtime > 0")->find()) {
                    $record = '<center><style border="#000" bgcolor="#B4C6E7">' . $attendance[0]['overtime'] + 1 . '</style></center>';
                } else {
                    if ($dateB < $employee_created_at) {
                        $record = '<center><style border="#000" bgcolor="#FFE699">--</style></center>';
                    } else {
                        $record = '<center><style border="#000" bgcolor="#F8CBAD">0</style></center>';
                    }
                }

                array_push($oneEmployeeRecord, $record);
            }
            array_push($records, $oneEmployeeRecord);
        }

        $exceldata = array_merge(array($datesArray), $records);
        include('SimpleXLSXGen.php');
        $xlsx = SimpleXLSXGen::fromArray($exceldata);
        $xlsx->downloadAs("summary" . '.xlsx');
        exit;
    }

    public function plusovertime()
    {
        $attendance_id = $_POST['attendance_id'];
        $currentOT = $_POST['current_ot'];

        if ($this->attendanceModel->where("id", $attendance_id)->find()) {
            if ($this->attendanceModel->where("id", $attendance_id)->set(["overtime" => $currentOT + 1])->update()) {
                return "200";
            } else {
                return "500";
            }
        } else {
            return "404";
        }
    }

    public function minusovertime()
    {
        $attendance_id = $_POST['attendance_id'];
        $currentOT = $_POST['current_ot'];

        if ($this->attendanceModel->where("id", $attendance_id)->find()) {
            if ($currentOT - 1 >= 0) {
                if ($this->attendanceModel->where("id", $attendance_id)->set(["overtime" => $currentOT - 1])->update()) {
                    return "200";
                } else {
                    return "500";
                }
            } else {
                return "400";
            }
        } else {
            return "404";
        }
    }

    public function changePassword()
    {
        $currentPassword = $_POST['currentPass'];
        $newPassword = password_hash($_POST['newPass'], PASSWORD_DEFAULT);

        $admin = $this->adminModel->where("id", "1")->find();
        if (password_verify($currentPassword, $admin[0]['password'])) {
            if ($this->adminModel->where("id", "1")->set(["password" => $newPassword])->update()) {
                return "200";
            } else {
                return "500";
            }
        } else {
            return "401";
        }
    }
}
