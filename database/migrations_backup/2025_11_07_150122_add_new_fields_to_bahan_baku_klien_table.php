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
        Schema::table('bahan_baku_klien', function (Blueprint $table) {
            // Check if columns don't already exist before adding
            if (!Schema::hasColumn('bahan_baku_klien', 'post')) {
                $table->boolean('post')->default(false)->after('status')->comment('Post checkmark status');
            }
            
            if (!Schema::hasColumn('bahan_baku_klien', 'present')) {
                // Add Present (dropdown values)
                $table->enum('present', [
                    'NotUsed', 
                    'Ready', 
                    'Not Reasonable Price', 
                    'Pos Closed', 
                    'Not Qualified Raw', 
                    'Not Updated Yet', 
                    'Didnt Have Supplier', 
                    'Factory No Need Yet', 
                    'Confirmed', 
                    'Sample Sent', 
                    'Hold', 
                    'Negotiate'
                ])->default('NotUsed')->after('post')->comment('Present status dropdown');
            }
            
            if (!Schema::hasColumn('bahan_baku_klien', 'cause')) {
                // Add Cause (note/explanation)
                $table->text('cause')->nullable()->after('present')->comment('Note explaining Present status');
            }
            
            if (!Schema::hasColumn('bahan_baku_klien', 'jenis')) {
                // Add Jenis (JSON field for tag system)
                $table->json('jenis')->nullable()->after('cause')->comment('Category tags: Aqua, Poultry, Ruminansia (can have multiple)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bahan_baku_klien', function (Blueprint $table) {
            $table->dropColumn(['post', 'present', 'cause', 'jenis']);
        });
    }
};
