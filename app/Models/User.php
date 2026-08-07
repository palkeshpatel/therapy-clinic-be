<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role_id',
        'status',
        'salary',
        'birth_date',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'role_id' => 'integer',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isAdministrator(): bool
    {
        $this->loadMissing('role');

        return $this->role
            && strcasecmp((string) $this->role->role_name, 'Admin') === 0;
    }

    public function hasPermission(string $module, string $action): bool
    {
        if ($this->isAdministrator()) {
            return true;
        }

        $this->loadMissing('role.permissions');

        if (! $this->role) {
            return false;
        }

        return $this->role->permissions->contains(function ($permission) use ($module, $action) {
            return $permission->module === $module && $permission->action === $action;
        });
    }

    /** @return list<string> e.g. ["patients:view", "scheduling:create"] or ["*"] for Admin */
    public function permissionKeys(): array
    {
        if ($this->isAdministrator()) {
            return ['*'];
        }

        $this->loadMissing('role.permissions');

        if (! $this->role) {
            return [];
        }

        return $this->role->permissions
            ->map(fn ($p) => "{$p->module}:{$p->action}")
            ->values()
            ->all();
    }

    public function toArrayWithPermissions(): array
    {
        $data = $this->toArray();
        $data['permissions'] = $this->permissionKeys();

        return $data;
    }

    public function therapist()
    {
        return $this->hasOne(Therapist::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }
}
