<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenance_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('facility_id');
            $table->string('issue_type');
            $table->string('trigger_month');
            $table->string('efficiency_rating');
            $table->string('trend');
            $table->string('maintenance_type')->default('Preventive');
            $table->string('maintenance_status')->default('Completed');
            $table->date('scheduled_date')->nullable();
            $table->string('assigned_to')->nullable();
            $table->date('completed_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->foreign('facility_id')->references('id')->on('facilities')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_history');
    }
};