<template>
	<DataTable
		v-if="!skeleton"
		:value="items"
		data-key="id"
		sortable
		sort-field="name"
		:sort-order="1"
		scrollable
		scroll-height="flex"
		v-bind="tableProps"
	>
		<Column
			v-for="field of fields"
			:field="String(field.key)"
			:header="field.label"
			sortable
			:data-type="
				field.type === 'number'
					? 'number'
					: field.type === 'date'
					? 'date'
					: field.type ?? undefined
			"
			:class="field.class"
		>
			<template #body="{ data: item }: { data: T }">
				<slot :name="`col-${String(field.key)}`" :item>
					<template v-if="!!readonly || !editing[item.id as T['id']]">
						<template v-if="field.display">
							{{ field.display(item[field.key]) }}
						</template>
						<template v-else-if="field.type === 'number'">
							{{ field.prefix ?? '' }}{{
								field.format?.format?.(item[field.key])
								?? item[field.key].toLocaleString(undefined, {
									minimumFractionDigits: field.fractionDigits,
									maximumFractionDigits: field.fractionDigits,
								})
							}}{{ field.suffix ?? '' }}
						</template>
						<template v-else-if="field.type === 'datetime'">
							<DateTime :date="item[field.key]" />
						</template>
						<template v-else>
							{{ item[field.key] }}
						</template>
					</template>

					<InputNumber
						v-else-if="field.type === 'number'"
						v-model="editing[item.id as T['id']][field.key]"
						:min="field.min"
						:max="field.max"
						:step="field.step"
						:prefix="field.prefix"
						:suffix="field.suffix"
						:min-fraction-digits="field.fractionDigits"
						:max-fraction-digits="field.fractionDigits"
						:required="field.required"
						:disabled="Boolean(updating[item.id as T['id']])"
						show-buttons
						fluid
						:pt="{ pcInputText: { props: { form: editFormId(item.id as T['id']) } } }"
						@input="clearEditError(item.id as T['id'], field.key)"
					/>
					<DatePicker
						v-else-if="field.type === 'datetime'"
						v-model="editing[item.id as T['id']][field.key]"
						:min-date="field.min"
						:max-date="field.max"
						:required="field.required"
						:disabled="Boolean(updating[item.id as T['id']])"
						show-time
						date-format="yy-mm-dd"
						hour-format="12"
						show-icon
						icon-display="input"
						fluid
						:pt="{ pcInputText: { props: { form: editFormId(item.id as T['id']) } } }"
						@value-change="clearEditError(item.id as T['id'], field.key)"
					/>
					<Select
						v-else-if="field.type === 'select' && !field.multiple"
						v-model="editing[item.id as T['id']][field.key]"
						:form="editFormId(item.id as T['id'])"
						option-label="label"
						option-value="value"
						:options="field.options"
						:disabled="Boolean(updating[item.id as T['id']])"
						fluid
						@change="clearEditError(item.id as T['id'], field.key)"
					/>
					<MultiSelect
						v-else-if="field.type === 'select' && field.multiple"
						v-model="editing[item.id as T['id']][field.key]"
						:form="editFormId(item.id as T['id'])"
						option-label="label"
						option-value="value"
						:options="field.options"
						:max-selected-labels="3"
						:disabled="Boolean(updating[item.id as T['id']])"
						display="chip"
						fluid
						@change="clearEditError(item.id as T['id'], field.key)"
					/>
					<Textarea
						v-else-if="field.type === 'textarea'"
						v-model="editing[item.id as T['id']][field.key]"
						:form="editFormId(item.id as T['id'])"
						:required="field.required"
						:disabled="Boolean(updating[item.id as T['id']])"
						:rows="1"
						auto-resize
						fluid
						@input="clearEditError(item.id as T['id'], field.key)"
					/>
					<InputText
						v-else
						v-model="editing[item.id as T['id']][field.key]"
						:form="editFormId(item.id as T['id'])"
						:required="field.required"
						:disabled="Boolean(updating[item.id as T['id']])"
						fluid
						@input="clearEditError(item.id as T['id'], field.key)"
					/>

					<Message
						v-if="editErrors[item.id as T['id']]?.[field.key]"
						size="small"
						severity="error"
						variant="simple"
					>
						{{ editErrors[item.id as T['id']][field.key] }}
					</Message>
				</slot>
			</template>

			<template #footer v-if="!readonly">
				<InputNumber
					v-if="field.type === 'number'"
					v-model="createForm[field.key]"
					:form="createFormId"
					:min="field.min"
					:max="field.max"
					:step="field.step"
					:prefix="field.prefix"
					:suffix="field.suffix"
					:min-fraction-digits="field.fractionDigits"
					:max-fraction-digits="field.fractionDigits"
					:required="field.required"
					:disabled="createForm.processing"
					:invalid="Boolean(createForm.errors[field.key])"
					show-buttons
					fluid
					@input="createForm.clearErrors(field.key)"
				/>
				<DatePicker
					v-else-if="field.type === 'datetime'"
					v-model="createForm[field.key]"
					:form="createFormId"
					:min-date="field.min"
					:max-date="field.max"
					:required="field.required"
					:disabled="createForm.processing"
					:invalid="Boolean(createForm.errors[field.key])"
					show-time
					date-format="yy-mm-dd"
					hour-format="12"
					show-icon
					icon-display="input"
					fluid
					@value-change="createForm.clearErrors(field.key)"
				/>
				<Textarea
					v-else-if="field.type === 'textarea'"
					v-model="createForm[field.key]"
					:form="createFormId"
					:required="field.required"
					:rows="1"
					:disabled="createForm.processing"
					:invalid="Boolean(createForm.errors[field.key])"
					auto-resize
					fluid
					@input="createForm.clearErrors(field.key)"
				/>
				<Select
					v-else-if="field.type === 'select' && !field.multiple"
					v-model="createForm[field.key]"
					:form="createFormId"
					option-label="label"
					option-value="value"
					:options="field.options"
					:disabled="createForm.processing"
					:invalid="Boolean(createForm.errors[field.key])"
					fluid
					@change="createForm.clearErrors(field.key)"
				/>
				<MultiSelect
					v-else-if="field.type === 'select' && field.multiple"
					v-model="createForm[field.key]"
					:form="createFormId"
					option-label="label"
					option-value="value"
					:options="field.options"
					:max-selected-labels="3"
					:disabled="createForm.processing"
					:invalid="Boolean(createForm.errors[field.key])"
					display="chip"
					fluid
					@change="createForm.clearErrors(field.key)"
				/>
				<InputText
					v-else
					v-model="createForm[field.key]"
					:form="createFormId"
					:required="field.required"
					:disabled="createForm.processing"
					:invalid="Boolean(createForm.errors[field.key])"
					fluid
					@input="createForm.clearErrors(field.key)"
				/>

				<Message
					v-if="createForm.errors[field.key]"
					size="small"
					severity="error"
					variant="simple"
				>
					{{ createForm.errors[field.key] }}
				</Message>
			</template>
		</Column>

		<Column
			v-if="!readonly"
			header="Actions"
			class="w-4 text-end"
			:pt="{ columnHeaderContent: { class: 'justify-end' } }"
		>
			<template #body="{ data: item }: { data: T }">
				<ButtonGroup :aria-label="`${entityLabel} actions`">
					<template v-if="!editing[item.id as T['id']]">
						<IconButton
							variant="text"
							size="small"
							severity="primary"
							:icon="faPencil"
							:disabled="Boolean(deleting[item.id as T['id']])"
							v-tooltip.bottom="'Edit'"
							@click="edit(item)"
						/>
						<IconButton
							variant="text"
							size="small"
							severity="danger"
							:icon="faTrash"
							:loading="Boolean(deleting[item.id as T['id']])"
							:disabled="Boolean(deleting[item.id as T['id']])"
							v-tooltip.bottom="'Delete'"
							@click="del(item.id)"
						/>
					</template>

					<template v-else>
						<form
							:id="editFormId(item.id as T['id'])"
							@submit.prevent="update(editing[item.id as T['id']])"
						>
							<IconButton
								type="submit"
								variant="text"
								size="small"
								severity="primary"
								:icon="faSave"
								:loading="Boolean(updating[item.id as T['id']])"
								:disabled="Boolean(updating[item.id as T['id']])"
								v-tooltip.bottom="'Save'"
							/>
						</form>
						<IconButton
							variant="text"
							size="small"
							severity="secondary"
							:icon="faCancel"
							:disabled="Boolean(updating[item.id as T['id']])"
							v-tooltip.bottom="'Cancel'"
							@click="cancel(item.id)"
						/>
					</template>
				</ButtonGroup>
			</template>

			<template #footer v-if="!readonly">
				<form :id="createFormId" @submit.prevent="create()">
					<ResponsiveButton
						type="submit"
						label="Create"
						:icon="faPlus"
						severity="success"
						:loading="createForm.processing"
						:disabled="createForm.processing"
					/>
				</form>
			</template>
		</Column>

		<template #empty>
			<slot name="empty">
				<p>There aren't any {{ entityPlural ?? `${entityName}s` }}.</p>
			</slot>
		</template>
	</DataTable>

	<SkeletonTable
		v-else
		:columns="[...fields.map(f => f.label), ...(!readonly ? ['Actions'] : [])]"
	/>
