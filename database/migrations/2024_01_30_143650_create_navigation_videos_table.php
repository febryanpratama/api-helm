<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNavigationVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('navigation_videos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('detail_navigation_id');
            $table->string('url_video');
            $table->enum('position_video', ['Left', 'Right', 'Center', 'Top', 'Bottom'])->default('Left');
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
        Schema::dropIfExists('navigation_videos');
    }
}
