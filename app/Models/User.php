<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nomor_induk',
        'jurusan',
        'angkatan',
        'no_telepon',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    public function mahasiswaProposals()
    {
        return $this->hasMany(ResearchProposal::class, 'mahasiswa_id');
    }

    public function reviewedProposals()
    {
        return $this->hasMany(ResearchProposal::class, 'reviewed_by');
    }

    public function supervisorAssignments()
    {
        return $this->hasMany(ResearchSupervisor::class, 'dosen_id');
    }

    public function assignedSupervisors()
    {
        return $this->hasMany(ResearchSupervisor::class, 'assigned_by');
    }

    public function guidanceRequests()
    {
        return $this->hasMany(GuidanceRequest::class, 'mahasiswa_id');
    }

    public function guidanceDosen()
    {
        return $this->hasMany(GuidanceRequest::class, 'dosen_id');
    }

    public function seminarMahasiswa()
    {
        return $this->hasMany(Seminar::class, 'mahasiswa_id');
    }

    public function seminarReviewers()
    {
        return $this->hasMany(SeminarReviewer::class, 'dosen_id');
    }

    public function seminarResults()
    {
        return $this->hasMany(SeminarResult::class, 'penguji_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function sentNotifications()
    {
        return $this->hasMany(Notification::class, 'pengirim_id');
    }
}
