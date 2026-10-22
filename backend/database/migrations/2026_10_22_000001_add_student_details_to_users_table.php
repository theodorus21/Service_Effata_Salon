<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('parent_name')->nullable()->after('nickname');
            $table->string('parent_phone')->nullable()->after('parent_name');
            $table->string('emergency_contact_name')->nullable()->after('parent_phone');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->text('child_characteristics')->nullable()->after('emergency_contact_phone');
            $table->text('child_hobbies')->nullable()->after('child_characteristics');
            $table->string('child_photo')->nullable()->after('child_hobbies');
            $table->text('reference')->nullable()->after('child_photo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'parent_name',
                'parent_phone',
                'emergency_contact_name',
                'emergency_contact_phone',
                'child_characteristics',
                'child_hobbies',
                'child_photo',
                'reference',
            ]);
        });
    }
};
