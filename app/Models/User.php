<?php
namespace App\Models;

    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Support\Facades\Storage;
    use Laravel\Sanctum\HasApiTokens;
    use App\Models\Traits\BelongsToFacility;

    class User extends Authenticatable
    {
        use HasFactory, Notifiable;

        private ?array $notificationPanelCache = null;

        // OTP relationship
        public function otps()
        {
            return $this->hasMany(\App\Models\Otp::class);
        }

        // User notifications
        public function notifications()
        {
            return $this->hasMany(Notification::class);
        }

        public function notificationPanelData(): array
        {
            if ($this->notificationPanelCache !== null) {
                return $this->notificationPanelCache;
            }

            return $this->notificationPanelCache = [
                'notifications' => $this->notifications()
                    ->orderByDesc('created_at')
                    ->take(10)
                    ->get(),
                'unreadNotifCount' => $this->notifications()
                    ->whereNull('read_at')
                    ->count(),
            ];
        }

        // Add your fillable, hidden, casts, etc. as needed
        protected $fillable = [
            'full_name',
            'name',
            'email',
            'username',
            'password',
            'role',
            'department',
            'contact_number',
            'status',
            'last_login',
        ];
        protected $hidden = [
            'password', 'remember_token',
        ];

        // Many-to-many: User can have many facilities
        public function facilities()
        {
            return $this->belongsToMany(Facility::class, 'facility_user', 'user_id', 'facility_id');
        }

        public function encodedSubmeterReadings()
        {
            return $this->hasMany(SubmeterReading::class, 'encoded_by_user_id');
        }

        public function approvedSubmeterReadings()
        {
            return $this->hasMany(SubmeterReading::class, 'approved_by_engineer_id');
        }

        public function encodedMainMeterReadings()
        {
            return $this->hasMany(MainMeterReading::class, 'encoded_by');
        }

        public function approvedMainMeterReadings()
        {
            return $this->hasMany(MainMeterReading::class, 'approved_by');
        }

        // Accessor for profile photo URL
        public function getProfilePhotoUrlAttribute()
        {
            if ($this->profile_photo_path) {
                $path = ltrim((string) $this->profile_photo_path, '/');

                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }

                if (str_starts_with($path, 'uploads/') || str_starts_with($path, 'img/')) {
                    return asset($path);
                }

                if (str_starts_with($path, 'storage/')) {
                    $path = substr($path, strlen('storage/'));
                }

                if ($path !== '' && Storage::disk('public')->exists($path)) {
                    return asset('storage/' . $path);
                }
            }
            return asset('img/default-avatar.png');
        }
    }
