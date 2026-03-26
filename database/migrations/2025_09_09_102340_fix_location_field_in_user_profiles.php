<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    DB::statement('ALTER TABLE `user_profiles` MODIFY COLUMN `location` VARCHAR(255) NULL;');
}

public function down()
{
    // This is a one-way migration
}
};
