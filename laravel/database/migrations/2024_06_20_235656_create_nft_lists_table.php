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
        Schema::create('nft_lists', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->comment('UID');
            $table->integer('nft_type')->comment('種別');
            $table->string('nft_title')->nullable()->comment('');
            $table->text('nft_address')->nullable()->comment('');
            $table->text('nft_url')->nullable()->comment('');
            $table->text('img_url')->nullable()->comment('');
            $table->integer('nft_valuation')->nullable()->comment('');
            $table->string('invest_month')->nullable()->comment('');
            $table->string('start_str')->nullable()->comment('');
            $table->string('end_str')->nullable()->comment('');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nft_lists');
    }
};
