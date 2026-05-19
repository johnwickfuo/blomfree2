export function formatNaira(value: number | string | null | undefined): string {
    const parsed = typeof value === 'string' ? parseFloat(value) : (value ?? 0);
    const safe = Number.isFinite(parsed) ? (parsed as number) : 0;

    return new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        maximumFractionDigits: 0,
    }).format(safe);
}

export function formatDate(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '—';
    }

    return date.toLocaleDateString('en-NG', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

// Formats a Nigerian phone number for display as the user types.
export function formatNigerianPhone(input: string): string {
    let digits = input.replace(/\D/g, '');

    if (digits.startsWith('234')) {
        digits = digits.slice(0, 13);
        const local = digits.slice(3);
        const groups = [
            local.slice(0, 3),
            local.slice(3, 6),
            local.slice(6, 10),
        ].filter(Boolean);

        return '+234' + (groups.length ? ' ' + groups.join(' ') : '');
    }

    digits = digits.slice(0, 11);
    const groups = [
        digits.slice(0, 4),
        digits.slice(4, 7),
        digits.slice(7, 11),
    ].filter(Boolean);

    return groups.join(' ');
}
