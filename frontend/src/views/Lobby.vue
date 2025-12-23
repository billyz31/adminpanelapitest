<template>
  <div class="lobby-container">
    <el-container>
      <el-header class="header">
        <div class="logo">遊戲大廳</div>
        <div class="user-info" v-if="authStore.user">
          <span>餘額: ${{ authStore.user.balance }}</span>
          <el-button type="success" size="small" @click="openDepositDialog">入金</el-button>
          <span class="username">{{ authStore.user.name }}</span>
          <el-button size="small" @click="handleLogout">登出</el-button>
        </div>
      </el-header>
      <el-main>
        <el-row :gutter="20">
          <el-col :span="6">
            <el-card class="game-card">
              <template #header>老虎機 (Slot)</template>
              <div class="game-content">
                <el-button type="primary" @click="router.push('/slot')">進入遊戲</el-button>
              </div>
            </el-card>
          </el-col>
          
          <el-col :span="6" v-for="i in 3" :key="i">
            <el-card class="game-card">
              <template #header>敬請期待 {{ i }}</template>
              <div class="game-content">
                <el-button type="info" disabled>維護中</el-button>
              </div>
            </el-card>
          </el-col>
        </el-row>
      </el-main>

      <!-- Deposit Dialog -->
      <el-dialog v-model="depositDialogVisible" title="帳戶入金" width="30%">
        <el-form>
          <el-form-item label="金額">
            <el-input-number v-model="depositAmount" :min="1" :precision="2" :step="100" />
          </el-form-item>
        </el-form>
        <template #footer>
          <span class="dialog-footer">
            <el-button @click="depositDialogVisible = false">取消</el-button>
            <el-button type="primary" @click="handleDeposit" :loading="depositLoading">確認入金</el-button>
          </span>
        </template>
      </el-dialog>
    </el-container>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'
import { ElMessage } from 'element-plus'

const authStore = useAuthStore()
const router = useRouter()

const depositDialogVisible = ref(false)
const depositAmount = ref(100)
const depositLoading = ref(false)

const handleLogout = () => {
  authStore.logout()
}

const openDepositDialog = () => {
  depositAmount.value = 100
  depositDialogVisible.value = true
}

const handleDeposit = async () => {
  if (depositAmount.value <= 0) {
    ElMessage.warning('請輸入有效的金額')
    return
  }
  
  depositLoading.value = true
  try {
    console.log('Sending deposit request...', depositAmount.value)
    const token = authStore.token
    const response = await axios.post('/api/deposit', {
      amount: depositAmount.value,
      description: 'User manual deposit'
    }, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    })
    console.log('Deposit response:', response.data)
    ElMessage.success('入金成功')
    depositDialogVisible.value = false
    await authStore.fetchUser() // Refresh balance
  } catch (error: any) {
    console.error('Deposit error:', error)
    ElMessage.error(error.response?.data?.message || '入金失敗')
  } finally {
    depositLoading.value = false
  }
}

onMounted(() => {
  authStore.fetchUser()
})
</script>

<style scoped>
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background-color: #409EFF;
  color: white;
  padding: 0 20px;
}
.logo {
  font-size: 20px;
  font-weight: bold;
}
.user-info {
  display: flex;
  gap: 15px;
  align-items: center;
}

@media (max-width: 768px) {
  .header {
    flex-direction: column;
    padding: 10px;
    gap: 10px;
  }
  .user-info {
    flex-wrap: wrap;
    justify-content: center;
  }
}

.game-card {
  margin-bottom: 20px;
  text-align: center;
}
</style>
