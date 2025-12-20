<template>
  <div class="admin-dashboard">
    <div class="header">
      <h1>Admin Dashboard</h1>
      <button @click="logout" class="logout-btn">Logout</button>
    </div>

    <div v-if="loading" class="loading">Loading...</div>
    <div v-else>
      <div class="stats-grid">
        <div class="stat-card">
          <h3>Total Users</h3>
          <p>{{ stats.total_users }}</p>
        </div>
        <div class="stat-card">
          <h3>Total Deposits</h3>
          <p>${{ stats.total_deposits }}</p>
        </div>
        <div class="stat-card">
          <h3>Total Withdrawals</h3>
          <p>${{ stats.total_withdrawals }}</p>
        </div>
        <div class="stat-card">
          <h3>Total Games</h3>
          <p>{{ stats.total_game_rounds }}</p>
        </div>
      </div>

      <div class="users-section">
        <h2>Registered Users</h2>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Balance</th>
                <th>Active</th>
                <th>Joined</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users" :key="user.id">
                <td>{{ user.id }}</td>
                <td>{{ user.username }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>
                  <span :class="['role-badge', user.role]">{{ user.role }}</span>
                </td>
                <td>${{ user.balance }}</td>
                <td>
                  <span :class="['status-badge', user.is_active ? 'active' : 'inactive']">
                    {{ user.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td>{{ new Date(user.created_at).toLocaleDateString() }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()
const users = ref<any[]>([])
const stats = ref<any>({})
const loading = ref(true)

const fetchData = async () => {
  try {
    const [usersRes, statsRes] = await Promise.all([
      axios.get('/api/admin/users'),
      axios.get('/api/admin/stats')
    ])
    users.value = usersRes.data
    stats.value = statsRes.data
  } catch (error) {
    console.error('Failed to fetch admin data:', error)
  } finally {
    loading.value = false
  }
}

const logout = async () => {
  await authStore.logout()
  router.push('/login')
}

onMounted(() => {
  fetchData()
})
</script>

<style scoped>
.admin-dashboard {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.logout-btn {
  background: #ff4757;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #f1f2f6;
  padding: 1.5rem;
  border-radius: 8px;
  text-align: center;
}

.stat-card h3 {
  margin: 0 0 0.5rem;
  color: #2f3542;
  font-size: 1rem;
}

.stat-card p {
  margin: 0;
  font-size: 1.5rem;
  font-weight: bold;
  color: #2ed573;
}

.table-container {
  overflow-x: auto;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #f1f2f6;
}

th {
  background: #f8f9fa;
  font-weight: 600;
}

.role-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.85rem;
  text-transform: capitalize;
}

.role-badge.admin {
  background: #dfe6e9;
  color: #2d3436;
  font-weight: bold;
}

.role-badge.player {
  background: #eccc68;
  color: #2f3542;
}

.status-badge.active {
  color: #2ed573;
}

.status-badge.inactive {
  color: #ff4757;
}
</style>
