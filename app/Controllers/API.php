<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class API extends BaseController
{
    use ResponseTrait;
    protected $adminModel;
    protected $employeeModel;
    protected $attendanceModel;

    function __construct()
    {
        $this->adminModel = model('AdminModel', true, $db);
        $this->employeeModel = model('EmployeeModel', true, $db);
        $this->attendanceModel = model('AttendanceModel', true, $db);
    }

    public function getMasterTag()
    {
        if (isset($_POST['api_key']) && $_POST['api_key'] == $_ENV['API_KEY']) {
            if ($admin = $this->adminModel->where("id", "1")->find()) {
                $master_tag = $admin[0]['master_tag'];
                $data = [
                    "success" => true,
                    "status" => "200 OK",
                    "message" => "Successfully get master tag information",
                    "data" => [
                        "admin" => $admin[0]['username'],
                        "uid" => $master_tag
                    ]
                ];

                return $this->respond($data, 200);
            } else {
                $data = [
                    "success" => false,
                    "status" => "404 Not Found",
                    "message" => "Admin account not found",
                    "data" => []
                ];

                return $this->respond($data, 404);
            }
        } else {
            $data = [
                "success" => false,
                "status" => "401 Unauthorized",
                "message" => "Unauthorized, Invalid API key",
                "data" => []
            ];

            return $this->respond($data, 401);
        }
    }

    public function register()
    {
        if (isset($_POST['api_key']) && $_POST['api_key'] == $_ENV['API_KEY']) {
            if (isset($_POST['new_uid']) && $_POST['new_uid'] != '') {
                $new_uid = $_POST['new_uid'];

                if ($employee = $this->employeeModel->find($new_uid)) {
                    $data = [
                        "success" => false,
                        "status" => "409 Conflict,",
                        "message" => "Tag's UID already exists",
                        "data" => [
                            "conflict_uid" => $employee['uid'],
                        ]
                    ];

                    return $this->respond($data, 409);
                } else {
                    $new_employee = [
                        "uid" => $new_uid,
                        "name" => "n/a",
                        "position" => "n/a"
                    ];
                    if ($this->employeeModel->insert($new_employee)) {
                        $data = [
                            "success" => true,
                            "status" => "201 Created",
                            "message" => "New employee's tag has been registered successfully",
                            "data" => [
                                "new_uid" => $new_uid,
                            ]
                        ];

                        return $this->respond($data, 201);
                    } else {
                        $data = [
                            "success" => false,
                            "status" => "500 Internal Server Error",
                            "message" => "Internal server error, please try again or contact your administrator",
                            "data" => []
                        ];

                        return $this->respond($data, 500);
                    }
                }
            } else {
                $data = [
                    "success" => false,
                    "status" => "400 Bad Request",
                    "message" => "Invalid parameters, new uid cannot be empty",
                    "data" => []
                ];

                return $this->respond($data, 400);
            }
        } else {
            $data = [
                "success" => false,
                "status" => "401 Unauthorized",
                "message" => "Unauthorized, Invalid API key",
                "data" => []
            ];

            return $this->respond($data, 401);
        }
    }

    public function attendance()
    {
        if (isset($_POST['api_key']) && $_POST['api_key'] == $_ENV['API_KEY']) {
            if (isset($_POST['uid']) && $_POST['uid'] != '') {
                $uid = $_POST['uid'];

                if ($employee = $this->employeeModel->find($uid)) {
                    $today = strtotime(date('Y-m-d'));
                    if ($employee['name'] != "n/a" && $employee['position'] != "n/a") {
                        if ($this->attendanceModel->where("employee_uid = '$uid' AND created_at >= '$today'")->find()) {
                            $data = [
                                "success" => true,
                                "status" => "200 Ok",
                                "message" => "Today's attendance record has been previously recorded",
                                "data" => [
                                    "uid" => $uid,
                                    "employee_name" => $employee['name'],
                                    "employee_position" => $employee['position']
                                ]
                            ];

                            return $this->respond($data, 200);
                        } else {
                            $attendanceRecord = [
                                "employee_uid" => $uid
                            ];
                            if ($this->attendanceModel->insert($attendanceRecord)) {
                                $data = [
                                    "success" => true,
                                    "status" => "201 Created",
                                    "message" => "Attendance has been recorded successfully",
                                    "data" => [
                                        "uid" => $uid,
                                        "employee_name" => $employee['name'],
                                        "employee_position" => $employee['position']
                                    ]
                                ];

                                return $this->respond($data, 201);
                            } else {
                                $data = [
                                    "success" => false,
                                    "status" => "500 Internal Server Error",
                                    "message" => "Internal server error, please try again or contact your administrator",
                                    "data" => []
                                ];

                                return $this->respond($data, 500);
                            }
                        }
                    } else {
                        $data = [
                            "success" => false,
                            "status" => "406 Not Acceptable",
                            "message" => "This uid is registered but not yet associated with any employee name",
                            "data" => [
                                "uid" => $uid
                            ]
                        ];

                        return $this->respond($data, 406);
                    }
                } else {
                    $data = [
                        "success" => false,
                        "status" => "404 Not found",
                        "message" => "No such employee with specified uid",
                        "data" => [
                            "uid" => $uid,
                        ]
                    ];

                    return $this->respond($data, 404);
                }
            } else {
                $data = [
                    "success" => false,
                    "status" => "400 Bad Request",
                    "message" => "Invalid parameters, uid cannot be empty",
                    "data" => []
                ];

                return $this->respond($data, 400);
            }
        } else {
            $data = [
                "success" => false,
                "status" => "401 Unauthorized",
                "message" => "Unauthorized, Invalid API key",
                "data" => []
            ];

            return $this->respond($data, 401);
        }
    }

    public function getUpdateMode()
    {
        if (isset($_POST['api_key']) && $_POST['api_key'] == $_ENV['API_KEY']) {
            if ($admin = $this->adminModel->where("id", "1")->find()) {
                $update_mode = $admin[0]['update_mode'];
                $data = [
                    "success" => true,
                    "status" => "200 OK",
                    "message" => $update_mode == "0" ? "Update mode is currently set to OFF" : "Update mode is currently set to ON",
                    "data" => [
                        "update_mode" => $update_mode
                    ]
                ];

                return $this->respond($data, 200);
            } else {
                $data = [
                    "success" => false,
                    "status" => "404 Not Found",
                    "message" => "Admin account not found",
                    "data" => []
                ];

                return $this->respond($data, 404);
            }
        } else {
            $data = [
                "success" => false,
                "status" => "401 Unauthorized",
                "message" => "Unauthorized, Invalid API key",
                "data" => []
            ];

            return $this->respond($data, 401);
        }
    }

    public function disableUpdate()
    {
        if (isset($_POST['api_key']) && $_POST['api_key'] == $_ENV['API_KEY']) {
            if ($admin = $this->adminModel->where("id", "1")->find()) {
                if ($this->adminModel->where("id", "1")->set("update_mode", "0")->update()) {
                    $data = [
                        "success" => true,
                        "status" => "200 OK",
                        "message" => "Update mode disabled successfully",
                        "data" => []
                    ];

                    return $this->respond($data, 200);
                } else {
                    $data = [
                        "success" => true,
                        "status" => "500 Internal Server Error",
                        "message" => "Internal server error, please try again or contact your administrator",
                        "data" => []
                    ];

                    return $this->respond($data, 500);
                }
            } else {
                $data = [
                    "success" => false,
                    "status" => "404 Not Found",
                    "message" => "Admin account not found",
                    "data" => []
                ];

                return $this->respond($data, 404);
            }
        } else {
            $data = [
                "success" => false,
                "status" => "401 Unauthorized",
                "message" => "Unauthorized, Invalid API key",
                "data" => []
            ];

            return $this->respond($data, 401);
        }
    }

    public function enableUpdate()
    {
        if (isset($_POST['api_key']) && $_POST['api_key'] == $_ENV['API_KEY']) {
            if ($admin = $this->adminModel->where("id", "1")->find()) {
                if ($this->adminModel->where("id", "1")->set("update_mode", "1")->update()) {
                    $data = [
                        "success" => true,
                        "status" => "200 OK",
                        "message" => "Update mode enabled successfully",
                        "data" => []
                    ];

                    return $this->respond($data, 200);
                } else {
                    $data = [
                        "success" => true,
                        "status" => "500 Internal Server Error",
                        "message" => "Internal server error, please try again or contact your administrator",
                        "data" => []
                    ];

                    return $this->respond($data, 500);
                }
            } else {
                $data = [
                    "success" => false,
                    "status" => "404 Not Found",
                    "message" => "Admin account not found",
                    "data" => []
                ];

                return $this->respond($data, 404);
            }
        } else {
            $data = [
                "success" => false,
                "status" => "401 Unauthorized",
                "message" => "Unauthorized, Invalid API key",
                "data" => []
            ];

            return $this->respond($data, 401);
        }
    }

    public function setUpdateUrl()
    {
        if (isset($_POST['api_key']) && $_POST['api_key'] == $_ENV['API_KEY']) {
            if (isset($_POST['update_url'])) {
                $update_url = $_POST['update_url'];
                if ($admin = $this->adminModel->where("id", "1")->find()) {
                    if ($this->adminModel->where("id", "1")->set("update_url", $update_url)->update()) {
                        $data = [
                            "success" => true,
                            "status" => "200 OK",
                            "message" => "Update url updated successfully",
                            "data" => [
                                "update_url" => $update_url
                            ]
                        ];

                        return $this->respond($data, 200);
                    } else {
                        $data = [
                            "success" => true,
                            "status" => "500 Internal Server Error",
                            "message" => "Internal server error, please try again or contact your administrator",
                            "data" => []
                        ];

                        return $this->respond($data, 500);
                    }
                } else {
                    $data = [
                        "success" => false,
                        "status" => "404 Not Found",
                        "message" => "Admin account not found",
                        "data" => []
                    ];

                    return $this->respond($data, 404);
                }
            } else {
                $data = [
                    "success" => false,
                    "status" => "400 Bad Request",
                    "message" => "Incomplete parameters",
                    "data" => []
                ];

                return $this->respond($data, 400);
            }
        } else {
            $data = [
                "success" => false,
                "status" => "401 Unauthorized",
                "message" => "Unauthorized, Invalid API key",
                "data" => []
            ];

            return $this->respond($data, 401);
        }
    }

    public function getUpdateUrl()
    {
        if (isset($_POST['api_key']) && $_POST['api_key'] == $_ENV['API_KEY']) {
            if ($admin = $this->adminModel->where("id", "1")->find()) {
                $update_url = $admin[0]['update_url'];
                $data = [
                    "success" => true,
                    "status" => "200 OK",
                    "message" => "Firmware update source currently set to : " . $update_url,
                    "data" => [
                        "update_url" => $update_url
                    ]
                ];

                return $this->respond($data, 200);
            } else {
                $data = [
                    "success" => false,
                    "status" => "404 Not Found",
                    "message" => "Admin account not found",
                    "data" => []
                ];

                return $this->respond($data, 404);
            }
        } else {
            $data = [
                "success" => false,
                "status" => "401 Unauthorized",
                "message" => "Unauthorized, Invalid API key",
                "data" => []
            ];

            return $this->respond($data, 401);
        }
    }
}
