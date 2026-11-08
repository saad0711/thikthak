<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->boolean('is_online')->default(false)->after('user_id');
            $table->unsignedSmallInteger('slot_minutes')->default(60)->after('is_online');
            $table->unsignedTinyInteger('completeness_percentage')->default(100)->after('slot_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('provider_profiles', function (Blueprint $table) {
            $table->dropColumn(['is_online', 'slot_minutes', 'completeness_percentage']);
        });
    }
};
