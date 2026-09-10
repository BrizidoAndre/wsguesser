<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->string('word');
            $table->foreignId('game_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('attempts');
    }
};
