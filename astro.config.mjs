import { defineConfig } from 'astro/config';
import cloudflare from '@astrojs/cloudflare';
import mdx from '@astrojs/mdx';

// https://astro.build/config
import vue from "@astrojs/vue";

// https://astro.build/config
import image from "@astrojs/image";

// https://astro.build/config
export default defineConfig({
  output: 'server',
  adapter: cloudflare({
    mode: "directory"
  }),
  integrations: [mdx(), vue(), image({serviceEntryPoint: '@astrojs/image/sharp'})]
});