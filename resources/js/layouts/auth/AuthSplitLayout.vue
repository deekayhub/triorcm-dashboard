<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="flex min-h-svh items-center justify-center bg-gray-100 p-4 sm:p-6 md:p-10 dark:bg-gray-950"
    >
        <div
            class="w-full max-w-4xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-950 dark:shadow-none"
        >
            <div class="grid min-h-[560px] grid-cols-1 md:grid-cols-2">
                <div
                    class="relative hidden flex-col justify-between border-r border-blue-900/40 bg-[radial-gradient(1200px_600px_at_0%_0%,#2563eb33,transparent),radial-gradient(800px_500px_at_100%_100%,#1e40af40,transparent)] bg-[#0b1026] p-10 md:flex dark:border-gray-800 dark:bg-[radial-gradient(1200px_600px_at_0%_0%,rgba(59,130,246,0.08),transparent),radial-gradient(800px_400px_at_100%_100%,rgba(139,92,246,0.08),transparent)] dark:bg-gray-950"
                >
                    <div class="absolute inset-0">
                        <div
                            class="absolute -top-24 -left-24 h-64 w-64 rounded-full bg-blue-400/20 blur-3xl dark:bg-blue-500/10"
                        />
                        <div
                            class="absolute -bottom-32 -right-10 h-72 w-72 rounded-full bg-indigo-400/15 blur-3xl dark:bg-violet-500/10"
                        />
                    </div>
                    <div class="relative z-10 flex items-center gap-3">
                        <Link
                            :href="home()"
                            class="flex items-center gap-2 font-medium"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg border border-white/10 bg-white/10 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80"
                            >
                                <AppLogoIcon
                                    class="size-6 fill-current text-white dark:text-white"
                                />
                            </div>
                            <span class="text-sm text-blue-100 dark:text-gray-400"
                                >TriORCM</span
                            >
                        </Link>
                    </div>
                    <div class="relative z-10 space-y-4">
                        <h2 class="text-3xl font-semibold tracking-tight text-white dark:text-white">
                            Welcome back
                        </h2>
                        <p class="max-w-sm text-sm text-blue-100/80 dark:text-gray-400">
                            Secure access to your dashboard. Sign in to continue
                            where you left off.
                        </p>
                    </div>
                    <div class="relative z-10 text-xs text-blue-200/70 dark:text-gray-500">
                        © {{ new Date().getFullYear() }} TriORCM
                    </div>
                </div>
                <div
                    class="flex flex-col items-center justify-center bg-white px-6 py-10 sm:px-10 dark:bg-gray-950"
                >
                    <div class="w-full max-w-sm">
                        <div class="mb-8 flex flex-col gap-4">
                            <div class="flex items-center gap-3 md:hidden">
                                <Link
                                    :href="home()"
                                    class="flex items-center gap-2 font-medium"
                                >
                                    <div
                                        class="flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 dark:border-gray-800"
                                    >
                                        <AppLogoIcon
                                            class="size-7 fill-current text-gray-900 dark:text-white"
                                        />
                                    </div>
                                    <span class="sr-only">{{ title }}</span>
                                </Link>
                            </div>
                            <div class="space-y-2 text-center md:text-left">
                                <h1 class="text-xl font-medium text-gray-900 dark:text-white">{{ title }}</h1>
                                <p
                                    class="text-center text-sm text-gray-600 dark:text-gray-400 md:text-left"
                                >
                                    {{ description }}
                                </p>
                            </div>
                        </div>
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
