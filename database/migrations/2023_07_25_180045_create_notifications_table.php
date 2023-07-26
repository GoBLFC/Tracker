<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('notifications', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->foreignId('user_id')->constrained();
			$table->foreignUuid('reward_id')->nullable()->constrained();
			$table->text('message');
			$table->boolean('has_read')->default(false);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('notifications');
	}
};
