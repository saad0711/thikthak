<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->text('bio')->nullable();
            $table->string('photo_path')->nullable();
            $table->unsignedTinyInteger('years_experience')->nullable();
            $table->string('area_name', 100)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedSmallInteger('radius_km')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'bio', 'photo_path', 'years_experience', 'area_name',
                'latitude', 'longitude', 'radius_km',
            ]);
        });
    }
};
