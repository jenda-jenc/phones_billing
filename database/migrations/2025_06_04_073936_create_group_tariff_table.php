<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupTariffTable extends Migration
{
    public function up()
    {
        Schema::create('group_tariff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('tariff_id');
            $table->string('action'); // Typ akce, např. ignorovat, do limitu, platí sám
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('groups');
            $table->foreign('tariff_id')->references('id')->on('tariffs');
        });
    }

    public function down()
    {
        Schema::dropIfExists('group_tariff');
    }
}
