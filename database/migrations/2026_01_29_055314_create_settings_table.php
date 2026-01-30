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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('group')->default('general'); // general, store, telegram, etc.
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Insert default store settings
        DB::table('settings')->insert([
            [
                'key' => 'store_name',
                'value' => 'Mini Mart POS',
                'type' => 'string',
                'group' => 'store',
                'description' => 'Store name displayed throughout the application',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'store_logo',
                'value' => null,
                'type' => 'string',
                'group' => 'store',
                'description' => 'Store logo image path',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'store_banner',
                'value' => null,
                'type' => 'string',
                'group' => 'store',
                'description' => 'Store banner image path',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'store_tagline',
                'value' => 'Your Friendly Neighborhood Store',
                'type' => 'string',
                'group' => 'store',
                'description' => 'Store tagline or slogan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'store_address',
                'value' => '',
                'type' => 'string',
                'group' => 'store',
                'description' => 'Store physical address',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'store_phone',
                'value' => '',
                'type' => 'string',
                'group' => 'store',
                'description' => 'Store contact phone number',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'store_email',
                'value' => '',
                'type' => 'string',
                'group' => 'store',
                'description' => 'Store contact email address',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'tax_rate',
                'value' => '0.00',
                'type' => 'string',
                'group' => 'store',
                'description' => 'Default tax rate percentage',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'currency_symbol',
                'value' => '$',
                'type' => 'string',
                'group' => 'store',
                'description' => 'Currency symbol',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'receipt_footer',
                'value' => 'Thank you for your business!',
                'type' => 'string',
                'group' => 'store',
                'description' => 'Footer text for receipts',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
