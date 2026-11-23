<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_makers', function (Blueprint $table) {
            $table->unsignedInteger('maker_id')->primary();
            $table->string('name');
            $table->string('group_name')->nullable();
            $table->timestamps();
        });

        Schema::create('auction_models', function (Blueprint $table) {
            $table->unsignedInteger('model_id')->primary();
            $table->unsignedInteger('maker_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('maker_id')->references('maker_id')->on('auction_makers')->cascadeOnDelete();
            $table->index('maker_id');
        });

        Schema::create('auction_lots', function (Blueprint $table) {
            $table->unsignedBigInteger('lot_id')->primary();
            $table->string('bid', 32)->nullable();
            $table->string('auction_name')->nullable();
            $table->unsignedInteger('auction_code')->nullable();
            $table->timestamp('auction_at')->nullable();
            $table->unsignedInteger('maker_id')->nullable();
            $table->unsignedInteger('model_id')->nullable();
            $table->string('maker_name')->nullable();
            $table->string('model_name')->nullable();
            $table->string('grade')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->unsignedInteger('mileage_km')->nullable();
            $table->unsignedInteger('displacement_cc')->nullable();
            $table->string('transmission')->nullable();
            $table->string('color')->nullable();
            $table->string('model_type')->nullable();
            $table->string('equipment')->nullable();
            $table->unsignedInteger('start_price_jpy')->nullable();
            $table->unsignedInteger('end_price_jpy')->nullable();
            $table->string('result', 64)->nullable();
            $table->string('score', 32)->nullable();
            $table->string('fuel')->nullable();
            $table->string('chassis')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamp('scraped_at')->nullable();
            $table->json('attributes')->nullable();
            $table->timestamps();

            $table->index(['maker_id', 'model_id']);
            $table->index('auction_at');
            $table->index('status');
            $table->index('result');
        });

        Schema::create('auction_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lot_id');
            $table->unsignedSmallInteger('sort_order');
            $table->string('preview_url', 2048)->nullable();
            $table->string('large_url', 2048)->nullable();
            $table->timestamps();

            $table->foreign('lot_id')->references('lot_id')->on('auction_lots')->cascadeOnDelete();
            $table->unique(['lot_id', 'sort_order']);
        });

        Schema::create('auction_runs', function (Blueprint $table) {
            $table->id();
            $table->string('mode', 32);
            $table->string('status', 32)->default('pending');
            $table->unsignedInteger('maker_id')->nullable();
            $table->unsignedInteger('pages_done')->default(0);
            $table->unsignedInteger('items_saved')->default(0);
            $table->unsignedInteger('items_skipped')->default(0);
            $table->unsignedInteger('items_marked_absent')->default(0);
            $table->json('log')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('needs_captcha')->default(false);
            $table->boolean('needs_auth')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_runs');
        Schema::dropIfExists('auction_photos');
        Schema::dropIfExists('auction_lots');
        Schema::dropIfExists('auction_models');
        Schema::dropIfExists('auction_makers');
    }
};
