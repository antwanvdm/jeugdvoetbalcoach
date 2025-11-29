<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('match_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('football_match_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('assist_player_id')->nullable()->constrained('players')->nullOnDelete();
            $table->integer('minute')->nullable();
            $table->string('subtype')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_goals');
    }
};
