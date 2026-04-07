<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// --- 勤怠管理 ---
$routes->get('/', 'AttendanceController::index');

$routes->group('attendance', function ($routes) {
    $routes->post('save', 'AttendanceController::save');   // /attendance/save
    $routes->post('apply', 'AttendanceController::apply'); // /attendance/apply
});
