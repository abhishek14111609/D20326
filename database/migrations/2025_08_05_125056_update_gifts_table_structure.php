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
        // First, add the new columns as nullable
        Schema::table('gifts', function (Blueprint $table) {
            // Add new columns as nullable first
            $table->string('name')->nullable()->after('id');
            $table->text('description')->nullable()->after('name');
            $table->string('image_path')->nullable()->after('description');
            $table->decimal('price', 10, 2)->default(0)->after('image_path');
            $table->boolean('is_active')->default(true)->after('price');
            $table->foreignId('category_id')->nullable()->constrained('gift_categories')->after('is_active');
            $table->softDeletes();
            
            // Rename existing columns to maintain backward compatibility
            $table->renameColumn('gift_type', 'type');
        });
        
        // Update existing records with default values
        DB::table('gifts')->update([
            'name' => DB::raw('type'),
            'description' => DB::raw('context'),
            'is_active' => true
        ]);
        
        // Make name column non-nullable after populating it
        Schema::table('gifts', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the columns we added
        Schema::table('gifts', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['category_id']);
            
            // Drop columns
            $table->dropColumn([
                'name',
                'description',
                'image_path',
                'price',
                'is_active',
                'category_id',
                'deleted_at'
            ]);
            
            // Rename back to original column name
            $table->renameColumn('type', 'gift_type');
        });
    }
};
