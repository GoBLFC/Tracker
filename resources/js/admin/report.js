import { DataTable } from 'simple-datatables';

document.addEventListener('DOMContentLoaded', () => {
	const dataTable = new DataTable('#report', {
		perPage: 25,
		perPageSelect: [10, 25, 50, 100, 200, ['All', 0]],
		columns: [{ select: defaultSortColumn, sort: defaultSortDirection }],
		classes: {
			active: 'active',
			disabled: 'disabled',
			input: 'form-control',
			selector: 'form-select',
			paginationList: 'pagination justify-content-md-end',
			paginationListItem: 'page-item',
			paginationListItemLink: 'page-link',
		},
		template: (options, dom) => `<div class="${options.classes.top} row g-2 g-md-4 mb-3">
			${
				options.searchable ?
					`<div class="${options.classes.search} col">
						<div class="input-group">
							<label for="${dom.id}-filter" class="input-group-text">Search</label>
							<input class="${options.classes.input}" type="search" title="${options.labels.searchTitle}"${dom.id ? ` aria-controls="${dom.id}"` : ''}>
						</div>
					</div>` :
					''
			}
			${
				options.paging && options.perPageSelect ?
					`<div class="${options.classes.dropdown} col-md-5 col-lg-4 col-xl-3">
						<div class="input-group">

							<select id="${dom.id}-perPage" class="${options.classes.selector}"></select>
							<label for="${dom.id}-perPage" class="input-group-text">${options.labels.perPage}</label>
						</div>
					</div>` :
					''
			}
			</div>
			<div class="${options.classes.container} table-responsive mb-3"${options.scrollY.length ? ` style="height: ${options.scrollY}; overflow-Y: auto;"` : ''}></div>
			<div class="${options.classes.bottom} row g-2 g-md-4">
				${
					options.paging ?
						`<div class="${options.classes.info} col-auto"></div>` :
						''
				}
				<nav class="${options.classes.pagination} col-auto ms-auto"></nav>
			</div>`,
		tableRender: (_data, table, _type) => {
			const headings = table.childNodes[0].childNodes[0].childNodes;
			for(const heading of headings) heading.attributes.scope = 'col';
			table.childNodes[1].attributes = { class: 'table-group-divider' };
		}
	});
});
