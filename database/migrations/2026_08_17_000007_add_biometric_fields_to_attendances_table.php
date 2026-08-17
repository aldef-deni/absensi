<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('face_verified')->default(false)->after('note');
            $table->decimal('distance_in', 9, 1)->nullable()->after('face_verified');
            $table->decimal('distance_out', 9, 1)->nullable()->after('distance_in');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['distance_out', 'distance_in', 'face_verified']);
        });
    }
};
