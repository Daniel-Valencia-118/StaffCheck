export const generateId = () => {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }
    return `${Date.now()}-${Math.random().toString(36).substring(2, 9)}`;
};

export const getFieldError = (errors, path) => {
    if (!errors) return undefined;
    if (errors[path]) return errors[path];

    let actual = errors;
    for (const clave of path.split('.')) {
        if (actual == null) return undefined;
        actual = actual[clave];
    }
    return typeof actual === 'string' ? actual : undefined;
};