</template>

<script setup lang="ts" generic="T extends { id: string }, EntityName extends string">
import { reactive, toRef, useId } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import { useTime } from '@/lib/time';
import { useConfirm } from '@/lib/confirm';
import type { Route } from '@/lib/route';

import SkeletonTable from '../Common/SkeletonTable.vue';
import ResponsiveButton from '../Common/ResponsiveButton.vue';
import IconButton from '../Common/IconButton.vue';
import DateTime from '../Common/DateTime.vue';
import { faSave, faCancel, faPencil, faTrash, faPlus } from '@fortawesome/free-solid-svg-icons';

const {
	items,
	entityName,
	entityPlural,
	routeSlug,
	createRoute,
	fields,
	readonly = true,
	skeleton = false,
} = defineProps<{
	entityName: EntityName;
	entityPlural?: string;
	routeSlug: string;
	createRoute?: Route;
	fields: Field<T, unknown>[];
	readonly?: boolean;
	items?: T[];
	tableProps?: Record<string, unknown>;
	skeleton?: boolean;
}>();

const { confirm } = useConfirm();
const { dateToTrackerTime, isoToPreferredLocalAdjustedTime } = useTime();

const entityLabel = toRef(() => {
	const first = entityName[0]!.toLocaleUpperCase();
	const rest = entityName.substring(1);
	return first + rest;
});

