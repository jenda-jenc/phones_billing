<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_phones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->string('phone')->unique();
            $table->timestamps();
            $table->unique(['person_id', 'phone']);
        });

        $existingPhones = DB::table('people')
            ->select('id', 'phone')
            ->whereNotNull('phone')
            ->get();

        $now = now();

        foreach ($existingPhones as $record) {
            if (empty($record->phone)) {
                continue;
            }

            DB::table('person_phones')->insert([
                'person_id' => $record->id,
                'phone' => $record->phone,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        Schema::table('people', function (Blueprint $table) {
            if (! Schema::hasColumn('people', 'phone')) {
                return;
            }

            $table->dropUnique(['phone']);
            $table->dropColumn('phone');
        });
    }

    public function down(): void
    {
        Schema::table('people', function (Blueprint $table) {
            if (!Schema::hasColumn('people', 'phone')) {
                $table->string('phone')->nullable();
                $table->unique('phone');
            }
        });

        $phones = DB::table('person_phones')
            ->select('person_id', 'phone')
            ->orderBy('id')
            ->get()
            ->groupBy('person_id');

        foreach ($phones as $personId => $entries) {
            $phone = $entries->firstWhere('phone', '!=', null)?->phone;

            if ($phone !== null) {
                DB::table('people')
                    ->where('id', $personId)
                    ->update(['phone' => $phone]);
            }
        }

        Schema::dropIfExists('person_phones');
    }
};
