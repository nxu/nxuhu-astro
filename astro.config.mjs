import { defineConfig } from 'astro/config';
import mdx from '@astrojs/mdx';

// https://astro.build/config
import vue from "@astrojs/vue";

// https://astro.build/config
import image from "@astrojs/image";

// https://astro.build/config
export default defineConfig({
  integrations: [mdx(), vue(), image({serviceEntryPoint: '@astrojs/image/sharp'})]
});