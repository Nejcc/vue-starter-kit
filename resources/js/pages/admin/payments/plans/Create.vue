<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    intervals: string[];
    trialOptions: Record<number, string>;
    defaultCurrency: string;
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    slug: '',
    description: '',
    amount: 0,
    currency: props.defaultCurrency,
    interval: 'month',
    interval_count: 1,
    trial_days: 0,
    features: [] as string[],
    is_active: true,
    is_public: true,
    is_featured: false,
    sort_order: 0,
});

const submit = (): void => {
    form.post('/admin/payments/plans');
};

const formatPrice = (cents: number): string => {
    return (cents / 100).toFixed(2);
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Payments', href: '/admin/payments' },
    { title: 'Plans', href: '/admin/payments/plans' },
    { title: 'Create', href: '#' },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head title="Create Plan" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/payments/plans"
                        class="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back
                    </Link>
                </div>

                <Heading
                    title="Create Plan"
                    description="Create a new subscription plan"
                    variant="small"
                />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-6 lg:grid-cols-2">
                        <!-- Basic Info -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Basic Information</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="space-y-2">
                                    <Label for="name">Name</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Pro Plan"
                                        required
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="text-sm text-red-600"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="slug">Slug (optional)</Label>
                                    <Input
                                        id="slug"
                                        v-model="form.slug"
                                        type="text"
                                        placeholder="pro-plan"
                                    />
                                    <p class="text-xs text-muted-foreground">
                                        Leave empty to auto-generate from name
                                    </p>
                                    <p
                                        v-if="form.errors.slug"
                                        class="text-sm text-red-600"
                                    >
                                        {{ form.errors.slug }}
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        placeholder="Plan description..."
                                    />
                                    <p
                                        v-if="form.errors.description"
                                        class="text-sm text-red-600"
                                    >
                                        {{ form.errors.description }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Pricing -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Pricing</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="amount"
                                            >Amount (cents)</Label
                                        >
                                        <Input
                                            id="amount"
                                            v-model.number="form.amount"
                                            type="number"
                                            min="0"
                                            required
                                        />
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            {{ formatPrice(form.amount) }}
                                            {{ form.currency }}
                                        </p>
                                        <p
                                            v-if="form.errors.amount"
                                            class="text-sm text-red-600"
                                        >
                                            {{ form.errors.amount }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="currency">Currency</Label>
                                        <Input
                                            id="currency"
                                            v-model="form.currency"
                                            type="text"
                                            maxlength="3"
                                            required
                                        />
                                        <p
                                            v-if="form.errors.currency"
                                            class="text-sm text-red-600"
                                        >
                                            {{ form.errors.currency }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="space-y-2">
                                        <Label for="interval">Interval</Label>
                                        <select
                                            id="interval"
                                            v-model="form.interval"
                                            class="w-full rounded-md border bg-background px-3 py-2 text-sm"
                                        >
                                            <option
                                                v-for="interval in intervals"
                                                :key="interval"
                                                :value="interval"
                                            >
                                                {{ interval }}
                                            </option>
                                        </select>
                                        <p
                                            v-if="form.errors.interval"
                                            class="text-sm text-red-600"
                                        >
                                            {{ form.errors.interval }}
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="interval_count">
                                            Interval Count
                                        </Label>
                                        <Input
                                            id="interval_count"
                                            v-model.number="form.interval_count"
                                            type="number"
                                            min="1"
                                            max="365"
                                            required
                                        />
                                        <p
                                            v-if="form.errors.interval_count"
                                            class="text-sm text-red-600"
                                        >
                                            {{ form.errors.interval_count }}
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <Label for="trial_days">Trial Days</Label>
                                    <select
                                        id="trial_days"
                                        v-model.number="form.trial_days"
                                        class="w-full rounded-md border bg-background px-3 py-2 text-sm"
                                    >
                                        <option
                                            v-for="(
                                                label, days
                                            ) in trialOptions"
                                            :key="days"
                                            :value="Number(days)"
                                        >
                                            {{ label }}
                                        </option>
                                    </select>
                                    <p
                                        v-if="form.errors.trial_days"
                                        class="text-sm text-red-600"
                                    >
                                        {{ form.errors.trial_days }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Settings -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Settings</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_active"
                                        v-model:checked="form.is_active"
                                    />
                                    <Label for="is_active">Active</Label>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_public"
                                        v-model:checked="form.is_public"
                                    />
                                    <Label for="is_public">
                                        Public (visible to customers)
                                    </Label>
                                </div>

                                <div class="flex items-center space-x-2">
                                    <Checkbox
                                        id="is_featured"
                                        v-model:checked="form.is_featured"
                                    />
                                    <Label for="is_featured">Featured</Label>
                                </div>

                                <div class="space-y-2">
                                    <Label for="sort_order">Sort Order</Label>
                                    <Input
                                        id="sort_order"
                                        v-model.number="form.sort_order"
                                        type="number"
                                        min="0"
                                    />
                                    <p
                                        v-if="form.errors.sort_order"
                                        class="text-sm text-red-600"
                                    >
                                        {{ form.errors.sort_order }}
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div class="flex gap-4">
                        <Button type="submit" :disabled="form.processing">
                            {{
                                form.processing ? 'Creating...' : 'Create Plan'
                            }}
                        </Button>
                        <Link href="/admin/payments/plans">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>
