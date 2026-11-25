import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './public/assets/js/app.js',
    ],
    theme: {
        extend: {
            colors: {
                'primary': {
                    DEFAULT: '#0F172A',
                    hover: '#1E293B',
                    container: '#1E293B',
                    fixed: '#E2E8F0',
                    'fixed-dim': '#CBD5E1',
                    dark: '#3B82F6'
                },
                'secondary': {
                    DEFAULT: '#2563EB',
                    hover: '#1D4ED8',
                    container: '#3B82F6',
                    fixed: '#DBEAFE',
                    dark: '#60A5FA'
                },
                'tertiary': {
                    DEFAULT: '#0D9488',
                    hover: '#0F766E',
                    container: '#CCFBF1',
                    dark: '#14B8A6'
                },
                'surface': '#F8FAFC',
                'surface-white': '#FFFFFF',
                'surface-container-low': '#F1F5F9',
                'surface-container': '#E2E8F0',
                'surface-container-high': '#CBD5E1',
                'background-canvas': '#F8FAFC',
                'background-subtle': '#F1F5F9',
                'border-subtle': '#E2E8F0',
                'border-strong': '#CBD5E1',
                'text-main': '#0F172A',
                'text-muted': '#475569',
                'wholesale-badge-bg': '#FEF2F2',
                'wholesale-badge-text': '#B91C1C',
                'payment-cash': '#16A34A',
                'payment-qris': '#2563EB',
                'payment-tempo': '#D97706',
                'status-void': '#DC2626',
                'status-paid': '#16A34A'
            },
            fontFamily: {
                sans: ['Inter', 'Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            spacing: {
                'space-xs': '4px',
                'space-sm': '8px',
                'space-md': '12px',
                'space-lg': '16px',
                'space-xl': '24px',
            }
        },
    },
    plugins: [],
};
