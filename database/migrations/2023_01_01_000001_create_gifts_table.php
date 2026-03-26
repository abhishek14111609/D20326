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
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->decimal('price', 10, 2);
            $table->foreignId('category_id')->constrained('gift_categories')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Add default gifts
        $this->seedDefaultGifts();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gifts');
    }

    /**
     * Seed default gifts.
     *
     * @return void
     */
    protected function seedDefaultGifts()
    {
        $popularCategory = \App\Models\GiftCategory::where('name', 'Popular')->first();
        $loveCategory = \App\Models\GiftCategory::where('name', 'Love')->first();
        $celebrationCategory = \App\Models\GiftCategory::where('name', 'Celebration')->first();
        $funnyCategory = \App\Models\GiftCategory::where('name', 'Funny')->first();
        $premiumCategory = \App\Models\GiftCategory::where('name', 'Premium')->first();

        $gifts = [
            // Popular gifts
            [
                'name' => 'Rose',
                'description' => 'A beautiful red rose',
                'image_path' => 'gifts/rose.png',
                'price' => 1.99,
                'category_id' => $popularCategory->id,
            ],
            [
                'name' => 'Chocolate',
                'description' => 'Delicious chocolate box',
                'image_path' => 'gifts/chocolate.png',
                'price' => 2.99,
                'category_id' => $popularCategory->id,
            ],
            [
                'name' => 'Teddy Bear',
                'description' => 'Cute teddy bear',
                'image_path' => 'gifts/teddy-bear.png',
                'price' => 4.99,
                'category_id' => $popularCategory->id,
            ],
            
            // Love gifts
            [
                'name' => 'Heart Locket',
                'description' => 'Beautiful heart-shaped locket',
                'image_path' => 'gifts/heart-locket.png',
                'price' => 3.99,
                'category_id' => $loveCategory->id,
            ],
            [
                'name' => 'Love Letter',
                'description' => 'Romantic love letter',
                'image_path' => 'gifts/love-letter.png',
                'price' => 0.99,
                'category_id' => $loveCategory->id,
            ],
            
            // Celebration gifts
            [
                'name' => 'Champagne',
                'description' => 'Bottle of champagne',
                'image_path' => 'gifts/champagne.png',
                'price' => 5.99,
                'category_id' => $celebrationCategory->id,
            ],
            [
                'name' => 'Cake',
                'description' => 'Celebration cake',
                'image_path' => 'gifts/cake.png',
                'price' => 4.99,
                'category_id' => $celebrationCategory->id,
            ],
            
            // Funny gifts
            [
                'name' => 'Clown Nose',
                'description' => 'Funny clown nose',
                'image_path' => 'gifts/clown-nose.png',
                'price' => 1.49,
                'category_id' => $funnyCategory->id,
            ],
            [
                'name' => 'Whoopee Cushion',
                'description' => 'Classic prank gift',
                'image_path' => 'gifts/whoopee-cushion.png',
                'price' => 0.99,
                'category_id' => $funnyCategory->id,
            ],
            
            // Premium gifts
            [
                'name' => 'Diamond Ring',
                'description' => 'Luxury diamond ring',
                'image_path' => 'gifts/diamond-ring.png',
                'price' => 19.99,
                'category_id' => $premiumCategory->id,
            ],
            [
                'name' => 'Sports Car',
                'description' => 'Luxury sports car',
                'image_path' => 'gifts/sports-car.png',
                'price' => 29.99,
                'category_id' => $premiumCategory->id,
            ],
            [
                'name' => 'Private Island',
                'description' => 'Your own private island',
                'image_path' => 'gifts/private-island.png',
                'price' => 99.99,
                'category_id' => $premiumCategory->id,
            ],
        ];

        foreach ($gifts as $gift) {
            \App\Models\Gift::create($gift);
        }
    }
};
