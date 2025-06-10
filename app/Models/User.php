<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'first_name',
        'last_name',
        'username',
        'phone',
        'role',
        'status',
        'provider',
        'provider_id',
        'remember_token',
        'email_verified_at',
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
        ];
    }

    public function providers()
    {
        return $this->hasMany(AuthUserProvider::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    public function people()
    {
        return $this->belongsToMany(Person::class);
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for inactive users
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Scope for suspended users
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole('administrator');
    }

    public function isStudent(): bool
    {
        return $this->hasRole('student');
    }

    public function isSecretary(): bool
    {
        return $this->hasRole('secretary');
    }

    public function isInstructor(): bool
    {
        return $this->hasRole('instructor');
    }

    public function isLegalRepresentative(): bool
    {
        return $this->hasRole('legal_representative');
    }

    public function getPrimaryRole(): ?string
    {
        return $this->roles->first()?->name;
    }

    // Check if user can manage another user
    public function canManageUser(User $user): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        if ($this->isSecretary() && $user->isStudent()) {
            return true;
        }

        return false;
    }

    public function canViewAcademicRecords(User $user): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        if ($this->id === $user->id) {
            return true;
        }

        if ($this->isLegalRepresentative() && $this->representsStudent($user)) {
            return true;
        }

        return false;
    }

    public function scopeWithRole($query, string $role)
    {
        return $query->role($role);
    }

    public static function getByRole(string $role)
    {
        return static::role($role)->get();
    }

    public function representedStudents()
    {
        return $this->belongsToMany(
            User::class,
            'legal_representatives',
            'representative_id',
            'student_id'
        )->withPivot([
            'relationship_type',
            'permissions',
            'is_primary',
            'valid_from',
            'valid_until'
        ])->wherePivot('status', 'active');
    }

    public function legalRepresentatives()
    {
        return $this->belongsToMany(
            User::class,
            'legal_representatives',
            'student_id',
            'representative_id'
        )->withPivot([
            'relationship_type',
            'permissions',
            'is_primary',
            'status',
            'valid_from',
            'valid_until'
        ])->wherePivot('status', 'active');
    }

    public function representsStudent(User $student): bool
    {
        return $this->representedStudents()
            ->where('users.id', $student->id)
            ->exists();
    }
}
