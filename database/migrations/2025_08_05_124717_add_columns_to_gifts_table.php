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
        Schema::table('gifts', function (Blueprint $table) {
            // Rename existing columns to avoid conflicts
            $table->renameColumn('sender_id', 'old_sender_id');
            $table->renameColumn('receiver_id', 'old_receiver_id');
            $table->renameColumn('gift_type', 'old_gift_type');
            $table->renameColumn('context', 'old_context');
            
            // Add new columns
            $table->string('name')->after('id');
            $table->text('description')->nullable()->after('name');
            $table->string('image_path')->nullable()->after('description');
            $table->decimal('price', 10, 2)->default(0)->after('image_path');
            $table->boolean('is_active')->default(true)->after('price');
            $table->foreignId('category_id')->nullable()->constrained('gift_categories')->after('is_active');
            $table->softDeletes();
            
            // Add new foreign key columns
            $table->unsignedBigInteger('sender_id')->after('id');
            $table->unsignedBigInteger('receiver_id')->after('sender_id');
            $table->string('type', 50)->after('receiver_id');
        });
        
        // Migrate data from old columns to new structure if needed
        // This is a simplified example - you might need to adjust based on your actual data
        DB::table('gifts')->update([
            'name' => DB::raw('old_gift_type'),
            'description' => DB::raw('old_context'),
            'sender_id' => DB::raw('old_sender_id'),
            'receiver_id' => DB::raw('old_receiver_id'),
            'type' => DB::raw('old_gift_type'),
            'is_active' => true
        ]);
        
        // Drop old columns after migration
        Schema::table('gifts', function (Blueprint $table) {
            $table->dropColumn(['old_sender_id', 'old_receiver_id', 'old_gift_type', 'old_context']);
            
            // Add foreign key constraints
            $table->foreign('sender_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
                  
            $table->foreign('receiver_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gifts', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['category_id']);
            $table->dropForeign(['sender_id']);
            $table->dropForeign(['receiver_id']);
            
            // Rename columns back to original
            $table->renameColumn('sender_id', 'old_sender_id');
            $table->renameColumn('receiver_id', 'old_receiver_id');
            $table->renameColumn('type', 'old_type');
            
            // Add back original columns
            $table->unsignedBigInteger('sender_id')->after('id');
            $table->unsignedBigInteger('receiver_id')->after('sender_id');
            $table->string('gift_type', 100)->after('receiver_id');
            $table->string('context', 100)->after('gift_type');
            
            // Migrate data back
            DB::table('gifts')->update([
                'sender_id' => DB::raw('old_sender_id'),
                'receiver_id' => DB::raw('old_receiver_id'),
                'gift_type' => DB::raw('type'),
                'context' => DB::raw('description')
            ]);
            
            // Drop temporary columns
            $table->dropColumn([
                'old_sender_id',
                'old_receiver_id',
                'old_type',
                'name',
                'description',
                'image_path',
                'price',
                'is_active',
                'category_id',
                'type',
                'deleted_at'
            ]);
            
            // Re-add original foreign keys
            $table->foreign('sender_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
                  
            $table->foreign('receiver_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};
