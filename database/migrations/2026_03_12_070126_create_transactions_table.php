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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['income', 'expense', 'transfer', 'adjustment']);
            $table->decimal('amount', 15, 2);
            $table->date('date');
            $table->string('description');
            $table->text('notes')->nullable();
            $table->string('reference_number')->nullable();
            $table->boolean('is_split')->default(false);
            // For transfers
            $table->foreignId('destination_account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->decimal('admin_fee', 15, 2)->default(0);
            $table->decimal('destination_amount', 15, 2)->nullable(); // amount received (after fees)
            // Metadata
            $table->json('attachments')->nullable();
            $table->timestamps();

            $table->softDeletes();
 
            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'type']);
            $table->index(['account_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
