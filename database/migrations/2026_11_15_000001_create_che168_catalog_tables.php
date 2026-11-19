<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->primary();
            $table->string('name');
            $table->string('py')->nullable();
            $table->string('icon_url', 2048)->nullable();
            $table->timestamps();
        });

        Schema::create('series', function (Blueprint $table) {
            $table->unsignedBigInteger('series_id')->primary();
            $table->unsignedBigInteger('brand_id');
            $table->string('name');
            $table->string('icon_url', 2048)->nullable();
            $table->timestamps();

            $table->foreign('brand_id')->references('brand_id')->on('brands')->cascadeOnDelete();
            $table->index('brand_id');
        });

        Schema::create('specs', function (Blueprint $table) {
            $table->unsignedBigInteger('spec_id')->primary();
            $table->unsignedBigInteger('series_id');
            $table->string('name');
            $table->string('year_label')->nullable();
            $table->json('raw_param')->nullable();
            $table->json('raw_config')->nullable();
            $table->timestamps();

            $table->foreign('series_id')->references('series_id')->on('series')->cascadeOnDelete();
        });

        Schema::create('listings', function (Blueprint $table) {
            $table->unsignedBigInteger('infoid')->primary();
            $table->unsignedBigInteger('spec_id')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('series_id')->nullable();
            $table->unsignedBigInteger('dealer_id')->nullable();
            $table->string('title')->nullable();
            $table->decimal('price_usd', 12, 2)->nullable();
            $table->decimal('export_fee_usd', 12, 2)->nullable();
            $table->decimal('port_fee_usd', 12, 2)->nullable();
            $table->unsignedInteger('mileage_km')->nullable();
            $table->string('reg_date', 32)->nullable();
            $table->timestamp('produce_date')->nullable();
            $table->string('city_slug')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->string('color')->nullable();
            $table->string('vin_masked', 64)->nullable();
            $table->string('fuel_name')->nullable();
            $table->string('engine')->nullable();
            $table->string('gearbox')->nullable();
            $table->string('driving_mode')->nullable();
            $table->string('steering')->nullable();
            $table->string('body_type')->nullable();
            $table->unsignedSmallInteger('doors')->nullable();
            $table->unsignedSmallInteger('seats')->nullable();
            $table->string('class_level')->nullable();
            $table->string('dimension')->nullable();
            $table->unsignedInteger('curb_weight_kg')->nullable();
            $table->unsignedSmallInteger('cert_status')->nullable();
            $table->unsignedSmallInteger('claim_status')->nullable();
            $table->unsignedSmallInteger('mr_status')->nullable();
            $table->unsignedSmallInteger('certification')->nullable();
            $table->string('paramkey')->nullable();
            $table->string('report_url', 2048)->nullable();
            $table->string('detail_url', 2048)->nullable();
            $table->string('whatsapp_url', 2048)->nullable();
            $table->string('wechat_url', 2048)->nullable();
            $table->string('language', 8)->default('en');
            $table->string('status', 32)->default('active');
            $table->timestamp('scraped_at')->nullable();
            $table->json('raw_carinfo')->nullable();
            $table->timestamps();

            $table->foreign('spec_id')->references('spec_id')->on('specs')->nullOnDelete();
            $table->index(['brand_id', 'series_id']);
            $table->index(['price_usd', 'mileage_km']);
            $table->index('status');
        });

        Schema::create('listing_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('infoid');
            $table->unsignedSmallInteger('sort_order');
            $table->string('preview_url', 2048)->nullable();
            $table->string('large_url', 2048)->nullable();
            $table->string('original_url', 2048)->nullable();
            $table->string('file_path', 2048)->nullable();
            $table->string('hash', 128)->nullable();
            $table->timestamps();

            $table->foreign('infoid')->references('infoid')->on('listings')->cascadeOnDelete();
            $table->unique(['infoid', 'sort_order']);
        });

        Schema::create('listing_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('infoid')->primary();
            $table->unsignedSmallInteger('claims_count')->nullable();
            $table->unsignedInteger('latest_mileage_km')->nullable();
            $table->string('insurance_url', 2048)->nullable();
            $table->string('maintenance_url', 2048)->nullable();
            $table->json('raw_summary')->nullable();
            $table->timestamps();

            $table->foreign('infoid')->references('infoid')->on('listings')->cascadeOnDelete();
        });

        Schema::create('scrape_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('last_page')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrape_checkpoints');
        Schema::dropIfExists('listing_reports');
        Schema::dropIfExists('listing_photos');
        Schema::dropIfExists('listings');
        Schema::dropIfExists('specs');
        Schema::dropIfExists('series');
        Schema::dropIfExists('brands');
    }
};