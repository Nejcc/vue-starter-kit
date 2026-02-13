<script setup lang="ts">
interface Column {
    key: string;
    label: string;
    class?: string;
    hideBelow?: 'sm' | 'md' | 'lg';
}

interface Props {
    columns: Column[];
    rows: Record<string, unknown>[];
    rowKey?: string;
}

withDefaults(defineProps<Props>(), {
    rowKey: 'id',
});

function getHideClass(hideBelow?: string): string {
    if (!hideBelow) return '';
    const map: Record<string, string> = {
        sm: 'hidden sm:table-cell',
        md: 'hidden md:table-cell',
        lg: 'hidden lg:table-cell',
    };
    return map[hideBelow] ?? '';
}
</script>

<template>
    <div class="overflow-hidden rounded-lg border">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b bg-muted/50">
                    <th
                        v-for="col in columns"
                        :key="col.key"
                        :class="[
                            'px-4 py-3 text-left font-medium',
                            col.class,
                            getHideClass(col.hideBelow),
                        ]"
                    >
                        {{ col.label }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <tr
                    v-for="row in rows"
                    :key="String(row[rowKey])"
                    class="hover:bg-muted/30"
                >
                    <td
                        v-for="col in columns"
                        :key="col.key"
                        :class="[
                            'px-4 py-3',
                            col.class,
                            getHideClass(col.hideBelow),
                        ]"
                    >
                        <slot :name="`cell-${col.key}`" :row="row" :value="row[col.key]">
                            {{ row[col.key] ?? '—' }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
