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
        Schema::create('bank_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('UID');
            $table->string('transactionDate')->nullable()->comment('');
            $table->string('valueDate')->nullable()->comment('');
            $table->integer('transactionType')->nullable()->comment('種別');
            $table->integer('amount')->nullable()->comment('課金');
            $table->string('remarks')->nullable()->comment('');
            $table->integer('balance')->nullable()->comment('');
            $table->string('itemKey')->nullable()->comment('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_histories');
    }
};
