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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                display: ['"Space Grotesk"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Paleta de marca VenExpress — blanco / amarillo / negro,
                // estilo Chilexpress. `blue`, `slate` e `indigo` se
                // sobrescriben a una escala de negro/gris (antes eran los
                // tonos estructurales del sistema); `amber` se sobrescribe
                // al amarillo de marca (ya se usaba para botones/CTAs
                // primarios). `red`/`emerald`/`gray` quedan con sus valores
                // por defecto de Tailwind: son colores funcionales
                // (error/éxito/neutral), nunca de marca.
                blue: {
                    50: '#F7F7F4',
                    100: '#F0F0EC',
                    200: '#E5E5E0',
                    300: '#D9D9D3',
                    400: '#B8B8B2',
                    500: '#8F8F88',
                    600: '#6B6B66',
                    700: '#4A4A45',
                    800: '#2A2A26',
                    900: '#111111',
                    950: '#0A0A09',
                },
                slate: {
                    50: '#F7F7F4',
                    100: '#F0F0EC',
                    200: '#E5E5E0',
                    300: '#D9D9D3',
                    400: '#B8B8B2',
                    500: '#8F8F88',
                    600: '#6B6B66',
                    700: '#4A4A45',
                    800: '#2A2A26',
                    900: '#111111',
                    950: '#0A0A09',
                },
                indigo: {
                    50: '#F7F7F4',
                    100: '#F0F0EC',
                    200: '#E5E5E0',
                    300: '#D9D9D3',
                    400: '#B8B8B2',
                    500: '#8F8F88',
                    600: '#6B6B66',
                    700: '#4A4A45',
                    800: '#2A2A26',
                    900: '#111111',
                    950: '#0A0A09',
                },
                violet: {
                    50: '#F7F7F4',
                    100: '#F0F0EC',
                    200: '#E5E5E0',
                    300: '#D9D9D3',
                    400: '#B8B8B2',
                    500: '#8F8F88',
                    600: '#6B6B66',
                    700: '#4A4A45',
                    800: '#2A2A26',
                    900: '#111111',
                    950: '#0A0A09',
                },
                orange: {
                    50: '#F7F7F4',
                    100: '#F0F0EC',
                    200: '#E5E5E0',
                    300: '#D9D9D3',
                    400: '#B8B8B2',
                    500: '#8F8F88',
                    600: '#6B6B66',
                    700: '#4A4A45',
                    800: '#2A2A26',
                    900: '#111111',
                    950: '#0A0A09',
                },
                amber: {
                    50: '#FFFEEB',
                    100: '#FFFDBA',
                    200: '#FFFC70',
                    300: '#FCFF33',
                    400: '#F7FF00',
                    500: '#DEE600',
                    600: '#B8BF00',
                    700: '#8C9100',
                    800: '#666B00',
                    900: '#454800',
                },
                // `purple` se usaba como color de identidad exclusivo del
                // rol Almacén (fondos, botones, insignia "Hub"). Se
                // sobrescribe a la misma escala amarilla/negra para que
                // ese rol también quede dentro de blanco/amarillo/negro.
                purple: {
                    50: '#FFFEEB',
                    100: '#FFFDBA',
                    200: '#FFFC70',
                    600: '#111111',
                    700: '#111111',
                    800: '#111111',
                    900: '#111111',
                    950: '#0A0A09',
                },
            },
            boxShadow: {
                brand: '0 20px 45px -18px rgba(11, 24, 48, 0.35)',
            },
        },
    },

    plugins: [forms],
};
