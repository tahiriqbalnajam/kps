<?php

use App\Models\Acl;
use Illuminate\Contracts\Routing\Registrar as RouteContract;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SyllabusController;
use App\Http\Controllers\SyllabusTrackingController;
use App\Http\Controllers\SyllabusCompletionController;

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

//Route::apiResource('settings', 'SettingController');
Route::apiResource('settings', 'SettingsController');
Route::apiResource('smsqueue', 'SmsQueueController');
Route::get('sendsms', 'SmsQueueController@sendsms');
Route::get('default/message/channel', 'SettingsController@defaultMessageChannel');
Route::get('send/whatsapp', 'SmsQueueController@sendWhatsapp');
Route::post('update/whatsapp/status', 'SmsQueueController@changeWhatsAppStatus');
Route::post('change_status', 'SmsQueueController@change_status');
Route::apiResource('dashboard', 'DashboardController');
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
Route::post('absent_comment', 'StudentAttendanceController@absent_comment');
Route::get('get_att_comments/{id}', 'StudentAttendanceController@get_att_comment');
Route::post('getdailyclasswise', 'StudentAttendanceController@dailyclasswise');
Route::post('attendance/summary', 'StudentAttendanceController@get_attendance_summry');
// Student attendance graph endpoint
Route::get('student/attendance/daily-graph', 'StudentAttendanceController@getDailyAttendanceGraph');
//teacher attendance
Route::apiResource('teacher_attendance', 'TeacherAttendanceController');
Route::post('check_salary_generated', 'TeacherAttendanceController@check_salary_generated');
Route::post('pay_salary', 'TeacherAttendanceController@pay_salary');
Route::post('generate_pay', 'TeacherAttendanceController@generate_pay');
Route::post('teachers_monthly_att_report', 'TeacherAttendanceController@teachers_monthly_att_report');
Route::apiResource('holidays', 'HolidayController');
Route::get('/teacher/{id}/test-classwise', 'TeacherController@get_tests');
Route::get('/teacher/{id}/online', 'TeacherController@online_attendance');
//Tests
Route::apiResource('tests', 'TestController');
Route::apiResource('tests-result', 'TestResultController');
Route::put('tests/{test_id}/results', 'TestController@updateTestResults');
//exam
//Route::apiResource('exam_result', 'ExamController');pe
//subjects
Route::apiResource('subjects', 'SubjectController');
Route::apiResource('subject_class', 'SubjectToClassController');
//Route::get('subject_class', 'SubjectToClassController@getSubjectsByClass');

//exams
Route::apiResource('examtest_result', 'ExamTestController');
Route::apiResource('exams', 'ExamController');
Route::get('exams/{id}/subjects', 'ExamController@getExamSubjects');
Route::get('/exams/{id}/subjects/marks', 'ExamController@getExamById');
Route::post('/exams/marks', 'ExamController@addExamMarks');
Route::get('exams/{id}/reports', 'ExamController@getExamReports');

//test
Route::apiResource('examstest', 'ExamTestController');
Route::get('examstest/{examstest}/test_results', 'ExamTestController@test_results');
Route::post('edit_class', 'StudentController@edit_class');
Route::apiResource('transaction', 'TransactionsController');
Route::apiResource('balances', 'BalanceController');
Route::apiResource('teacher-observations', 'TeacherObservationController');
Route::get('teacher-observations/progress/{teacherId}', 'TeacherObservationController@getTeacherProgress');
Route::apiResource('sections', 'App\Http\Controllers\SectionController');
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

        // Student Observation Routes
        Route::get('student-observations', 'StudentObservationController@index');
        Route::get('student-observations/{id}', 'StudentObservationController@show');
        Route::post('student-observations', 'StudentObservationController@store');
        Route::put('student-observations/{id}', 'StudentObservationController@update');
        Route::delete('student-observations/{id}', 'StudentObservationController@destroy');
        Route::get('students/{studentId}/observations', 'StudentObservationController@getStudentObservations');
    });
});

Route::prefix('syllabus')->group(function () {
    Route::get('repository', [SyllabusController::class, 'index']);
    Route::post('repository', [SyllabusController::class, 'store']);
    Route::put('repository/{id}', [SyllabusController::class, 'update']);
    Route::delete('repository/{id}', [SyllabusController::class, 'destroy']);

    Route::get('tracking', [SyllabusTrackingController::class, 'index']);
    Route::post('tracking', [SyllabusTrackingController::class, 'store']);
    Route::put('tracking/{id}', [SyllabusTrackingController::class, 'update']);
    Route::delete('tracking/{id}', [SyllabusTrackingController::class, 'destroy']);

    Route::get('completion', [SyllabusCompletionController::class, 'index']);
    Route::put('completion/{id}', [SyllabusCompletionController::class, 'markCompletion']);
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
