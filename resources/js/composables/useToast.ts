export function useToast() {
    const toast = (options: {
        title: string;
        description: string;
        variant?: 'success' | 'error' | 'warning';
    }) => {
        // TODO: Implement toast notification
        console.log('Toast:', options);
    };

    return { toast };
}