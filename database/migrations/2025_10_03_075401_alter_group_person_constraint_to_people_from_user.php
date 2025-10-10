<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('group_person', function (Blueprint $table) {
            // zrušení starého FK na users
            $table->dropForeign('group_user_user_id_foreign');

        });
    }

    public function down(): void
    {
        Schema::table('group_person', function (Blueprint $table) {
            // obnovení FK na users
            $table->foreign('person_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
