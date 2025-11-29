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
        Schema::table('contact_custom_field_values', function (Blueprint $table) {
            $table->unsignedBigInteger('source_contact_id')->nullable()->after('contact_id');
            $table->foreign('source_contact_id')->references('id')->on('contacts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_custom_field_values', function (Blueprint $table) {
            $table->dropForeign(['source_contact_id']);
            $table->dropColumn('source_contact_id');
        });
    }
};
