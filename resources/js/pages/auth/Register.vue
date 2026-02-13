<script setup lang="ts">
import { Form, Head, router } from '@inertiajs/vue3';
import { UserPlus } from 'lucide-vue-next';
import AuthFormCard from '@/components/AuthFormCard.vue';
import FormField from '@/components/form/FormField.vue';
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import type { RegisterPageProps } from '@/types';

defineProps<RegisterPageProps>();

const quickRegister = (role: string): void => {
    router.post(`/quick-register/${role}`);
};
</script>

<template>
    <AuthBase>
        <Head title="Register" />

        <AuthFormCard
            :icon="UserPlus"
            title="Create an account"
            description="Enter your details below to create your account"
        >
            <Form
                v-bind="store.form()"
                :reset-on-success="['password', 'password_confirmation']"
                v-slot="{ errors, processing }"
                class="flex flex-col gap-6"
            >
                <div class="grid gap-6">
                    <FormField
                        id="name"
                        label="Name"
                        type="text"
                        name="name"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        placeholder="Full name"
                        :error="errors.name"
                        class="grid gap-2"
                    />

                    <FormField
                        id="email"
                        label="Email address"
                        type="email"
                        name="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        placeholder="email@example.com"
                        :error="errors.email"
                        class="grid gap-2"
                    />

                    <div class="grid gap-2">
                        <FormField
                            id="password"
                            label="Password"
                            type="password"
                            name="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            placeholder="Password"
                            :error="errors.password"
                            class="grid gap-2"
                        />
                        <p class="text-xs text-muted-foreground">
                            Must be at least 12 characters with uppercase,
                            lowercase, numbers, and symbols.
                        </p>
                    </div>

                    <FormField
                        id="password_confirmation"
                        label="Confirm password"
                        type="password"
                        name="password_confirmation"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        placeholder="Confirm password"
                        :error="errors.password_confirmation"
                        class="grid gap-2"
                    />

                    <!-- GDPR Data Processing Consent -->
                    <div class="grid gap-2">
                        <div class="flex items-start space-x-2">
                            <Checkbox
                                id="data_processing_consent"
                                name="data_processing_consent"
                                :tabindex="5"
                                required
                            />
                            <div class="grid gap-1.5 leading-none">
                                <Label
                                    for="data_processing_consent"
                                    class="text-sm leading-none font-medium peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                >
                                    I agree to the processing of my personal data
                                </Label>
                                <p class="text-xs text-muted-foreground">
                                    By creating an account, you agree to our
                                    <a
                                        href="/privacy-policy"
                                        class="text-primary hover:underline"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        Privacy Policy
                                    </a>
                                    and the processing of your personal data in
                                    accordance with GDPR regulations.
                                </p>
                            </div>
                        </div>
                        <InputError
                            :message="errors.data_processing_consent"
                        />
                    </div>

                    <Button
                        type="submit"
                        class="mt-2 w-full"
                        tabindex="6"
                        :disabled="processing"
                        data-test="register-user-button"
                    >
                        <Spinner v-if="processing" />
                        Create account
                    </Button>

                    <div v-if="devQuickLogin" class="flex flex-col gap-2">
                        <p class="text-center text-xs text-muted-foreground">
                            Development Quick Register
                        </p>
                        <div class="grid grid-cols-3 gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                :tabindex="8"
                                data-test="quick-register-super-admin"
                                @click="quickRegister('super-admin')"
                            >
                                Super Admin
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                :tabindex="9"
                                data-test="quick-register-admin"
                                @click="quickRegister('admin')"
                            >
                                Admin
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                :tabindex="10"
                                data-test="quick-register-user"
                                @click="quickRegister('user')"
                            >
                                User
                            </Button>
                        </div>
                    </div>
                </div>
            </Form>

            <template #footer>
                Already have an account?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="7"
                    prefetch
                    >Log in</TextLink
                >
            </template>
        </AuthFormCard>
    </AuthBase>
</template>
