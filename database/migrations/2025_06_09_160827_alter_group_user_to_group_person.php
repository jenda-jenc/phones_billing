<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGroupUserToGroupPerson extends Migration
{
    public function up()
    {
        // 1. Přejmenuj tabulku
        if (Schema::hasTable('group_user') && !Schema::hasTable('group_person')) {
            Schema::rename('group_user', 'group_person');
        }

        // 2. Drop původní foreign keys
        Schema::table('group_person', function (Blueprint $table) {
            try { $table->dropForeign('group_user_user_id_foreign'); } catch (\Throwable $e) {}
            try { $table->dropForeign('group_user_group_id_foreign'); } catch (\Throwable $e) {}
        });

        // 3. Přejmenuj sloupec user_id na person_id
        Schema::table('group_person', function (Blueprint $table) {
            if (Schema::hasColumn('group_person', 'user_id')) {
                $table->renameColumn('user_id', 'person_id');
            }
        });

        // 4. Přidej správné foreign keys
        Schema::table('group_person', function (Blueprint $table) {
            $table->foreign('person_id')->references('id')->on('people')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });

        // 5. Unikátní index
        Schema::table('group_person', function (Blueprint $table) {
            $table->unique(['group_id', 'person_id']);
        });
    }

    public function down()
    {
        Schema::table('group_person', function (Blueprint $table) {
            $table->dropUnique(['group_id', 'person_id']);
            $table->dropForeign(['person_id']);
            $table->dropForeign(['group_id']);
            if (Schema::hasColumn('group_person', 'person_id')) {
                $table->renameColumn('person_id', 'user_id');
            }
        });
        if (Schema::hasTable('group_person') && !Schema::hasTable('group_user')) {
            Schema::rename('group_person', 'group_user');
        }
    }

}
