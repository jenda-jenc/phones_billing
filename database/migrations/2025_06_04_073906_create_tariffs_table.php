<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTariffsTable extends Migration
{
    public function up()
    {
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Název tarifu
            $table->text('description')->nullable(); // Popis tarifu
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tariffs');
    }
}
