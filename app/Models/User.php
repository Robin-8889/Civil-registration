<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'registration_office_id',
        'role',
        'status',
        'is_system_admin',
        'is_approved',
        'email_verified_at',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string',
            'status' => 'string',
            'is_system_admin' => 'boolean',
            'is_approved' => 'boolean',
        ];
    }

    /**
     * Get the registration office where this user works
     */
    public function office()
    {
        return $this->belongsTo(RegistrationOffice::class, 'registration_office_id');
    }

    /**
     * Get citizen profile associated with this user
     */
    public function citizen()
    {
        return $this->hasOne(Citizen::class, 'user_id');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is a registrar
     */
    public function isRegistrar()
    {
        return $this->role === 'registrar';
    }

    /**
     * Check if user is a clerk
     */
    public function isClerk()
    {
        return $this->role === 'clerk';
    }

    /**
     * Check if user is a citizen
     */
    public function isCitizen()
    {
        return $this->role === 'citizen';
    }

    /**
     * Check if user is a system admin
     */
    public function isSystemAdmin()
    {
        return $this->is_system_admin === true;
    }

    /**
     * Check if user is approved as registrar/staff
     */
    public function isApproved()
    {
        return $this->is_approved === true;
    }

    /**
     * Check if user has access to system
     */
    public function hasSystemAccess()
    {
        // System admin has full access
        if ($this->isSystemAdmin()) {
            return true;
        }

        // Approved registrars/clerks can access their office data
        if ($this->isApproved() && ($this->isRegistrar() || $this->isClerk())) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can manage office (office admin)
     */
    public function isOfficeAdmin()
    {
        return $this->isAdmin() && !$this->isSystemAdmin();
    }

    /**
     * Check if user can access records in a specific office
     */
    public function canAccessOffice($officeId)
    {
        // System admin can access all offices
        if ($this->isSystemAdmin()) {
            return true;
        }

        // Registrars can access records from any office in their region
        if ($this->isApproved() && $this->isRegistrar() && $this->office) {
            // Get the office of the record
            $recordOffice = RegistrationOffice::find($officeId);

            // Allow access if both offices are in the same region
            if ($recordOffice && $this->office->region === $recordOffice->region) {
                return true;
            }
        }

        // Office admin can access their assigned office only
        if ($this->registration_office_id == $officeId && $this->isApproved()) {
            return true;
        }

        return false;
    }
}
