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
        Schema::table('tier_talk_sessions', function (Blueprint $table) {
            $table->boolean('show_average_to_all')->default(true);
            $table->boolean('show_hover_to_all')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tier_talk_sessions', function (Blueprint $table) {
            $table->dropColumn(['show_average_to_all', 'show_hover_to_all']);
        });
    }
};
