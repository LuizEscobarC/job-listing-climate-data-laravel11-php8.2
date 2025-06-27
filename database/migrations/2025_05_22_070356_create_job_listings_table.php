<?php

use App\Enums\JobType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The recruiter who posted the job
            $table->string('title');
            $table->text('description');
            $table->string('company_name');
            $table->string('location');
            $table->enum('type', array_column(JobType::cases(), 'value'));
            $table->decimal('salary', 10, 2)->nullable();
            $table->json('requirements')->nullable();
            $table->json('benefits')->nullable();
            $table->date('expiration_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('vacancies')->default(1);
            $table->string('experience_level')->nullable(); // Junior, Mid, Senior, etc.
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
