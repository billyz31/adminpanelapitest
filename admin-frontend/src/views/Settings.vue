<template>
  <div class="settings-container">
    <div class="header">
      <h2>System Settings</h2>
    </div>

    <el-card shadow="hover">
      <template #header>
        <div class="card-header">
          <span>General Settings</span>
        </div>
      </template>
      
      <el-form :model="form" label-width="120px">
        <el-form-item label="Site Name">
          <el-input v-model="form.siteName" />
        </el-form-item>
        <el-form-item label="Maintenance Mode">
          <el-switch v-model="form.maintenanceMode" />
        </el-form-item>
        <el-form-item label="Admin Email">
          <el-input v-model="form.adminEmail" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="saveSettings">Save Changes</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <el-card shadow="hover" class="mt-4">
      <template #header>
        <div class="card-header">
          <span>Security</span>
        </div>
      </template>
      
      <el-form label-width="120px">
        <el-form-item label="Change Password">
          <el-button @click="showPasswordDialog = true">Update Password</el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- Password Dialog -->
    <el-dialog v-model="showPasswordDialog" title="Change Password" width="30%">
      <el-form :model="passwordForm" label-width="140px">
        <el-form-item label="Current Password">
          <el-input v-model="passwordForm.current" type="password" show-password />
        </el-form-item>
        <el-form-item label="New Password">
          <el-input v-model="passwordForm.new" type="password" show-password />
        </el-form-item>
        <el-form-item label="Confirm Password">
          <el-input v-model="passwordForm.confirm" type="password" show-password />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="showPasswordDialog = false">Cancel</el-button>
          <el-button type="primary" @click="updatePassword">Confirm</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { ElMessage } from 'element-plus'
import axios from '../config/axios'

const form = reactive({
  siteName: 'GoFun Admin',
  maintenanceMode: false,
  adminEmail: 'admin@example.com'
})

const showPasswordDialog = ref(false)
const passwordForm = reactive({
  current: '',
  new: '',
  confirm: ''
})

const saveSettings = () => {
  // Mock implementation for general settings
  ElMessage.info('General settings saved (Mock)')
}

const updatePassword = async () => {
  if (passwordForm.new !== passwordForm.confirm) {
    ElMessage.error('Passwords do not match')
    return
  }

  try {
    const token = localStorage.getItem('admin_token')
    await axios.post('/api/admin/change-password', {
      current_password: passwordForm.current,
      new_password: passwordForm.new,
      new_password_confirmation: passwordForm.confirm
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    ElMessage.success('Password updated successfully')
    showPasswordDialog.value = false
    passwordForm.current = ''
    passwordForm.new = ''
    passwordForm.confirm = ''
  } catch (error: any) {
    console.error('Error updating password:', error)
    const msg = error.response?.data?.message || 'Failed to update password'
    ElMessage.error(msg)
  }
}
</script>

<style scoped>
.settings-container {
  padding: 20px;
}

.header {
  margin-bottom: 20px;
}

.mt-4 {
  margin-top: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
