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
			$table->jsonb('value');
			$table->timestamps();
		});

		DB::table('settings')->insert([
			[
				'id' => 'active-event',
				'value' => 'null',
				'created_at' => now(),
				'updated_at' => now(),
			],
			[
				'id' => 'dev-mode',
				'value' => 'true',
				'created_at' => now(),
				'updated_at' => now(),
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
