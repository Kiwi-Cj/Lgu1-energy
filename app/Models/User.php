<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

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
    public function facility()
    {
        return $this->belongsTo(\App\Models\Facility::class, 'facility_id');
    }
}
