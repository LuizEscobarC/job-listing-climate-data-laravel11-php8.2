<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('climate_data', function (Blueprint $table) {
            $table->id();
            $table->timestamp('recorded_at')->index();
            $table->decimal('temperature', 5, 2);
            $table->string('source')->default('csv_import');
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
            
            // Index para consultas de performance
            $table->index(['recorded_at', 'temperature']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('climate_data');
    }
};
