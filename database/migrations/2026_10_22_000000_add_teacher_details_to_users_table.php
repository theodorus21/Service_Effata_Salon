<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nickname')->nullable()->after('name');
            $table->string('phone')->nullable()->after('nickname');
            $table->string('bank_account_number')->nullable()->after('phone');
            $table->string('bank_name')->nullable()->after('bank_account_number');
            $table->text('teaching_quote')->nullable()->after('bank_name');
            $table->string('selfie_image')->nullable()->after('teaching_quote');
            $table->string('ktp_image')->nullable()->after('selfie_image');
            $table->text('address')->nullable()->after('ktp_image');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nickname',
                'phone',
                'bank_account_number',
                'bank_name',
                'teaching_quote',
                'selfie_image',
                'ktp_image',
                'address',
            ]);
        });
    }
};
