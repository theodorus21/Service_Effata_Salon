<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'nickname',
    'parent_name',
    'parent_phone',
    'child_pickup_person',
    'emergency_contact_name',
    'emergency_contact_phone',
    'child_characteristics',
    'child_hobbies',
    'child_photo',
    'reference',
    'phone',
    'bank_account_number',
    'bank_name',
    'teaching_quote',
    'selfie_image',
    'ktp_image',
    'address',
    'birth_date',
    'role',
    'email',
    'password',
    'created_by',
    'created_date',
    'updated_by',
    'updated_date',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_MASTER_ADMIN = 'master_admin';

    public const ROLE_OWNER = 'owner';

    private ?array $permissionCache = null;

    public function hasPermission(string $permission): bool
    {
        if ($this->role === self::ROLE_MASTER_ADMIN) {
            return true;
        }

        $this->permissionCache ??= Role::query()
            ->where('code', $this->role)
            ->first()
            ?->permissions()
            ->pluck('permissions.key')
            ->all() ?? [];

        return in_array($permission, $this->permissionCache, true);
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
            'birth_date' => 'date',
            'created_by' => 'integer',
            'created_date' => 'datetime',
            'updated_by' => 'integer',
            'updated_date' => 'datetime',
        ];
    }
}
