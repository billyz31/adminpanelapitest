<template>
  <div class="login-container">
    <el-card class="login-card">
      <template #header>
        <h2 class="login-title">Admin Login</h2>
      </template>
      
      <el-form :model="form" :rules="rules" ref="formRef" label-position="top">
        <el-form-item label="Email" prop="email">
          <el-input v-model="form.email" placeholder="admin@example.com" />
        </el-form-item>
        
        <el-form-item label="Password" prop="password">
          <el-input 
            v-model="form.password" 
            type="password" 
            placeholder="********" 
            show-password 
            @keyup.enter="handleLogin"
          />
        </el-form-item>
        
        <el-form-item>
          <el-button type="primary" class="login-button" :loading="loading" @click="handleLogin">
            Login
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { ElMessage } from 'element-plus'
import type { FormInstance, FormRules } from 'element-plus'
import axios from '../config/axios'

const router = useRouter()
const formRef = ref<FormInstance>()
const loading = ref(false)

const form = reactive({
  email: '',
  password: ''
})

const rules = reactive<FormRules>({
  email: [
    { required: true, message: 'Please input email address', trigger: 'blur' },
    { type: 'email', message: 'Please input correct email address', trigger: 'blur' }
  ],
  password: [
    { required: true, message: 'Please input password', trigger: 'blur' },
    { min: 6, message: 'Password must be at least 6 characters', trigger: 'blur' }
  ]
})

const handleLogin = async () => {
  if (!formRef.value) return
  
  await formRef.value.validate(async (valid, _fields) => {
    if (valid) {
      loading.value = true
      try {
        const response = await axios.post('/api/admin/login', {
          email: form.email,
          password: form.password
        })
        
        const token = response.data.token
        localStorage.setItem('admin_token', token)
        
        if (response.data.user) {
          localStorage.setItem('admin_user', JSON.stringify(response.data.user))
        }
        
        ElMessage.success('Login successful')
        router.push('/')
      } catch (error: any) {
        console.error(error)
        ElMessage.error(error.response?.data?.message || 'Login failed')
      } finally {
        loading.value = false
      }
    }
  })
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f0f2f5;
}

.login-card {
  width: 100%;
  max-width: 400px;
}

.login-title {
  text-align: center;
  margin: 0;
  color: #303133;
}

.login-button {
  width: 100%;
}
</style>
