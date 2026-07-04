<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/clear-cache', 'SystemController@clearCache');

$router->group(['prefix' => 'api/v1'], function () use ($router) {
    $router->group(['prefix' => 'auth'], function () use ($router) {
        $router->post('login', 'Api\\V1\\AuthController@login');
        $router->post('otp/send', 'Api\\V1\\AuthController@sendOtp');
        $router->post('otp/verify', 'Api\\V1\\AuthController@verifyOtp');

        $router->group(['middleware' => 'auth'], function () use ($router) {
            $router->get('me', 'Api\\V1\\AuthController@me');
            $router->post('refresh', 'Api\\V1\\AuthController@refresh');
            $router->post('logout', 'Api\\V1\\AuthController@logout');
            $router->post('change-password', 'Api\\V1\\AuthController@changePassword');
            $router->put('profile', 'Api\\V1\\AuthController@updateProfile');
        });
    });

    $router->group(['middleware' => ['auth', 'role:admin', 'permission:users,view']], function () use ($router) {
        $router->get('users', 'Api\\V1\\UserController@index');
        $router->get('users/{id}', 'Api\\V1\\UserController@show');
    });
    $router->group(['middleware' => ['auth', 'role:admin', 'permission:users,create']], function () use ($router) {
        $router->post('users', 'Api\\V1\\UserController@store');
    });
    $router->group(['middleware' => ['auth', 'role:admin', 'permission:users,edit']], function () use ($router) {
        $router->put('users/{id}', 'Api\\V1\\UserController@update');
    });
    $router->group(['middleware' => ['auth', 'role:admin', 'permission:users,delete']], function () use ($router) {
        $router->delete('users/{id}', 'Api\\V1\\UserController@destroy');
    });

    $router->group(['middleware' => ['auth', 'role:admin', 'permission:roles,view']], function () use ($router) {
        $router->get('roles', 'Api\\V1\\RoleController@index');
        $router->get('roles/{id}', 'Api\\V1\\RoleController@show');
        $router->get('permissions', 'Api\\V1\\PermissionController@index');
    });
    $router->group(['middleware' => ['auth', 'role:admin', 'permission:roles,create']], function () use ($router) {
        $router->post('roles', 'Api\\V1\\RoleController@store');
    });
    $router->group(['middleware' => ['auth', 'role:admin', 'permission:roles,edit']], function () use ($router) {
        $router->put('roles/{id}', 'Api\\V1\\RoleController@update');
        $router->put('roles/{id}/permissions', 'Api\\V1\\RoleController@syncPermissions');
    });
    $router->group(['middleware' => ['auth', 'role:admin', 'permission:roles,delete']], function () use ($router) {
        $router->delete('roles/{id}', 'Api\\V1\\RoleController@destroy');
    });

    $router->group(['middleware' => ['auth', 'role:admin', 'administrator']], function () use ($router) {
        $router->get('holidays', 'Api\\V1\\HolidayController@index');
        $router->post('holidays/generate-year', 'Api\\V1\\HolidayController@generateYear');
        $router->post('holidays', 'Api\\V1\\HolidayController@store');
        $router->put('holidays/{id}', 'Api\\V1\\HolidayController@update');
        $router->delete('holidays/{id}', 'Api\\V1\\HolidayController@destroy');
    });

    $router->group(['middleware' => ['auth', 'role:admin', 'permission:attendance,view']], function () use ($router) {
        $router->get('attendance/summary', 'Api\\V1\\AttendanceController@summary');
    });

    // Clinic — READ: Admin + Staff + Therapist (Therapist needs settings for geofence check)
    $router->group(['middleware' => ['auth', 'role:admin,therapist']], function () use ($router) {
        $router->get('clinic', 'Api\V1\ClinicController@show');
        $router->get('clinic/settings', 'Api\V1\ClinicSettingController@index');
    });
    $router->group(['middleware' => ['auth', 'role:admin']], function () use ($router) {
        $router->put('clinic', 'Api\\V1\\ClinicController@update');
        $router->put('clinic/settings', 'Api\\V1\\ClinicSettingController@bulkUpsert');
    });

    // Patients (Admin/Staff full access, Therapist view access)
    $router->group(['middleware' => ['auth', 'role:admin,therapist']], function () use ($router) {
        $router->get('patients', 'Api\\V1\\PatientController@index');
        $router->get('patients/{id}', 'Api\\V1\\PatientController@show');
        $router->get('patients/{id}/medical-record', 'Api\\V1\\PatientMedicalRecordController@show');
        $router->get('patients/{id}/documents', 'Api\\V1\\PatientDocumentController@index');
        $router->get('patients/{id}/sessions', 'Api\\V1\\PatientController@sessions');
        $router->get('patients/{id}/invoices', 'Api\\V1\\PatientController@invoices');

        $router->get('patient-intakes', 'Api\\V1\\PatientIntakeController@index');
        $router->get('patient-intakes/{id}', 'Api\\V1\\PatientIntakeController@show');
        $router->post('patient-intakes', 'Api\\V1\\PatientIntakeController@store');
        $router->put('patient-intakes/{id}', 'Api\\V1\\PatientIntakeController@update');
        $router->delete('patient-intakes/{id}', 'Api\\V1\\PatientIntakeController@destroy');
        $router->get('patient-intakes/{id}/pdf', 'Api\\V1\\PatientIntakeController@downloadPdf');
    });
    $router->group(['middleware' => ['auth', 'role:admin']], function () use ($router) {
        $router->post('patients', 'Api\\V1\\PatientController@store');
        $router->put('patients/{id}', 'Api\\V1\\PatientController@update');
        $router->delete('patients/{id}', 'Api\\V1\\PatientController@destroy');

        $router->put('patients/{id}/medical-record', 'Api\\V1\\PatientMedicalRecordController@upsert');
        $router->post('patients/{id}/documents', 'Api\\V1\\PatientDocumentController@store');
        $router->delete('patients/{id}/documents/{docId}', 'Api\\V1\\PatientDocumentController@destroy');
    });

    // Therapies (Admin/Staff full access, Therapist view access)
    $router->group(['middleware' => ['auth', 'role:admin,therapist']], function () use ($router) {
        $router->get('therapies', 'Api\\V1\\TherapyController@index');
        $router->get('therapies/{id}', 'Api\\V1\\TherapyController@show');

        $router->get('patient-therapies', 'Api\\V1\\PatientTherapyController@index');
    });
    $router->group(['middleware' => ['auth', 'role:admin']], function () use ($router) {
        $router->post('therapies', 'Api\\V1\\TherapyController@store');
        $router->put('therapies/{id}', 'Api\\V1\\TherapyController@update');
        $router->delete('therapies/{id}', 'Api\\V1\\TherapyController@destroy');

        $router->post('patient-therapies', 'Api\\V1\\PatientTherapyController@store');
        $router->put('patient-therapies/{id}', 'Api\\V1\\PatientTherapyController@update');
        $router->delete('patient-therapies/{id}', 'Api\\V1\\PatientTherapyController@destroy');
    });

    // Therapists (Admin/Staff full access, Therapist view access)
    $router->group(['middleware' => ['auth', 'role:admin,therapist']], function () use ($router) {
        $router->get('therapists', 'Api\\V1\\TherapistController@index');
        $router->get('therapists/{id}', 'Api\\V1\\TherapistController@show');
        $router->get('therapists/{id}/documents', 'Api\\V1\\TherapistDocumentController@index');
        $router->get('therapists/{id}/sessions', 'Api\\V1\\TherapistController@sessions');
        $router->get('therapists/{id}/schedule', 'Api\\V1\\TherapistController@schedule');
    });
    $router->group(['middleware' => ['auth', 'role:admin']], function () use ($router) {
        $router->post('therapists', 'Api\\V1\\TherapistController@store');
        $router->put('therapists/{id}', 'Api\\V1\\TherapistController@update');
        $router->delete('therapists/{id}', 'Api\\V1\\TherapistController@destroy');

        $router->post('therapists/{id}/documents', 'Api\\V1\\TherapistDocumentController@store');
        $router->delete('therapists/{id}/documents/{docId}', 'Api\\V1\\TherapistDocumentController@destroy');
    });

    // Attendance + Leaves (Admin APIs)
    $router->group(['middleware' => ['auth', 'role:admin']], function () use ($router) {
        $router->get('attendance',       'Api\V1\AttendanceController@index');
        $router->get('attendance/today', 'Api\V1\AttendanceController@today');

        $router->post('attendance/check-in',  'Api\V1\AttendanceController@checkIn');
        $router->post('attendance/check-out', 'Api\V1\AttendanceController@checkOut');

        $router->get('leaves',           'Api\V1\LeaveController@index');
        $router->post('leaves',          'Api\V1\LeaveController@store');
        $router->delete('leaves/{id}',   'Api\V1\LeaveController@destroy');
    });
    $router->group(['middleware' => ['auth', 'role:therapist']], function () use ($router) {
        $router->get('therapist/attendance', 'Api\V1\TherapistPortalController@attendanceIndex');
        $router->get('therapist/attendance/today', 'Api\V1\TherapistPortalController@attendanceToday');
        $router->post('therapist/attendance/check-in', 'Api\V1\TherapistPortalController@attendanceCheckIn');
        $router->post('therapist/attendance/check-out', 'Api\V1\TherapistPortalController@attendanceCheckOut');

        $router->get('therapist/leaves', 'Api\V1\TherapistPortalController@leavesIndex');
        $router->post('therapist/leaves', 'Api\V1\TherapistPortalController@leavesStore');
        $router->delete('therapist/leaves/{id}', 'Api\V1\TherapistPortalController@leavesDestroy');
    });
    $router->group(['middleware' => ['auth', 'role:admin']], function () use ($router) {
        // Only Admin/Staff can approve or reject leaves
        $router->put('leaves/{id}', 'Api\V1\LeaveController@update');
    });

    // Salary + Payroll (Admin)
    $router->group(['middleware' => ['auth', 'role:admin', 'permission:salary,view']], function () use ($router) {
        $router->get('salary/models', 'Api\\V1\\SalaryModelController@index');
        $router->post('salary/models', 'Api\\V1\\SalaryModelController@store');
        $router->put('salary/models/{id}', 'Api\\V1\\SalaryModelController@update');

        $router->get('salary/payroll', 'Api\\V1\\PayrollController@index');
        $router->post('salary/payroll/generate', 'Api\\V1\\PayrollController@generate');
        $router->put('salary/payroll/{id}', 'Api\\V1\\PayrollController@update');
        $router->post('salary/payroll/{id}/pay', 'Api\\V1\\PayrollController@pay');
    });

    // Scheduling (Admin APIs)
    $router->group(['middleware' => ['auth', 'role:admin']], function () use ($router) {
        $router->get('time-slots', 'Api\\V1\\TimeSlotController@index');
        $router->get('scheduling/daily', 'Api\\V1\\SchedulingController@daily');
        $router->get('scheduling/availability', 'Api\\V1\\SchedulingController@availability');
        $router->get('waiting-list', 'Api\\V1\\WaitingListController@index');
        $router->put('scheduling/daily/{id}', 'Api\\V1\\SchedulingController@update');
    });
    $router->group(['middleware' => ['auth', 'role:therapist']], function () use ($router) {
        $router->get('therapist/scheduling/daily', 'Api\\V1\\TherapistPortalController@schedulingDaily');
        $router->put('therapist/scheduling/daily/{id}', 'Api\\V1\\TherapistPortalController@schedulingUpdate');
        $router->get('therapist/scheduling/availability', 'Api\\V1\\TherapistPortalController@schedulingAvailability');
    });
    $router->group(['middleware' => ['auth', 'role:admin']], function () use ($router) {
        $router->post('time-slots', 'Api\\V1\\TimeSlotController@store');
        $router->put('time-slots/{id}', 'Api\\V1\\TimeSlotController@update');
        $router->delete('time-slots/{id}', 'Api\\V1\\TimeSlotController@destroy');

        $router->post('scheduling/daily', 'Api\\V1\\SchedulingController@book');
        $router->delete('scheduling/daily/{id}', 'Api\\V1\\SchedulingController@cancel');

        $router->post('waiting-list', 'Api\\V1\\WaitingListController@store');
        $router->put('waiting-list/{id}', 'Api\\V1\\WaitingListController@update');
        $router->delete('waiting-list/{id}', 'Api\\V1\\WaitingListController@destroy');
    });

    // Sessions (Admin APIs)
    $router->group(['middleware' => ['auth', 'role:admin']], function () use ($router) {
        $router->get('sessions', 'Api\\V1\\SessionController@index');
        $router->get('sessions/{id}', 'Api\\V1\\SessionController@show');
        $router->post('sessions', 'Api\\V1\\SessionController@store');
    });
    $router->group(['middleware' => ['auth', 'role:therapist']], function () use ($router) {
        $router->get('therapist/sessions', 'Api\\V1\\TherapistPortalController@sessionsIndex');
        $router->post('therapist/sessions', 'Api\\V1\\TherapistPortalController@sessionsStore');
        $router->get('therapist/sessions/{id}', 'Api\\V1\\TherapistPortalController@sessionsShow');
    });
    $router->group(['middleware' => ['auth', 'role:admin']], function () use ($router) {
        $router->put('sessions/{id}', 'Api\\V1\\SessionController@update');
        $router->delete('sessions/{id}', 'Api\\V1\\SessionController@destroy');
    });

    // Billing
    $router->group(['middleware' => ['auth', 'role:admin,therapist']], function () use ($router) {
        $router->get('invoices', 'Api\\V1\\InvoiceController@index');
        $router->get('invoices/{id}', 'Api\\V1\\InvoiceController@show');
        $router->get('payments', 'Api\\V1\\PaymentController@index');
    });
    $router->group(['middleware' => ['auth', 'role:admin']], function () use ($router) {
        $router->post('invoices', 'Api\\V1\\InvoiceController@store');
        $router->put('invoices/{id}', 'Api\\V1\\InvoiceController@update');
        $router->delete('invoices/{id}', 'Api\\V1\\InvoiceController@destroy');

        $router->post('invoices/{invoiceId}/payments', 'Api\\V1\\PaymentController@store');
        $router->delete('payments/{id}', 'Api\\V1\\PaymentController@destroy');
    });

    $router->group(['middleware' => ['auth', 'role:admin', 'permission:reports,view']], function () use ($router) {
        $router->get('dashboard/stats', 'Api\\V1\\DashboardController@stats');

        $router->get('reports/revenue', 'Api\\V1\\ReportController@revenue');
        $router->get('reports/sessions', 'Api\\V1\\ReportController@sessions');
        $router->get('reports/outstanding-invoices', 'Api\\V1\\ReportController@outstandingInvoices');
        $router->get('reports/therapist-performance', 'Api\\V1\\ReportController@therapistPerformance');
    });
});
