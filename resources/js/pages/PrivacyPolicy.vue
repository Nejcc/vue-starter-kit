<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
</script>

<template>
    <Head title="Privacy Policy" />

    <PublicLayout :centered="true">
        <main
            class="mx-auto w-full max-w-sm space-y-6 px-4 py-6 sm:max-w-2xl sm:space-y-8 sm:px-6 sm:py-8 md:max-w-4xl lg:max-w-6xl"
        >
            <div>
                <h1 class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]">Privacy Policy</h1>
                <p class="mt-2 text-[#706f6c] dark:text-[#A1A09A]">
                    Last updated: {{ new Date().toLocaleDateString() }}
                </p>
            </div>

            <div class="prose prose-slate dark:prose-invert max-w-none space-y-6 text-[#1b1b18] dark:text-[#EDEDEC]">
                <section>
                    <h2 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Introduction</h2>
                    <p>
                        This Privacy Policy describes how we collect, use, and
                        protect your personal information when you use our
                        service. We are committed to protecting your privacy and
                        ensuring the security of your personal data.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Information We Collect
                    </h2>
                    <p>We collect information that you provide directly to us:</p>
                    <ul>
                        <li>
                            <strong>Account Information:</strong> Name, email
                            address, and password when you create an account
                        </li>
                        <li>
                            <strong>Profile Information:</strong> Any additional
                            information you choose to provide in your profile
                        </li>
                        <li>
                            <strong>Usage Data:</strong> Information about how
                            you interact with our service
                        </li>
                        <li>
                            <strong>Cookie Preferences:</strong> Your cookie
                            consent preferences and data processing consent
                        </li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                        How We Use Your Information
                    </h2>
                    <p>We use the information we collect to:</p>
                    <ul>
                        <li>Provide, maintain, and improve our services</li>
                        <li>Process your transactions and send related information</li>
                        <li>Send you technical notices and support messages</li>
                        <li>Respond to your comments and questions</li>
                        <li>Monitor and analyze trends and usage</li>
                        <li>Detect, prevent, and address technical issues</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Data Protection
                    </h2>
                    <p>
                        We implement appropriate technical and organizational
                        measures to protect your personal information against
                        unauthorized access, alteration, disclosure, or
                        destruction. However, no method of transmission over the
                        Internet or electronic storage is 100% secure.
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Your Rights</h2>
                    <p>You have the right to:</p>
                    <ul>
                        <li>Access your personal data</li>
                        <li>Correct inaccurate data</li>
                        <li>Request deletion of your data</li>
                        <li>Object to processing of your data</li>
                        <li>Request data portability</li>
                        <li>Withdraw consent at any time</li>
                    </ul>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Cookies</h2>
                    <p>
                        We use cookies and similar tracking technologies to
                        track activity on our service. You can control cookie
                        preferences through your account settings. For more
                        information, please see our{' '}
                        <Link
                            href="/cookie-policy"
                            class="text-primary hover:underline"
                            prefetch
                        >
                            Cookie Policy
                        </Link>
                        .
                    </p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Contact Us</h2>
                    <p>
                        If you have any questions about this Privacy Policy,
                        please contact us through the appropriate channels
                        provided in our application.
                    </p>
                </section>
            </div>
        </main>
    </PublicLayout>
</template>

