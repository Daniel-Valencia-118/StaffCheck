import { useForm } from '@inertiajs/react';
import { useCallback } from 'react';

export function useFormWizard(initialValues) {
    const { data: form, setData, errors, processing, post } = useForm(initialValues);

    const handleChange = useCallback((nameOrEvent, value) => {
        if (nameOrEvent && nameOrEvent.target) {
            const { name, value: targetValue, type, checked } = nameOrEvent.target;
            setData(name, type === 'checkbox' ? checked : targetValue);
        } else if (typeof nameOrEvent === 'string') {
            setData(nameOrEvent, value);
        }
    }, [setData]);

    const handleLocationChange = useCallback((latField = 'latitude', lngField = 'longitude', coords) => {
        setData((prev) => ({
            ...prev,
            [latField]: coords.lat,
            [lngField]: coords.lng,
        }));
    }, [setData]);

    const handleNestedChange = useCallback((section, index, field, valueOrEvent) => {
        const actualValue = (valueOrEvent && typeof valueOrEvent === 'object' && 'target' in valueOrEvent)
            ? valueOrEvent.target.value
            : valueOrEvent;

        setData((prev) => ({
            ...prev,
            [section]: (prev[section] || []).map((item, i) =>
                i === index ? { ...item, [field]: actualValue } : item
            ),
        }));
    }, [setData]);

    const addItem = useCallback((section, newItem) => {
        setData((prev) => ({
            ...prev,
            [section]: [...(prev[section] || []), newItem],
        }));
    }, [setData]);

    const removeItem = useCallback((section, index) => {
        setData((prev) => ({
            ...prev,
            [section]: (prev[section] || []).filter((_, i) => i !== index),
        }));
    }, [setData]);

    return {
        form,
        errors,
        processing,
        post,
        handleChange,
        handleNestedChange,
        handleLocationChange,
        addItem,
        removeItem,
    };
}
