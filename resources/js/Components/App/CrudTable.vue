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
		<!-- Entity field columns -->
		<Column
			v-for="field of fields"
			:field="String(field.key)"
			:header="field.label"
			sortable
			:data-type="
				field.type === 'number' ? 'number'
					: field.type === 'datetime' ? 'date'
					: field.type === 'switch' ? 'boolean'
					: (field.type ?? undefined)
			"
			:class="field.class"
		>
			<template #body="{ data: item }: { data: T }">
				<slot :name="`col-${String(field.key)}`" :item>
					<!-- Viewing - Field text -->
					<template v-if="readonly || !editing.get(item.id)">
						<template v-if="field.display">{{ field.display(item[field.key]) }}</template>
						<template v-else-if="field.type === 'number'">
							{{ field.prefix ?? "" }}
							{{ field.format?.format?.(item[field.key] as number) ??
								(item[field.key] as number).toLocaleString(undefined, {
									minimumFractionDigits: field.fractionDigits,
									maximumFractionDigits: field.fractionDigits,
								}) }}{{ field.suffix ?? "" }}
						</template>
						<template v-else-if="field.type === 'datetime'">
							<DateTime :date="item[field.key] as Date | string" />
						</template>
						<template v-else-if="field.type === 'select' && field.multiple">
							{{ (item[field.key] as string[] | number[])?.join?.(', ') ?? '' }}
						</template>
						<template v-else-if="field.type === 'switch'">
							<ToggleSwitch :model-value="item[field.key] as boolean" readonly />
						</template>
						<template v-else>{{ item[field.key] }}</template>
					</template>

					<!-- Editing - Field inputs -->
					<InputNumber
						v-else-if="field.type === 'number'"
						v-model="editing.get(item.id)![field.key]"
						:form="editFormId(item.id)"
						:min="field.min"
						:max="field.max"
						:step="field.step"
						:prefix="field.prefix"
						:suffix="field.suffix"
						:min-fraction-digits="field.fractionDigits"
						:max-fraction-digits="field.fractionDigits"
						:required="field.required"
						:disabled="Boolean(updating.get(item.id))"
						show-buttons
						fluid
						:pt="{ pcInputText: { props: { form: editFormId(item.id) } } }"
						@input="clearEditError(item.id, field.key)"
					/>
					<DatePicker
						v-else-if="field.type === 'datetime'"
						v-model="editing.get(item.id)![field.key]"
						:form="editFormId(item.id)"
						:min-date="field.min"
						:max-date="field.max"
						:required="field.required"
						:disabled="Boolean(updating.get(item.id))"
						show-time
						date-format="yy-mm-dd"
						hour-format="12"
						show-icon
						icon-display="input"
						fluid
						:pt="{ pcInputText: { props: { form: editFormId(item.id) } } }"
						@value-change="clearEditError(item.id, field.key)"
					/>
					<Select
						v-else-if="field.type === 'select' && !field.multiple"
						v-model="editing.get(item.id)![field.key]"
						:form="editFormId(item.id)"
						option-label="label"
						option-value="value"
						:options="field.options"
						:disabled="Boolean(updating.get(item.id))"
						fluid
						@change="clearEditError(item.id, field.key)"
					/>
					<MultiSelect
						v-else-if="field.type === 'select' && field.multiple"
						v-model="editing.get(item.id)![field.key]"
						:form="editFormId(item.id)"
						option-label="label"
						option-value="value"
						:options="field.options"
						:max-selected-labels="3"
						:disabled="Boolean(updating.get(item.id))"
						display="chip"
						fluid
						@change="clearEditError(item.id, field.key)"
					/>
					<ToggleSwitch
						v-else-if="field.type === 'switch'"
						v-model="editing.get(item.id)![field.key]"
						:form="editFormId(item.id)"
						:disabled="Boolean(updating.get(item.id))"
						@change="clearEditError(item.id, field.key)"
					/>
					<Textarea
						v-else-if="field.type === 'textarea'"
						v-model="editing.get(item.id)![field.key]"
						:form="editFormId(item.id)"
						:required="field.required"
						:minlength="field.min"
						:maxlength="field.max"
						:disabled="Boolean(updating.get(item.id))"
						:rows="1"
						auto-resize
						fluid
						@input="clearEditError(item.id, field.key)"
					/>
					<InputText
						v-else
						v-model="editing.get(item.id)![field.key]"
						:form="editFormId(item.id)"
						:required="field.required"
						:minlength="field.min"
						:maxlength="field.max"
						:disabled="Boolean(updating.get(item.id))"
						fluid
						@input="clearEditError(item.id, field.key)"
					/>

					<!-- Editing - Field errors -->
					<Message v-if="editErrors.get(item.id)?.[field.key]" size="small" severity="error" variant="simple">
						{{ editErrors.get(item.id)![field.key] }}
					</Message>
				</slot>
			</template>

			<!-- Creation - Field inputs -->
			<template #footer v-if="!readonly">
				<InputNumber
					v-if="field.type === 'number'"
					v-model="createForm[field.key]"
					ref="createField"
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
					@input="
						// @ts-expect-error
						createForm.clearErrors(field.key)
					"
				/>
				<DatePicker
					v-else-if="field.type === 'datetime'"
					v-model="createForm[field.key]"
					ref="createField"
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
					@value-change="
						// @ts-expect-error
						createForm.clearErrors(field.key)
					"
				/>
				<Textarea
					v-else-if="field.type === 'textarea'"
					v-model="createForm[field.key]"
					ref="createField"
					:form="createFormId"
					:required="field.required"
					:minlength="field.min"
					:maxlength="field.max"
					:rows="1"
					:disabled="createForm.processing"
					:invalid="Boolean(createForm.errors[field.key])"
					auto-resize
					fluid
					@input="
						// @ts-expect-error
						createForm.clearErrors(field.key)
					"
				/>
				<Select
					v-else-if="field.type === 'select' && !field.multiple"
					v-model="createForm[field.key]"
					ref="createField"
					:form="createFormId"
					option-label="label"
					option-value="value"
					:options="field.options"
					:disabled="createForm.processing"
					:invalid="Boolean(createForm.errors[field.key])"
					fluid
					@change="
						// @ts-expect-error
						createForm.clearErrors(field.key)
					"
				/>
				<MultiSelect
					v-else-if="field.type === 'select' && field.multiple"
					v-model="createForm[field.key]"
					ref="createField"
					:form="createFormId"
					option-label="label"
					option-value="value"
					:options="field.options"
					:max-selected-labels="3"
					:disabled="createForm.processing"
					:invalid="Boolean(createForm.errors[field.key])"
					display="chip"
					fluid
					@change="
						// @ts-expect-error
						createForm.clearErrors(field.key)
					"
				/>
				<ToggleSwitch
					v-else-if="field.type === 'switch'"
					v-model="createForm[field.key]"
					ref="createField"
					:form="createFormId"
					:disabled="createForm.processing"
					:invalid="Boolean(createForm.errors[field.key])"
					@change="
						// @ts-expect-error
						createForm.clearErrors(field.key)
					"
				/>
				<InputText
					v-else
					v-model="createForm[field.key]"
					ref="createField"
					:form="createFormId"
					:required="field.required"
					:minlength="field.min"
					:maxlength="field.max"
					:disabled="createForm.processing"
					:invalid="Boolean(createForm.errors[field.key])"
					fluid
					@input="
						// @ts-expect-error
						createForm.clearErrors(field.key)
					"
				/>

				<!-- Creation - Field errors -->
				<Message v-if="createForm.errors[field.key]" size="small" severity="error" variant="simple">
					{{ createForm.errors[field.key] }}
				</Message>
			</template>
		</Column>

		<!-- Actions column -->
		<Column
			v-if="!readonly"
			header="Actions"
			class="w-4 text-end"
			:pt="{ columnHeaderContent: { class: 'justify-end' } }"
		>
			<template #body="{ data: item }: { data: T }">
				<ButtonGroup :aria-label="`${entityLabel} actions`">
					<!-- Edit/delete buttons -->
					<template v-if="!editing.get(item.id)">
						<IconButton
							variant="text"
							size="small"
							severity="primary"
							:icon="faPencil"
							:disabled="Boolean(deleting.get(item.id))"
							v-tooltip.bottom="'Edit'"
							@click="edit(item)"
						/>
						<IconButton
							variant="text"
							size="small"
							severity="danger"
							:icon="faTrash"
							:loading="Boolean(deleting.get(item.id))"
							:disabled="Boolean(deleting.get(item.id))"
							v-tooltip.bottom="'Delete'"
							@click="del(item.id)"
						/>
					</template>

					<!-- Cancel/save editing buttons -->
					<template v-else>
						<form :id="editFormId(item.id)" @submit.prevent="update(editing.get(item.id)!)">
							<IconButton
								type="submit"
								variant="text"
								size="small"
								severity="primary"
								:icon="faSave"
								:loading="Boolean(updating.get(item.id))"
								:disabled="Boolean(updating.get(item.id))"
								v-tooltip.bottom="'Save'"
							/>
						</form>
						<IconButton
							variant="text"
							size="small"
							severity="secondary"
							:icon="faCancel"
							:disabled="Boolean(updating.get(item.id))"
							v-tooltip.bottom="'Cancel'"
							@click="cancel(item.id)"
						/>
					</template>
				</ButtonGroup>
			</template>

			<!-- Create button -->
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

			<template v-if="$slots.help" #header>
				<HelpDialogButton :header="helpTitle" class="mt-[-100%] mb-[-100%]">
					<slot name="help" />
				</HelpDialogButton>
			</template>
		</Column>

		<!-- Empty table placeholder -->
		<template #empty>
			<slot name="empty"><p>There aren't any {{ entityPlural ?? `${entityName}s` }}.</p></slot>
		</template>
	</DataTable>

	<SkeletonTable v-else :columns="[...fields.map((f) => f.label), ...(!readonly ? ['Actions'] : [])]" />
