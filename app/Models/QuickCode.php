<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $code
 * @property string $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @property-read int|null $users_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuickCode unexpired()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\QuickCode expired()
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @method static \Illuminate\Database\Eloquent\Builder|static query()
 * @method static static make(array $attributes = [])
 * @method static static create(array $attributes = [])
 * @method static static forceCreate(array $attributes)
 * @method \App\Models\QuickCode firstOrNew(array $attributes = [], array $values = [])
 * @method \App\Models\QuickCode firstOrFail($columns = ['*'])
 * @method \App\Models\QuickCode firstOrCreate(array $attributes, array $values = [])
 * @method \App\Models\QuickCode firstOr($columns = ['*'], \Closure $callback = null)
 * @method \App\Models\QuickCode firstWhere($column, $operator = null, $value = null, $boolean = 'and')
 * @method \App\Models\QuickCode updateOrCreate(array $attributes, array $values = [])
 * @method null|static first($columns = ['*'])
 * @method static static findOrFail($id, $columns = ['*'])
 * @method static static findOrNew($id, $columns = ['*'])
 * @method static null|static find($id, $columns = ['*'])
 */
class QuickCode extends UuidModel {
	use MassPrunable;

	protected static function boot() {
		parent::boot();

		// Add a listener for the model being created to add a random code if one hasn't already been specified
		static::creating(function ($model) {
			if (!isset($model->code)) $model->generateCode();
		});
	}

	/**
	 * Get the user this quick code is for
	 */
	public function user(): BelongsTo {
		return $this->belongsTo(User::class);
	}

	/**
	 * Get the prunable model query.
	 */
	public function prunable(): Builder {
		return static::expired();
	}

	/**
	 * Scope a query to only include unexpired codes
	 */
	public function scopeUnexpired(Builder $query): void {
		$query->where('updated_at', '>', now()->subSeconds(30));
	}

	/**
	 * Scope a query to only include expired codes
	 */
	public function scopeExpired(Builder $query): void {
		$query->where('updated_at', '<=', now()->subSeconds(30));
	}

	/**
	 * Check whether the code is expired
	 */
	public function isExpired(): bool {
		return $this->updated_at->lte(now()->subSeconds(30));
	}

	/**
	 * Generates and assigns a new code
	 */
	public function generateCode(): void {
		$code = '';
		while (strlen($code) < 4) {
			// It is typically unwise to use base_convert for secure string generation due to precision issues,
			// but in this case we are working with small enough numbers that the precision should never come into question
			$code = base_convert(bin2hex(random_bytes(3)), 16, 36);
		}

		$this->code = Str::upper(Str::substr($code, 0, 4));
	}
}
