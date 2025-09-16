<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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

    /**
     * Stores assigned to the user.
     */
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class)->withTimestamps()->withPivot('role');
    }

    public function hasRoleValue(UserRole $role): bool
    {
        if ($this->hasRole($role->value)) {
            return true;
        }

        $registrar = app(PermissionRegistrar::class);
        $currentTeam = $registrar->getPermissionsTeamId();

        if ($currentTeam === null) {
            return false;
        }

        $registrar->setPermissionsTeamId(null);
        $hasGlobalRole = $this->hasRole($role->value);
        $registrar->setPermissionsTeamId($currentTeam);

        return $hasGlobalRole;
    }

    /**
     * Determine if the user holds any of the provided roles.
     *
     * @param  array<int, UserRole>  $roles
     */
    public function hasAnyRoleValue(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRoleValue($role)) {
                return true;
            }
        }

        return false;
    }
}
