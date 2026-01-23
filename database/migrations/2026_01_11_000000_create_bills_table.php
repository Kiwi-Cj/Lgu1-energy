<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facility_id')->constrained('facilities')->onDelete('cascade');
            $table->string('month', 7); // Format: YYYY-MM
            $table->decimal('kwh_consumed', 10, 2);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_bill', 12, 2)->nullable();
            $table->enum('status', ['Paid', 'Unpaid', 'Pending'])->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
