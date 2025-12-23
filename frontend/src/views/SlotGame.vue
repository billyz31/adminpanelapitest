<template>
  <div class="slot-container">
    <el-card class="game-card">
      <template #header>
        <div class="header">
          <div class="left-section">
            <el-button link @click="router.push('/lobby')">❮ 返回大廳</el-button>
            <span class="title">🎰 幸運老虎機</span>
          </div>
          <span class="balance">餘額: ${{ userBalance }}</span>
        </div>
      </template>

      <!-- 老虎機轉軸區域 -->
      <div class="reels-container">
        <div v-for="(reel, index) in reels" :key="index" class="reel">
          <div class="reel-content" :class="{ 'spinning': isSpinning }">
            <div class="symbol">{{ reel.icon }}</div>
          </div>
        </div>
      </div>

      <!-- 操作區域 -->
      <div class="controls">
        <div class="bet-input">
          <span>下注金額: </span>
          <el-input-number v-model="betAmount" :min="10" :max="1000" :step="10" />
        </div>
        
        <div class="message" :class="{ 'win': isWin }">
          {{ message }}
        </div>

        <el-button 
          type="primary" 
          size="large" 
          @click="spin" 
          :loading="isSpinning"
          class="spin-button"
        >
          SPIN!
        </el-button>
      </div>
      
      <div class="rules">
        <p>規則: 3個相同符號即中獎！</p>
        <p>🍒(2x) 🍋(3x) 🍇(5x) 💎(10x) 7️⃣(20x)</p>
      </div>
    </el-card>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'
import { ElMessage } from 'element-plus'

const authStore = useAuthStore()
const router = useRouter()
const betAmount = ref(10)
const isSpinning = ref(false)
const isWin = ref(false)
const message = ref('準備開始')

// 初始符號
const reels = ref([
  { icon: '❓', name: 'Question' },
  { icon: '❓', name: 'Question' },
  { icon: '❓', name: 'Question' }
])

const userBalance = computed(() => {
    return authStore.user?.balance ? parseFloat(authStore.user.balance).toFixed(2) : '0.00'
})

const spin = async () => {
  if (authStore.user.balance < betAmount.value) {
    ElMessage.error('餘額不足！')
    return
  }

  isSpinning.value = true
  isWin.value = false
  message.value = '轉動中...'

  try {
    // 呼叫後端 API
    const response = await axios.post('/api/slot/spin', {
      bet_amount: betAmount.value
    })

    // 模擬動畫延遲 (讓轉動效果持續一下)
    setTimeout(() => {
      const result = response.data
      reels.value = result.reels
      
      // 更新餘額
      authStore.user.balance = result.balance
      
      isSpinning.value = false
      
      if (result.is_win) {
        isWin.value = true
        message.value = `🎉 恭喜中獎！贏得 $${result.win_amount}`
        ElMessage.success(`贏得 $${result.win_amount}！`)
      } else {
        message.value = '再接再厲！'
      }
    }, 1000) // 1秒動畫

  } catch (error: any) {
    isSpinning.value = false
    message.value = '發生錯誤'
    ElMessage.error(error.response?.data?.message || '遊戲發生錯誤')
  }
}

onMounted(() => {
  authStore.fetchUser()
})
</script>

<style scoped>
.slot-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background-color: #2c3e50;
}

.game-card {
  width: 95%;
  max-width: 500px;
  background-color: #ecf0f1;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
  font-size: 1.2rem;
}

.left-section {
  display: flex;
  align-items: center;
  gap: 10px;
}

.reels-container {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin: 30px 0;
  background-color: #34495e;
  padding: 20px;
  border-radius: 10px;
}

.reel {
  width: 80px;
  height: 100px;
  background-color: white;
  border-radius: 5px;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 3rem;
  box-shadow: inset 0 0 10px rgba(0,0,0,0.2);
  overflow: hidden;
}

/* 簡單的轉動動畫 */
@keyframes shake {
  0% { transform: translateY(0); }
  25% { transform: translateY(-10px); }
  75% { transform: translateY(10px); }
  100% { transform: translateY(0); }
}

.spinning .symbol {
  animation: shake 0.1s infinite;
  filter: blur(2px);
}

.controls {
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.message {
  font-size: 1.2rem;
  font-weight: bold;
  height: 30px;
  color: #7f8c8d;
}

.message.win {
  color: #e74c3c;
  animation: pulse 0.5s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.1); }
  100% { transform: scale(1); }
}

.spin-button {
  width: 100%;
  font-size: 1.5rem;
  font-weight: bold;
}

.rules {
  margin-top: 20px;
  font-size: 0.9rem;
  color: #95a5a6;
  text-align: center;
}
</style>
