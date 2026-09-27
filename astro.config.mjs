import { defineConfig } from 'astro/config';
import mdx from '@astrojs/mdx';
import vue from '@astrojs/vue';
import tailwindcss from '@tailwindcss/vite';

// https://astro.build/config
export default defineConfig({
  // Astro 7 defaults to JSX whitespace rules, which drop spaces around inline elements.
  compressHTML: true,
  integrations: [mdx(), vue()],
  vite: {
    plugins: [tailwindcss()],
  },
});
