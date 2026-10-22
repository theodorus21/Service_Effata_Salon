<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasLongReference = DB::table('users')
            ->whereRaw('LENGTH(reference) > 255')
            ->exists();

        if ($hasLongReference) {
            throw new RuntimeException(
                'Tidak dapat mengubah kolom referensi: ada data melebihi 255 karakter. '
                .'Rapikan data tersebut sebelum menjalankan migration kembali.',
            );
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('reference', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('reference')->nullable()->change();
        });
    }
};
