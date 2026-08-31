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
            $table->string('slug')->nullable()->after('business_name')->unique();
        });

        DB::table('owner_profiles')
            ->select(['id', 'business_name'])
            ->orderBy('id')
            ->each(function (object $profile): void {
                DB::table('owner_profiles')
                    ->where('id', $profile->id)
                    ->update([
                        'slug' => Str::slug($profile->business_name).'-'.$profile->id,
                    ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('owner_profiles', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
