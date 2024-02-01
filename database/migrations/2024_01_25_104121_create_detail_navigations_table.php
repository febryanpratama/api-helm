<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDetailNavigationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detail_navigations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('navigasi_id');
            $table->string('title');
            $table->string('link')->comment('link navigasi / SLUG');
            $table->json('content')->comment('isi konten navigasi');
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
        Schema::dropIfExists('detail_navigations');
    }
}
