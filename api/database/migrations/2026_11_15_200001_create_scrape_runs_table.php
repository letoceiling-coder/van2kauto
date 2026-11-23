<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrape_runs', function (Blueprint $table) {
            $table->id();
            $table->string('mode', 32);
            $table->string('status', 32)->default('pending');
            $table->unsignedInteger('pages_done')->default(0);
            $table->unsignedInteger('items_saved')->default(0);
            $table->unsignedInteger('items_skipped')->default(0);
            $table->unsignedInteger('items_marked_absent')->default(0);
            $table->json('log')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('needs_captcha')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrape_runs');
    }
};