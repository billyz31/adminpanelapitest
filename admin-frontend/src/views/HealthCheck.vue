<template>
  <div class="container">
    <h1>System Health Diagnostic Center</h1>
    
    <div class="actions">
      <el-button type="primary" size="large" @click="checkHealth" :loading="loading">
        Run System Diagnostics
      </el-button>
    </div>
    
    <div v-if="results || error" class="dashboard">
      <!-- Nginx / Web Server Status -->
      <el-card class="status-card" :class="{ 'status-ok': nginxStatus === 'ok', 'status-error': nginxStatus === 'error' }">
        <template #header>
          <div class="card-header">
            <span>Web Server (Nginx)</span>
            <el-tag :type="nginxStatus === 'ok' ? 'success' : 'danger'">
              {{ nginxStatus === 'ok' ? 'Online' : 'Unreachable' }}
            </el-tag>
          </div>
        </template>
        <div v-if="results?.api?.server_info" class="info-list">
          <div class="info-item">
            <span class="label">Software:</span>
            <span class="value">{{ results.api.server_info.software }}</span>
          </div>
          <div class="info-item">
            <span class="label">Host:</span>
            <span class="value">{{ results.api.server_info.host }}</span>
          </div>
          <div class="info-item">
            <span class="label">Client IP (You):</span>
            <span class="value">{{ results.api.server_info.client_ip }}</span>
          </div>
        </div>
        <div v-else class="error-message">
          Unable to reach Nginx Proxy.
        </div>
      </el-card>

      <!-- Backend / Laravel Status -->
      <el-card class="status-card" :class="{ 'status-ok': backendStatus === 'ok', 'status-error': backendStatus === 'error' }">
        <template #header>
          <div class="card-header">
            <span>Backend API (Laravel)</span>
            <el-tag :type="backendStatus === 'ok' ? 'success' : 'danger'">
              {{ backendStatus === 'ok' ? 'Online' : 'Offline' }}
            </el-tag>
          </div>
        </template>
        <div v-if="results?.api" class="info-list">
          <div class="info-item">
            <span class="label">Laravel Version:</span>
            <span class="value">{{ results.api.server_info?.laravel_version }}</span>
          </div>
          <div class="info-item">
            <span class="label">PHP Version:</span>
            <span class="value">{{ results.api.server_info?.php_version }}</span>
          </div>
          <div class="info-item">
            <span class="label">Server IP:</span>
            <span class="value">{{ results.api.server_info?.server_ip }}</span>
          </div>
        </div>
        <div v-else class="error-message">
          {{ error || 'Connection failed' }}
        </div>
      </el-card>

      <!-- Database Status -->
      <el-card class="status-card" :class="{ 'status-ok': dbStatus === 'ok', 'status-error': dbStatus === 'error' }">
        <template #header>
          <div class="card-header">
            <span>Database (PostgreSQL)</span>
            <el-tag :type="dbStatus === 'ok' ? 'success' : 'danger'">
              {{ dbStatus === 'ok' ? 'Connected' : 'Disconnected' }}
            </el-tag>
          </div>
        </template>
        <div v-if="results?.db?.status === 'ok'" class="info-list">
           <div class="info-item">
            <span class="label">Database Name:</span>
            <span class="value">{{ results.db.database }}</span>
          </div>
           <div class="info-item">
            <span class="label">Version:</span>
            <span class="value">{{ results.db.version }}</span>
          </div>
           <div class="info-item">
            <span class="label">Registered Users:</span>
            <span class="value"><strong>{{ results.db.user_count }}</strong></span>
          </div>
        </div>
        <div v-else-if="results?.db" class="error-message">
          {{ results.db.message }}
        </div>
        <div v-else class="error-message">
          Waiting for diagnostics...
        </div>
      </el-card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import axios from 'axios'
import { ElMessage } from 'element-plus'

// Interfaces
interface ServerInfo {
  software: string;
  php_version: string;
  laravel_version: string;
  client_ip: string;
  server_ip: string;
  host: string;
}

interface ApiResult {
  status: string;
  message: string;
  server_info: ServerInfo;
  timestamp: string;
}

interface DbResult {
  status: string;
  message: string;
  database?: string;
  version?: string;
  user_count?: number;
  timestamp: string;
}

interface Results {
  api: ApiResult | null;
  db: DbResult | null;
}

const loading = ref(false)
const error = ref<string | null>(null)
const results = ref<Results | null>(null)

// Computed Statuses
const nginxStatus = computed(() => {
  if (results.value?.api?.status === 'ok') return 'ok'
  if (error.value && error.value.includes('Network Error')) return 'error'
  return 'unknown'
})

const backendStatus = computed(() => {
  if (results.value?.api?.status === 'ok') return 'ok'
  return 'error'
})

const dbStatus = computed(() => {
  if (results.value?.db?.status === 'ok') return 'ok'
  return 'error'
})

const checkHealth = async () => {
  loading.value = true
  error.value = null
  results.value = { api: null, db: null }
  
  try {
    // 1. Check API & Server Info
    const apiResponse = await axios.get('/api/health')
    results.value.api = apiResponse.data

    // 2. Check Database
    const dbResponse = await axios.get('/api/health/db')
    results.value.db = dbResponse.data

    ElMessage.success('System diagnostics completed successfully')
  } catch (err: any) {
    console.error('Health check failed:', err)
    error.value = err.message || 'Failed to connect to server'
    ElMessage.error('System diagnostics failed: ' + error.value)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Container & Layout */
.container {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem;
  font-family: 'Inter', sans-serif;
}

h1 {
  text-align: center;
  margin-bottom: 2rem;
  color: #2c3e50;
  font-weight: 700;
}

.actions {
  display: flex;
  justify-content: center;
  margin-bottom: 3rem;
}

/* Dashboard Grid */
.dashboard {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
}

/* Status Cards */
.status-card {
  border-radius: 12px;
  transition: all 0.3s ease;
  border: 1px solid #e0e0e0;
}

.status-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: 600;
  font-size: 1.1rem;
}

/* Status Indicators */
.status-ok {
  border-left: 5px solid #67c23a;
}

.status-error {
  border-left: 5px solid #f56c6c;
}

/* Info List Styling */
.info-list {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  border-bottom: 1px solid #f0f0f0;
  padding-bottom: 0.5rem;
}

.info-item:last-child {
  border-bottom: none;
}

.label {
  color: #606266;
  font-weight: 500;
}

.value {
  color: #303133;
  font-family: 'Roboto Mono', monospace;
  font-weight: 600;
}

.error-message {
  color: #f56c6c;
  text-align: center;
  padding: 1rem;
  background-color: #fef0f0;
  border-radius: 4px;
}
</style>
