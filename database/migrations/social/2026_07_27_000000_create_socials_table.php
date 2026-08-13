<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(table: 'socials', callback: function (Blueprint $table): void {
            $table->id();
            $table->morphs(name: 'socialable');
            $table->string(column: 'provider');
            $table->string(column: 'provider_id');
            $table->string(column: 'name')->nullable();
            $table->string(column: 'nickname')->nullable();
            $table->string(column: 'email')->nullable();
            $table->text(column: 'avatar_path')->nullable();
            $table->string(column: 'token', length: 4000)->nullable();
            $table->string(column: 'refresh_token', length: 4000)->nullable();
            $table->dateTime(column: 'expires_at')->nullable();
            $table->timestamps();

            $table->unique(columns: ['provider', 'provider_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(table: 'socials');
    }
};
