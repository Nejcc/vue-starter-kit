<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import type { RegisterPageProps } from '@/types';
import { Form, Head } from '@inertiajs/vue3';

defineProps<RegisterPageProps>();
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account"
    >
        <Head title="Register" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Full name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Password"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirm password"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

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
                    <InputError :message="errors.data_processing_consent" />
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
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="7"
                    prefetch
                    >Log in</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>
