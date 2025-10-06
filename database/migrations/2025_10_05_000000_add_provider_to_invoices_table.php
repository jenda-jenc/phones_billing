<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'billing_period')) {
                try {
                    $table->dropUnique('invoices_billing_period_unique');
                } catch (\Throwable $exception) {
                    // Index might not exist on fresh installations.
                }
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('provider', ['t-mobile', 'o2', 'other'])
                ->default('other')
                ->after('billing_period');
        });

        DB::table('invoices')->whereNull('provider')->update(['provider' => 'other']);

        Schema::table('invoices', function (Blueprint $table) {
            $table->unique(['billing_period', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            try {
                $table->dropUnique('invoices_billing_period_provider_unique');
            } catch (\Throwable $exception) {
                // Index might have been removed manually.
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('provider');
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'billing_period')) {
                $table->unique('billing_period');
            }
        });
    }
};
