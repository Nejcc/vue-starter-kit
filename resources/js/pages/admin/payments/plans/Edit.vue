<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, RefreshCw, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

import Heading from '@/components/Heading.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Plan {
    id: number;
    uuid: string;
    name: string;
    slug: string;
    description: string | null;
    amount: number;
    formatted_price: string;
    currency: string;
    interval: string;
    interval_count: number;
    trial_days: number | null;
    features: string[];
    limits: Record<string, any>;
    is_active: boolean;
    is_public: boolean;
    is_featured: boolean;
    sort_order: number;
    stripe_price_id: string | null;
    stripe_product_id: string | null;
    paypal_plan_id: string | null;
    metadata: Record<string, any> | null;
}

interface Props {
    plan: Plan;
    intervals: string[];
    trialOptions: Record<number, string>;
}

const props = defineProps<Props>();

const showDeleteDialog = ref(false);

const form = useForm({
    name: props.plan.name,
    slug: props.plan.slug,
    description: props.plan.description || '',
    amount: props.plan.amount,
    currency: props.plan.currency,
    interval: props.plan.interval,
    interval_count: props.plan.interval_count,
    trial_days: props.plan.trial_days || 0,
    features: props.plan.features || [],
    limits: props.plan.limits || {},
    is_active: props.plan.is_active,
    is_public: props.plan.is_public,
    is_featured: props.plan.is_featured,
    sort_order: props.plan.sort_order,
});

const submit = (): void => {
    form.put(`/admin/payments/plans/${props.plan.id}`);
};

const deletePlan = (): void => {
    router.delete(`/admin/payments/plans/${props.plan.id}`);
};

const syncToStripe = (): void => {
    router.post(`/admin/payments/plans/${props.plan.id}/sync`, {
        driver: 'stripe',
    });
};

const syncToPayPal = (): void => {
    router.post(`/admin/payments/plans/${props.plan.id}/sync`, {
        driver: 'paypal',
    });
};

const formatPrice = (cents: number): string => {
    return (cents / 100).toFixed(2);
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Payments', href: '/admin/payments' },
    { title: 'Plans', href: '/admin/payments/plans' },
    { title: props.plan.name, href: '#' },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Edit Plan: ${plan.name}`" />

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

                <div class="flex items-center justify-between">
                    <Heading
                        :title="`Edit: ${plan.name}`"
                        :description="plan.formatted_price"
                        variant="small"
                    />
                    <div class="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="syncToStripe"
                        >
                            <RefreshCw class="mr-2 h-4 w-4" />
                            Sync to Stripe
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            @click="syncToPayPal"
                        >
                            <RefreshCw class="mr-2 h-4 w-4" />
                            Sync to PayPal
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="showDeleteDialog = true"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                        </Button>
                    </div>
                </div>

                <!-- Provider IDs -->
                <div
                    v-if="plan.stripe_price_id || plan.paypal_plan_id"
                    class="flex flex-wrap gap-4 rounded-lg border p-4"
                >
                    <div v-if="plan.stripe_price_id">
                        <p class="text-xs text-muted-foreground">
                            Stripe Price ID
                        </p>
                        <p class="font-mono text-sm">{{ plan.stripe_price_id }}</p>
                    </div>
                    <div v-if="plan.stripe_product_id">
                        <p class="text-xs text-muted-foreground">
                            Stripe Product ID
                        </p>
                        <p class="font-mono text-sm">
                            {{ plan.stripe_product_id }}
                        </p>
                    </div>
                    <div v-if="plan.paypal_plan_id">
                        <p class="text-xs text-muted-foreground">
                            PayPal Plan ID
                        </p>
                        <p class="font-mono text-sm">{{ plan.paypal_plan_id }}</p>
                    </div>
                </div>

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
                                    <Label for="slug">Slug</Label>
                                    <Input
                                        id="slug"
                                        v-model="form.slug"
                                        type="text"
                                        placeholder="pro-plan"
                                    />
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
                                        <Label for="amount">Amount (cents)</Label>
                                        <Input
                                            id="amount"
                                            v-model.number="form.amount"
                                            type="number"
                                            min="0"
                                            required
                                        />
                                        <p class="text-xs text-muted-foreground">
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
                                            v-for="(label, days) in trialOptions"
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

                        <!-- Metadata -->
                        <Card v-if="plan.metadata">
                            <CardHeader>
                                <CardTitle>Metadata</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <pre class="text-xs bg-muted p-3 rounded overflow-auto">{{ JSON.stringify(plan.metadata, null, 2) }}</pre>
                            </CardContent>
                        </Card>
                    </div>

                    <div class="flex gap-4">
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
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

        <!-- Delete Dialog -->
        <AlertDialog v-model:open="showDeleteDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Delete Plan</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to delete this plan? This action
                        cannot be undone.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-red-600 hover:bg-red-700"
                        @click="deletePlan"
                    >
                        Delete Plan
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AdminLayout>
</template>
