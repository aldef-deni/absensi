<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->decimal('latitude', 10, 7)->nullable()->after('timezone');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedInteger('radius_meters')->default(100)->after('longitude');
            $table->boolean('use_location_lock')->default(false)->after('radius_meters');
            $table->boolean('use_face_biometric')->default(false)->after('use_location_lock');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['use_face_biometric', 'use_location_lock', 'radius_meters', 'longitude', 'latitude']);
        });
    }
};
