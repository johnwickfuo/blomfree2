import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    orange: '#F58220',
                    orangeDark: '#FF6B00',
                    dark: '#1A1A1A',
                    cream: '#FFF8F2',
                },
            },
            fontFamily: {
                sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
            },
        },
    },

    plugins: [forms],
};
