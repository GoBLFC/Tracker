<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use App\Models\Contracts\HasDisplayName;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property string $name
 * @property bool $hidden
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string $display_name
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\TimeBonus>|\App\Models\TimeBonus[] $timeBonuses
 * @property-read int|null $time_bonuses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\TimeEntry>|\App\Models\TimeEntry[] $timeEntries
 * @property-read int|null $time_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<\App\Models\Activity>|\App\Models\Activity[] $activities
 * @property-read int|null $activities_count
 *
 * @method static \Database\Factories\DepartmentFactory<self> factory($count = null, $state = [])
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static static make(array $attributes = [])
 * @method static static create(array $attributes = [])
 * @method static static forceCreate(array $attributes)
 * @method \App\Models\Department firstOrNew(array $attributes = [], array $values = [])
 * @method \App\Models\Department firstOrFail($columns = ['*'])
 * @method \App\Models\Department firstOrCreate(array $attributes, array $values = [])
 * @method \App\Models\Department firstOr($columns = ['*'], \Closure $callback = null)
 * @method \App\Models\Department firstWhere($column, $operator = null, $value = null, $boolean = 'and')
 * @method \App\Models\Department updateOrCreate(array $attributes, array $values = [])
 * @method null|static first($columns = ['*'])
 * @method static static findOrFail($id, $columns = ['*'])
 * @method static static findOrNew($id, $columns = ['*'])
 * @method static null|static find($id, $columns = ['*'])
 */
class Department extends UuidModel implements HasDisplayName {
	use HasFactory, SoftDeletes, LogsActivity;

	protected $casts = [
		'hidden' => 'boolean',
	];

	protected $fillable = [
		'name',
		'hidden',
	];

	public function getActivitylogOptions(): LogOptions {
		return LogOptions::defaults()
			->logOnly(['name', 'hidden'])
			->logOnlyDirty()
			->submitEmptyLogs();
	}

	public function getDisplayNameAttribute(): string {
		return !$this->deleted_at ? $this->name : "{$this->name} (del)";
	}

	/**
	 * Get the time bonuses associated with this department
	 */
	public function timeBonuses(): BelongsToMany {
		return $this->belongsToMany(TimeBonus::class);
	}

	/**
	 * Get the time entries associated with this department
	 */
	public function timeEntries(): HasMany {
		return $this->hasMany(TimeEntry::class);
	}
}
