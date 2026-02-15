<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Download, Info, ShieldCheck } from 'lucide-vue-next';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { download } from '@/routes/data-export';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Data Export',
        href: '/settings/data-export',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Data Export" />

        <h1 class="sr-only">Data Export</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Export your data"
                    description="Download a copy of all your personal data in JSON format, as required by GDPR Article 20 (Right to Data Portability)."
                />

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <Info class="h-4 w-4" />
                            What's included
                        </CardTitle>
                        <CardDescription>
                            Your export will contain the following data:
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ul
                            class="list-inside list-disc space-y-1 text-sm text-muted-foreground"
                        >
                            <li>
                                Profile information (name, email, preferences)
                            </li>
                            <li>Roles and permissions</li>
                            <li>Notifications</li>
                            <li>Activity and audit logs</li>
                            <li>Active sessions</li>
                            <li>
                                Payment and subscription data (if applicable)
                            </li>
                            <li>Organization memberships (if applicable)</li>
                            <li>Newsletter subscriptions (if applicable)</li>
                        </ul>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base">
                            <ShieldCheck class="h-4 w-4" />
                            Privacy
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">
                            Sensitive data such as passwords, authentication
                            tokens, and two-factor secrets are never included in
                            your export. Session payloads are also excluded for
                            security reasons.
                        </p>
                    </CardContent>
                </Card>

                <div>
                    <Button as-child>
                        <a :href="download().url">
                            <Download class="h-4 w-4" />
                            Download my data
                        </a>
                    </Button>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
