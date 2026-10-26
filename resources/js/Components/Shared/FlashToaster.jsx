import { useEffect } from 'react';
import { usePage } from '@inertiajs/react';
import toast, { Toaster } from 'react-hot-toast';

export default function FlashToaster() {
    const { flash } = usePage().props;

    useEffect(() => {
        if (flash?.success) toast.success(flash.success, { duration: 4000 });
        if (flash?.error) toast.error(flash.error, { duration: 5000 });
    }, [flash]);

    return <Toaster position="top-right" />;
}
