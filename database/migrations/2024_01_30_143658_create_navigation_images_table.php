<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNavigationImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('navigation_images', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('detail_navigation_id');
            $table->longText('image');
            $table->enum('position_image', ['Left', 'Right', 'Center', 'Top', 'Bottom'])->default('Left');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('navigation_images');
    }
}
