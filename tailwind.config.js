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
                // Paleta de marca VenExpress — azul noche (autoridad/logística),
                // dorado (movimiento/acción) y rojo tricolor (acento, muy contenido).
                navy: {
                    50: '#EEF2FA',
                    100: '#D6DFF0',
                    200: '#A9BBDE',
                    300: '#7690C4',
                    400: '#4A67A0',
                    500: '#2C4677',
                    600: '#1E3259',
                    700: '#16244A',
                    800: '#101B3A',
                    900: '#0B1830',
                    950: '#070F20',
                },
                gold: {
                    50: '#FFF9E8',
                    100: '#FFEFC2',
                    200: '#FFE18F',
                    300: '#FDCB4D',
                    400: '#F9BB24',
                    500: '#F0A80D',
                    600: '#CC8A08',
                    700: '#9C690A',
                },
                flag: {
                    red: '#CE1126',
                },
            },
            boxShadow: {
                brand: '0 20px 45px -18px rgba(11, 24, 48, 0.35)',
            },
        },
    },

    plugins: [forms],
};
