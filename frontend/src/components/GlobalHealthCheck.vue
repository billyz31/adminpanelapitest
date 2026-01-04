<template>
  <div class="health-check-container">
    <el-popover
      placement="top-end"
      title="系統健康狀態"
      :width="400"
      trigger="click"
      @show="checkHealth"
    >
      <template #reference>
        <el-button 
          class="health-btn" 
          :type="overallStatus === 'error' ? 'danger' : 'success'" 
          circle 
          size="large"
        >
          <el-icon><Monitor /></el-icon>
        </el-button>
      </template>

      <div v-loading="loading">
        <el-tabs v-model="activeTab">
          <el-tab-pane label="基本資訊" name="basic">
            <div class="status-item">
              <span class="label">API 狀態:</span>
              <el-tag :type="apiStatus === 'ok' ? 'success' : 'danger'">{{ apiStatus }}</el-tag>
            </div>
            <div class="status-item">
              <span class="label">資料庫:</span>
              <el-tag :type="dbStatus === 'ok' ? 'success' : 'danger'">{{ dbStatus }}</el-tag>
            </div>
            <div class="status-item">
              <span class="label">延遲:</span>
              <span>{{ latency }} ms</span>
            </div>
            <el-divider />
            <div class="raw-data">
                <p><strong>Server Info:</strong></p>
                <pre>{{ apiData?.server_info ? JSON.stringify(apiData.server_info, null, 2) : '無資料' }}</pre>
            </div>
          </el-tab-pane>
          <el-tab-pane label="詳細回應" name="raw">
            <el-scrollbar height="300px">
              <h4>API Response:</h4>
              <pre class="code-block">{{ JSON.stringify(apiData, null, 2) }}</pre>
              <h4>DB Response:</h4>
              <pre class="code-block">{{ JSON.stringify(dbData, null, 2) }}</pre>
              <h4>Error Log:</h4>
              <pre class="code-block error">{{ errorLog }}</pre>
            </el-scrollbar>
          </el-tab-pane>
        </el-tabs>
        
        <div class="actions">
          <el-button type="primary" size="small" @click="checkHealth" :loading="loading">重新檢查</el-button>
        </div>
      </div>
    </el-popover>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { Monitor } from '@element-plus/icons-vue'

const loading = ref(false)
const activeTab = ref('basic')
const apiStatus = ref('unknown')
const dbStatus = ref('unknown')
const apiData = ref<any>(null)
const dbData = ref<any>(null)
const errorLog = ref('')
const latency = ref(0)

const overallStatus = computed(() => {
  if (apiStatus.value === 'error' || dbStatus.value === 'error') return 'error'
  if (apiStatus.value === 'ok' && dbStatus.value === 'ok') return 'ok'
  return 'unknown'
})

const checkHealth = async () => {
  loading.value = true
  errorLog.value = ''
  apiStatus.value = 'checking'
  dbStatus.value = 'checking'
  
  const start = performance.now()
  
  try {
    // Check API Health
    try {
        const apiRes = await axios.get('/api/health')
        apiData.value = apiRes.data
        apiStatus.value = 'ok'
    } catch (e: any) {
        apiStatus.value = 'error'
        apiData.value = null
        errorLog.value += `API Error: ${e.message}\n`
        if (e.response) {
            errorLog.value += `Status: ${e.response.status}\n`
            errorLog.value += `Data: ${JSON.stringify(e.response.data)}\n`
        }
    }

    // Check DB Health
    try {
        const dbRes = await axios.get('/api/health/db')
        dbData.value = dbRes.data
        dbStatus.value = 'ok'
    } catch (e: any) {
        dbStatus.value = 'error'
        dbData.value = null
        errorLog.value += `DB Error: ${e.message}\n`
        if (e.response) {
            errorLog.value += `Status: ${e.response.status}\n`
            errorLog.value += `Data: ${JSON.stringify(e.response.data)}\n`
        }
    }

  } catch (error: any) {
    errorLog.value += `General Error: ${error.message}\n`
  } finally {
    const end = performance.now()
    latency.value = Math.round(end - start)
    loading.value = false
  }
}

onMounted(() => {
    // Initial check after a short delay
    setTimeout(checkHealth, 1000)
})
</script>

<style scoped>
.health-check-container {
  position: fixed;
  bottom: 20px;
  right: 20px;
  z-index: 9999;
}

.health-btn {
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}

.status-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.label {
  font-weight: bold;
  color: #606266;
}

.code-block {
  background-color: #f5f7fa;
  padding: 10px;
  border-radius: 4px;
  font-size: 12px;
  overflow-x: auto;
  white-space: pre-wrap;
  word-wrap: break-word;
}

.code-block.error {
    color: #f56c6c;
    background-color: #fef0f0;
}

.raw-data {
    font-size: 12px;
    color: #909399;
}

.actions {
    margin-top: 15px;
    text-align: right;
}
</style>
