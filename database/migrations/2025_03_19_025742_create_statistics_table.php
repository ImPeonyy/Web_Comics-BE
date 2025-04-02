<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatisticsTable extends Migration
{
    public function up()
    {
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comic_id'); // Khóa ngoại liên kết tới bảng Comics
            $table->integer('view_count')->default(0); // Số view trong 1 ngày
            $table->timestamps();

            // Định nghĩa khóa ngoại
            $table->foreign('comic_id')->references('id')->on('comics')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('statistics');
    }
}
