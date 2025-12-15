<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('userid'); // User ID
            $table->string('reference'); // Transaction reference
            $table->string('orderid'); // Order ID from provider
            $table->decimal('amount', 10, 2); // Transaction amount
            $table->string('currency', 10); // Currency code
            $table->string('status'); // Transaction status (success, pending, failed)
            $table->string('provider'); // Payment gateway provider (swipepointe, etc.)
            $table->string('card_name'); // Cardholder name
            $table->string('card_number_masked'); // Masked card number (last 4 digits)
            $table->string('card_expiry'); // Card expiry (MM/YY)
            $table->string('card_type')->nullable(); // Card type (Visa, Mastercard, etc.)
            $table->string('transaction_type'); // 2D or 3D Secure
            $table->decimal('fees', 10, 2); // Gateway fees
            $table->decimal('user_fees', 10, 2); // User-specific fees
            $table->string('redirect_link')->nullable(); // 3D Secure redirect link
            $table->text('gateway_response'); // Full response from payment gateway
            $table->string('ip_address')->nullable(); // Customer IP address
            $table->string('callback_url')->nullable(); // Callback URL
            $table->string('webhook_url')->nullable(); // Webhook URL
            $table->text('customer_details'); // JSON with customer info (name, email, phone, address)
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('userid');
            $table->index('reference');
            $table->index('status');
            $table->index('provider');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Create a model for the card transactions table
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('card_transactions');
    }
};
