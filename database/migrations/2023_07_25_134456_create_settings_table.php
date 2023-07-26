<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		Schema::create('settings', function (Blueprint $table) {
			$table->string('id', 32)->primary();
			$table->string('value', 64);
			$table->timestamps();
		});

		DB::table('settings')->insert([
			[
				'id' => 'active-event',
				'value' => 'null',
			],
			[
				'id' => 'dev-mode',
				'value' => 'true',
			],
		]);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		Schema::dropIfExists('settings');
	}
};
