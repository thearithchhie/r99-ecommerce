import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'path'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    proxy: {
      // Forward /api requests to Laravel backend
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      },
      // Forward /sanctum requests to Laravel backend
      '/sanctum': {
        target: 'http://localhost:8000',
        changeOrigin: true,
      }
    }
  }
}) 