<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('owner_profiles', function (Blueprint $table) {
            $table->uuid('public_uuid')->nullable()->after('id')->unique();
            $table->string('logo_disk')->nullable()->after('slug');
            $table->string('logo_path')->nullable()->after('logo_disk');
            $table->string('logo_alt_text')->nullable()->after('logo_path');
        });

        DB::table('owner_profiles')
            ->whereNull('public_uuid')
            ->orderBy('id')
            ->select(['id'])
            ->each(function (object $profile): void {
                DB::table('owner_profiles')
                    ->where('id', $profile->id)
                    ->update(['public_uuid' => (string) Str::uuid()]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owner_profiles', function (Blueprint $table) {
            $table->dropUnique(['public_uuid']);
            $table->dropColumn([
                'public_uuid',
                'logo_disk',
                'logo_path',
                'logo_alt_text',
            ]);
        });
    }
};
