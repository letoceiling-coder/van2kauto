<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->unsignedSmallInteger('power_hp')->nullable()->after('engine');
            $table->decimal('displacement_l', 3, 1)->nullable()->after('power_hp');
            $table->index('power_hp');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('showcase_cars', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->unsignedBigInteger('source_infoid')->nullable();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('series_id')->nullable();
            $table->unsignedBigInteger('spec_id')->nullable();
            $table->string('country', 16)->default('china');
            $table->string('brand');
            $table->string('model');
            $table->string('generation')->nullable();
            $table->string('trim')->nullable();
            $table->unsignedSmallInteger('year');
            $table->string('condition', 16)->default('used');
            $table->string('body', 16)->default('crossover');
            $table->string('engine', 16)->default('petrol');
            $table->string('engine_label')->nullable();
            $table->decimal('displacement_l', 3, 1)->nullable();
            $table->unsignedSmallInteger('power_hp')->nullable();
            $table->string('drive', 8)->default('FWD');
            $table->string('gearbox')->nullable();
            $table->string('steering', 8)->default('left');
            $table->unsignedInteger('mileage_km')->default(0);
            $table->unsignedBigInteger('price_from_rub')->nullable();
            $table->string('status', 16)->default('order');
            $table->boolean('utilization_override')->nullable();
            $table->string('badge')->nullable();
            $table->text('summary')->nullable();
            $table->json('highlights')->nullable();
            $table->unsignedSmallInteger('eta_from')->default(30);
            $table->unsignedSmallInteger('eta_to')->default(45);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_hit')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
            $table->index('series_id');
        });

        Schema::create('showcase_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('showcase_car_id')->constrained('showcase_cars')->cascadeOnDelete();
            $table->string('path', 1024);
            $table->string('source_url', 2048)->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('source', 16)->default('site');
            $table->string('name')->nullable();
            $table->string('contact');
            $table->string('channel', 32)->nullable();
            $table->text('comment')->nullable();
            $table->foreignId('showcase_car_id')->nullable()->constrained('showcase_cars')->nullOnDelete();
            $table->string('car_slug')->nullable();
            $table->string('car_title')->nullable();
            $table->string('page', 1024)->nullable();
            $table->json('context')->nullable();
            $table->json('utm')->nullable();
            $table->string('ym_client_id', 64)->nullable();
            $table->string('ip', 64)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->bigInteger('tg_user_chat_id')->nullable();
            $table->string('status', 16)->default('new');
            $table->string('manager')->nullable();
            $table->text('note')->nullable();
            $table->json('tg_messages')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->text('notify_error')->nullable();
            $table->unsignedSmallInteger('notify_attempts')->default(0);
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('tg_subscribers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('chat_id')->unique();
            $table->string('username')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('start_param')->nullable();
            $table->string('phone', 32)->nullable();
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tg_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->string('photo_url', 2048)->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url', 2048)->nullable();
            $table->string('status', 16)->default('draft');
            $table->bigInteger('last_subscriber_id')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->string('created_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('site_builds', function (Blueprint $table) {
            $table->id();
            $table->string('trigger', 32);
            $table->string('status', 16)->default('running');
            $table->text('output')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_builds');
        Schema::dropIfExists('tg_broadcasts');
        Schema::dropIfExists('tg_subscribers');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('showcase_photos');
        Schema::dropIfExists('showcase_cars');
        Schema::dropIfExists('settings');
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex(['power_hp']);
            $table->dropColumn(['power_hp', 'displacement_l']);
        });
    }
};
