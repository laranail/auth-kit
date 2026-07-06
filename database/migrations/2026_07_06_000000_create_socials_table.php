<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('socials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('provider_id');
            $table->string('name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('email')->nullable();
            $table->text('avatar_path')->nullable();
            $table->string('token', 4000)->nullable();
            $table->string('refresh_token', 4000)->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_id']);
            $table->unique(['provider', 'provider_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('socials');
    }
};
