export interface Column {
    name: string;
    type: string;
    nullable: boolean;
    default: string | null;
    primary: boolean;
}

export interface Index {
    name: string;
    unique: boolean;
    columns: string[];
}

export interface ForeignKey {
    name: string;
    columns: string[];
    referencedTable: string;
    referencedColumns: string[];
    onDelete: string | null;
    onUpdate: string | null;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Pagination {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: PaginationLink[];
}

export interface TableInfo {
    name: string;
    columns: Column[];
    indexes: Index[];
    foreignKeys: ForeignKey[];
    rowCount: number;
    data: Record<string, any>[];
    pagination: Pagination | null;
}
