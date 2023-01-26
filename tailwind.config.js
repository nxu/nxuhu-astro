module.exports = {
  content: ['./public/**/*.html', './src/**/*.{astro,js,jsx,ts,tsx,vue,mdx}'],
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        'serif': ['Noto Serif', 'ui-serif'],
      },
      screens: {
        'print': {'raw': 'print'},
      }
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
}
