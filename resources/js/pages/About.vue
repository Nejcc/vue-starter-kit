<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
</script>

<template>
    <Head title="About" />

    <PublicLayout :centered="true">
        <main
            class="mx-auto w-full max-w-sm space-y-6 px-4 py-6 sm:max-w-2xl sm:space-y-8 sm:px-6 sm:py-8 md:max-w-4xl lg:max-w-6xl"
        >
            <div>
                <h1
                    class="text-3xl font-bold text-[#1b1b18] dark:text-[#EDEDEC]"
                >
                    About Us
                </h1>
                <p class="mt-2 text-[#706f6c] dark:text-[#A1A09A]">
                    Learn more about our platform and what we do
                </p>
            </div>

            <div
                class="prose prose-slate dark:prose-invert max-w-none space-y-6 text-[#1b1b18] dark:text-[#EDEDEC]"
            >
                <section>
                    <h2
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        Our Mission
                    </h2>
                    <p>
                        This is a modern Laravel starter kit built with
                        cutting-edge technologies to help developers quickly
                        bootstrap their web applications. Our mission is to
                        provide a solid foundation that follows best practices
                        and includes essential features out of the box.
                    </p>
                </section>

                <section>
                    <h2
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        What We Offer
                    </h2>
                    <p>Our platform includes:</p>
                    <ul>
                        <li>
                            <strong>Modern Tech Stack:</strong> Built with
                            Laravel 12, Vue 3, Inertia.js 2, and Tailwind CSS 4
                        </li>
                        <li>
                            <strong>Complete Authentication:</strong>
                            Registration, login, two-factor authentication,
                            email verification, and password reset
                        </li>
                        <li>
                            <strong>Role-Based Access Control:</strong>
                            Comprehensive roles and permissions system using
                            Spatie Laravel Permission
                        </li>
                        <li>
                            <strong>Admin Panel:</strong> Full-featured admin
                            panel with user management, settings, and database
                            browser
                        </li>
                        <li>
                            <strong>GDPR Compliance:</strong> Cookie consent
                            management and privacy controls
                        </li>
                        <li>
                            <strong>Clean Architecture:</strong> Repository,
                            Service, and Action patterns for maintainable code
                        </li>
                    </ul>
                </section>

                <section>
                    <h2
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        Built With Best Practices
                    </h2>
                    <p>
                        This starter kit follows industry best practices
                        including:
                    </p>
                    <ul>
                        <li>Strict PHP typing with explicit return types</li>
                        <li>
                            Comprehensive test coverage (feature and unit tests)
                        </li>
                        <li>Code formatting with Laravel Pint</li>
                        <li>
                            Type-safe route generation with Laravel Wayfinder
                        </li>
                        <li>
                            Security-first approach with rate limiting and CSRF
                            protection
                        </li>
                        <li>
                            Performance optimization with caching and eager
                            loading
                        </li>
                    </ul>
                </section>

                <section>
                    <h2
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        Technology Stack
                    </h2>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <h3
                                class="text-lg font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                            >
                                Backend
                            </h3>
                            <ul class="mt-2">
                                <li>Laravel 12</li>
                                <li>PHP 8.4+</li>
                                <li>Laravel Fortify</li>
                                <li>Spatie Permission</li>
                                <li>Laravel Wayfinder</li>
                            </ul>
                        </div>
                        <div>
                            <h3
                                class="text-lg font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                            >
                                Frontend
                            </h3>
                            <ul class="mt-2">
                                <li>Vue 3</li>
                                <li>TypeScript</li>
                                <li>Inertia.js 2</li>
                                <li>Tailwind CSS 4</li>
                                <li>shadcn-vue</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section>
                    <h2
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        Key Features
                    </h2>
                    <div class="space-y-3">
                        <div>
                            <h3
                                class="text-lg font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                            >
                                User Management
                            </h3>
                            <p>
                                Complete user management with profile settings,
                                password changes, two-factor authentication, and
                                account deletion.
                            </p>
                        </div>
                        <div>
                            <h3
                                class="text-lg font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                            >
                                Admin Panel
                            </h3>
                            <p>
                                Powerful admin interface for managing users,
                                roles, permissions, settings, and viewing
                                database tables.
                            </p>
                        </div>
                        <div>
                            <h3
                                class="text-lg font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                            >
                                Security Features
                            </h3>
                            <p>
                                Built-in security with rate limiting, CSRF
                                protection, two-factor authentication, and
                                comprehensive audit logging.
                            </p>
                        </div>
                        <div>
                            <h3
                                class="text-lg font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                            >
                                Dark Mode
                            </h3>
                            <p>
                                Full dark mode support with light, dark, and
                                system theme options that persist across
                                sessions.
                            </p>
                        </div>
                    </div>
                </section>

                <section>
                    <h2
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        Get Started
                    </h2>
                    <p>
                        Ready to explore?
                        <Link
                            v-if="!$page.props.auth?.user"
                            href="/register"
                            class="text-primary hover:underline"
                            prefetch
                        >
                            Create an account
                        </Link>
                        <Link
                            v-else
                            href="/dashboard"
                            class="text-primary hover:underline"
                            prefetch
                        >
                            Go to your dashboard
                        </Link>
                        to get started with our platform.
                    </p>
                    <p class="mt-4">
                        Have questions? Check out our{' '}
                        <Link
                            href="/privacy-policy"
                            class="text-primary hover:underline"
                            prefetch
                        >
                            Privacy Policy
                        </Link>
                        {' '}or{' '}
                        <Link
                            href="/cookie-policy"
                            class="text-primary hover:underline"
                            prefetch
                        >
                            Cookie Policy
                        </Link>
                        {' '}for more information.
                    </p>
                </section>
            </div>
        </main>
    </PublicLayout>
</template>
