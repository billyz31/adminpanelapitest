<template>
  <div class="admin-dashboard">
    <el-container>
      <el-header class="header">
        <div class="logo">Admin Panel</div>
        <div class="header-right">
          <span>Welcome, Admin</span>
          <el-button type="danger" size="small" @click="logout">Logout</el-button>
        </div>
      </el-header>
      
      <el-main>
        <!-- Stats Cards -->
        <el-row :gutter="20" class="mb-4">
          <el-col :span="6">
            <el-card shadow="hover">
              <template #header>Total Users</template>
              <div class="stat-value">{{ stats.total_users || 0 }}</div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card shadow="hover">
              <template #header>Total Deposits</template>
              <div class="stat-value text-success">${{ stats.total_deposits || 0 }}</div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card shadow="hover">
              <template #header>Total Withdrawals</template>
              <div class="stat-value text-danger">${{ stats.total_withdrawals || 0 }}</div>
            </el-card>
          </el-col>
          <el-col :span="6">
            <el-card shadow="hover">
              <template #header>Game Rounds</template>
              <div class="stat-value">{{ stats.total_game_rounds || 0 }}</div>
            </el-card>
          </el-col>
        </el-row>

        <!-- User Management -->
        <el-card class="box-card">
          <template #header>
            <div class="card-header">
              <span>User Management</span>
              <div class="search-box">
                <el-input
                  v-model="searchQuery"
                  placeholder="Search username/email..."
                  clearable
                  @clear="handleSearch"
                  @keyup.enter="handleSearch"
                >
                  <template #append>
                    <el-button @click="handleSearch">Search</el-button>
                  </template>
                </el-input>
              </div>
            </div>
          </template>

          <el-table :data="users" v-loading="loading" style="width: 100%" border stripe>
            <el-table-column prop="id" label="ID" width="60" />
            <el-table-column prop="username" label="Username" />
            <el-table-column prop="name" label="Name" />
            <el-table-column prop="email" label="Email" />
            <el-table-column prop="balance" label="Balance" sortable>
              <template #default="scope">
                <span class="balance">${{ Number(scope.row.balance).toFixed(2) }}</span>
              </template>
            </el-table-column>
            <el-table-column prop="is_active" label="Status" width="100">
              <template #default="scope">
                <el-tag :type="scope.row.is_active ? 'success' : 'danger'">
                  {{ scope.row.is_active ? 'Active' : 'Banned' }}
                </el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="created_at" label="Joined" width="180">
              <template #default="scope">
                {{ new Date(scope.row.created_at).toLocaleString() }}
              </template>
            </el-table-column>
            <el-table-column label="Actions" width="250" fixed="right">
              <template #default="scope">
                <el-button-group>
                  <el-button 
                    size="small" 
                    type="primary" 
                    @click="openTransactionDialog(scope.row, 'deposit')"
                  >
                    Deposit
                  </el-button>
                  <el-button 
                    size="small" 
                    type="warning" 
                    @click="openTransactionDialog(scope.row, 'withdraw')"
                  >
                    Withdraw
                  </el-button>
                  <el-button 
                    size="small" 
                    :type="scope.row.is_active ? 'danger' : 'success'"
                    @click="toggleUserStatus(scope.row)"
                  >
                    {{ scope.row.is_active ? 'Ban' : 'Unban' }}
                  </el-button>
                </el-button-group>
              </template>
            </el-table-column>
          </el-table>

          <div class="pagination-container">
            <el-pagination
              v-model:current-page="currentPage"
              v-model:page-size="pageSize"
              :total="totalUsers"
              layout="total, prev, pager, next"
              @current-change="handlePageChange"
            />
          </div>
        </el-card>
      </el-main>
    </el-container>

    <!-- Transaction Dialog -->
    <el-dialog
      v-model="transactionDialogVisible"
      :title="transactionType === 'deposit' ? 'Manual Deposit' : 'Manual Withdrawal'"
      width="30%"
    >
      <el-form :model="transactionForm" label-width="100px">
        <el-form-item label="User">
          <el-input v-model="selectedUser.username" disabled />
        </el-form-item>
        <el-form-item label="Amount">
          <el-input-number v-model="transactionForm.amount" :min="1" :precision="2" :step="100" style="width: 100%" />
        </el-form-item>
        <el-form-item label="Description">
          <el-input v-model="transactionForm.description" placeholder="Reason for adjustment" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="transactionDialogVisible = false">Cancel</el-button>
          <el-button type="primary" @click="submitTransaction" :loading="submitting">
            Confirm
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'
import { ElMessage, ElMessageBox } from 'element-plus'

