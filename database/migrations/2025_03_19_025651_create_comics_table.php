<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComicsTable extends Migration
{
    public function up()
    {
        Schema::create('comics', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255); // Tên của bộ truyện
            $table->text('description')->nullable(); // Mô tả của bộ truyện
            $table->string('author', 100)->default('Peonyy~'); // Tác giả của bộ truyện
            $table->text('cover_image'); // Trang bìa của bộ truyện
            $table->enum('status', ['ongoing', 'completed'])->default('ongoing'); // Trạng thái của bộ truyện
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comics');
    }
}
