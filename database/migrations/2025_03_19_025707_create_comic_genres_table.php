<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComicGenresTable extends Migration
{
    public function up()
    {
        Schema::create('comic_genres', function (Blueprint $table) {
            $table->unsignedBigInteger('comic_id'); // Khóa ngoại liên kết tới bảng Comics
            $table->unsignedBigInteger('genre_id'); // Khóa ngoại liên kết tới bảng Genres

            // Định nghĩa các khóa ngoại
            $table->foreign('comic_id')->references('id')->on('comics')->onDelete('cascade');
            $table->foreign('genre_id')->references('id')->on('genres')->onDelete('cascade');

            $table->primary(['comic_id', 'genre_id']); // Định nghĩa khóa chính tổng hợp
        });
    }

    public function down()
    {
        Schema::dropIfExists('comic_genres');
    }
}