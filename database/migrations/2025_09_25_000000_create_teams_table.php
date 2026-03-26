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
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->foreignId('leader_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('active'); // active, disqualified, withdrawn
            $table->integer('size')->default(1);
            $table->integer('score')->default(0);
            $table->integer('rank')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create team_user pivot table for team members
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('member'); // member, co-leader, etc.
            $table->string('status')->default('pending'); // pending, accepted, rejected
            $table->timestamps();
            
            // Ensure a user can only be in one team per competition
            $table->unique(['team_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_user');
        Schema::dropIfExists('teams');
    }
};
