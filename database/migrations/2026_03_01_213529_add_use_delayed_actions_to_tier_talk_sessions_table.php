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
            $table->boolean('use_delayed_actions')->default(true)->after('show_hover_to_all');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tier_talk_sessions', function (Blueprint $table) {
            $table->dropColumn('use_delayed_actions');
        });
    }
};
