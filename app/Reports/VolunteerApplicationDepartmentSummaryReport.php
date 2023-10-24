<?php

namespace App\Reports;

use App\Facades\ConCat;
use Illuminate\Support\Collection;
use App\Reports\Concerns\WithTotals;
use App\Reports\Concerns\FormatsAsTable;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class VolunteerApplicationDepartmentSummaryReport extends Report implements FromCollection, WithMapping, WithHeadings, WithColumnFormatting, WithTotals, WithStrictNullComparison, WithEvents, ShouldAutoSize {
	use RegistersEventListeners, FormatsAsTable;

	private Collection $departments;
	private Collection $volunteers;

	public function prefetch(): void {
		// Authenticate with ConCat and retrieve the volunteers
		if (!ConCat::isAuthorized()) ConCat::authorize();
		$this->volunteers = new Collection(ConCat::searchVolunteers());
		$this->volunteers = $this->volunteers->keyBy('user.id');

		if ($this->volunteers->isEmpty()) return;

		// Replace the options array with a keyed collection on each volunteer
		foreach ($this->volunteers as $volunteer) {
			$options = new Collection($volunteer->options);
			$volunteer->options = $options->keyBy('name');
		}

		// Build a list of departments used
		$this->departments = $this->volunteers->pluck('departments')
			->flatten()
			->unique('id')
			->sortBy('name')
			->values()
			->each(function ($department) {
				$department->assignedVolunteers = 0;
				$department->unassignedVolunteers = 0;
				$department->totalVolunteers = 0;
				$department->assignedHours = 0.0;
				$department->unassignedHours = 0.0;
				$department->totalHours = 0.0;
			});
	}

	public function collection(): Collection {
		if (!isset($this->volunteers)) $this->prefetch();

		// Iterate through each volunteer and add their volunteer/hours contributions to the departments
		foreach ($this->volunteers as $volunteer) {
			// Build a list of assigned department names for the volunteer
			$assignedDepartments = collect($volunteer->departments)
				->filter(fn ($dept) => in_array('assignment', $dept->states))
				->pluck('name');

			// If the volunteer is assigned to any departments, we only want to contribute their numbers to them.
			// If they're not assigned to any departments, then each of the departments that they put down as
			// interested or having experience in get their numbers instead.
			// Hours are always split evenly between the assigned or associated departments.
			if (count($assignedDepartments) > 0) {
				// Add a volunteer and their hours to their assigned departments
				$this->departments->whereIn('name', $assignedDepartments)
					->each(function ($department) use ($volunteer, $assignedDepartments) {
						$department->assignedVolunteers += 1;
						$department->totalVolunteers += 1;

						$hours = ($volunteer->options->get('Available Hours')?->value ?? 0) / count($assignedDepartments);
						$department->assignedHours += $hours;
						$department->totalHours += $hours;
					});
			} else {
				// Build a list of all departments that the volunteer put anything down for, as long as they haven't
				// marked them as "avoid".
				$departments = collect($volunteer->departments)
					->filter(fn ($dept) => !in_array('avoid', $dept->states))
					->pluck('name');

				// Add a volunteer and their hours to the departments
				$this->departments->whereIn('name', $departments)
					->each(function ($department) use ($volunteer, $departments) {
						$department->unassignedVolunteers += 1;
						$department->totalVolunteers += 1;

						$hours = ($volunteer->options->get('Available Hours')?->value ?? 0) / count($departments);
						$department->unassignedHours += $hours;
						$department->totalHours += $hours;
					});
			}
		}

		return $this->departments;
	}

	public function map($department, $excelDates = true): array {
		return [
			$department->name,
			$department->assignedVolunteers,
			$department->assignedHours,
			$department->unassignedVolunteers,
			$department->unassignedHours,
			$department->totalVolunteers,
			$department->totalHours,
		];
	}

	public function headings(): array {
		$headings = [
			'Department',
			'Volunteers (assigned)',
			'Desired Hours (assigned)',
			'Volunteers (unassigned)',
			'Desired Hours (unassigned)',
			'Volunteers (total)',
			'Desired Hours (total)',
		];

		return $headings;
	}

	public function columnFormats(): array {
		return [
			'B' => NumberFormat::FORMAT_NUMBER,
			'C' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
			'D' => NumberFormat::FORMAT_NUMBER,
			'E' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
			'F' => NumberFormat::FORMAT_NUMBER,
			'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
		];
	}

	public function totalsLabel(): string {
		return 'Totals';
	}

	public function totalsFunctions(): array {
		return [
			'none',
			'sum',
			'sum',
			'sum',
			'sum',
			'sum',
			'sum',
		];
	}

	public static function name(): string {
		return 'Application Department Summary';
	}

	public static function slug(): string {
		return 'application-departments';
	}

	public static function afterSheet(AfterSheet $event) {
		static::formatAsTable($event->sheet);
	}
}
