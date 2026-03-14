<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('camera_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('stream_key')->unique();
            $table->enum('ingest_mode', ['camera_push', 'server_pull'])->default('camera_push');
            $table->text('source_rtsp_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('camera_sources');
    }
};
