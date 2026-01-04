import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// https://vite.dev/config/
export default defineConfig({
  plugins: [vue()],
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src')
    }
  },
  server: {
    host: '0.0.0.0', // 允許外部訪問（對 Docker 很重要）
    port: 5173,
    watch: {
      usePolling: true, // 在 Windows Docker 環境下強制輪詢文件變更
    },
    proxy: {
      '/api': {
        target: 'http://127.0.0.1:8003', // Docker 環境下指向 Laravel 服務 (使用 IP 避免 IPv6 解析問題)
        changeOrigin: true,
        secure: false,
      }
    }
  }
})
