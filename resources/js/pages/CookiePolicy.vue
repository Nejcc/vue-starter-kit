<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
</script>

<template>
    <Head title="Cookie Policy" />

    <PublicLayout :centered="true">
        <main
            class="mx-auto w-full max-w-sm space-y-6 px-4 py-6 sm:max-w-2xl sm:space-y-8 sm:px-6 sm:py-8 md:max-w-4xl lg:max-w-6xl"
        >
            <div>
                <h1
                    class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]"
                >
                    Cookie Policy
                </h1>
                <p class="mt-2 text-[#706f6c] dark:text-[#A1A09A]">
                    Last updated: {{ new Date().toLocaleDateString() }}
                </p>
            </div>

            <div
                class="prose prose-slate dark:prose-invert max-w-none space-y-6 text-[#1b1b18] dark:text-[#EDEDEC]"
            >
                <section>
                    <h2
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        What Are Cookies
                    </h2>
                    <p>
                        Cookies are small text files that are placed on your
                        computer or mobile device when you visit a website. They
                        are widely used to make websites work more efficiently
                        and provide information to the website owners.
                    </p>
                </section>

                <section>
                    <h2
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        How We Use Cookies
                    </h2>
                    <p>We use cookies for various purposes, including:</p>
                    <ul>
                        <li>
                            <strong>Essential Cookies:</strong> Required for the
                            website to function properly. These cannot be
                            disabled.
                        </li>
                        <li>
                            <strong>Analytics Cookies:</strong> Help us
                            understand how visitors interact with our website by
                            collecting and reporting information anonymously.
                        </li>
                        <li>
                            <strong>Marketing Cookies:</strong> Used to track
                            visitors across websites to display relevant
                            advertisements.
                        </li>
                        <li>
                            <strong>Preference Cookies:</strong> Remember your
                            preferences and settings for a better experience.
                        </li>
                    </ul>
                </section>

                <section>
                    <h2
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        Managing Cookies
                    </h2>
                    <p>You can control and manage cookies in several ways:</p>
                    <ul>
                        <li>
                            Through your browser settings (most browsers allow
                            you to refuse or delete cookies)
                        </li>
                        <li>
                            Through your account settings in our application
                        </li>
                        <li>
                            By visiting our{' '}
                            <Link
                                href="/settings/cookie-preferences"
                                class="text-primary hover:underline"
                                prefetch
                            >
                                Cookie Preferences </Link
                            >{' '} page
                        </li>
                    </ul>
                    <p class="mt-4">
                        Please note that disabling certain cookies may affect
                        the functionality of our website.
                    </p>
                </section>

                <section>
                    <h2
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        Third-Party Cookies
                    </h2>
                    <p>
                        In addition to our own cookies, we may also use various
                        third-party cookies to report usage statistics and
                        deliver advertisements. These third parties may use
                        cookies to collect information about your online
                        activities across different websites.
                    </p>
                </section>

                <section>
                    <h2
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        Cookie Consent
                    </h2>
                    <p>
                        When you first visit our website, you will be asked to
                        consent to our use of cookies. You can change your
                        preferences at any time through your account settings or
                        by visiting our cookie preferences page.
                    </p>
                </section>

                <section>
                    <h2
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        More Information
                    </h2>
                    <p>
                        For more information about how we handle your personal
                        data, please see our{' '}
                        <Link
                            href="/privacy-policy"
                            class="text-primary hover:underline"
                            prefetch
                        >
                            Privacy Policy
                        </Link>
                        .
                    </p>
                </section>

                <section>
                    <h2
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        Contact Us
                    </h2>
                    <p>
                        If you have any questions about our use of cookies,
                        please contact us through the appropriate channels
                        provided in our application.
                    </p>
                </section>
            </div>
        </main>
    </PublicLayout>
</template>