const authStore = useAuthStore()
const router = useRouter()

// Data
const users = ref<any[]>([])
const stats = ref<any>({})
const loading = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const pageSize = ref(20)
const totalUsers = ref(0)

// Transaction Dialog
const transactionDialogVisible = ref(false)
const transactionType = ref<'deposit' | 'withdraw'>('deposit')
const selectedUser = ref<any>({})
const submitting = ref(false)
const transactionForm = reactive({
  amount: 100,
  description: ''
})

// Methods
const fetchStats = async () => {
  try {
    const res = await axios.get('/api/admin/stats')
    stats.value = res.data
  } catch (error) {
    console.error('Failed to fetch stats:', error)
  }
}

const fetchUsers = async () => {
  loading.value = true
  try {
    const res = await axios.get('/api/admin/users', {
      params: {
        page: currentPage.value,
        search: searchQuery.value
      }
    })
    users.value = res.data.data
    totalUsers.value = res.data.total
    pageSize.value = res.data.per_page
  } catch (error) {
    ElMessage.error('Failed to fetch users')
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchUsers()
}

const handlePageChange = (page: number) => {
  currentPage.value = page
  fetchUsers()
}

const toggleUserStatus = async (user: any) => {
  const action = user.is_active ? 'Ban' : 'Unban'
  try {
    await ElMessageBox.confirm(
      `Are you sure you want to ${action} user ${user.username}?`,
      'Warning',
      {
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }
    )
    
    await axios.post(`/api/admin/users/${user.id}/status`, {
      is_active: !user.is_active
    })
    
    ElMessage.success(`User ${action}ned successfully`)
    fetchUsers() // Refresh list
  } catch (error: any) {
    if (error !== 'cancel') {
      ElMessage.error('Operation failed')
    }
  }
}

const openTransactionDialog = (user: any, type: 'deposit' | 'withdraw') => {
  selectedUser.value = user
  transactionType.value = type
  transactionForm.amount = 100
  transactionForm.description = ''
  transactionDialogVisible.value = true
}

const submitTransaction = async () => {
  if (transactionForm.amount <= 0) {
    ElMessage.warning('Invalid amount')
    return
  }
  
  submitting.value = true
  try {
    await axios.post(`/api/admin/users/${selectedUser.value.id}/balance`, {
      amount: transactionForm.amount,
      type: transactionType.value,
      description: transactionForm.description
    })
    
    ElMessage.success(`${transactionType.value === 'deposit' ? 'Deposit' : 'Withdrawal'} successful`)
    transactionDialogVisible.value = false
    fetchUsers()
    fetchStats()
  } catch (error: any) {
    ElMessage.error(error.response?.data?.message || 'Transaction failed')
  } finally {
    submitting.value = false
  }
}

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  fetchStats()
  fetchUsers()
})
</script>

<style scoped>
.admin-dashboard {
  min-height: 100vh;
  background-color: #f5f7fa;
}

.header {
  background-color: #fff;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  padding: 0 20px;
}

.logo {
  font-size: 20px;
  font-weight: bold;
  color: #409EFF;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 15px;
}

.mb-4 {
  margin-bottom: 20px;
}

.stat-value {
  font-size: 24px;
  font-weight: bold;
  text-align: center;
}

.text-success { color: #67C23A; }
.text-danger { color: #F56C6C; }

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.search-box {
  width: 300px;
}

.balance {
  font-family: monospace;
  font-weight: bold;
}

.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: flex-end;
}
</style>