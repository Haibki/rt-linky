import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { resolve } from 'path';

export default defineConfig({
  plugins: [react()],
  root: 'assets/src',
  base: './',
  build: {
    outDir: '../../build',
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        admin: resolve(__dirname, 'assets/src/ts/admin.tsx'),
        frontend: resolve(__dirname, 'assets/src/ts/frontend.ts'),
        'block-editor': resolve(__dirname, 'assets/src/blocks/linky-block/index.tsx'),
        styles: resolve(__dirname, 'assets/src/scss/admin.scss'),
      },
      output: {
        entryFileNames: 'js/[name]-[hash].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          const info = assetInfo.name.split('.');
          const ext = info[info.length - 1];
          if (/\.(css|scss)$/i.test(assetInfo.name)) {
            return 'css/[name]-[hash][extname]';
          }
          return 'assets/[name]-[hash][extname]';
        },
      },
    },
    minify: 'terser',
    sourcemap: true,
  },
  css: {
    preprocessorOptions: {
      scss: {
        additionalData: `@import "scss/variables.scss";`,
      },
    },
  },
  resolve: {
    alias: {
      '@': resolve(__dirname, 'assets/src'),
      '@components': resolve(__dirname, 'assets/src/ts/components'),
      '@stores': resolve(__dirname, 'assets/src/ts/stores'),
      '@types': resolve(__dirname, 'assets/src/ts/types'),
      '@utils': resolve(__dirname, 'assets/src/ts/utils'),
    },
  },
});
