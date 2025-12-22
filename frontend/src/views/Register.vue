<template>
  <div class="register-container">
    <el-card class="register-card">
      <template #header>
        <div class="card-header">
          <span>遊戲平台註冊</span>
        </div>
      </template>
      <el-form :model="form" :rules="rules" ref="registerForm" label-width="80px">
        <el-form-item label="帳號">
          <el-input v-model="form.username" placeholder="請輸入帳號" />
        </el-form-item>
        <el-form-item label="暱稱">
          <el-input v-model="form.name" placeholder="請輸入顯示暱稱" />
        </el-form-item>
        <el-form-item label="密碼">
          <el-input v-model="form.password" type="password" placeholder="請輸入密碼" show-password />
        </el-form-item>
        <el-form-item label="確認密碼">
          <el-input v-model="form.password_confirmation" type="password" placeholder="請再次輸入密碼" show-password />
        </el-form-item>
        
        <!-- Turnstile Widget -->
        <div class="turnstile-container">
          <div class="cf-turnstile" :data-sitekey="siteKey" data-callback="onRegisterTurnstileSuccess"></div>
        </div>

        <el-form-item>
          <el-button type="primary" @click="handleRegister" :loading="loading" style="width: 100%">註冊</el-button>
        </el-form-item>
        <div class="links">
          <router-link to="/login">已有帳號？立即登入</router-link>
        </div>
      </el-form>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'

const authStore = useAuthStore()
const loading = ref(false)
const registerForm = ref<FormInstance>()
const siteKey = import.meta.env.VITE_TURNSTILE_SITE_KEY
const turnstileToken = ref('')

const form = ref({
  username: '',
  name: '',
  password: '',
  password_confirmation: ''
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
      (window as any).onRegisterTurnstileSuccess = (token: string) => {
          turnstileToken.value = token
      }
  }
})

const rules = ref<FormRules>({
  username: [
    { required: true, message: '請輸入帳號', trigger: 'blur' },
    { min: 3, max: 20, message: '帳號長度應該在 3 到 20 個字符', trigger: 'blur' }
  ],
  name: [
    { required: true, message: '請輸入暱稱', trigger: 'blur' },
    { min: 2, max: 50, message: '暱稱長度應該在 2 到 50 個字符', trigger: 'blur' }
  ],
  password: [
    { required: true, message: '請輸入密碼', trigger: 'blur' },
    { min: 6, message: '密碼長度至少 6 個字符', trigger: 'blur' }
  ],
  password_confirmation: [
    { required: true, message: '請確認密碼', trigger: 'blur' },
    {
      validator: (_rule, value, callback) => {
        if (value === '') {
          callback(new Error('請再次輸入密碼'))
        } else if (value !== form.value.password) {
          callback(new Error('兩次輸入密碼不一致!'))
        } else {
          callback()
        }
      },
      trigger: 'blur'
    }
  ]
})

const handleRegister = async () => {
  if (!registerForm.value) return
  
  await registerForm.value.validate(async (valid) => {
    if (valid) {
      loading.value = true
      try {
        await authStore.register(form.value)
        ElMessage.success('註冊成功')
      } catch (error: any) {
        ElMessage.error(error.response?.data?.message || '註冊失敗')
        // 如果有詳細錯誤訊息
        if (error.response?.data?.errors) {
            console.log(error.response.data.errors)
        }
      } finally {
        loading.value = false
      }
    }
  })
}
</script>

<style scoped>
.register-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #f0f2f5;
}
.register-card {
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
