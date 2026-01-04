<template>
  <div class="game-rounds-container">
    <div class="header">
      <h2>Game History</h2>
      <div class="filter-container">
        <el-input
          v-model="searchQuery"
          placeholder="Search by username"
          style="width: 200px; margin-right: 10px;"
          @keyup.enter="handleSearch"
          clearable
          @clear="handleSearch"
        />
        <el-date-picker
          v-model="dateRange"
          type="daterange"
          range-separator="To"
          start-placeholder="Start date"
          end-placeholder="End date"
          style="margin-right: 10px;"
          value-format="YYYY-MM-DD"
          @change="handleSearch"
        />
        <el-button @click="handleSearch" type="primary">Search</el-button>
      </div>
    </div>

    <el-card shadow="hover">
      <el-table :data="rounds" style="width: 100%" v-loading="loading">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="user.username" label="Username" width="150" />
        <el-table-column prop="game_name" label="Game" width="150" />
        <el-table-column prop="bet_amount" label="Bet">
          <template #default="scope">
            ${{ Number(scope.row.bet_amount).toFixed(2) }}
          </template>
        </el-table-column>
        <el-table-column prop="payout_amount" label="Payout">
          <template #default="scope">
            <span :class="getPayoutClass(scope.row)">
              ${{ Number(scope.row.payout_amount).toFixed(2) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="result" label="Result">
          <template #default="scope">
            <el-tag :type="getResultType(scope.row)">
              {{ getResultLabel(scope.row) }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="Time" width="180">
          <template #default="scope">
            {{ new Date(scope.row.created_at).toLocaleString() }}
          </template>
        </el-table-column>
        <el-table-column label="Details" width="100">
          <template #default="scope">
            <el-button size="small" @click="viewDetails(scope.row)">View</el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="pagination-container">
        <el-pagination
          v-if="total > 0"
          background
          layout="prev, pager, next"
          :total="total"
          :page-size="pageSize"
          :current-page="currentPage"
          @current-change="handlePageChange"
        />
      </div>
    </el-card>

    <!-- Details Dialog -->
    <el-dialog v-model="detailsDialogVisible" title="Game Round Details" width="600px">
      <div v-if="selectedRound">
        <p><strong>Game:</strong> {{ selectedRound.game_name }}</p>
        <p><strong>Round ID:</strong> {{ selectedRound.id }}</p>
        <div class="json-viewer">
          <pre>{{ formattedDetails }}</pre>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { ElMessage } from 'element-plus'

interface GameRound {
  id: number
  user_id: number
  game_name: string
  bet_amount: string
  payout_amount: string
  result: string
  result_data: any
  created_at: string
  user: {
    username: string
  }
}

const rounds = ref<GameRound[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)
const searchQuery = ref('')
const dateRange = ref('')

// Details Dialog
const detailsDialogVisible = ref(false)
const selectedRound = ref<GameRound | null>(null)

const formattedDetails = computed(() => {
  if (!selectedRound.value || !selectedRound.value.result_data) return '{}'
  try {
    return JSON.stringify(selectedRound.value.result_data, null, 2)
  } catch (e) {
    return String(selectedRound.value.result_data)
  }
})

const viewDetails = (round: GameRound) => {
  selectedRound.value = round
  detailsDialogVisible.value = true
}

const fetchRounds = async (page = 1) => {
  loading.value = true
  try {
    let url = `/api/admin/game-rounds?page=${page}`
    
    if (searchQuery.value) {
      url += `&username=${encodeURIComponent(searchQuery.value)}`
    }
    
    if (dateRange.value && dateRange.value.length === 2) {
      url += `&start_date=${dateRange.value[0]}&end_date=${dateRange.value[1]}`
    }

    const response = await axios.get(url)
    
    rounds.value = response.data.data
    total.value = response.data.total
    pageSize.value = response.data.per_page
    currentPage.value = response.data.current_page
  } catch (error) {
    console.error('Error fetching game rounds:', error)
    ElMessage.error('Failed to load game history')
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchRounds(1)
}

const handlePageChange = (page: number) => {
  fetchRounds(page)
}

const getPayoutClass = (row: GameRound) => {
  const bet = Number(row.bet_amount)
  const payout = Number(row.payout_amount)
  return payout >= bet ? 'text-success' : 'text-danger'
}

const getResultType = (row: GameRound) => {
  const bet = Number(row.bet_amount)
  const payout = Number(row.payout_amount)
  return payout > bet ? 'success' : (payout < bet ? 'danger' : 'info')
}

const getResultLabel = (row: GameRound) => {
  const bet = Number(row.bet_amount)
  const payout = Number(row.payout_amount)
  if (payout > bet) return 'WIN'
  if (payout < bet) return 'LOSS'
  return 'DRAW'
}

onMounted(() => {
  fetchRounds()
})
</script>

<style scoped>
.game-rounds-container {
  padding: 20px;
}

.header {
  margin-bottom: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.filter-container {
  display: flex;
  align-items: center;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}

.text-success {
  color: #67c23a;
  font-weight: bold;
}

.text-danger {
  color: #f56c6c;
}

.json-viewer {
  background-color: #f5f7fa;
  padding: 10px;
  border-radius: 4px;
  overflow-x: auto;
}

pre {
  margin: 0;
  font-family: monospace;
}
</style>