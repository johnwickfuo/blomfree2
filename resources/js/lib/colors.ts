// Maps common colour names to hex so product swatches render as real colours.
// Anything not listed falls back to a neutral swatch labelled with the name.
const COLOR_HEX: Record<string, string> = {
    red: '#DC2626',
    blue: '#2563EB',
    navy: '#1E3A8A',
    black: '#1A1A1A',
    white: '#FFFFFF',
    green: '#16A34A',
    yellow: '#CA8A04',
    orange: '#F58220',
    purple: '#7C3AED',
    pink: '#DB2777',
    gray: '#6B7280',
    grey: '#6B7280',
    brown: '#92400E',
    beige: '#D6CCC2',
    cream: '#FFF8F2',
    silver: '#C0C5CE',
    gold: '#D4AF37',
    khaki: '#A89F68',
    midnight: '#191970',
    starlight: '#F5F0E6',
    natural: '#C2B8A3',
};

export function colorToHex(name: string): string | null {
    return COLOR_HEX[name.trim().toLowerCase()] ?? null;
}
