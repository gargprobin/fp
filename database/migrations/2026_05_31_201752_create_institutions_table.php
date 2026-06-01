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
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->enum('type', ['school', 'college', 'university', 'coaching_center']);
            $table->string('website')->nullable();
            $table->text('description')->nullable();
            $table->string('location');
            $table->json('branding_settings')->nullable(); // Store colors, logos, layout styles: {"primary_color": "#123456", "theme_preset": "emerald"}
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
