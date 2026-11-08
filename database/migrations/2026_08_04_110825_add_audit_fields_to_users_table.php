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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('role');
            $table->timestamp('created_date')->nullable()->after('created_by');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_date');
            $table->timestamp('updated_date')->nullable()->after('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['created_by', 'created_date', 'updated_by', 'updated_date']);
        });
    }
};
