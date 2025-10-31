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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('role')->default(2)->after('email')->comment('1=admin, 2=user');
            $table->string('team_name')->nullable()->after('role');
            $table->string('logo')->nullable()->after('team_name');
            $table->boolean('is_active')->default(true)->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'team_name', 'logo', 'is_active']);
        });
    }
};
