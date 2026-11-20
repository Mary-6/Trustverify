import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Manrope', ...defaultTheme.fontFamily.sans],
                display: ['Manrope', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    navy: '#0A0F1E',
                    'navy-light': '#131B33',
                    teal: '#06B6D4',
                    'teal-soft': '#E0F7FA',
                    gold: '#F59E0B',
                    bg: '#F7F9FC',
                    text: '#101828',
                    muted: '#667085',
                    success: '#10B981',
                    error: '#EF4444',
                },
            },
        },
    },

    plugins: [forms],
};
