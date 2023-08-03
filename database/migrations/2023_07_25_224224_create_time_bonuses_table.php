<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('time_bonuses', function (Blueprint $table) {
			$table->uuid('id')->primary();
			$table->datetime('start');
			$table->datetime('stop');
			$table->float('modifier', 4, 2);
			$table->foreignUuid('department_id')->constrained()->cascadeOnDelete();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('time_bonuses');
	}
};
