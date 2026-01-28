<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('energy_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id');
            $table->string('action_type');
            $table->text('description');
            $table->string('priority');
            $table->date('target_date');
            $table->string('status')->default('Active');
            $table->decimal('risk_score', 6, 2)->nullable();
            $table->string('alert_level')->nullable();
            $table->string('trigger_reason')->nullable();
            $table->decimal('current_kwh', 12, 2)->nullable();
            $table->decimal('baseline_kwh', 12, 2)->nullable();
            $table->decimal('deviation', 6, 2)->nullable();
            $table->timestamps();
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('energy_actions');
    }
};
