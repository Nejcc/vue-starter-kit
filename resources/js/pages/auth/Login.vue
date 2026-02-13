<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { LogIn } from 'lucide-vue-next';
import AuthFormCard from '@/components/AuthFormCard.vue';
import FormField from '@/components/form/FormField.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import type { LoginPageProps } from '@/types';

defineProps<LoginPageProps>();

const quickLogin = (userId: number): void => {
    router.post(`/quick-login/${userId}`);
};
</script>

<template>
    <AuthBase>
        <Head title="Log in" />

        <AuthFormCard
            :icon="LogIn"
            title="Log in to your account"
            description="Enter your email and password below to log in"
        >
            <div
                v-if="status"
                class="mb-4 text-center text-sm font-medium text-green-600 dark:text-green-500"
            >
                {{ status }}
            </div>

            <div
                v-if="error"
                class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400"
            >
                {{ error }}
            </div>

            <Form
                v-bind="store.form()"
                :reset-on-success="['password']"
                v-slot="{ errors, processing }"
                class="flex flex-col gap-6"
            >
                <div class="grid gap-6">
                    <FormField
                        id="email"
                        label="Email address"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                        :error="errors.email"
                        class="grid gap-2"
                    />

                    <FormField
                        id="password"
                        label="Password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Password"
                        :error="errors.password"
                        class="grid gap-2"
                    >
                        <template #label-end>
                            <TextLink
                                v-if="canResetPassword"
                                :href="request()"
                                class="text-sm"
                                :tabindex="5"
                                prefetch
                            >
                                Forgot password?
                            </TextLink>
                        </template>
                    </FormField>

                    <div class="flex items-center justify-between">
                        <Label
                            for="remember"
                            class="flex items-center space-x-3"
                        >
                            <Checkbox
                                id="remember"
                                name="remember"
                                :tabindex="3"
                            />
                            <span>Remember me</span>
                        </Label>
                    </div>

                    <Button
                        type="submit"
                        class="mt-4 w-full"
                        :tabindex="4"
                        :disabled="processing"
                        data-test="login-button"
                    >
                        <Spinner v-if="processing" />
                        Log in
                    </Button>

                    <div v-if="devQuickLogin" class="flex flex-col gap-2">
                        <p class="text-center text-xs text-muted-foreground">
                            Development Quick Login
                        </p>
                        <div class="grid grid-cols-3 gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                :tabindex="6"
                                @click="quickLogin(1)"
                                data-test="quick-login-super-admin"
                            >
                                Super Admin
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                :tabindex="7"
                                @click="quickLogin(2)"
                                data-test="quick-login-admin"
                            >
                                Admin
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                :tabindex="8"
                                @click="quickLogin(3)"
                                data-test="quick-login-user"
                            >
                                User
                            </Button>
                        </div>
                    </div>
                </div>
            </Form>

            <template #footer>
                <span v-if="canRegister">
                    Don't have an account?
                    <TextLink :href="register()" :tabindex="5" prefetch
                        >Sign up</TextLink
                    >
                </span>
            </template>
        </AuthFormCard>
    </AuthBase>
</template>
