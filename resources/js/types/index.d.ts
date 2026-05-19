import type { CartData } from './models';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
    };
    cart: CartData;
    flash: {
        success: string | null;
        error: string | null;
    };
};
