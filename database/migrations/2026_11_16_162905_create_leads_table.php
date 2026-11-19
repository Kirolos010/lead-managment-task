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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('source')->nullable();
            $table->string('status')->nullable()->default(\App\Enums\LeadStatus::NEW); //enum file
            $table->timestamp('next_follow_up_at')->nullable();
            $table->string('priority')->nullable(); //enum file
            $table->foreignIdFor(\App\Models\User::class, 'assigned_to')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            //indexes
            $table->index(['assigned_to', 'status']);
            $table->index(['assigned_to', 'assigned_at']);
            $table->index(['status', 'next_follow_up_at']);
            $table->index(['priority', 'status']);
            $table->index('phone');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
