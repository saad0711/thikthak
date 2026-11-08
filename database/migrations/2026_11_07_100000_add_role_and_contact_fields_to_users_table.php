<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the account fields every ThikThak user needs: role, BD phone number,
     * area, address, profile photo and an active flag.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['customer', 'provider', 'admin'])->default('customer')->after('email')->index();
            $table->string('phone', 11)->nullable()->unique()->after('role');
            $table->string('area', 60)->nullable()->after('phone');
            $table->string('address')->nullable()->after('area');
            $table->string('avatar_path')->nullable()->after('address');
            $table->boolean('is_active')->default(true)->after('avatar_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone']);
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'phone', 'area', 'address', 'avatar_path', 'is_active']);
        });
    }
};
