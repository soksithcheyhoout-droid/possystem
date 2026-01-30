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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('house_number')->nullable()->after('phone');
            $table->string('street')->nullable()->after('house_number');
            $table->decimal('points_earned', 8, 2)->default(0)->after('loyalty_points');
            $table->decimal('points_redeemed', 8, 2)->default(0)->after('points_earned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['house_number', 'street', 'points_earned', 'points_redeemed']);
        });
    }
};