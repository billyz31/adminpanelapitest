<template>
  <div class="api-tester-container">
    <el-button 
      type="warning" 
      circle 
      class="tester-btn"
      @click="visible = true"
      title="API 測試工具"
    >
      <el-icon><Monitor /></el-icon>
    </el-button>

    <el-drawer
      v-model="visible"
      title="API 測試控制台"
      direction="rtl"
      size="400px"
    >
      <div class="test-controls">
        <el-divider content-position="left">基礎連線</el-divider>
        <div class="button-group">
          <el-button @click="testEndpoint('/api/health', 'GET')" :loading="loading['/api/health']">
            測試 Health Check
          </el-button>
          <el-button @click="testEndpoint('/api/health/db', 'GET')" :loading="loading['/api/health/db']">
            測試 DB 連線
          </el-button>
        </div>

        <el-divider content-position="left">用戶狀態</el-divider>
        <div class="button-group">
          <el-button type="primary" @click="testEndpoint('/api/me', 'GET')" :loading="loading['/api/me']">
            獲取當前用戶 (/api/me)
          </el-button>
        </div>

        <el-divider content-position="left">測試結果</el-divider>
        <div class="console-output" ref="consoleRef">
          <div v-if="logs.length === 0" class="empty-log">
            暫無測試記錄
          </div>
          <div v-else v-for="(log, index) in logs" :key="index" class="log-entry" :class="log.type">
            <div class="log-header">
              <span class="timestamp">{{ log.time }}</span>
              <span class="method">{{ log.method }}</span>
              <span class="url">{{ log.url }}</span>
              <span class="status" :class="statusClass(log.status)">{{ log.status }}</span>
            </div>
            <pre class="log-body">{{ log.data }}</pre>
          </div>
        </div>
        
        <div class="actions">
            <el-button type="danger" link @click="clearLogs">清除日誌</el-button>
        </div>
      </div>
    </el-drawer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Monitor } from '@element-plus/icons-vue'
import axios from '../config/axios'

const visible = ref(false)
const loading = ref<Record<string, boolean>>({})
const logs = ref<any[]>([])
const consoleRef = ref<HTMLElement | null>(null)

const statusClass = (status: number) => {
  if (status >= 200 && status < 300) return 'status-success'
  if (status >= 400 && status < 500) return 'status-warning'
  if (status >= 500) return 'status-error'
  return ''
}

const clearLogs = () => {
  logs.value = []
}

const testEndpoint = async (url: string, method: string = 'GET') => {
  loading.value[url] = true
  const startTime = Date.now()
  
  try {
    const response = await axios({
      method,
      url,
      validateStatus: () => true // Resolve all status codes
    })
    
    const duration = Date.now() - startTime
    
    addLog({
      type: response.status >= 200 && response.status < 300 ? 'success' : 'error',
      method,
      url,
      status: response.status,
      time: `${duration}ms`,
      data: response.data
    })
    
  } catch (error: any) {
    addLog({
      type: 'error',
      method,
      url,
      status: 'ERR',
      time: `${Date.now() - startTime}ms`,
      data: error.message
    })
  } finally {
    loading.value[url] = false
  }
}

const addLog = (log: any) => {
  logs.value.unshift(log) // Add new logs to top
}
</script>

<style scoped>
.api-tester-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
}

.tester-btn {
  width: 50px;
  height: 50px;
  font-size: 24px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.test-controls {
  display: flex;
  flex-direction: column;
  height: 100%;
}

.button-group {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 10px;
}

.console-output {
  flex: 1;
  background: #1e1e1e;
  color: #d4d4d4;
  padding: 10px;
  border-radius: 4px;
  overflow-y: auto;
  font-family: 'Consolas', 'Monaco', monospace;
  font-size: 12px;
  max-height: 60vh;
}

.empty-log {
  color: #666;
  text-align: center;
  padding: 20px;
}

.log-entry {
  margin-bottom: 15px;
  border-bottom: 1px solid #333;
  padding-bottom: 10px;
}

.log-header {
  display: flex;
  gap: 8px;
  margin-bottom: 4px;
  align-items: center;
}

.method {
  font-weight: bold;
  color: #569cd6;
}

.status {
  font-weight: bold;
}

.status-success { color: #4ec9b0; }
.status-warning { color: #ce9178; }
.status-error { color: #f44747; }

.log-body {
  margin: 0;
  white-space: pre-wrap;
  word-break: break-all;
  color: #9cdcfe;
}

.actions {
    margin-top: 10px;
    text-align: right;
}
</style>