<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { KeyRound } from 'lucide-vue-next';
import AuthFormCard from '@/components/AuthFormCard.vue';
import FormField from '@/components/form/FormField.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { email } from '@/routes/password';
import type { ForgotPasswordPageProps } from '@/types';

defineProps<ForgotPasswordPageProps>();
</script>

<template>
    <AuthLayout>
        <Head title="Forgot password" />

        <AuthFormCard
            :icon="KeyRound"
            title="Forgot password"
            description="Enter your email to receive a password reset link"
        >
            <div
                v-if="status"
                class="mb-4 text-center text-sm font-medium text-green-600 dark:text-green-500"
            >
                {{ status }}
            </div>

            <Form
                v-bind="email.form()"
                v-slot="{ errors, processing }"
                class="space-y-6"
            >
                <FormField
                    id="email"
                    label="Email address"
                    type="email"
                    name="email"
                    autocomplete="off"
                    autofocus
                    placeholder="email@example.com"
                    :error="errors.email"
                    class="grid gap-2"
                />

                <Button
                    class="w-full"
                    :disabled="processing"
                    data-test="email-password-reset-link-button"
                >
                    <Spinner v-if="processing" />
                    Email password reset link
                </Button>
            </Form>

            <template #footer>
                <span>Or, return to </span>
                <TextLink :href="login()" prefetch>log in</TextLink>
            </template>
        </AuthFormCard>
    </AuthLayout>
</template>
