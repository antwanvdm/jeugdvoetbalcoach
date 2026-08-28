<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('football_matches', function (Blueprint $table) {
            $table->unsignedTinyInteger('substitutions_per_quarter_override')
                ->nullable()
                ->after('date');
        });
    }

    public function down(): void
    {
        Schema::table('football_matches', function (Blueprint $table) {
            $table->dropColumn('substitutions_per_quarter_override');
        });
    }
};
