<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { LockKeyhole } from 'lucide-vue-next';
import { ref } from 'vue';
import AuthFormCard from '@/components/AuthFormCard.vue';
import FormField from '@/components/form/FormField.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { update } from '@/routes/password';

import type { ResetPasswordPageProps } from '@/types';

const props = defineProps<ResetPasswordPageProps>();

const inputEmail = ref(props.email);
</script>

<template>
    <AuthLayout>
        <Head title="Reset password" />

        <AuthFormCard
            :icon="LockKeyhole"
            title="Reset password"
            description="Please enter your new password below"
        >
            <Form
                v-bind="update.form()"
                :transform="(data) => ({ ...data, token, email })"
                :reset-on-success="['password', 'password_confirmation']"
                v-slot="{ errors, processing }"
            >
                <div class="grid gap-6">
                    <FormField
                        id="email"
                        label="Email"
                        type="email"
                        name="email"
                        autocomplete="email"
                        v-model="inputEmail"
                        readonly
                        :error="errors.email"
                        class="grid gap-2"
                    />

                    <FormField
                        id="password"
                        label="Password"
                        type="password"
                        name="password"
                        autocomplete="new-password"
                        autofocus
                        placeholder="Password"
                        :error="errors.password"
                        class="grid gap-2"
                    />

                    <FormField
                        id="password_confirmation"
                        label="Confirm Password"
                        type="password"
                        name="password_confirmation"
                        autocomplete="new-password"
                        placeholder="Confirm password"
                        :error="errors.password_confirmation"
                        class="grid gap-2"
                    />

                    <Button
                        type="submit"
                        class="mt-4 w-full"
                        :disabled="processing"
                        data-test="reset-password-button"
                    >
                        <Spinner v-if="processing" />
                        Reset password
                    </Button>
                </div>
            </Form>
        </AuthFormCard>
    </AuthLayout>
</template>
