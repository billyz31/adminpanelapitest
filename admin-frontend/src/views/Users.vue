<template>
  <div class="users-container">
    <div class="header">
      <h2>User Management</h2>
      <div class="filter-container">
        <el-input
          v-model="searchQuery"
          placeholder="Search by username or email"
          style="width: 300px; margin-right: 10px;"
          @keyup.enter="handleSearch"
          clearable
          @clear="handleSearch"
        >
          <template #append>
            <el-button @click="handleSearch">Search</el-button>
          </template>
        </el-input>
        <el-select v-model="statusFilter" placeholder="Filter by Status" style="width: 150px;" clearable @change="handleSearch">
          <el-option label="Active" value="true" />
          <el-option label="Inactive" value="false" />
        </el-select>
      </div>
    </div>

    <el-card shadow="hover">
      <el-table :data="users" style="width: 100%" v-loading="loading">
        <el-table-column prop="id" label="ID" width="80" />
        <el-table-column prop="username" label="Username" width="150" />
        <el-table-column prop="email" label="Email" />
        <el-table-column prop="balance" label="Balance">
          <template #default="scope">
            ${{ Number(scope.row.balance).toFixed(2) }}
          </template>
        </el-table-column>
        <el-table-column prop="created_at" label="Registered Date">
          <template #default="scope">
            {{ new Date(scope.row.created_at).toLocaleDateString() }}
          </template>
        </el-table-column>
        <el-table-column prop="is_active" label="Status" width="100">
          <template #default="scope">
            <el-tag :type="scope.row.is_active ? 'success' : 'danger'">
              {{ scope.row.is_active ? 'Active' : 'Inactive' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="200" fixed="right">
          <template #default="scope">
            <el-button size="small" @click="openBalanceDialog(scope.row)">Edit Balance</el-button>
            <el-button 
              size="small" 
              :type="scope.row.is_active ? 'danger' : 'success'"
              @click="toggleStatus(scope.row)"
            >
              {{ scope.row.is_active ? 'Block' : 'Unblock' }}
            </el-button>
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

    <!-- Balance Edit Dialog -->
    <el-dialog v-model="balanceDialogVisible" title="Edit User Balance" width="400px">
      <el-form :model="balanceForm" label-width="100px">
        <el-form-item label="Username">
          <span>{{ selectedUser?.username }}</span>
        </el-form-item>
        <el-form-item label="Current">
          <span>${{ Number(selectedUser?.balance || 0).toFixed(2) }}</span>
        </el-form-item>
        <el-form-item label="Operation">
          <el-radio-group v-model="balanceForm.type">
            <el-radio label="add">Add (+)</el-radio>
            <el-radio label="subtract">Subtract (-)</el-radio>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="Amount">
          <el-input-number v-model="balanceForm.amount" :min="0.01" :precision="2" :step="10" />
        </el-form-item>
      </el-form>
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="balanceDialogVisible = false">Cancel</el-button>
          <el-button type="primary" @click="submitBalanceUpdate" :loading="actionLoading">Confirm</el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import axios from '../config/axios'
import { ElMessage, ElMessageBox } from 'element-plus'

interface User {
  id: number
  username: string
  email: string
  balance: string
  created_at: string
  is_active: boolean
}

const users = ref<User[]>([])
const loading = ref(false)
const currentPage = ref(1)
const pageSize = ref(10)
const total = ref(0)
const searchQuery = ref('')
const statusFilter = ref('')

// Balance Dialog
const balanceDialogVisible = ref(false)
const selectedUser = ref<User | null>(null)
const actionLoading = ref(false)
const balanceForm = reactive({
  type: 'add',
  amount: 0
})

const fetchUsers = async (page = 1) => {
  loading.value = true
  try {
    const token = localStorage.getItem('admin_token')
    let url = `http://localhost:8001/api/admin/users?page=${page}`
    if (searchQuery.value) {
      url += `&search=${encodeURIComponent(searchQuery.value)}`
    }
    if (statusFilter.value) {
      url += `&status=${statusFilter.value}`
    }

    const response = await axios.get(url, {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
    
    users.value = response.data.data
    total.value = response.data.total
    pageSize.value = response.data.per_page
    currentPage.value = response.data.current_page
  } catch (error) {
    console.error('Error fetching users:', error)
    ElMessage.error('Failed to load users')
  } finally {
    loading.value = false
  }
}

const handleSearch = () => {
  currentPage.value = 1
  fetchUsers(1)
}

const handlePageChange = (page: number) => {
  fetchUsers(page)
}

const openBalanceDialog = (user: User) => {
  selectedUser.value = user
  balanceForm.type = 'add'
  balanceForm.amount = 0
  balanceDialogVisible.value = true
}

const submitBalanceUpdate = async () => {
  if (!selectedUser.value || balanceForm.amount <= 0) {
    ElMessage.warning('Please enter a valid amount')
    return
  }

  actionLoading.value = true
  try {
    const token = localStorage.getItem('admin_token')
    await axios.post(`/api/admin/users/${selectedUser.value.id}/balance`, {
      type: balanceForm.type,
      amount: balanceForm.amount
    }, {
      headers: { Authorization: `Bearer ${token}` }
    })

    ElMessage.success('Balance updated successfully')
    balanceDialogVisible.value = false
    fetchUsers(currentPage.value) // Refresh list
  } catch (error) {
    console.error('Error updating balance:', error)
    ElMessage.error('Failed to update balance')
  } finally {
    actionLoading.value = false
  }
}

const toggleStatus = async (user: User) => {
  const action = user.is_active ? 'block' : 'unblock'
  try {
    await ElMessageBox.confirm(
      `Are you sure you want to ${action} user "${user.username}"?`,
      'Confirm Action',
      {
        confirmButtonText: 'Yes',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }
    )

    await axios.post(`/admin/users/${user.id}/toggle-status`, {})

    ElMessage.success(`User ${action}ed successfully`)
    fetchUsers(currentPage.value)
  } catch (error) {
    if (error !== 'cancel') {
      console.error('Error toggling status:', error)
      ElMessage.error('Failed to update user status')
    }
  }
}

onMounted(() => {
  fetchUsers()
})
</script>

<style scoped>
.users-container {
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
</style>
