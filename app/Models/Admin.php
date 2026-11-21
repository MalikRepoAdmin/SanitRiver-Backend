<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; 

#[Fillable(['email', 'username', 'password'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    /** @use HasFactory<AdminFactory> */

    //menambah trait HasRoles spatie permission utk manajemen role dan akses
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'admins';
    
    //pengaturan Eloquent - Primary Key
    protected $primaryKey = 'admin_id';

    //guard spatie agar sesuai seeder ('admin')
    protected $guard_name = 'admin';

    //mass-assignment method Admin::create()
    protected $fillable = ['username', 'email', 'password'];
    /**
     * Relationships
     */

    /**
     * Rivers managed by this administrator.
     *
     * An administrator can manage multiple rivers,
     * and a river can be managed by multiple administrators.
     */
    public function sungais(): BelongsToMany
    {
        return $this->belongsToMany(
            Sungai::class,
            'admin_sungai',
            'admin_id',
            'sungai_id'
        );
    }
}
