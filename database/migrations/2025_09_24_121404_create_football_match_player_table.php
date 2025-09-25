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
        Schema::create('football_match_player', function (Blueprint $table) {
            $table->foreignId('player_id')->constrained()->cascadeOnDelete();
            $table->foreignId('football_match_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('quarter');
            $table->primary(['player_id', 'football_match_id', 'quarter']);
            $table->foreignId('position_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('football_match_player');
    }
};
