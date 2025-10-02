<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('billing_period', 7)->nullable()->after('source_filename');
        });

        // vyplníme unikátní hodnoty pro existující řádky
        // např. naformátujeme "TEMP-<id>"
        DB::table('invoices')->get()->each(function ($invoice) {
            DB::table('invoices')
                ->where('id', $invoice->id)
                ->update(['billing_period' => 'TEMP-' . $invoice->id]);
        });

        // vytvoříme unikátní index
        Schema::table('invoices', function (Blueprint $table) {
            $table->unique('billing_period');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique('invoices_billing_period_unique');
            $table->dropColumn('billing_period');
        });
    }
};
