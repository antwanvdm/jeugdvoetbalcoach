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
        Schema::table('opponents', function (Blueprint $table) {
            $table->string('kit_reference')->nullable();
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropForeign(['team_id']);
            $table->dropColumn('team_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opponents', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('longitude')->constrained()->nullOnDelete();
            $table->foreignId('team_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->dropColumn('kit_reference');
        });
    }
};
