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
        Schema::create('candidate_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['academic', 'coaching']);
            $table->string('current_designation')->nullable();
            $table->string('highest_degree');
            $table->text('subjects_taught'); // JSON array of subjects, e.g., ["Physics", "Mathematics"]
            $table->text('experience_summary')->nullable();
            $table->decimal('experience_years', 4, 1)->default(0.0);
            $table->string('google_scholar_url')->nullable();
            $table->integer('h_index')->default(0);
            $table->string('demo_video_url')->nullable();
            $table->string('cv_path')->nullable();
            $table->decimal('current_ctc', 10, 2)->nullable();
            $table->decimal('expected_ctc', 10, 2)->nullable();
            $table->string('preferred_location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_profiles');
    }
};
