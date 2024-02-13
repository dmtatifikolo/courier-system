<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Http\Resources\PermissionResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use OwenIt\Auditing\Contracts\Auditable;

class User extends Authenticatable implements Auditable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    use \OwenIt\Auditing\Auditable;

    protected $guard_name = 'sanctum';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    const CASL_PERMISSION_LOOKUP = [
        'create user' => [
            'subject' => 'User',
            'action' => 'create',
        ],
        'update user' => [
            'subject' => 'User',
            'action' => 'update',
        ],
        'view user' => [
            'subject' => 'User',
            'action' => 'view',
        ],
        'delete user' => [
            'subject' => 'User',
            'action' => 'delete',
        ],
        'create role' => [
            'subject' => 'Role',
            'action' => 'create',
        ],
        'update role' => [
            'subject' => 'Role',
            'action' => 'update',
        ],
        'view role' => [
            'subject' => 'Role',
            'action' => 'view',
        ],
        'delete role' => [
            'subject' => 'Role',
            'action' => 'delete',
        ],
        'create permission' => [
            'subject' => 'Permission',
            'action' => 'create',
        ],
        'update permission' => [
            'subject' => 'Permission',
            'action' => 'update',
        ],
        'view permission' => [
            'subject' => 'Permission',
            'action' => 'view',
        ],
        'delete permission' => [
            'subject' => 'Permission',
            'action' => 'delete',
        ],
        'view audit log' => [
            'subject' => 'Audit Log',
            'action' => 'view',
        ],
    ];

    public function getCaslPermissions(){
        $permissions = PermissionResource::collection($this->getAllPermissions());
        $caslPermissions = [];
        foreach ($permissions as  $permission) {
            $caslPermissions[] = self::CASL_PERMISSION_LOOKUP[$permission['name']];
        }

        //add default permission
        $caslPermissions[] = [
            'subject' => 'Auth',
            'action' => 'read',
        ];

        return $caslPermissions;
    }
}
