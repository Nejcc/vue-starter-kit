<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowLeft, RotateCcw, Trash2 } from 'lucide-vue-next';

import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useDateFormat } from '@/composables/useDateFormat';
import AdminLayout from '@/layouts/admin/AdminLayout.vue';
import { index as failedJobsIndex } from '@/routes/admin/failed-jobs';
import { type BreadcrumbItem } from '@/types';

interface FailedJob {
    id: number;
    uuid: string;
    connection: string;
    queue: string;
    job_name: string;
    payload: Record<string, unknown>;
    exception: string;
    failed_at: string;
}

interface Props {
    job: FailedJob;
}

const props = defineProps<Props>();
const { formatShortDate } = useDateFormat();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Failed Jobs', href: failedJobsIndex().url },
    { title: props.job.job_name, href: '#' },
];

function retryJob(): void {
    router.post(
        `/admin/failed-jobs/${props.job.uuid}/retry`,
        {},
        { preserveScroll: true },
    );
}

function deleteJob(): void {
    if (!confirm('Are you sure you want to delete this failed job?')) return;
    router.delete(`/admin/failed-jobs/${props.job.id}`, {
        onSuccess: () => {
            router.visit(failedJobsIndex().url);
        },
    });
}
</script>

<template>
    <AdminLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Failed Job: ${job.job_name}`" />

        <div class="container mx-auto py-8">
            <div class="flex flex-col gap-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button
                            class="text-muted-foreground hover:text-foreground"
                            @click="router.visit(failedJobsIndex().url)"
                        >
                            <ArrowLeft class="h-5 w-5" />
                        </button>
                        <Heading
                            :title="job.job_name"
                            description="Failed job details"
                            variant="small"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" @click="retryJob">
                            <RotateCcw class="mr-2 h-4 w-4" />
                            Retry
                        </Button>
                        <Button
                            variant="destructive"
                            size="sm"
                            @click="deleteJob"
                        >
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                        </Button>
                    </div>
                </div>

                <!-- Job Info -->
                <div class="rounded-lg border">
                    <div class="border-b px-6 py-4">
                        <h3 class="font-semibold">Job Information</h3>
                    </div>
                    <div class="grid gap-4 p-6 md:grid-cols-2">
                        <div>
                            <p class="text-xs text-muted-foreground">UUID</p>
                            <p class="font-mono text-sm">{{ job.uuid }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Connection
                            </p>
                            <p class="text-sm">{{ job.connection }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">Queue</p>
                            <Badge variant="outline">{{ job.queue }}</Badge>
                        </div>
                        <div>
                            <p class="text-xs text-muted-foreground">
                                Failed At
                            </p>
                            <p class="text-sm">
                                {{ formatShortDate(job.failed_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Exception -->
                <div class="rounded-lg border">
                    <div class="border-b px-6 py-4">
                        <h3 class="font-semibold text-destructive">
                            Exception
                        </h3>
                    </div>
                    <div class="p-6">
                        <pre
                            class="max-h-96 overflow-auto rounded-lg bg-muted p-4 font-mono text-xs"
                            >{{ job.exception }}</pre
                        >
                    </div>
                </div>

                <!-- Payload -->
                <div class="rounded-lg border">
                    <div class="border-b px-6 py-4">
                        <h3 class="font-semibold">Payload</h3>
                    </div>
                    <div class="p-6">
                        <pre
                            class="max-h-96 overflow-auto rounded-lg bg-muted p-4 font-mono text-xs"
                            >{{ JSON.stringify(job.payload, null, 2) }}</pre
                        >
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
