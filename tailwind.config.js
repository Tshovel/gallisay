/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './public/index.html',
    ],
    theme: {
        extend: {
            colors: {
                gold: {
                    DEFAULT: '#C9A84C',
                    light: '#e2c97e',
                    dark: '#a07828',
                },
                dark: {
                    DEFAULT: '#0f0f0f',
                    surface: '#1a1a1a',
                    card: '#222222',
                    border: '#333333',
                },
            },
            fontFamily: {
                sans: ['Inter', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
