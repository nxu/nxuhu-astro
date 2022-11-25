module.exports = {
  content: ['./public/**/*.html', './src/**/*.{astro,js,jsx,ts,tsx,vue,mdx}'],
  darkMode: 'class',
  theme: {
    extend: {
      fontFamily: {
        'serif': ['Noto Serif', 'ui-serif'],
      }
    },
  },
  plugins: [
    require('@tailwindcss/typography'),
  ],
}
