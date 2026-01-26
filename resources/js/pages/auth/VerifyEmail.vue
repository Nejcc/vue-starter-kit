<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { AlertTriangle } from 'lucide-vue-next';
import TextLink from '@/components/TextLink.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import type { VerifyEmailPageProps } from '@/types';

interface Props extends VerifyEmailPageProps {
    emailConfigured?: boolean;
}

withDefaults(defineProps<Props>(), {
    emailConfigured: true,
});
</script>

<template>
    <AuthLayout
        title="Verify email"
        description="Please verify your email address by clicking on the link we just emailed to you."
    >
        <Head title="Email verification" />

        <Alert v-if="!emailConfigured" variant="destructive" class="mb-4">
            <AlertTriangle class="h-4 w-4" />
            <AlertTitle>Email Not Configured</AlertTitle>
            <AlertDescription>
                Email functionality is not properly configured. Please contact
                an administrator to verify your email address manually.
            </AlertDescription>
        </Alert>

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-4 text-center text-sm font-medium text-green-600 dark:text-green-500"
        >
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <Button
                :disabled="processing || !emailConfigured"
                variant="secondary"
            >
                <Spinner v-if="processing" />
                Resend verification email
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm"
                prefetch
            >
                Log out
            </TextLink>
        </Form>
    </AuthLayout>
</template>
