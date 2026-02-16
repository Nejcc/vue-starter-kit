<script setup lang="ts">
import { Form, Head, Link, router, useForm } from '@inertiajs/vue3';
import { Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

import FormField from '@/components/FormField.vue';
import Heading from '@/components/Heading.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useEcommerceNav } from '@ecommerce/composables/useEcommerceNav';
import ModuleLayout from '@/layouts/admin/ModuleLayout.vue';
import { type BreadcrumbItem } from '@/types';

const {
    title: moduleTitle,
    icon: moduleIcon,
    items: moduleItems,
} = useEcommerceNav();

interface AttributeType {
    value: string;
    label: string;
}

interface Attribute {
    id: number;
    name: string;
    slug: string;
    type: string;
    sort_order: number;
    is_filterable: boolean;
    is_required: boolean;
    is_active: boolean;
    values: string[] | null;
}

interface AttributeGroup {
    id: number;
    name: string;
    slug: string;
    sort_order: number;
    is_active: boolean;
    attributes?: Attribute[];
}

interface EditAttributeGroupPageProps {
    attributeGroup: AttributeGroup;
    attributeTypes: AttributeType[];
}

const props = defineProps<EditAttributeGroupPageProps>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Ecommerce', href: '#' },
    { title: 'Attributes', href: '/admin/ecommerce/attributes' },
    { title: props.attributeGroup.name, href: '#' },
];

// Add attribute form
const showAttributeForm = ref(false);
const attributeForm = useForm({
    name: '',
    slug: '',
    type: 'text',
    sort_order: '0',
    is_filterable: false,
    is_required: false,
    is_active: true,
    values: '',
});

function addAttribute() {
    const data: Record<string, unknown> = {
        name: attributeForm.name,
        slug: attributeForm.slug || undefined,
        type: attributeForm.type,
        sort_order: Number(attributeForm.sort_order),
        is_filterable: attributeForm.is_filterable,
        is_required: attributeForm.is_required,
        is_active: attributeForm.is_active,
    };

    if (attributeForm.type === 'select' && attributeForm.values) {
        data.values = attributeForm.values.split(',').map((v: string) => v.trim()).filter(Boolean);
    }

    router.post(
        `/admin/ecommerce/attributes/${props.attributeGroup.slug}/items`,
        data,
        {
            onSuccess: () => {
                attributeForm.reset();
                showAttributeForm.value = false;
            },
        },
    );
}

function deleteAttribute(attributeId: number) {
    if (confirm('Are you sure you want to delete this attribute?')) {
        router.delete(
            `/admin/ecommerce/attributes/${props.attributeGroup.slug}/items/${attributeId}`,
        );
    }
}
</script>

