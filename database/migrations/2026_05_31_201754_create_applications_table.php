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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacancy_id')->constrained('vacancies')->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained('users')->onDelete('cascade');
            $table->text('cover_letter')->nullable();
            $table->enum('status', [
                'applied', 
                'shortlisted_by_admin', 
                'shortlisted_by_institution', 
                'unlocked', 
                'written_test', 
                'interview', 
                'demo', 
                'appointed', 
                'rejected'
            ])->default('applied');
            $table->decimal('demo_rating', 3, 1)->nullable(); // rubrics rating scale 1.0 to 10.0
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
