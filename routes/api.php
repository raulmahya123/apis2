<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\GuidanceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\ProposalController;
use App\Http\Controllers\Api\SeminarController;
use App\Http\Controllers\Api\SupervisorController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/dosen', [UserController::class, 'dosenList']);
    Route::get('/users/mahasiswa', [UserController::class, 'mahasiswaList']);
    Route::get('/users/{user}', [UserController::class, 'show']);
    Route::put('/users/{user}', [UserController::class, 'update']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);

    // Proposals
    Route::get('/proposals', [ProposalController::class, 'index']);
    Route::post('/proposals', [ProposalController::class, 'store']);
    Route::get('/proposals/{proposal}', [ProposalController::class, 'show']);
    Route::put('/proposals/{proposal}', [ProposalController::class, 'update']);
    Route::post('/proposals/{proposal}/review', [ProposalController::class, 'review'])->middleware('role:kaprodi');

    // Supervisors
    Route::post('/proposals/{proposal}/supervisors', [SupervisorController::class, 'assignSupervisor'])->middleware('role:kaprodi');
    Route::get('/proposals/{proposal}/supervisors', [SupervisorController::class, 'proposalSupervisors']);
    Route::get('/my-assignments', [SupervisorController::class, 'myAssignments']);
    Route::post('/supervisors/{supervisor}/respond', [SupervisorController::class, 'respondSupervisor']);

    // Progress
    Route::get('/progress', [ProgressController::class, 'index']);
    Route::post('/progress', [ProgressController::class, 'store']);
    Route::get('/progress/{progress}', [ProgressController::class, 'show']);
    Route::post('/progress/{progress}/review', [ProgressController::class, 'reviewProgress'])->middleware('role:dosen_pembimbing');

    // Guidance
    Route::get('/guidance', [GuidanceController::class, 'index']);
    Route::post('/guidance', [GuidanceController::class, 'store']);
    Route::get('/guidance/{guidanceRequest}', [GuidanceController::class, 'show']);
    Route::post('/guidance/{guidanceRequest}/documents', [GuidanceController::class, 'uploadDocument']);
    Route::post('/guidance/{guidanceRequest}/comments', [GuidanceController::class, 'addComment']);
    Route::post('/guidance/{guidanceRequest}/approve', [GuidanceController::class, 'approveDocument'])->middleware('role:dosen_pembimbing');

    // Seminars
    Route::get('/seminars', [SeminarController::class, 'index']);
    Route::post('/seminars', [SeminarController::class, 'store']);
    Route::get('/seminars/{seminar}', [SeminarController::class, 'show']);
    Route::post('/seminars/{seminar}/approve', [SeminarController::class, 'approve'])->middleware('role:kaprodi');
    Route::post('/seminars/{seminar}/schedule', [SeminarController::class, 'schedule'])->middleware('role:kaprodi,admin_akademik');
    Route::post('/seminars/{seminar}/reviewers', [SeminarController::class, 'assignReviewer'])->middleware('role:kaprodi');
    Route::post('/seminars/{seminar}/results', [SeminarController::class, 'submitResult'])->middleware('role:penguji,dosen_pembimbing');
    Route::post('/seminars/{seminar}/validate-syarat', [SeminarController::class, 'validateSyarat'])->middleware('role:kaprodi,admin_akademik');

    // Documents
    Route::post('/documents/upload', [DocumentController::class, 'upload']);
    Route::get('/documents/versions', [DocumentController::class, 'versions']);
    Route::get('/documents/{documentVersion}', [DocumentController::class, 'show']);
    Route::delete('/documents/{documentVersion}', [DocumentController::class, 'destroy']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

    // Files
    Route::get('/files/{path}', [FileController::class, 'show'])->where('path', '.*');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/report', [DashboardController::class, 'report']);
    Route::get('/export', [DashboardController::class, 'export']);
});
