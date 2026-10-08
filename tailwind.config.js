import defaultTheme from 'tailwindcss/defaultTheme';

export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                vino: {
                    50:  '#fdf2f3',
                    100: '#fce7e9',
                    200: '#f8d0d4',
                    300: '#f2aab0',
                    400: '#e97a85',
                    500: '#da4d5d',
                    600: '#c63047',
                    700: '#a72039',
                    800: '#8c1e34',
                    900: '#722F37', // Color principal
                    950: '#420d18',
                },
            },
        },
    },
    plugins: [],
};