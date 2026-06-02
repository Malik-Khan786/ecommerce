<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('ack_token')->nullable()->unique()->after('uuid');
            $table->enum('ack_status', ['pending', 'received', 'issue'])->nullable()->after('ack_token');
            $table->text('ack_message')->nullable()->after('ack_status');
            $table->timestamp('ack_at')->nullable()->after('ack_message');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['ack_token', 'ack_status', 'ack_message', 'ack_at']);
        });
    }
};
