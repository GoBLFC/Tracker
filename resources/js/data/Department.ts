export default interface Department {
	id: DepartmentId;
	name: string;
	hidden: boolean;
}

export type DepartmentId = string & { __departmentIdType: never };
