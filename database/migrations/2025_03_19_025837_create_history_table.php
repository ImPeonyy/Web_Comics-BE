<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryTable extends Migration
{
    public function up()
    {
        Schema::create('history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Khóa ngoại liên kết tới bảng Users
            $table->unsignedBigInteger('comic_id'); // Lưu ID của bộ truyện
            $table->unsignedBigInteger('chapter_id'); // Khóa ngoại liên kết tới bảng Chapters
            $table->timestamps();

            // Định nghĩa các khóa ngoại
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('history');
    }
}