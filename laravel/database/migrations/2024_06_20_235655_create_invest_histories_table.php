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
        Schema::create('invest_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('UID');
            $table->string('invest_month')->nullable()->comment('');
            $table->integer('account_id')->nullable()->comment('');
            $table->string('result_code')->nullable()->comment('');
            $table->string('apply_no')->nullable()->comment('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invest_histories');
    }
};
