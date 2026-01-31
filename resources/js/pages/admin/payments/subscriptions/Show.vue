<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Pause, Play, XCircle } from 'lucide-vue-next';
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
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { type BreadcrumbItem } from '@/types';

interface Plan {
    id: number;
    name: string;
    slug: string;
    amount: number;
    formatted_price: string;
}

interface User {
    id: number;
    name: string;
    email: string;
}

interface Customer {
    id: number;
    email: string;
    name: string | null;
}

interface Subscription {
    id: number;
    uuid: string;
    provider_id: string | null;
    provider_plan_id: string | null;
    amount: number;
    formatted_amount: string;
    currency: string;
    status: string;
    driver: string;
    interval: string;
    interval_count: number;
    quantity: number;
    billing_description: string;
    current_period_start: string | null;
    current_period_end: string | null;
    trial_start: string | null;
    trial_end: string | null;
    canceled_at: string | null;
    ended_at: string | null;
    cancel_at_period_end: boolean;
    paused_at: string | null;
    resume_at: string | null;
    on_trial: boolean;
    on_grace_period: boolean;
    is_active: boolean;
    is_canceled: boolean;
    is_paused: boolean;
    days_remaining: number | null;
    metadata: Record<string, any> | null;
    provider_response: Record<string, any> | null;
    plan: Plan | null;
    user: User | null;
    customer: Customer | null;
    can_cancel: boolean;
    can_resume: boolean;
    created_at: string;
    updated_at: string;
}

interface Props {
    subscription: Subscription;
}

const props = defineProps<Props>();

const showCancelDialog = ref(false);
const cancelImmediately = ref(false);

const cancelForm = useForm({
    immediately: false,
});

const handleCancel = (): void => {
    cancelForm.immediately = cancelImmediately.value;
    cancelForm.post(
        `/admin/payments/subscriptions/${props.subscription.id}/cancel`,
        {
            onSuccess: () => {
                showCancelDialog.value = false;
            },
        },
    );
};

const handleResume = (): void => {
    router.post(
        `/admin/payments/subscriptions/${props.subscription.id}/resume`,
    );
};

