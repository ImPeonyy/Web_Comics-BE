<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('chapter_id'); // Khóa ngoại liên kết tới bảng Chapters
            $table->string('image_url', 255); // Link ảnh
            $table->integer('image_order'); // Thứ tự các ảnh
            $table->timestamps();

            // Định nghĩa khóa ngoại
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('images');
    }
}