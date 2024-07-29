<?php

use App\Models\Acl;
use Illuminate\Contracts\Routing\Registrar as RouteContract;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//students
Route::apiResource('students', 'StudentController');
Route::get('students/{id}/subject-wise-scores', 'StudentController@getSubjectWiseScores');
//classes
Route::apiResource('classes', 'ClassesController');
//teachers
Route::apiResource('teachers', 'TeacherController');
Route::post('/teacher/all-teaches-pay', 'TeacherController@calculateAllTeachersPay');
Route::post('/teacher/find_save_salary', 'TeacherController@find_already_saved_salary');
Route::post('save_salary', 'TeacherController@save_salary');
Route::get('/teacher/{id}/calculate-pay','TeacherController@calculateTeacherPay');
//parents
Route::apiResource('parents', 'ParentController');
//chapters
Route::apiResource('chapters', 'ChapterController');
Route::apiResource('questions', 'QuestionController');
//periods
Route::apiResource('periods', 'PeriodController');
//timetable
Route::apiResource('timetable', 'TimetableController');
//fee
Route::apiResource('feetypes', 'FeeTypeController');
Route::apiResource('fee', 'FeeController');
Route::apiResource('pendingfee', 'PendingFeeController');

Route::apiResource('settings', 'SettingController');
Route::apiResource('smsqueue', 'SmsQueueController');
Route::get('sendsms', 'SmsQueueController@sendsms');
Route::post('change_status', 'SmsQueueController@change_status');
Route::apiResource('dashboard', 'dashboardController');
//student attendance
Route::apiResource('has_student_attendance', 'StudentAttendanceController@has_student_attendance');
Route::apiResource('attendance', 'StudentAttendanceController');
Route::post('student_attendance_marked', 'StudentAttendanceController@student_attendance_marked');
Route::post('student_monthly_attendance_report', 'StudentAttendanceController@student_monthly_attendance_report');
Route::post('student_monthly_attendance_report', 'StudentAttendanceController@student_monthly_attendance_report');
Route::post('student_daily_classwise_attendance_report', 'StudentAttendanceController@student_daily_classwise_attendance_report');
Route::apiResource('attendance_student_monthly', 'StudentAttendanceController@attendance_student_monthly');
Route::get('student_att_report', 'StudentAttendanceController@student_att_report');
Route::get('absent_student_each_class', 'StudentAttendanceController@absent_student_each_class');
Route::get('student_attendance_total/{id}', 'StudentAttendanceController@student_attendance_total');

//teacher attendance
Route::apiResource('teacher_attendance', 'TeacherAttendanceController');
Route::post('check_salary_generated', 'TeacherAttendanceController@check_salary_generated');
Route::post('pay_salary', 'TeacherAttendanceController@pay_salary');
Route::post('generate_pay', 'TeacherAttendanceController@generate_pay');
Route::post('teachers_monthly_att_report', 'TeacherAttendanceController@teachers_monthly_att_report');
Route::apiResource('holidays', 'HolidayController');
//Tests
Route::apiResource('tests', 'TestController');
Route::apiResource('tests-result', 'TestResultController');
Route::put('tests/{test_id}/results', 'TestController@updateTestResults');
//exam
//Route::apiResource('exam_result', 'ExamController');
Route::apiResource('examtest_result', 'ExamTestController');
//subjects
Route::apiResource('subjects', 'SubjectController');
Route::apiResource('subject_class', 'SubjectToClassController');

Route::apiResource('exams', 'ExamController');
Route::apiResource('examstest', 'ExamTestController');
Route::get('examstest/{examstest}/test_results', 'ExamTestController@test_results');
Route::post('getdailyclasswise', 'StudentAttendanceController@dailyclasswise');
Route::post('edit_class', 'StudentController@edit_class');
Route::apiResource('transaction', 'TransactionsController');
Route::apiResource('balances', 'BalanceController');
Route::namespace('Api')->group(function() {
    Route::post('auth/login', 'AuthController@login');
    Route::apiResource('getusers', 'UserController');
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post('auth/logout', 'AuthController@logout');

        Route::get('/user', 'AuthController@user');
        // Api resource routes
        Route::apiResource('roles', 'RoleController')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);
        Route::apiResource('users', 'UserController')->middleware('permission:' . Acl::PERMISSION_USER_MANAGE);
        Route::apiResource('permissions', 'PermissionController')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);

        // Custom routes
        Route::group(['prefix' => 'users'], function (RouteContract $api) {
            $api->get('{user}/permissions', 'UserController@permissions')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);
            $api->put('{user}/permissions', 'UserController@updatePermissions')->middleware('permission:' .Acl::PERMISSION_PERMISSION_MANAGE);
            $api->get('{user}/logs', 'LogController@index');
        });

        Route::get('roles/{role}/permissions', 'RoleController@permissions')->middleware('permission:' . Acl::PERMISSION_PERMISSION_MANAGE);
        Route::get('requests', 'RequestController@index');
    });
});

Route::get('/orders', function () {
    $rowsNumber = 8;
    $data = [];
    for ($rowIndex = 0; $rowIndex < $rowsNumber; $rowIndex++) {
        $row = [
            'order_no' => 'LARAVUE' . mt_rand(1000000, 9999999),
            'price' => mt_rand(10000, 999999),
            'status' => randomInArray(['success', 'pending']),
        ];

        $data[] = $row;
    }

    return responseSuccess(['items' => $data]);
});