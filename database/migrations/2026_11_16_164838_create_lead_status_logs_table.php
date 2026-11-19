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
        Schema::create('lead_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Lead::class, 'lead_id')->constrained()->onDelete('cascade');
            $table->foreignIdFor(\App\Models\User::class, 'user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->timestamp('changed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_status_logs');
    }
};
