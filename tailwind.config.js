import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import colors from 'tailwindcss/colors';

const brandPalette = {
    50: '#f5f7fc',
    100: '#e9eff8',
    200: '#cedaed',
    300: '#b2c4e1',
    400: '#6e94c3',
    500: '#4f6ba3',
    600: '#465a87',
    700: '#3b4f74',
    800: '#2f3f5a',
    900: '#243145',
    950: '#151d2b',
};

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                danger: colors.rose,
                primary: brandPalette,
                success: colors.green,
                warning: colors.yellow,
            },
            fontFamily: {
                // Default body font
                sans: ['"Helvetica World"', 'Helvetica', 'Arial', ...defaultTheme.fontFamily.sans],
                // Headings font
                serif: ['"Libre Baskerville"', ...defaultTheme.fontFamily.serif],
            },
            typography: ({ theme }) => {
                const headingFont = theme('fontFamily.serif').join(', ');

                return {
                    DEFAULT: {
                        css: {
                            color: theme('colors.gray.700'),
                            maxWidth: '70ch',
                            a: {
                                color: theme('colors.primary.500'),
                                fontWeight: '600',
                                textDecorationThickness: '2px',
                                textUnderlineOffset: '6px',
                                '&:hover': {
                                    color: theme('colors.primary.400'),
                                },
                                '&:focus-visible': {
                                    outline: 'none',
                                    boxShadow: `0 0 0 2px ${theme('colors.primary.400')}`,
                                },
                            },
                            strong: {
                                color: theme('colors.gray.900'),
                            },
                            blockquote: {
                                fontStyle: 'normal',
                                fontWeight: '500',
                                color: theme('colors.gray.900'),
                                borderLeftColor: theme('colors.primary.400'),
                                backgroundColor: theme('colors.primary.50'),
                                paddingLeft: '1rem',
                                paddingRight: '1rem',
                                paddingTop: '0.75rem',
                                paddingBottom: '0.75rem',
                                borderRadius: '0.75rem',
                            },
                            code: {
                                color: theme('colors.primary.600'),
                                fontWeight: '600',
                                backgroundColor: theme('colors.primary.50'),
                                padding: '0.25rem 0.375rem',
                                borderRadius: '0.5rem',
                            },
                            'code::before': {
                                content: 'none',
                            },
                            'code::after': {
                                content: 'none',
                            },
                            pre: {
                                color: theme('colors.primary.100'),
                                backgroundColor: theme('colors.slate.900'),
                                borderRadius: '1.25rem',
                                overflow: 'auto',
                                padding: '1.5rem',
                                border: `1px solid ${theme('colors.slate.800')}`,
                            },
                            hr: {
                                borderColor: theme('colors.gray.200'),
                            },
                            mark: {
                                color: 'inherit',
                                backgroundColor: theme('colors.primary.100'),
                                borderRadius: '0.375rem',
                                padding: '0.125rem 0.25rem',
                            },
                            'ol > li::marker': {
                                color: theme('colors.primary.500'),
                                fontWeight: '600',
                            },
                            'ul > li::marker': {
                                color: theme('colors.primary.400'),
                            },
                            thead: {
                                color: theme('colors.gray.900'),
                            },
                            tbody: {
                                tr: {
                                    borderBottomColor: theme('colors.gray.200'),
                                },
                            },
                            'h1, h2, h3, h4': {
                                fontFamily: headingFont,
                                color: theme('colors.gray.900'),
                                letterSpacing: '-0.01em',
                            },
                            h1: {
                                fontWeight: '800',
                                lineHeight: '1.1',
                            },
                            h2: {
                                fontWeight: '700',
                                lineHeight: '1.2',
                            },
                            h3: {
                                fontWeight: '700',
                                lineHeight: '1.25',
                            },
                        },
                    },
                    invert: {
                        css: {
                            color: theme('colors.gray.300'),
                            a: {
                                color: theme('colors.primary.300'),
                                '&:hover': {
                                    color: theme('colors.primary.200'),
                                },
                            },
                            strong: {
                                color: theme('colors.gray.100'),
                            },
                            blockquote: {
                                color: theme('colors.gray.100'),
                                borderLeftColor: theme('colors.primary.400'),
                                backgroundColor: 'rgb(110 148 195 / 0.15)',
                            },
                            code: {
                                backgroundColor: 'rgb(110 148 195 / 0.15)',
                                color: theme('colors.primary.200'),
                            },
                            pre: {
                                backgroundColor: 'rgb(15 23 42 / 0.75)',
                                borderColor: 'rgb(30 41 59 / 0.8)',
                            },
                            mark: {
                                backgroundColor: 'rgb(110 148 195 / 0.25)',
                                color: theme('colors.primary.100'),
                            },
                            'ol > li::marker': {
                                color: theme('colors.primary.300'),
                            },
                            'ul > li::marker': {
                                color: theme('colors.primary.200'),
                            },
                            'h1, h2, h3, h4': {
                                color: theme('colors.gray.50'),
                            },
                        },
                    },
                };
            },
        },
    },

    plugins: [forms, typography],
};
