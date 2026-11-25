import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

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
                    navy: '#071C1A',
                    'navy-light': '#13B89A',
                    teal: '#13B89A',
                    'teal-soft': '#E7F4EF',
                    gold: '#B6F36B',
                    bg: '#F3F7F5',
                    text: '#071C1A',
                    muted: '#64756E',
                    success: '#13B89A',
                    error: '#DC2626',
                },
            },
        },
    },

    plugins: [forms],
};
