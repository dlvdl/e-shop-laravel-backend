<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('type', 45);
            $table->string('adress 1', 255);
            $table->string('address 2', 255);
            $table->string('city', 255);
            $table->string('state', 45);
            $table->string('zipcode', 45);
            $table->string('country_code', 3);
            $table->foreignId('user_id')->constrained('users', 'id');
            $table->foreign('country_code')->references('code')->on('countries');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
