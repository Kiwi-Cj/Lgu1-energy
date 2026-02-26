<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('facility_audit_logs')) {
            return;
        }

        Schema::create('facility_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id')->nullable()->index();
            $table->string('facility_name')->nullable();
            $table->string('action', 50)->index();
            $table->string('reason', 500)->nullable();
            $table->unsignedBigInteger('performed_by')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_audit_logs');
    }
};
