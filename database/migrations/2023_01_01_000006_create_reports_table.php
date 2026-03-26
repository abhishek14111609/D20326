<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            
            // Reporter (user who submitted the report)
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            
            // Reported user (if applicable)
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // Admin who handled the report
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Report type (user, content, bug, other)
            $table->enum('type', ['user', 'content', 'bug', 'other'])->default('other');
            
            // Report reason/details
            $table->text('reason');
            
            // Report status
            $table->enum('status', ['pending', 'in_progress', 'resolved', 'rejected'])->default('pending');
            
            // Polymorphic relationship for the reported item
            $table->string('reported_type')->nullable()->comment('The type of the reported item (user, post, comment, etc.)');
            $table->unsignedBigInteger('reported_id')->nullable()->comment('The ID of the reported item');
            
            // Evidence (e.g., screenshots, links)
            $table->json('evidence')->nullable()->comment('Array of URLs to evidence (screenshots, etc.)');
            
            // Admin fields
            $table->text('admin_notes')->nullable()->comment('Internal notes about the report');
            $table->text('action_taken')->nullable()->comment('Action taken in response to the report');
            
            // Additional information
            $table->json('additional_info')->nullable()->comment('Additional structured data about the report');
            
            // Timestamps
            $table->timestamp('resolved_at')->nullable()->comment('When the report was resolved');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['reported_type', 'reported_id']);
            $table->index('status');
            $table->index('type');
            $table->index('reporter_id');
            $table->index('reported_user_id');
        });
        
        // Add a reported_count column to the users table
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('reported_count')->default(0)->after('total_points');
            $table->unsignedInteger('report_count')->default(0)->after('reported_count');
            
            // Indexes
            $table->index('reported_count');
            $table->index('report_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop the reports table
        Schema::dropIfExists('reports');
        
        // Remove the reported_count and report_count columns if they exist
        if (Schema::hasColumn('users', 'reported_count')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['reported_count', 'report_count']);
            });
        }
    }
};
