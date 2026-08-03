<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create(table: 'pending_email_tokens', callback: function (Blueprint $table): void {
            $table->id();
            $table->string(column: 'email')->unique();
            $table->text(column: 'token');
            $table->timestamp(column: 'expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(table: 'pending_email_tokens');
    }
};
