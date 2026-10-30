import { createApp, h, ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';

interface ConfirmOptions {
    title?: string;
    description?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: 'default' | 'destructive';
}

let resolvePromise: ((value: boolean) => void) | null = null;

const isOpen = ref(false);
const options = ref<ConfirmOptions>({});

export function useConfirm() {
    const confirm = (opts: ConfirmOptions = {}): Promise<boolean> => {
        options.value = {
            title: opts.title || 'Are you sure?',
            description: opts.description || 'This action cannot be undone.',
            confirmText: opts.confirmText || 'Confirm',
            cancelText: opts.cancelText || 'Cancel',
            variant: opts.variant || 'default',
        };
        isOpen.value = true;

        return new Promise((resolve) => {
            resolvePromise = resolve;
        });
    };

    return { confirm, isOpen, options, resolvePromise };
}

export const confirmState = { isOpen, options };
export const resolveConfirm = (value: boolean) => {
    isOpen.value = false;
    if (resolvePromise) {
        resolvePromise(value);
        resolvePromise = null;
    }
};
