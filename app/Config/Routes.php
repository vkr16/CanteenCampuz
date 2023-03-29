<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Main');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// Authentication Routes...
$routes->get('/', 'Main::index');
$routes->get('/login', 'Main::login');
$routes->post('/login', 'Main::auth');
$routes->get('/logout', 'Main::logout');

// aPanel
$routes->get('/apanel', 'Main::attendance');
$routes->get('/apanel/attendance', 'Main::attendance');
$routes->post('/apanel/get_attendants', 'Main::getAttendants');
$routes->get('/apanel/employees', 'Main::employees');
$routes->post('/apanel/employees/delete', 'Main::employeesDelete');
$routes->post('/apanel/employees/update', 'Main::employeesUpdate');
$routes->post('/apanel/attendance/present', 'Main::attendantPresent');
$routes->post('/apanel/attendance/absent', 'Main::attendantAbsent');
$routes->get('/apanel/summary', 'Main::summary');
$routes->get('/apanel/summary/get', 'Main::getSummary');
$routes->post('/apanel/attendance/plusovertime', 'Main::plusovertime');
$routes->post('/apanel/attendance/minusovertime', 'Main::minusovertime');
$routes->post('/apanel/changepassword', 'Main::changePassword');
$routes->post('/apanel/changemaster', 'Main::changeMaster');
$routes->get('/apanel/config', 'Main::config');
$routes->get('/apanel/config/update/setsource', 'Main::setUpdateSource');



// API Endpoints
// Endpoint Master Tag
$routes->post('/api/v1/mastertag', 'API::getMasterTag');

// Endpoint Register 
$routes->post('/api/v1/register', 'API::register');

// Endpoint Attendance
$routes->post('/api/v1/attendance', 'API::attendance');

// Endpoint Get Update Mode
$routes->post('/api/v1/update/mode/get', 'API::getUpdatemode');

// Endpoint Disable & Enable Update Mode
$routes->post('/api/v1/update/mode/disable', 'API::disableUpdate');
$routes->post('/api/v1/update/mode/enable', 'API::enableUpdate');

// Endpoint Set Update URL
$routes->post('/api/v1/update/url/set', 'API::setUpdateUrl');

// Endpoint Get Update URL
$routes->post('/api/v1/update/url/get', 'API::getUpdateUrl');

// Endpoint Get register Mode Status
$routes->post('/api/v1/register/mode/get', 'API::getRegisterMode');

// Endpoint Disable & Enable Register Mode
$routes->post('/api/v1/register/mode/disable', 'API::disableRegister');
$routes->post('/api/v1/register/mode/enable', 'API::enableRegister');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
