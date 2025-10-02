<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('person_phones')) {
            return;
        }

        Schema::table('person_phones', function (Blueprint $table) {
            if (! Schema::hasColumn('person_phones', 'limit')) {
                $table->decimal('limit', 10, 2)->default(0);
            }
        });

        $peopleLimits = Schema::hasTable('people')
            ? DB::table('people')->pluck('limit', 'id')->all()
            : [];

        $phones = DB::table('person_phones')
            ->select('id', 'person_id')
            ->get();

        foreach ($phones as $phone) {
            $limit = $peopleLimits[$phone->person_id] ?? 0;

            DB::table('person_phones')
                ->where('id', $phone->id)
                ->update(['limit' => $limit]);
        }

        if (Schema::hasTable('people') && Schema::hasColumn('people', 'limit')) {
            Schema::table('people', function (Blueprint $table) {
                $table->dropColumn('limit');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('people') && ! Schema::hasColumn('people', 'limit')) {
            Schema::table('people', function (Blueprint $table) {
                $table->float('limit');
            });
        }

        if (Schema::hasTable('person_phones') && Schema::hasColumn('person_phones', 'limit')) {
            $limits = DB::table('person_phones')
                ->select('person_id', 'limit')
                ->get()
                ->groupBy('person_id');

            foreach ($limits as $personId => $entries) {
                $limit = (float) ($entries->max('limit') ?? 0);

                DB::table('people')
                    ->where('id', $personId)
                    ->update(['limit' => $limit]);
            }

            Schema::table('person_phones', function (Blueprint $table) {
                $table->dropColumn('limit');
            });
        }
    }
};
