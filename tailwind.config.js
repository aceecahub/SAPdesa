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
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#f0f4f1',
                    100: '#dce8dd',
                    200: '#bcd2be',
                    300: '#91b495',
                    400: '#64906a',
                    500: '#46744c',
                    600: '#345b39',
                    700: '#2d5a27', // Leaf Green
                    800: '#264825',
                    900: '#1e4620', // Forest Green
                    950: '#102712',
                },
                surface: {
                    50: '#fdfbf7', // Crisp clean cream
                    100: '#f5f4ef',
                    200: '#e5e4df',
                    300: '#d4d2cc',
                    400: '#9e9c96',
                    500: '#71706b',
                    600: '#52514c',
                    700: '#43423e',
                    800: '#2d3748', // Charcoal grey for text
                    900: '#1a1f29',
                },
                sage: '#e8efe9',
                wood: '#8c6239',
            },
            borderRadius: {
                'sm': '0.25rem',
                'DEFAULT': '0.375rem',
                'md': '0.5rem',
            },
        },
    },

    plugins: [forms],
};
