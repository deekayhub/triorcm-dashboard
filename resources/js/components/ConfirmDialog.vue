<script setup lang="ts">
import { confirmState, resolveConfirm } from '@/composables/useConfirm';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

const { isOpen, options } = confirmState;
</script>

<template>
    <Dialog :open="isOpen" @update:open="(open) => { if (!open) resolveConfirm(false); }">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ options.title }}</DialogTitle>
                <DialogDescription>{{ options.description }}</DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <Button variant="outline" @click="resolveConfirm(false)">
                    {{ options.cancelText }}
                </Button>
                <Button
                    :variant="options.variant === 'destructive' ? 'destructive' : 'default'"
                    @click="resolveConfirm(true)"
                >
                    {{ options.confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
