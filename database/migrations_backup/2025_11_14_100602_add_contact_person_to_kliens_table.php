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
        Schema::table('kliens', function (Blueprint $table) {
            // Add foreign key for contact person
            $table->unsignedBigInteger('contact_person_id')->nullable()->after('cabang');
            $table->foreign('contact_person_id')->references('id')->on('kontak_klien')->onDelete('set null');
            
            // Remove the old no_hp field
            $table->dropColumn('no_hp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kliens', function (Blueprint $table) {
            // Add back the no_hp field
            $table->string('no_hp')->nullable()->after('cabang');
            
            // Drop foreign key and column
            $table->dropForeign(['contact_person_id']);
            $table->dropColumn('contact_person_id');
        });
    }
};
