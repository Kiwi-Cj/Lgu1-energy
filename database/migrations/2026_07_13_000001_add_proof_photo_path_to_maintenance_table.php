<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('maintenance', 'proof_photo_path')) {
            Schema::table('maintenance', function (Blueprint $table) {
                $table->string('proof_photo_path')->nullable()->after('completed_date');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('maintenance', 'proof_photo_path')) {
            Schema::table('maintenance', function (Blueprint $table) {
                $table->dropColumn('proof_photo_path');
            });
        }
    }
};
