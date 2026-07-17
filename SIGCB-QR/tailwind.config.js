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
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#E8F5E4',
                    100: '#C5E3BC',
                    200: '#9FCF91',
                    300: '#7ABB66',
                    400: '#63A355',
                    500: '#4A7D3F',
                    600: '#3A6631',
                    700: '#2A4F23',
                    800: '#1A3815',
                    900: '#0A2108',
                },
                gold: {
                    50: '#FDF8E8',
                    100: '#F9EEC5',
                    200: '#F0D99A',
                    300: '#E4C06A',
                    400: '#C9A94E',
                    500: '#B8942F',
                    600: '#9A7A25',
                    700: '#7C611C',
                    800: '#5E4914',
                    900: '#40310C',
                },
            },
        },
    },

    plugins: [forms],
};