</template>

<script setup lang="ts" generic="T extends { id: string, [key: string]: any }">
import { reactive, shallowReactive, toRef, useId, useTemplateRef } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import type { Errors } from '@inertiajs/core';
import { useTime } from '@/lib/time';
import { useConfirm } from '@/lib/confirm';

import { faSave, faCancel, faPencil, faTrash, faPlus } from '@fortawesome/free-solid-svg-icons';
import SkeletonTable from '../Common/SkeletonTable.vue';
import ResponsiveButton from '../Common/ResponsiveButton.vue';
import IconButton from '../Common/IconButton.vue';
import DateTime from '../Common/DateTime.vue';
import HelpDialogButton from '../Common/HelpDialogButton.vue';

const {
	items,
	entityName,
	entityPlural,
	routeSlug,
	createRoute,
	fields,
	readonly = false,
	skeleton = false,
} = defineProps<{
	entityName: string;
	entityPlural?: string;
	routeSlug: string;
	createRoute?: string | Parameters<typeof route>;
	fields: Field<T>[];
	readonly?: boolean;
	items?: T[];
	tableProps?: Record<string, unknown>;
	helpTitle?: string;
	skeleton?: boolean;
}>();

const entityLabel = toRef(() => {
	const first = entityName[0]!.toLocaleUpperCase();
	const rest = entityName.substring(1);
	return first + rest;
});

