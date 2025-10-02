<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('source_filename')->nullable();
            $table->json('mapping')->nullable();
            $table->unsignedInteger('row_count')->default(0);
            $table->decimal('total_without_vat', 12, 4)->default(0);
            $table->decimal('total_with_vat', 12, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('invoice_people', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();
            $table->string('phone');
            $table->decimal('vat_rate', 5, 2)->nullable();
            $table->decimal('total_without_vat', 12, 4)->default(0);
            $table->decimal('total_with_vat', 12, 4)->default(0);
            $table->decimal('limit', 12, 2)->default(0);
            $table->decimal('payable', 12, 2)->default(0);
            $table->json('applied_rules')->nullable();
            $table->timestamps();

            $table->unique(['invoice_id', 'person_id', 'phone']);
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_person_id')->constrained('invoice_people')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->noActionOnDelete();
            $table->string('group_name')->nullable();
            $table->string('tariff')->nullable();
            $table->string('service_name');
            $table->decimal('price_without_vat', 12, 4);
            $table->decimal('price_with_vat', 12, 4);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoice_people');
        Schema::dropIfExists('invoices');
    }
};
