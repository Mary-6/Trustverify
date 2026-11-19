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
                    navy: '#101A2C',
                    'navy-light': '#17263D',
                    teal: '#16C7B7',
                    'teal-soft': '#D9F8F3',
                    gold: '#F4B942',
                    bg: '#F6F8FC',
                    text: '#172033',
                    muted: '#718096',
                    success: '#16A36A',
                    error: '#DC4545',
                },
            },
        },
    },

    plugins: [forms],
};