/**
 * Entity field definition
 */
type Field<T> = { [K in keyof T & string]: FieldConfig<T, K> }[keyof T & string];

/**
 * Configuration for a single field of an entity
 */
type FieldConfig<T, K extends keyof T & string> = {
	key: K;
	label: string;
	class?: string;
	required?: boolean;
	default?: T[K];
	display?: (data: T[K]) => string;
} & (
	| {
			type?: 'text';
			min?: number;
			max?: number;
	  }
	| {
			type: 'textarea';
			min?: number;
			max?: number;
	  }
	| {
			type: 'select';
			options: { label: string; value: T[K] }[];
			multiple: false;
	  }
	| (NonNullable<T[K]> extends string[] | number[]
			? {
					type: 'select';
					options: { label: string; value: NonNullable<T[K]> extends (infer I)[] ? I : never }[];
					multiple: true;
				}
			: never)
	| (NonNullable<T[K]> extends number
			? {
					type: 'number';
					min?: number;
					max?: number;
					step?: number;
					format?: Intl.NumberFormat;
					fractionDigits?: number;
					prefix?: string;
					suffix?: string;
				}
			: never)
	| (NonNullable<T[K]> extends Date | string
			? {
					type: 'datetime';
					min?: Date;
					max?: Date;
				}
			: never)
	| (NonNullable<T[K]> extends boolean ? { type: 'switch' } : never)
);

