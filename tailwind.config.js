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
                sans: ['"Public Sans"', 'Figtree', ...defaultTheme.fontFamily.sans],
            },

            colors: {
                // Warna utama Vuexy (#7367f0) — remap dari indigo bawaan Tailwind
                // agar seluruh tombol/link/aksen yang memakai indigo ikut berganti.
                indigo: {
                    25: '#fcfcff',
                    50: '#f5f4fd',
                    100: '#ece9fb',
                    200: '#d4cff8',
                    300: '#b3abf4',
                    400: '#9286f4',
                    500: '#7367f0',
                    600: '#5e50ee',
                    700: '#4d3fe0',
                    800: '#3f33c0',
                    900: '#352a9e',
                },

                primary: {
                    DEFAULT: '#7367f0',
                    25: '#fcfcff',
                    50: '#f5f4fd',
                    100: '#ece9fb',
                    200: '#d4cff8',
                    300: '#b3abf4',
                    400: '#9286f4',
                    500: '#7367f0',
                    600: '#5e50ee',
                    700: '#4d3fe0',
                    800: '#3f33c0',
                    900: '#352a9e',
                },

                vuexy: {
                    body: '#f8f8f8',
                    sidebar: '#ffffff',
                    'sidebar-dark': '#283046',
                    'sidebar-dark-hover': '#2f3a52',
                    success: '#28c76f',
                    warning: '#ff9f43',
                    danger: '#ff4c51',
                    info: '#00bad1',
                    border: '#e6e6e8',
                    black: '#2f2b3d',
                },
            },

            boxShadow: {
                card: '0 2px 10px rgba(47, 43, 61, 0.05)',
                'card-hover': '0 4px 18px rgba(47, 43, 61, 0.09)',
                navbar: '0 2px 10px rgba(47, 43, 61, 0.04)',
                'primary-glow': '0 4px 14px rgba(115, 103, 240, 0.35)',
            },

            borderRadius: {
                card: '0.625rem',
            },
        },
    },

    plugins: [forms],
};
