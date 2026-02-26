<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('facility_meters')) {
            return;
        }

        Schema::create('facility_meters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id')->index();
            $table->string('meter_name');
            $table->string('meter_number')->nullable()->index();
            $table->string('meter_type', 20)->default('sub')->index(); // main | sub
            $table->unsignedBigInteger('parent_meter_id')->nullable()->index();
            $table->string('location')->nullable();
            $table->string('status', 20)->default('active')->index(); // active | inactive
            $table->decimal('multiplier', 12, 4)->default(1);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable()->index();
            $table->string('archive_reason', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facility_meters');
    }
};
