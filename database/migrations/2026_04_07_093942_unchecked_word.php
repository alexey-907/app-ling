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
        Schema::create('unchecked_words', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_id')->constrained('original_words')->onDelete('cascade');
            $table->string('en')->nullable();
            $table->string('locale');
            $table->string('word');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
