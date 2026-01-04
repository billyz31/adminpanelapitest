<template>
  <div class="transactions-container">
    <div class="header">
      <h2>Transaction History</h2>
      <div class="filter-container">
        <el-input
          v-model="searchQuery"
          placeholder="Search by username"
          style="width: 200px; margin-right: 10px;"
          @keyup.enter="handleSearch"
          clearable
          @clear="handleSearch"
        />
        <el-select v-model="typeFilter" placeholder="Type" style="width: 150px; margin-right: 10px;" clearable @change="handleSearch">
          <el-option label="Deposit" value="deposit" />
          <el-option label="Withdrawal" value="withdrawal" />
          <el-option label="Bet" value="bet" />
          <el-option label="Payout" value="payout" />
          <el-option label="Manual Add" value="manual_add" />
          <el-option label="Manual Subtract" value="manual_subtract" />
          <el-option label="Adjustment" value="adjustment" />
        </el-select>
        <el-button @click="handleSearch" type="primary">Search</el-button>
      </div>
    </div>

    <el-card shadow="hover">
      <el-table :data="transactions" style="width: 100%" v-loading="loading">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="user.username" label="Username" width="150" />
        <el-table-column prop="type" label="Type" width="120">
          <template #default="scope">
            <el-tag :type="getTypeTag(scope.row.type)">
              {{ scope.row.type.toUpperCase() }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="amount" label="Amount">
          <template #default="scope">
            <span :class="getAmountClass(scope.row)">
              {{ getSign(scope.row) }}${{ Number(scope.row.amount).toFixed(2) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="balance_after" label="Balance After">
          <template #default="scope">
             ${{ Number(scope.row.balance_after).toFixed(2) }}
          </template>
        </el-table-column>
        <el-table-column prop="description" label="Description" show-overflow-tooltip />
        <el-table-column prop="created_at" label="Time" width="180">
          <template #default="scope">
            {{ new Date(scope.row.created_at).toLocaleString() }}
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
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { ElMessage } from 'element-plus'

interface Transaction {
  id: number
  user_id: number
  type: string
  amount: string
  balance_after: string
  description: string
  created_at: string
  user: {
    username: string
  }
}

const transactions = ref<Transaction[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(20)
const total = ref(0)
const searchQuery = ref('')
const typeFilter = ref('')

const fetchTransactions = async (page = 1) => {
  loading.value = true
  try {
    let url = `/api/admin/transactions?page=${page}`
    
    if (searchQuery.value) {
      url += `&username=${encodeURIComponent(searchQuery.value)}`
    }
    
    if (typeFilter.value) {
      url += `&type=${typeFilter.value}`
    }

    const response = await axios.get(url)
    
    transactions.value = response.data.data
    total.value = response.data.total
    pageSize.value = response.data.per_page
    currentPage.value = response.data.current_page
  } catch (error) {
    console.error('Error fetching transactions:', error)
    ElMessage.error('Failed to load transactions')
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchTransactions(1)
}

const handlePageChange = (page: number) => {
  fetchTransactions(page)
}

const getTypeTag = (type: string) => {
  switch (type) {
    case 'deposit': return 'success'
    case 'withdrawal': return 'warning'
    case 'bet': return 'danger'
    case 'payout': return 'success'
    case 'manual_add': return 'success'
    case 'manual_subtract': return 'warning'
    case 'adjustment': return 'info'
    default: return ''
  }
}

const getAmountClass = (row: Transaction) => {
  if (row.type === 'deposit' || row.type === 'payout' || row.type === 'manual_add' || (row.type === 'adjustment' && Number(row.amount) > 0)) {
    return 'text-success'
  }
  return 'text-danger'
}

const getSign = (row: Transaction) => {
   if (row.type === 'deposit' || row.type === 'payout' || row.type === 'manual_add') return '+'
   if (row.type === 'bet' || row.type === 'withdrawal' || row.type === 'manual_subtract') return '-'
   return Number(row.amount) >= 0 ? '+' : ''
}

onMounted(() => {
  fetchTransactions()
})
</script>

<style scoped>
.transactions-container {
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
</style>