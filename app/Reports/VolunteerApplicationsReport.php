<?php

namespace App\Reports;

use App\Facades\ConCat;
use App\Reports\Concerns\FormatsAsTable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class VolunteerApplicationsReport extends EventReport implements FromCollection, ShouldAutoSize, WithColumnFormatting, WithEvents, WithHeadings, WithMapping, WithStrictNullComparison {
	use FormatsAsTable, RegistersEventListeners;

	private Collection $departments;
	private Collection $volunteers;

	public function prefetch(): void {
		// Authenticate with ConCat and retrieve the volunteers
		if (!ConCat::isAuthorized()) ConCat::authorize();
		$this->volunteers = new Collection(ConCat::searchVolunteers());
		$this->volunteers = $this->volunteers->keyBy('user.id');

		if ($this->volunteers->isEmpty()) {
			$this->departments = new Collection;
			return;
		}

		// Retrieve registrations for the volunteers
		$userIds = $this->volunteers->keys()->map(fn ($key) => (string) $key)->toArray();
		$registrations = new Collection(ConCat::searchRegistrationsByUserIds($userIds));

		// Add the badge name from the registration to each volunteer
		if ($registrations->isNotEmpty()) {
			foreach ($registrations as $registration) {
				$volunteer = $this->volunteers->get($registration->user->id);
				$volunteer->badgeName = $registration->badgeName;
			}
		}

		// Replace the options array with a keyed collection on each volunteer
		foreach ($this->volunteers as $volunteer) {
			$options = new Collection($volunteer->options);

			// For the "previous experience at <con>" option, we need to wipe the con name out since it's not constant.
			// This is pretty hacky because of how options are provided by the ConCat API.
			foreach ($options as $option) {
				if (Str::contains($option->name, 'previously volunteered with')) {
					$con = Str::between($option->name, 'with ', '?');
					$option->name = Str::replace($con, 'CONVENTION', $option->name);
				}
			}

			// Key the collection
			$volunteer->options = $options->keyBy('name');
		}

		// Build a list of departments used
		$this->departments = $this->volunteers->pluck('departments')
			->flatten()
			->unique('id')
			->pluck('name')
			->sort()
			->values();
	}

	public function collection(): Collection {
		if (!isset($this->volunteers)) $this->prefetch();
		return $this->volunteers;
	}

	public function map($volunteer, $excelDates = true): array {
		$legalName = "{$volunteer->user->firstName} {$volunteer->user->lastName}";
		$departments = new Collection($volunteer->departments);
		$contacts = new Collection($volunteer->contactMethods);
		$createdAt = Carbon::parse($volunteer->createdAt)->timezone(config('tracker.timezone'));
		$updatedAt = Carbon::parse($volunteer->updatedAt)->timezone(config('tracker.timezone'));
		$days = static::getOptionValue($volunteer, 'Volunteer Days');

		$values = [
			// General information
			$volunteer->user->id,
			$volunteer->badgeName ?? $volunteer->user->username,
			$volunteer->user->preferredName ? "{$volunteer->user->preferredName} ({$legalName})" : $legalName,
			$volunteer->user->isStaff ? 'Yes' : 'No',
			$departments->filter(fn ($dept) => in_array('assignment', $dept->states))
				->pluck('name')
				->sort()
				->join(', '),
			$contacts->firstWhere('isPrimary', true)?->name,
			$volunteer->user->email,
			$volunteer->user->phone,
			$contacts->firstWhere('name', 'telegram')?->value,
			$contacts->firstWhere('name', 'twitter')?->value,
			$contacts->firstWhere('name', 'discord')?->value,
			$excelDates ? Date::dateTimeToExcel($createdAt) : $createdAt,
			$excelDates ? Date::dateTimeToExcel($updatedAt) : $updatedAt,

			// Application options
			static::getOptionValue($volunteer, 'Available Hours'),
			$days && in_array('Wednesday', $days) ? 'W' : null,
			$days && in_array('Thursday', $days) ? 'Th' : null,
			$days && in_array('Friday', $days) ? 'F' : null,
			$days && in_array('Saturday', $days) ? 'Sa' : null,
			$days && in_array('Sunday', $days) ? 'Su' : null,
			$days && in_array('Monday', $days) ? 'M' : null,
			$days && in_array('Tuesday', $days) ? 'Tu' : null,

			// The rest of this is pretty hacky because of how options are provided by the ConCat API.
			// Any slight change of wording or the removal of these options could break the report.
			// In other words: This assumes default ConCat volunteer application form fields are retained.

			// "Are you available to help out in the months before the con?"
			// This is a single-select option with two values: "yes" or "no"
			static::getOptionValue($volunteer, 'Are you available to help out in the months before the con?'),

			// "Are there any events you can not miss? [Optional]"
			// Freetext field, max length 4000
			static::getOptionValue($volunteer, 'Are there any events you can not miss? [Optional]'),

			// "Have you previously volunteered with <CON NAME>? [Optional]"
			// Freetext field, max length 4000
			static::getOptionValue($volunteer, 'Have you previously volunteered with CONVENTION? [Optional]'),

			// "Have you previously volunteered for another convention? If so, which? [Optional]"
			// Freetext field, max length 4000
			static::getOptionValue($volunteer, 'Have you previously volunteered for another convention? If so, which? [Optional]'),

			// "Is there anything else you would like to mention?"
			// Freetext field, max length 4000
			static::getOptionValue($volunteer, 'Is there anything else you would like to mention?'),
		];

		// Add a cell for each department that occurs in the entire result set
		foreach ($this->departments as $dept) {
			$values[] = static::departmentStatesToString($departments->firstWhere('name', $dept)?->states);
		}

		return $values;
	}

	public function headings(): array {
		$headings = [
			// General information
			'#',
			'Badge Name',
			'Legal Name',
			'Staff?',
			'Assigned Departments',
			'Pref. Contact',
			'Email',
			'Phone',
			'Telegram',
			'Twitter',
			'Discord',
			'Created At',
			'Updated At',

			// Application options
			'Desired Hours',
			'W',
			'Th',
			'F',
			'Sa',
			'Su',
			'M',
			'Tu',
			'P',
			'Can\'t Miss',
			'Previous Experience',
			'Other Con Experience',
			'Comments',
		];

		// Add department headings
		array_push($headings, ...$this->departments);

		return $headings;
	}

	public function columnFormats(): array {
		return [
			'L' => NumberFormat::FORMAT_DATE_DATETIME,
			'M' => NumberFormat::FORMAT_DATE_DATETIME,
			'N' => NumberFormat::FORMAT_NUMBER,
		];
	}

	public static function name(): string {
		return 'Volunteer Applications';
	}

	public static function slug(): string {
		return 'applications';
	}

	public static function defaultSortColumn(): int {
		return 12;
	}

	public static function defaultSortDirection(): string {
		return 'desc';
	}

	public static function afterSheet(AfterSheet $event) {
		static::formatAsTable($event->sheet);
	}

	/**
	 * Get the value of the specified option for a volunteer
	 */
	private static function getOptionValue($volunteer, $name): string|array|null {
		return $volunteer->options->get($name)?->value;
	}

	/**
	 * Build a string of emoji to indicate department states
	 */
	private static function departmentStatesToString(?array $states): ?string {
		if (!$states) return null;

		$string = '';
		if (in_array('avoid', $states)) $string .= '❌';
		if (in_array('assignment', $states)) $string .= '✔️';
		if (in_array('experience', $states)) $string .= '❗';
		if (in_array('interest', $states)) $string .= '❤️';

		return $string;
	}
}