<template>
    <ModuleLayout
        :breadcrumbs="breadcrumbItems"
        :module-title="moduleTitle"
        :module-icon="moduleIcon"
        :module-items="moduleItems"
    >
        <Head :title="`Edit ${attributeGroup.name}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-8">
                <Heading
                    variant="small"
                    :title="`Edit ${attributeGroup.name}`"
                    description="Update attribute group details and manage attributes"
                />

                <!-- Group Details Form -->
                <div class="rounded-lg border p-6">
                    <h3 class="mb-4 text-lg font-medium">Group Details</h3>
                    <Form
                        :action="`/admin/ecommerce/attributes/${attributeGroup.slug}`"
                        method="put"
                        class="space-y-4"
                        v-slot="{ errors, processing, recentlySuccessful }"
                    >
                        <div class="grid gap-4 md:grid-cols-2">
                            <FormField
                                label="Name"
                                id="name"
                                :error="errors.name"
                                required
                            >
                                <Input
                                    id="name"
                                    name="name"
                                    type="text"
                                    :value="attributeGroup.name"
                                    required
                                />
                            </FormField>

                            <FormField
                                label="Slug"
                                id="slug"
                                :error="errors.slug"
                            >
                                <Input
                                    id="slug"
                                    name="slug"
                                    type="text"
                                    :value="attributeGroup.slug"
                                />
                            </FormField>
                        </div>

                        <FormField
                            label="Sort Order"
                            id="sort_order"
                            :error="errors.sort_order"
                        >
                            <Input
                                id="sort_order"
                                name="sort_order"
                                type="number"
                                min="0"
                                :value="String(attributeGroup.sort_order)"
                            />
                        </FormField>

                        <div class="flex items-center gap-2">
                            <Checkbox
                                id="is_active"
                                name="is_active"
                                :default-checked="attributeGroup.is_active"
                                value="1"
                            />
                            <Label for="is_active">Active</Label>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button :disabled="processing" type="submit">
                                Update Group
                            </Button>
                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p
                                    v-show="recentlySuccessful"
                                    class="text-sm text-neutral-600 dark:text-neutral-400"
                                >
                                    Updated.
                                </p>
                            </Transition>
                        </div>
                    </Form>
                </div>

                <!-- Attributes Section -->
                <div class="rounded-lg border p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-medium">Attributes</h3>
                        <Button
                            size="sm"
                            @click="showAttributeForm = !showAttributeForm"
                        >
                            <Plus class="mr-2 h-4 w-4" />
                            Add Attribute
                        </Button>
                    </div>

                    <!-- Add Attribute Form -->
                    <div
                        v-if="showAttributeForm"
                        class="mb-4 rounded-lg border bg-muted/50 p-4"
                    >
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="grid gap-2">
                                <Label for="attr_name">Name</Label>
                                <Input
                                    id="attr_name"
                                    v-model="attributeForm.name"
                                    type="text"
                                    placeholder="e.g. Color"
                                />
                            </div>
                            <div class="grid gap-2">
                                <Label for="attr_type">Type</Label>
                                <select
                                    id="attr_type"
                                    v-model="attributeForm.type"
                                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm"
                                >
                                    <option
                                        v-for="attrType in attributeTypes"
                                        :key="attrType.value"
                                        :value="attrType.value"
                                    >
                                        {{ attrType.label }}
                                    </option>
                                </select>
                            </div>
                            <div class="grid gap-2">
                                <Label for="attr_sort">Sort Order</Label>
                                <Input
                                    id="attr_sort"
                                    v-model="attributeForm.sort_order"
                                    type="number"
                                    min="0"
                                />
                            </div>
                            <div
                                v-if="attributeForm.type === 'select'"
                                class="grid gap-2"
                            >
                                <Label for="attr_values">Values (comma-separated)</Label>
                                <Input
                                    id="attr_values"
                                    v-model="attributeForm.values"
                                    type="text"
                                    placeholder="Small, Medium, Large"
                                />
                            </div>
                        </div>
                        <div class="mt-3 flex flex-wrap gap-4">
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="attr_filterable"
                                    v-model:checked="attributeForm.is_filterable"
                                />
                                <Label for="attr_filterable">Filterable</Label>
                            </div>
                            <div class="flex items-center gap-2">
                                <Checkbox
                                    id="attr_required"
                                    v-model:checked="attributeForm.is_required"
                                />
                                <Label for="attr_required">Required</Label>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-2">
                            <Button
                                size="sm"
                                :disabled="attributeForm.processing"
                                @click="addAttribute"
                            >
                                Save Attribute
                            </Button>
                            <Button
                                variant="outline"
                                size="sm"
                                @click="showAttributeForm = false"
                            >
                                Cancel
                            </Button>
                        </div>
                    </div>

                    <!-- Attributes List -->
                    <div
                        v-if="attributeGroup.attributes && attributeGroup.attributes.length > 0"
                        class="space-y-3"
                    >
                        <div
                            v-for="attribute in attributeGroup.attributes"
                            :key="attribute.id"
                            class="flex items-center justify-between rounded-lg border p-3"
                        >
                            <div>
                                <p class="font-medium">{{ attribute.name }}</p>
                                <p class="text-sm text-muted-foreground">
                                    Type: {{ attribute.type }}
                                    &middot; Order: {{ attribute.sort_order }}
                                    <span v-if="attribute.values">
                                        &middot; Values: {{ attribute.values.join(', ') }}
                                    </span>
                                </p>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    <span
                                        v-if="attribute.is_filterable"
                                        class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700 dark:bg-blue-900 dark:text-blue-300"
                                    >
                                        Filterable
                                    </span>
                                    <span
                                        v-if="attribute.is_required"
                                        class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-xs text-orange-700 dark:bg-orange-900 dark:text-orange-300"
                                    >
                                        Required
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <StatusBadge
                                    :label="attribute.is_active ? 'Active' : 'Inactive'"
                                    :variant="attribute.is_active ? 'success' : 'default'"
                                />
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    class="text-destructive hover:text-destructive"
                                    @click="deleteAttribute(attribute.id)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">
                        No attributes yet. Add attributes to this group.
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/ecommerce/attributes"
                        class="text-sm text-muted-foreground hover:underline"
                    >
                        Back to Attribute Groups
                    </Link>
                </div>
            </div>
        </div>
    </ModuleLayout>
</template>
