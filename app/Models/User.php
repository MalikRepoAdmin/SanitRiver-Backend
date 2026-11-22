<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 
use Spatie\Permission\Traits\HasRoles;

//mass-assignment method User::create()
#[Fillable([
    'username',
    'password',
    'nama_lengkap',
    'bio',
    'email',
    'tgl_lahir',
    'pekerjaan',
    'domisili',
])
]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */

    //menambah trait HasRoles spatie permission utk manajemen role dan akses
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    //pengaturan Eloquent - Primary Key
    protected $primaryKey = 'user_id';

    //guard spatie agar sesuai seeder ('user')
    protected $guard_name = 'user';

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
     * Relationships
     */

    // Reports submitted by this user.
    public function laporanSungais(): HasMany
    {
        return $this->hasMany(
            LaporanSungai::class,
            'user_id',
            'user_id'
        );
    }
}
