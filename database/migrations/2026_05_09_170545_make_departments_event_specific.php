<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void {
		// Add the event_id column
		Schema::table('departments', function (Blueprint $table) {
			$table->uuid('event_id')->nullable()->after('hidden');
		});

		$eventIds = DB::table('events')->select(['id'])->get()->pluck('id');
		$oldDepts = DB::table('departments')->get();
		$now = now();

		// Create new departments for each event and update all references to the old ones
		foreach ($eventIds as $eventId) {
			foreach ($oldDepts as $oldDept) {
				$newId = Uuid::uuid7();
				DB::table('departments')->insert([
					'id' => $newId,
					'name' => $oldDept->name,
					'hidden' => $oldDept->hidden,
					'event_id' => $eventId,
					'created_at' => $oldDept->created_at,
					'updated_at' => $now,
					'deleted_at' => $oldDept->deleted_at,
				]);

				DB::table('time_entries')
					->where('event_id', $eventId)
					->where('department_id', $oldDept->id)
					->update(['department_id' => $newId]);
				DB::table('department_time_bonus')
					->join('time_bonuses', 'department_time_bonus.time_bonus_id', '=', 'time_bonuses.id')
					->where('event_id', $eventId)
					->where('department_id', $oldDept->id)
					->update(['department_id' => $newId]);
			}
		}

		// Delete all old departments
		DB::table('departments')->whereNull('event_id')->delete();

		// Remove nullable and add the foreign key constraint
		Schema::table('departments', function (Blueprint $table) {
			$table->uuid('event_id')->nullable(false)->change();
			$table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void {
		// Drop the event_id column
		Schema::table('departments', function (Blueprint $table) {
			$table->dropConstrainedForeignId('event_id');
		});

		$oldDepts = DB::table('departments')->get();
		$now = now();

		// Delete all departments with duplicate names, keeping the oldest, and update all references to them
		foreach ($oldDepts->groupBy('name') as $name => $depts) {
			$oldest = $depts->sortBy('created_at')->first();
			$oldest->updated_at = $now;
			unset($oldest->event_id);
			DB::table('departments')->where('id', $oldest->id)->update((array) $oldest);

			$oldIds = $depts->where('id', '!==', $oldest->id)->pluck('id');
			DB::table('departments')->whereIn('id', $oldIds)->delete();
			DB::table('time_entries')
				->whereIn('department_id', $oldIds)
				->update(['department_id' => $oldest->id]);
			DB::table('department_time_bonus')
				->whereIn('department_id', $oldIds)
				->update(['department_id' => $oldest->id]);
		}
	}
};
