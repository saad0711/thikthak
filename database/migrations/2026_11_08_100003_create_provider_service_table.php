<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pivot table: which provider offers which service, and at what price.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();

            // The provider's own price in BDT.
            $table->decimal('price', 10, 2);
            $table->timestamps();

            // A provider can list the same service only once.
            $table->unique(['provider_profile_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_service');
    }
};
