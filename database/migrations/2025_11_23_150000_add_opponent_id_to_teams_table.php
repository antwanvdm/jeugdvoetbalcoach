<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('opponent_id')->nullable()->after('maps_location')->constrained('opponents')->nullOnDelete();
            $table->dropColumn('name');
            $table->dropColumn('logo');
            $table->dropColumn('maps_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Voeg kolommen terug
        Schema::table('teams', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->string('logo')->nullable();
            $table->string('maps_location')->nullable();
            $table->dropConstrainedForeignId('opponent_id');
        });

    }
};
