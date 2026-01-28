<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Traits\BelongsToFacility;

class User extends Authenticatable
{
    use Notifiable;

    // OTP relationship
    public function otps()
    {
        return $this->hasMany(\App\Models\Otp::class);
    }

    // Add your fillable, hidden, casts, etc. as needed
    protected $fillable = [
        'full_name',
        'email',
        'username',
        'password',
        'role',
        'department',
        'contact_number',
        'status',
        'last_login',
        'facility_id', // Facility assignment for Staff users
    ];
    protected $hidden = [
        'password', 'remember_token',
    ];

    // Facility relationship (optional, if user is assigned to a facility)
    use BelongsToFacility;
}
