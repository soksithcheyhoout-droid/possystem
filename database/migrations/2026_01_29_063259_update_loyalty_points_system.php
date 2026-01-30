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
            // Ensure loyalty_points column exists with correct type
            if (!Schema::hasColumn('customers', 'loyalty_points')) {
                $table->decimal('loyalty_points', 8, 2)->default(0)->after('address');
            }
            
            // Ensure points_earned and points_redeemed columns exist
            if (!Schema::hasColumn('customers', 'points_earned')) {
                $table->decimal('points_earned', 8, 2)->default(0)->after('loyalty_points');
            }
            
            if (!Schema::hasColumn('customers', 'points_redeemed')) {
                $table->decimal('points_redeemed', 8, 2)->default(0)->after('points_earned');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Don't drop columns in down method to preserve data
        });
    }
};
