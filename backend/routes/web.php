<?php

use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\LearningClassController;
use App\Http\Controllers\LearningClassLessonActionController;
use App\Http\Controllers\LearningClassLessonController;
use App\Http\Controllers\LearningClassScheduleController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\RoleAccessController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/signin');

Route::get('/signin', [SessionController::class, 'create'])
    ->middleware('guest')
    ->name('signin');
Route::post('/signin', [SessionController::class, 'store'])
    ->middleware('throttle:5,1')
    ->middleware('guest')
    ->name('signin.store');
Route::view('/signup', 'signup')->name('signup');

Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::view('/dashboard', 'dashboard')
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    Route::get('/role-access', [RoleAccessController::class, 'index'])
        ->middleware('permission:role_access.view')
        ->name('role-access.index');
    Route::get('/role-access/create', [RoleAccessController::class, 'create'])
        ->middleware('permission:role_access.create')
        ->name('role-access.create');
    Route::post('/role-access', [RoleAccessController::class, 'store'])
        ->middleware('permission:role_access.create')
        ->name('role-access.store');
    Route::get('/role-access/{role}/edit', [RoleAccessController::class, 'edit'])
        ->middleware('permission:role_access.update')
        ->name('role-access.edit');
    Route::put('/role-access/{role}', [RoleAccessController::class, 'update'])
        ->middleware('permission:role_access.update')
        ->name('role-access.update');
    Route::delete('/role-access/{role}', [RoleAccessController::class, 'destroy'])
        ->middleware('permission:role_access.delete')
        ->name('role-access.destroy');
    Route::get('/role-access/{role}/permissions', [RoleAccessController::class, 'editPermissions'])
        ->middleware('permission:role_access.permissions')
        ->name('role-access.permissions.edit');
    Route::put('/role-access/{role}/permissions', [RoleAccessController::class, 'updatePermissions'])
        ->middleware('permission:role_access.permissions')
        ->name('role-access.permissions.update');

    Route::get('/class-schedules', [LearningClassScheduleController::class, 'index'])
        ->middleware('permission:class_schedules.view')
        ->name('class-schedules.index');
    Route::get('/class-schedules/{learningClass}', [LearningClassScheduleController::class, 'show'])
        ->middleware('permission:class_schedules.view')
        ->name('class-schedules.show');
    Route::post('/learning-classes/{learningClass}/generate-lessons', [LearningClassLessonController::class, 'generate'])
        ->middleware('permission:learning_classes.generate')
        ->name('learning-classes.lessons.generate');
    Route::post('/class-schedules/{learningClass}/lessons/{lesson}/attendance', [LearningClassLessonActionController::class, 'attendance'])
        ->middleware('permission:class_schedules.attendance')
        ->name('class-schedules.lessons.attendance');
    Route::get('/class-schedules/{learningClass}/lessons/{lesson}/report', [LearningClassLessonActionController::class, 'editReport'])
        ->middleware('permission:class_schedules.report')
        ->name('class-schedules.lessons.report.edit');
    Route::post('/class-schedules/{learningClass}/lessons/{lesson}/report', [LearningClassLessonActionController::class, 'updateReport'])
        ->middleware('permission:class_schedules.report')
        ->name('class-schedules.lessons.report.update');
    Route::get('/class-schedules/{learningClass}/lessons/{lesson}/photo', [LearningClassLessonActionController::class, 'lessonPhoto'])
        ->middleware('permission:class_schedules.photo')
        ->name('class-schedules.lessons.photo');
    Route::get('/class-schedules/{learningClass}/lessons/{lesson}/reschedule', [LearningClassLessonActionController::class, 'editReschedule'])
        ->middleware('permission:class_schedules.reschedule')
        ->name('class-schedules.lessons.reschedule.edit');
    Route::get('/class-schedules/{learningClass}/lessons/{lesson}', [LearningClassLessonActionController::class, 'show'])
        ->middleware('permission:class_schedules.view')
        ->name('class-schedules.lessons.show');
    Route::put('/class-schedules/{learningClass}/lessons/{lesson}/reschedule', [LearningClassLessonActionController::class, 'updateReschedule'])
        ->middleware('permission:class_schedules.reschedule')
        ->name('class-schedules.lessons.reschedule.update');
    Route::post('/class-schedules/{learningClass}/lessons/{lesson}/cancel', [LearningClassLessonActionController::class, 'cancel'])
        ->middleware('permission:class_schedules.cancel')
        ->name('class-schedules.lessons.cancel');

    Route::get('/learning-classes', [LearningClassController::class, 'index'])
        ->middleware('permission:learning_classes.view')
        ->name('learning-classes.index');
    Route::get('/learning-classes/create', [LearningClassController::class, 'create'])
        ->middleware('permission:learning_classes.create')
        ->name('learning-classes.create');
    Route::post('/learning-classes', [LearningClassController::class, 'store'])
        ->middleware('permission:learning_classes.create')
        ->name('learning-classes.store');
    Route::get('/learning-classes/{learningClass}', [LearningClassController::class, 'show'])
        ->middleware('permission:learning_classes.show')
        ->name('learning-classes.show');
    Route::get('/learning-classes/{learningClass}/edit', [LearningClassController::class, 'edit'])
        ->middleware('permission:learning_classes.update')
        ->name('learning-classes.edit');
    Route::put('/learning-classes/{learningClass}', [LearningClassController::class, 'update'])
        ->middleware('permission:learning_classes.update')
        ->name('learning-classes.update');
    Route::patch('/learning-classes/{learningClass}', [LearningClassController::class, 'update'])
        ->middleware('permission:learning_classes.update')
        ->name('learning-classes.update.patch');
    Route::delete('/learning-classes/{learningClass}', [LearningClassController::class, 'destroy'])
        ->middleware('permission:learning_classes.delete')
        ->name('learning-classes.destroy');

    Route::prefix('settings')
        ->name('settings.')
        ->controller(SettingsController::class)
        ->group(function (): void {
            Route::get('/', 'index')->middleware('permission:settings.view')->name('index');
            Route::get('/programs/create', 'create')->middleware('permission:settings.programs.create')->name('programs.create');
            Route::post('/programs', 'store')->middleware('permission:settings.programs.create')->name('programs.store');
            Route::get('/programs/{program}/edit', 'edit')->middleware('permission:settings.programs.update')->name('programs.edit');
            Route::put('/programs/{program}', 'update')->middleware('permission:settings.programs.update')->name('programs.update');
            Route::delete('/programs/{program}', 'destroy')->middleware('permission:settings.programs.delete')->name('programs.destroy');
            Route::get('/class-categories/create', 'createCategory')->middleware('permission:settings.categories.create')->name('class-categories.create');
            Route::post('/class-categories', 'storeCategory')->middleware('permission:settings.categories.create')->name('class-categories.store');
            Route::get('/class-categories/{classCategory}/edit', 'editCategory')->middleware('permission:settings.categories.update')->name('class-categories.edit');
            Route::put('/class-categories/{classCategory}', 'updateCategory')->middleware('permission:settings.categories.update')->name('class-categories.update');
            Route::delete('/class-categories/{classCategory}', 'destroyCategory')->middleware('permission:settings.categories.delete')->name('class-categories.destroy');
            Route::get('/lesson-statuses/create', 'createLessonStatus')->middleware('permission:settings.lesson_statuses.create')->name('lesson-statuses.create');
            Route::post('/lesson-statuses', 'storeLessonStatus')->middleware('permission:settings.lesson_statuses.create')->name('lesson-statuses.store');
            Route::get('/lesson-statuses/{lessonStatus}/edit', 'editLessonStatus')->middleware('permission:settings.lesson_statuses.update')->name('lesson-statuses.edit');
            Route::put('/lesson-statuses/{lessonStatus}', 'updateLessonStatus')->middleware('permission:settings.lesson_statuses.update')->name('lesson-statuses.update');
            Route::delete('/lesson-statuses/{lessonStatus}', 'destroyLessonStatus')->middleware('permission:settings.lesson_statuses.delete')->name('lesson-statuses.destroy');
        });

    Route::prefix('master-data/{role}')
        ->name('master-data.')
        ->where(['role' => 'guru|admin|murid'])
        ->controller(MasterDataController::class)
        ->middleware('permission:master_data')
        ->group(function (): void {
            Route::get('/', 'index')->name('index');
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::get('/{user}', 'show')->name('show');
            Route::get('/{user}/images/{image}', 'image')
                ->where(['image' => 'selfie_image|ktp_image|child_photo'])
                ->name('image');
            Route::get('/{user}/edit', 'edit')->name('edit');
            Route::put('/{user}', 'update')->name('update');
            Route::delete('/{user}', 'destroy')->name('destroy');
        });
});
