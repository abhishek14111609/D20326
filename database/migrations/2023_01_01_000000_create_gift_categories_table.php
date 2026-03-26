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
        Schema::create('gift_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add default gift categories
        $this->seedDefaultCategories();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gift_categories');
    }

    /**
     * Seed default gift categories.
     *
     * @return void
     */
    protected function seedDefaultCategories()
    {
        $categories = [
            [
                'name' => 'Popular',
                'description' => 'Most popular gifts',
                'icon' => 'gifts/categories/popular.png',
                'sort_order' => 1,
            ],
            [
                'name' => 'Love',
                'description' => 'Romantic gifts',
                'icon' => 'gifts/categories/love.png',
                'sort_order' => 2,
            ],
            [
                'name' => 'Celebration',
                'description' => 'Gifts for celebrations',
                'icon' => 'gifts/categories/celebration.png',
                'sort_order' => 3,
            ],
            [
                'name' => 'Funny',
                'description' => 'Funny and playful gifts',
                'icon' => 'gifts/categories/funny.png',
                'sort_order' => 4,
            ],
            [
                'name' => 'Premium',
                'description' => 'Exclusive premium gifts',
                'icon' => 'gifts/categories/premium.png',
                'sort_order' => 5,
            ],
        ];

        foreach ($categories as $category) {
            \App\Models\GiftCategory::create($category);
        }
    }
};
