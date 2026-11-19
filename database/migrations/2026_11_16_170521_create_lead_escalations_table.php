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
        Schema::create('lead_escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Lead::class, 'lead_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignIdFor(\App\Models\User::class, 'escalated_to')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_escalations');
    }
};