const createFormId = useId();
const createForm = useForm(
	fields.reduce((prev, field) => {
		prev[field.key] = field.default ?? '';
		return prev;
	}, {}),
);

/**
 * Creates a new entity with the entered values
 */
async function create() {
	createForm.post(route(...(createRoute ?? [`${routeSlug}.store`])), {
		replace: true,
		preserveState: true,
		preserveScroll: true,
		only: [entityPlural ?? `${entityName}s`, 'flash'],
		onSuccess() {
			createForm.resetAndClearErrors();
		},
	});
}

const editing: Partial<Record<T['id'], T>> = reactive({});
const editErrors: Partial<Record<T['id'], Record<keyof T, string>>> = reactive({});
const editFormIdBase = useId();

/**
 * Starts editing an entity
 */
function edit(entity: T) {
	editing[entity.id as T['id']] = toEditable(entity);
}

/**
 * Gets the ID for an entity's edit form
 */
function editFormId(id: T['id']) {
	return `${editFormIdBase}_${id}`;
}

/**
 * Cancels editing of an entity
 */
function cancel(id: T['id']) {
	editing[id] = undefined;
}

const updating: Partial<Record<T['id'], boolean>> = reactive({});

/**
 * Saves an edited entity
 */
function update(edited: T) {
	router.patch(
		route(`${routeSlug}.update`, edited.id),
		{ ...toRaw(edited), id: undefined },
		{
			replace: true,
			preserveState: true,
			preserveScroll: true,
			only: [entityPlural ?? `${entityName}s`, 'flash'],

			onStart() {
				updating[edited.id as T['id']] = true;
			},
			onFinish() {
				updating[edited.id as T['id']] = false;
			},
			onSuccess() {
				editing[edited.id as T['id']] = undefined;
				editErrors[edited.id as T['id']] = undefined;
			},
			onError(err) {
				console.error(`Error updating ${entityName} ${edited.id}`, err, edited);
				editErrors[edited.id as T['id']] = err;
			},
		},
	);
}

/**
 * Clears any errors for an entity's edit field
 */
function clearEditError(id: T['id'], field: keyof T) {
	if (!editErrors[id]) return;
	editErrors[id][field] = undefined;
}

/**
 * Converts an entity's raw representation to one that's directly editable by input fields
 */
function toEditable(entity: T) {
	const mapped = { ...entity };

	for (const field of fields) {
		if (field.type !== 'datetime') continue;
		mapped[field.key] = isoToPreferredLocalAdjustedTime(entity[field.key] as string).toJSDate();
	}

	return mapped;
}

/**
 * Converts an entity's editable representation to its raw data
 */
function toRaw(entity: T) {
	const mapped = { ...entity };

	for (const field of fields) {
		if (field.type !== 'datetime') continue;
		mapped[field.key] = dateToTrackerTime(entity[field.key] as Date).toISO();
	}

	return mapped;
}

const deleting: Partial<Record<T['id'], boolean>> = reactive({});

/**
 * Deletes an entity
 */
async function del(id: T['id']) {
	const confirmed = await confirm(`Delete ${entityName}?`, {
		accept: { label: 'Delete', severity: 'danger' },
	});
	if (!confirmed) return;

	router.delete(route(`${routeSlug}.destroy`, id), {
		replace: true,
		preserveState: true,
		preserveScroll: true,
		only: [entityPlural ?? `${entityName}s`, 'flash'],

		onStart() {
			deleting[id as T['id']] = true;
		},
		onFinish() {
			deleting[id as T['id']] = false;
		},
		onError(err) {
			console.error(`Error deleting ${entityName} ${id}`, err);
		},
	});
}

type Field<T, V> = {
	key: keyof T;
	label: string;
	class?: string;
	required?: boolean;
	default?: V;
	display?: (data: V) => string;
} & (
	| { type?: 'text' }
	| { type: 'textarea' }
	| {
			type: 'number';
			min?: number;
			max?: number;
			step?: number;
			format?: Intl.NumberFormat;
			fractionDigits?: number;
			prefix?: string;
			suffix?: string;
	  }
	| { type: 'datetime'; min?: Date; max?: Date }
	| {
			type: 'select';
			options: { label: string; value: string | number }[];
			multiple?: boolean;
	  }
);
</script>