/**
 * Key for an entity field
 */
type Key = keyof T & string;

//
// Entity creation
//

const createFormId = useId();
const createFields = useTemplateRef('createField');
const createForm = useForm<T & { [key: string]: unknown }>(
	fields.reduce((prev, field) => {
		// @ts-expect-error
		prev[field.key] = field.default ?? null;
		return prev;
	}, {} as T),
);

/**
 * Creates a new entity with the entered values
 */
async function create() {
	if (createForm.processing) return;

	createForm.post(Array.isArray(createRoute) ? route(...createRoute) : route(createRoute ?? `${routeSlug}.store`), {
		replace: true,
		preserveState: true,
		preserveScroll: true,
		only: [entityPlural ?? `${entityName}s`, 'flash'],
		onSuccess() {
			createForm.resetAndClearErrors();
			// @ts-expect-error
			setTimeout(() => createFields.value![0]!.$el.focus(), 0);
		},
	});
}

//
// Entity editing
//

const { dateToTrackerTime, isoToPreferredLocalAdjustedTime } = useTime();
const editing = reactive(new Map<T['id'], T>()) as Map<T['id'], T>;
const updating = shallowReactive(new Map<T['id'], boolean>()) as Map<T['id'], boolean>;
const editErrors = reactive(new Map<T['id'], Errors>()) as Map<T['id'], Errors>;
const editFormIdBase = useId();

/**
 * Starts editing an entity
 */
function edit(entity: T) {
	editing.set(entity.id, toEditable(entity));

	const formId = editFormId(entity.id);
	setTimeout(() => {
		(
			document.querySelector(`input[form="${formId}"], textarea[form="${formId}"], [form="${formId}"] input`) as
				| HTMLInputElement
				| HTMLTextAreaElement
		)?.focus?.();
	}, 0);
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
	editing.delete(id);
	editErrors.delete(id);
}

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
				updating.set(edited.id, true);
			},
			onFinish() {
				updating.set(edited.id, false);
			},
			onSuccess() {
				cancel(edited.id);
			},
			onError(err) {
				console.error(`Error updating ${entityName} ${edited.id}`, err, edited);
				editErrors.set(edited.id, err);
			},
		},
	);
}

/**
 * Clears any errors for an entity's edit field
 */
function clearEditError(id: T['id'], field: Key) {
	const errors = editErrors.get(id);
	if (!errors) return;
	delete errors[field];
}

/**
 * Converts an entity's raw representation to one that's directly editable by input fields
 */
function toEditable(entity: T) {
	const mapped = { ...entity };

	for (const field of fields) {
		if (field.type !== 'datetime') continue;
		if (typeof entity[field.key] === 'object') continue;

		// @ts-expect-error
		mapped[field.key] = isoToPreferredLocalAdjustedTime(entity[field.key]).toJSDate();
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

		// @ts-expect-error
		mapped[field.key] = dateToTrackerTime(entity[field.key] as Date).toISO();
	}

	return mapped;
}

//
// Entity deletion
//

const { confirm } = useConfirm();
const deleting = shallowReactive(new Map<T['id'], boolean>());

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
			deleting.set(id, true);
		},
		onFinish() {
			deleting.delete(id);
		},
		onError(err) {
			console.error(`Error deleting ${entityName} ${id}`, err);
		},
	});
}
</script>
