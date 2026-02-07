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
        Schema::create('tier_talk_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('host_token', 64)->unique();
            $table->string('slug', 32)->unique();
            $table->string('title')->nullable();
            $table->integer('max_participants')->default(50);
            $table->enum('status', ['active', 'ended'])->default('active');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tier_talk_sessions');
    }
};
