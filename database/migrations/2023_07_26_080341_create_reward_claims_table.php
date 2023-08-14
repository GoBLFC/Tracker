<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('reward_claims', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
			$table->foreignUuid('reward_id')->constrained()->cascadeOnDelete();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('reward_claims');
	}
};
