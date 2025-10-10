<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('group_user') && !Schema::hasTable('group_person')) {
            Schema::rename('group_user', 'group_person');
        }

        if (!Schema::hasTable('group_person')) {
            Schema::create('group_person', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('group_id');
                $table->unsignedBigInteger('person_id');
                $table->timestamps();

                $table->foreign('group_id', 'group_person_group_id_foreign')
                    ->references('id')
                    ->on('groups')
                    ->cascadeOnDelete();

                $table->foreign('person_id', 'group_person_person_id_foreign')
                    ->references('id')
                    ->on('people')
                    ->cascadeOnDelete();

                $table->unique(['group_id', 'person_id'], 'group_person_group_id_person_id_unique');
            });
        } else {
            // tabulka už existovala → dorovnání schématu
            $this->ensureGroupPersonSchema();
        }

        if (Schema::hasTable('group_user')) {
            $this->migrateLegacyAssignments();
            Schema::dropIfExists('group_user');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('group_person')) {
            return;
        }

        if (!Schema::hasTable('group_user')) {
            Schema::create('group_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('group_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();

                $table->foreign('group_id', 'group_user_group_id_foreign')
                    ->references('id')
                    ->on('groups');

                $table->foreign('user_id', 'group_user_user_id_foreign')
                    ->references('id')
                    ->on('users');
            });
        }

        $this->restoreLegacyAssignments();

        Schema::dropIfExists('group_person');
    }

    private function ensureGroupPersonSchema(): void
    {
        if (!Schema::hasTable('group_person')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::dropIfExists('group_person');

            Schema::create('group_person', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('group_id');
                $table->unsignedBigInteger('person_id');
                $table->timestamps();

                $table->foreign('group_id', 'group_person_group_id_foreign')
                    ->references('id')
                    ->on('groups')
                    ->cascadeOnDelete();

                $table->foreign('person_id', 'group_person_person_id_foreign')
                    ->references('id')
                    ->on('people')
                    ->cascadeOnDelete();

                $table->unique(['group_id', 'person_id'], 'group_person_group_id_person_id_unique');
            });

            return;
        }

        if (Schema::hasColumn('group_person', 'user_id') && !Schema::hasColumn('group_person', 'person_id')) {
            $this->dropForeignIfExists('group_person', ['group_person_user_id_foreign', 'group_user_user_id_foreign']);

            Schema::table('group_person', function (Blueprint $table) {
                $table->renameColumn('user_id', 'person_id');
            });
        }

        $missingGroupId = !Schema::hasColumn('group_person', 'group_id');
        $missingPersonId = !Schema::hasColumn('group_person', 'person_id');
        $missingCreatedAt = !Schema::hasColumn('group_person', 'created_at');
        $missingUpdatedAt = !Schema::hasColumn('group_person', 'updated_at');

        if ($missingGroupId || $missingPersonId || $missingCreatedAt || $missingUpdatedAt) {
            Schema::table('group_person', function (Blueprint $table) use ($missingGroupId, $missingPersonId, $missingCreatedAt, $missingUpdatedAt) {
                if ($missingGroupId) {
                    $table->unsignedBigInteger('group_id');
                }

                if ($missingPersonId) {
                    $table->unsignedBigInteger('person_id');
                }

                if ($missingCreatedAt) {
                    $table->timestamp('created_at')->nullable();
                }

                if ($missingUpdatedAt) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }

        $this->dropIndexIfExists('group_person', 'group_user_group_id_user_id_unique');
        $this->dropIndexIfExists('group_person', 'group_person_group_id_user_id_unique');

        $this->dropForeignIfExists('group_person', ['group_user_group_id_foreign']);
        $this->dropForeignIfExists('group_person', ['group_person_group_id_foreign']);
        $this->dropForeignIfExists('group_person', ['group_user_user_id_foreign']);
        $this->dropForeignIfExists('group_person', ['group_person_person_id_foreign']);

        $hasGroupId = Schema::hasColumn('group_person', 'group_id');
        $hasPersonId = Schema::hasColumn('group_person', 'person_id');

        if ($hasGroupId && !$this->hasForeignKey('group_person', 'group_person_group_id_foreign')) {
            Schema::table('group_person', function (Blueprint $table) {
                $table->foreign('group_id', 'group_person_group_id_foreign')
                    ->references('id')
                    ->on('groups')
                    ->cascadeOnDelete();
            });
        }

        if ($hasPersonId && !$this->hasForeignKey('group_person', 'group_person_person_id_foreign')) {
            Schema::table('group_person', function (Blueprint $table) {
                $table->foreign('person_id', 'group_person_person_id_foreign')
                    ->references('id')
                    ->on('people')
                    ->cascadeOnDelete();
            });
        }

        if ($hasGroupId && $hasPersonId && !$this->hasIndex('group_person', 'group_person_group_id_person_id_unique')) {
            Schema::table('group_person', function (Blueprint $table) {
                $table->unique(['group_id', 'person_id'], 'group_person_group_id_person_id_unique');
            });
        }
    }

    private function migrateLegacyAssignments(): void
    {
        if (!Schema::hasTable('group_user') || !Schema::hasTable('group_person')) {
            return;
        }

        $rows = DB::table('group_user')
            ->select('group_id', 'user_id', 'created_at', 'updated_at')
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $payload = $rows->map(function ($row) {
            return [
                'group_id' => $row->group_id,
                'person_id' => $row->user_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
        })->toArray();

        foreach (array_chunk($payload, 500) as $chunk) {
            DB::table('group_person')->insertOrIgnore($chunk);
        }
    }

    private function restoreLegacyAssignments(): void
    {
        if (!Schema::hasTable('group_person') || !Schema::hasTable('group_user')) {
            return;
        }

        $rows = DB::table('group_person')
            ->select('group_id', 'person_id', 'created_at', 'updated_at')
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $payload = $rows->map(function ($row) {
            return [
                'group_id' => $row->group_id,
                'user_id' => $row->person_id,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ];
        })->toArray();

        foreach (array_chunk($payload, 500) as $chunk) {
            DB::table('group_user')->insertOrIgnore($chunk);
        }
    }

    private function dropForeignIfExists(string $table, array $foreignKeyNames): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        foreach ($foreignKeyNames as $foreignKeyName) {
            if (! $this->hasForeignKey($table, $foreignKeyName)) {
                continue;
            }

            Schema::table($table, function (Blueprint $table) use ($foreignKeyName) {
                $table->dropForeign($foreignKeyName);
            });
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table) || ! $this->hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function hasForeignKey(string $table, string $foreignKeyName): bool
    {
        if (!Schema::hasTable($table)) {
            return false;
        }

        $schemaManager = $this->getSchemaManager();

        if (! $schemaManager) {
            return false;
        }

        foreach ($schemaManager->listTableForeignKeys($table) as $foreignKey) {
            if (strcasecmp($foreignKey->getName(), $foreignKeyName) === 0) {
                return true;
            }
        }

        return false;
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        if (!Schema::hasTable($table)) {
            return false;
        }

        $connection = Schema::getConnection();

        if ($connection->getDriverName() === 'sqlite') {
            $indexes = $connection->select('PRAGMA index_list("'.$table.'")');

            foreach ($indexes as $index) {
                $name = is_object($index) ? ($index->name ?? null) : ($index['name'] ?? null);

                if ($name && strcasecmp($name, $indexName) === 0) {
                    return true;
                }
            }
        }

        $schemaManager = $this->getSchemaManager();

        if ($schemaManager) {
            foreach ($schemaManager->listTableIndexes($table) as $index) {
                if (strcasecmp($index->getName(), $indexName) === 0) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getSchemaManager(): ?object
    {
        $connection = Schema::getConnection();

        if (method_exists($connection, 'getDoctrineSchemaManager')) {
            return $connection->getDoctrineSchemaManager();
        }

        if (method_exists($connection, 'createSchemaManager')) {
            return $connection->createSchemaManager();
        }

        return null;
    }
};
