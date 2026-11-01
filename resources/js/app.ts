import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const pages = import.meta.glob('./pages/**/*.vue');

const resolveComponent = (name: string): Promise<unknown> => {
    const page = Object.entries(pages).find(
        ([path]) => path.endsWith(`/${name}.vue`) || path.endsWith(`/${name}/index.vue`),
    );
    return page ? page[1]() : Promise.resolve(null);
};

const inertiaOptions: any = {
    title: (title: string) => (title ? `${title} - ${appName}` : appName),
    layout: (name: string) => {
        switch (true) {
            case name === 'Welcome':
                return null;
            case name.startsWith('auth/'):
                return AuthLayout;
            case name.startsWith('settings/'):
                return [AppLayout, SettingsLayout];
            default:
                return AppLayout;
        }
    },
    resolve: resolveComponent,
    withApp: (app: any) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus: any) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#4B5563',
    },
};

void createInertiaApp(inertiaOptions);

// This will set light / dark mode on page load...
initializeTheme();

// This will listen for flash toast data from the server...
initializeFlashToast();
