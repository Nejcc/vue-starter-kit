<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Globe, Laptop, Monitor, Smartphone, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { destroy, destroyAll, index } from '@/routes/sessions';
import { type BreadcrumbItem } from '@/types';

type SessionDevice = {
    browser: string;
    platform: string;
    is_desktop: boolean;
    is_mobile: boolean;
};

type Session = {
    id: string;
    ip_address: string;
    is_current: boolean;
    last_active: string;
    last_active_at: string;
    device: SessionDevice;
};

type Props = {
    sessions: Session[];
    currentSessionId: string;
};

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Sessions',
        href: index().url,
    },
];

const revokeSessionId = ref<string | null>(null);
const showRevokeAllDialog = ref(false);

const revokeForm = useForm({
    password: '',
});

const revokeAllForm = useForm({
    password: '',
});

function handleRevokeSession() {
    if (!revokeSessionId.value) return;

    revokeForm.delete(destroy(revokeSessionId.value).url, {
        preserveScroll: true,
        onSuccess: () => {
            revokeSessionId.value = null;
            revokeForm.reset();
        },
        onError: () => {
            revokeForm.reset('password');
        },
    });
}

function handleRevokeAll() {
    revokeAllForm.delete(destroyAll().url, {
        preserveScroll: true,
        onSuccess: () => {
            showRevokeAllDialog.value = false;
            revokeAllForm.reset();
        },
        onError: () => {
            revokeAllForm.reset('password');
        },
    });
}

function getDeviceIcon(device: SessionDevice) {
    if (device.is_mobile) return Smartphone;
    if (device.is_desktop) return Monitor;
    return Laptop;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Sessions" />

        <h1 class="sr-only">Session Management</h1>

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Browser sessions"
                    description="Manage and log out your active sessions on other browsers and devices."
                />

                <p class="text-sm text-muted-foreground">
                    If necessary, you may log out of all of your other browser
                    sessions across all of your devices. Your current session
                    will not be affected.
                </p>

                <div class="space-y-4">
                    <div
                        v-for="session in sessions"
                        :key="session.id"
                        class="flex items-center gap-4 rounded-lg border p-4"
                        :class="{
                            'border-primary/30 bg-primary/5':
                                session.is_current,
                        }"
                    >
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-muted"
                        >
                            <component
                                :is="getDeviceIcon(session.device)"
                                class="h-5 w-5 text-muted-foreground"
                            />
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium">
                                    {{
                                        session.device.browser +
                                        ' on ' +
                                        session.device.platform
                                    }}
                                </p>
                                <Badge
                                    v-if="session.is_current"
                                    variant="default"
                                    class="text-xs"
                                >
                                    This device
                                </Badge>
                            </div>
                            <div
                                class="flex items-center gap-2 text-xs text-muted-foreground"
                            >
                                <Globe class="h-3 w-3" />
                                <span>{{ session.ip_address }}</span>
                                <span>&middot;</span>
                                <span>{{ session.last_active }}</span>
                            </div>
                        </div>

                        <div v-if="!session.is_current" class="shrink-0">
                            <Dialog
                                :open="revokeSessionId === session.id"
                                @update:open="
                                    (open) => {
                                        if (!open) {
                                            revokeSessionId = null;
                                            revokeForm.reset();
                                        }
                                    }
                                "
                            >
                                <DialogTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="revokeSessionId = session.id"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle
                                            >Revoke Session</DialogTitle
                                        >
                                        <DialogDescription>
                                            Please enter your password to
                                            confirm you would like to log out of
                                            this session.
                                        </DialogDescription>
                                    </DialogHeader>

                                    <form @submit.prevent="handleRevokeSession">
                                        <div class="grid gap-2 py-4">
                                            <Label for="revoke-password"
                                                >Password</Label
                                            >
                                            <Input
                                                id="revoke-password"
                                                v-model="revokeForm.password"
                                                type="password"
                                                placeholder="Enter your password"
                                                autocomplete="current-password"
                                            />
                                            <InputError
                                                :message="
                                                    revokeForm.errors.password
                                                "
                                            />
                                        </div>

                                        <DialogFooter>
                                            <DialogClose as-child>
                                                <Button variant="outline">
                                                    Cancel
                                                </Button>
                                            </DialogClose>
                                            <Button
                                                type="submit"
                                                variant="destructive"
                                                :disabled="
                                                    revokeForm.processing
                                                "
                                            >
                                                Revoke Session
                                            </Button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </div>

                    <div
                        v-if="sessions.length === 0"
                        class="rounded-lg border border-dashed p-8 text-center"
                    >
                        <Monitor
                            class="mx-auto h-8 w-8 text-muted-foreground"
                        />
                        <p class="mt-2 text-sm text-muted-foreground">
                            No active sessions found.
                        </p>
                    </div>
                </div>

                <div v-if="sessions.length > 1" class="border-t pt-6">
                    <Dialog
                        :open="showRevokeAllDialog"
                        @update:open="
                            (open) => {
                                showRevokeAllDialog = open;
                                if (!open) revokeAllForm.reset();
                            }
                        "
                    >
                        <DialogTrigger as-child>
                            <Button variant="destructive">
                                Log out other browser sessions
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle
                                    >Log Out Other Sessions</DialogTitle
                                >
                                <DialogDescription>
                                    Please enter your password to confirm you
                                    would like to log out of all your other
                                    browser sessions.
                                </DialogDescription>
                            </DialogHeader>

                            <form @submit.prevent="handleRevokeAll">
                                <div class="grid gap-2 py-4">
                                    <Label for="revoke-all-password"
                                        >Password</Label
                                    >
                                    <Input
                                        id="revoke-all-password"
                                        v-model="revokeAllForm.password"
                                        type="password"
                                        placeholder="Enter your password"
                                        autocomplete="current-password"
                                    />
                                    <InputError
                                        :message="revokeAllForm.errors.password"
                                    />
                                </div>

                                <DialogFooter>
                                    <DialogClose as-child>
                                        <Button variant="outline">
                                            Cancel
                                        </Button>
                                    </DialogClose>
                                    <Button
                                        type="submit"
                                        variant="destructive"
                                        :disabled="revokeAllForm.processing"
                                    >
                                        Log Out Other Sessions
                                    </Button>
                                </DialogFooter>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