const getStatusColor = (status: string): string => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400';
        case 'trialing':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400';
        case 'canceled':
            return 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400';
        case 'paused':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400';
        case 'past_due':
            return 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400';
    }
};

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Payments', href: '/admin/payments' },
    { title: 'Subscriptions', href: '/admin/payments/subscriptions' },
    { title: `#${props.subscription.id}`, href: '#' },
];
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Subscription #${subscription.id}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col space-y-6">
                <div class="flex items-center gap-4">
                    <Link
                        href="/admin/payments/subscriptions"
                        class="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back
                    </Link>
                </div>

                <div class="flex items-center justify-between">
                    <Heading
                        :title="
                            subscription.plan?.name || 'Custom Subscription'
                        "
                        :description="subscription.billing_description"
                        variant="small"
                    />
                    <div class="flex items-center gap-2">
                        <Badge :class="getStatusColor(subscription.status)">
                            {{ subscription.status }}
                        </Badge>
                        <Badge
                            v-if="subscription.on_trial"
                            class="bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                        >
                            Trial
                        </Badge>
                        <Badge
                            v-if="subscription.on_grace_period"
                            class="bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400"
                        >
                            Grace Period
                        </Badge>
                        <Badge variant="outline">{{
                            subscription.driver
                        }}</Badge>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <Button
                        v-if="subscription.can_cancel"
                        variant="destructive"
                        @click="showCancelDialog = true"
                    >
                        <XCircle class="mr-2 h-4 w-4" />
                        Cancel Subscription
                    </Button>
                    <Button
                        v-if="subscription.can_resume"
                        variant="default"
                        @click="handleResume"
                    >
                        <Play class="mr-2 h-4 w-4" />
                        Resume Subscription
                    </Button>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <!-- Subscription Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Subscription Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Amount
                                    </p>
                                    <p class="font-medium">
                                        {{ subscription.formatted_amount }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Currency
                                    </p>
                                    <p class="font-medium">
                                        {{ subscription.currency }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Interval
                                    </p>
                                    <p class="font-medium">
                                        {{ subscription.interval_count }}
                                        {{ subscription.interval }}(s)
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Quantity
                                    </p>
                                    <p class="font-medium">
                                        {{ subscription.quantity }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Status
                                    </p>
                                    <Badge
                                        :class="
                                            getStatusColor(subscription.status)
                                        "
                                    >
                                        {{ subscription.status }}
                                    </Badge>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">
                                        Driver
                                    </p>
                                    <p class="font-medium">
                                        {{ subscription.driver }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="subscription.provider_id">
                                <p class="text-sm text-muted-foreground">
                                    Provider ID
                                </p>
                                <p class="font-mono text-sm">
                                    {{ subscription.provider_id }}
                                </p>
                            </div>
                            <div
                                v-if="subscription.days_remaining !== null"
                                class="rounded-lg bg-muted p-3"
                            >
                                <p class="text-sm">
                                    <span class="font-medium">
                                        {{ subscription.days_remaining }}
                                    </span>
                                    days remaining in current period
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Plan Details -->
                    <Card v-if="subscription.plan">
                        <CardHeader>
                            <CardTitle>Plan</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Name
                                </p>
                                <p class="font-medium">
                                    {{ subscription.plan.name }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Price
                                </p>
                                <p class="font-medium">
                                    {{ subscription.plan.formatted_price }}
                                </p>
                            </div>
                            <Link
                                :href="`/admin/payments/plans/${subscription.plan.id}/edit`"
                                class="text-sm text-primary hover:underline"
                            >
                                View Plan
                            </Link>
                        </CardContent>
                    </Card>

                    <!-- Billing Period -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Billing Period</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div v-if="subscription.current_period_start">
                                    <p class="text-sm text-muted-foreground">
                                        Period Start
                                    </p>
                                    <p class="text-sm">
                                        {{
                                            new Date(
                                                subscription.current_period_start,
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>
                                <div v-if="subscription.current_period_end">
                                    <p class="text-sm text-muted-foreground">
                                        Period End
                                    </p>
                                    <p class="text-sm">
                                        {{
                                            new Date(
                                                subscription.current_period_end,
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>
                                <div v-if="subscription.trial_start">
                                    <p class="text-sm text-muted-foreground">
                                        Trial Start
                                    </p>
                                    <p class="text-sm">
                                        {{
                                            new Date(
                                                subscription.trial_start,
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>
                                <div v-if="subscription.trial_end">
                                    <p class="text-sm text-muted-foreground">
                                        Trial End
                                    </p>
                                    <p class="text-sm">
                                        {{
                                            new Date(
                                                subscription.trial_end,
                                            ).toLocaleDateString()
                                        }}
                                    </p>
                                </div>
                            </div>
                            <div
                                v-if="subscription.cancel_at_period_end"
                                class="rounded-lg bg-yellow-50 p-3 dark:bg-yellow-900/20"
                            >
                                <p
                                    class="text-sm text-yellow-800 dark:text-yellow-400"
                                >
                                    Will be canceled at period end
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Customer/User -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Customer</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div v-if="subscription.user">
                                <p class="text-sm text-muted-foreground">
                                    User
                                </p>
                                <p class="font-medium">
                                    {{ subscription.user.name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    {{ subscription.user.email }}
                                </p>
                            </div>
                            <div v-if="subscription.customer">
                                <p class="text-sm text-muted-foreground">
                                    Payment Customer
                                </p>
                                <p class="font-medium">
                                    {{
                                        subscription.customer.name ||
                                        subscription.customer.email
                                    }}
                                </p>
                                <Link
                                    :href="`/admin/payments/customers/${subscription.customer.id}`"
                                    class="text-sm text-primary hover:underline"
                                >
                                    View Customer
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Cancellation Details -->
                    <Card v-if="subscription.canceled_at">
                        <CardHeader>
                            <CardTitle class="text-red-600">
                                Cancellation
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <p class="text-sm text-muted-foreground">
                                    Canceled At
                                </p>
                                <p class="text-sm">
                                    {{
                                        new Date(
                                            subscription.canceled_at,
                                        ).toLocaleString()
                                    }}
                                </p>
                            </div>
                            <div v-if="subscription.ended_at">
                                <p class="text-sm text-muted-foreground">
                                    Ended At
                                </p>
                                <p class="text-sm">
                                    {{
                                        new Date(
                                            subscription.ended_at,
                                        ).toLocaleString()
                                    }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Metadata -->
                    <Card v-if="subscription.metadata">
                        <CardHeader>
                            <CardTitle>Metadata</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <pre
                                class="overflow-auto rounded bg-muted p-3 text-xs"
                                >{{
                                    JSON.stringify(
                                        subscription.metadata,
                                        null,
                                        2,
                                    )
                                }}</pre
                            >
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- Cancel Dialog -->
        <AlertDialog v-model:open="showCancelDialog">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Cancel Subscription</AlertDialogTitle>
                    <AlertDialogDescription>
                        Are you sure you want to cancel this subscription?
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <div class="flex items-center space-x-2 py-4">
                    <Checkbox
                        id="immediately"
                        v-model:checked="cancelImmediately"
                    />
                    <Label for="immediately">
                        Cancel immediately (don't wait for period end)
                    </Label>
                </div>
                <AlertDialogFooter>
                    <AlertDialogCancel>Keep Subscription</AlertDialogCancel>
                    <AlertDialogAction
                        class="bg-red-600 hover:bg-red-700"
                        @click="handleCancel"
                    >
                        Cancel Subscription
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AdminLayout>
</template>
