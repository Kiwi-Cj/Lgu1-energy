<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('facilities')) {
            Schema::create('facilities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type')->nullable();
                $table->string('location')->nullable();
                $table->decimal('floor_area', 12, 2)->nullable();
                $table->string('status')->default('active');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('facility_id')->nullable();
                $table->string('full_name')->nullable();
                $table->string('name')->nullable();
                $table->string('email')->unique();
                $table->string('username')->nullable()->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('role')->default('staff');
                $table->string('status')->default('active');
                $table->string('contact_number')->nullable();
                $table->string('department')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        if (!Schema::hasTable('energy_profiles')) {
            Schema::create('energy_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('facility_id')->nullable();
                $table->decimal('average_monthly_kwh', 14, 2)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('energy_records')) {
            Schema::create('energy_records', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('facility_id')->nullable();
                $table->integer('year')->nullable();
                $table->string('month', 2)->nullable();
                $table->date('date')->nullable();
                $table->decimal('actual_kwh', 14, 2)->nullable();
                $table->tinyInteger('alert')->default(0);
                $table->unsignedBigInteger('recorded_by')->nullable();
                $table->decimal('energy_cost', 14, 2)->nullable();
                $table->string('bill_image')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('energy_incidents')) {
            Schema::create('energy_incidents', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('facility_id')->nullable();
                $table->string('severity')->nullable();
                $table->string('status')->default('Pending');
                $table->text('message')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('maintenance')) {
            Schema::create('maintenance', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('facility_id')->nullable();
                $table->string('maintenance_status')->nullable();
                $table->string('trend')->nullable();
                $table->string('efficiency_rating')->nullable();
                $table->text('description')->nullable();
                $table->date('completed_date')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('otps')) {
            Schema::create('otps', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('code', 10);
                $table->timestamp('expires_at');
                $table->boolean('used')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Keep base tables on rollback to avoid accidental data loss.
    }
};
