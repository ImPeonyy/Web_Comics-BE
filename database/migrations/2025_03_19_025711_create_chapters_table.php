<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChaptersTable extends Migration
{
    public function up()
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comic_id'); // Khóa ngoại liên kết tới bảng Comics
            $table->float('chapter_order'); // Thứ tự các Chapters
            $table->string('title', 255); // Tên Chapter
            $table->timestamps();

            // Định nghĩa khóa ngoại
            $table->foreign('comic_id')->references('id')->on('comics')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chapters');
    }
}