<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { ShieldCheck } from 'lucide-vue-next';
import AuthFormCard from '@/components/AuthFormCard.vue';
import FormField from '@/components/form/FormField.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/password/confirm';
import type { ConfirmPasswordPageProps } from '@/types';

defineProps<ConfirmPasswordPageProps>();
</script>

<template>
    <AuthLayout>
        <Head title="Confirm password" />

        <AuthFormCard
            :icon="ShieldCheck"
            title="Confirm your password"
            description="This is a secure area of the application. Please confirm your password before continuing."
        >
            <Form
                v-bind="store.form()"
                reset-on-success
                v-slot="{ errors, processing }"
            >
                <div class="space-y-6">
                    <FormField
                        id="password"
                        label="Password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        autofocus
                        :error="errors.password"
                        class="grid gap-2"
                    />

                    <Button
                        class="w-full"
                        :disabled="processing"
                        data-test="confirm-password-button"
                    >
                        <Spinner v-if="processing" />
                        Confirm Password
                    </Button>
                </div>
            </Form>
        </AuthFormCard>
    </AuthLayout>
</template>
