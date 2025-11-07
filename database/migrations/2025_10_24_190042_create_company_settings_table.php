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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->text('company_address');
            $table->string('company_phone');
            $table->string('company_email');
            $table->string('company_website')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('tax_number')->nullable(); // NPWP
            $table->text('invoice_terms_conditions')->nullable();
            $table->text('invoice_footer_notes')->nullable();
            $table->decimal('tax_percentage', 5, 2)->default(11.00);
            $table->integer('invoice_due_days')->default(30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
