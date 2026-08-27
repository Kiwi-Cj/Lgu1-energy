<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('maintenance_history')) {
            return;
        }

        $hasProofPhotoPath = Schema::hasColumn('maintenance_history', 'proof_photo_path');
        $hasPhotoRequirement = Schema::hasColumn('maintenance_history', 'photo_requirement');

        if (! $hasProofPhotoPath || ! $hasPhotoRequirement) {
            Schema::table('maintenance_history', function (Blueprint $table) use ($hasProofPhotoPath, $hasPhotoRequirement) {
                if (! $hasProofPhotoPath) {
                    $table->string('proof_photo_path')->nullable()->after('completed_date');
                }
                if (! $hasPhotoRequirement) {
                    $table->string('photo_requirement')->default('Optional')->after('proof_photo_path');
                }
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('maintenance_history')) {
            return;
        }

        $columns = array_values(array_filter(
            ['photo_requirement', 'proof_photo_path'],
            fn (string $column) => Schema::hasColumn('maintenance_history', $column)
        ));

        if ($columns !== []) {
            Schema::table('maintenance_history', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
