<template>
  <div class="login-container">
    <el-card class="login-card">
      <template #header>
        <div class="card-header">
          <span>遊戲平台登入</span>
        </div>
      </template>
      <el-form :model="form" label-width="80px">
        <el-form-item label="帳號">
          <el-input v-model="form.username" placeholder="請輸入帳號" />
        </el-form-item>
        <el-form-item label="密碼">
          <el-input v-model="form.password" type="password" placeholder="請輸入密碼" show-password />
        </el-form-item>
        
        <!-- Turnstile Widget -->
        <div class="turnstile-container">
          <div class="cf-turnstile" :data-sitekey="siteKey" data-callback="onTurnstileSuccess"></div>
        </div>

        <el-form-item>
          <el-button type="primary" @click="handleLogin" :loading="loading" style="width: 100%">登入</el-button>
        </el-form-item>
        <div class="links">
          <router-link to="/register">還沒有帳號？立即註冊</router-link>
        </div>
      </el-form>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { ElMessage } from 'element-plus'

const authStore = useAuthStore()
const loading = ref(false)
const siteKey = import.meta.env.VITE_TURNSTILE_SITE_KEY
const turnstileToken = ref('')

const form = ref({
  username: '',
  password: ''
})

onMounted(() => {
  // Ensure Turnstile renders
  if (window.turnstile) {
    window.turnstile.render('.cf-turnstile', {
      sitekey: siteKey,
      callback: (token: string) => {
        turnstileToken.value = token
      }
    })
  } else {
      // If script hasn't loaded yet, it will auto-render due to class name when it loads
      // But we need to capture the callback. 
      // Setting a global callback is one way, or using a Vue wrapper.
      // Simple approach: global function
      (window as any).onTurnstileSuccess = (token: string) => {
          turnstileToken.value = token
      }
  }
})

const handleLogin = async () => {
  if (!turnstileToken.value && siteKey) {
     ElMessage.warning('請完成驗證')
     return
  }

  loading.value = true
  try {
    await authStore.login({
        ...form.value,
        'cf-turnstile-response': turnstileToken.value
    })
    ElMessage.success('登入成功')
  } catch (error: any) {
    ElMessage.error(error.response?.data?.message || '登入失敗')
    // Reset turnstile on error
    if (window.turnstile) window.turnstile.reset()
    turnstileToken.value = ''
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.turnstile-container {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #f0f2f5;
}
.login-card {
  width: 400px;
}
.card-header {
  text-align: center;
  font-size: 18px;
  font-weight: bold;
}
.links {
  text-align: center;
  margin-top: 10px;
}
</style>
