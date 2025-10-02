<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGroupUserToGroupPerson extends Migration
{
    public function up()
    {
        // 1. Přejmenuj tabulku, pokud existuje
        if (Schema::hasTable('group_user') && !Schema::hasTable('group_person')) {
            Schema::rename('group_user', 'group_person');
        }

        // 2. Drop původní foreign keys, pokud tabulka existuje
        if (Schema::hasTable('group_person')) {
            try {
                Schema::table('group_person', function (Blueprint $table) {
                    if (Schema::hasColumn('group_person', 'user_id')) {
                        $table->dropForeign(['user_id']);
                    }
                    if (Schema::hasColumn('group_person', 'group_id')) {
                        $table->dropForeign(['group_id']);
                    }
                });
            } catch (\Exception $e) {
                // Ignoruj chybu, pokud constraint neexistuje
            }

            // 3. Přejmenuj sloupec user_id na person_id
            Schema::table('group_person', function (Blueprint $table) {
                if (Schema::hasColumn('group_person', 'user_id')) {
                    $table->renameColumn('user_id', 'person_id');
                }
            });

            // 4. Přidej nové foreign keys
            Schema::table('group_person', function (Blueprint $table) {
                if (Schema::hasColumn('group_person', 'person_id')) {
                    $table->foreign('person_id')->references('id')->on('people')->onDelete('cascade');
                }
                if (Schema::hasColumn('group_person', 'group_id')) {
                    $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
                }
            });

            // 5. Unikátní index
            Schema::table('group_person', function (Blueprint $table) {
                if (Schema::hasColumn('group_person', 'group_id') && Schema::hasColumn('group_person', 'person_id')) {
                    $table->unique(['group_id', 'person_id']);
                }
            });
        }
    }

    public function down()
    {
        // Pokud tabulka existuje, proveď rollback
        if (Schema::hasTable('group_person')) {
            Schema::table('group_person', function (Blueprint $table) {
                if (Schema::hasColumn('group_person', 'group_id') && Schema::hasColumn('group_person', 'person_id')) {
                    $table->dropUnique(['group_id', 'person_id']);
                }
                if (Schema::hasColumn('group_person', 'person_id')) {
                    $table->dropForeign(['person_id']);
                    $table->renameColumn('person_id', 'user_id');
                }
                if (Schema::hasColumn('group_person', 'group_id')) {
                    $table->dropForeign(['group_id']);
                }
            });

            // Přejmenuj tabulku zpět
            if (!Schema::hasTable('group_user')) {
                Schema::rename('group_person', 'group_user');
            }
        }
    }
}
