<template>
  <div class="layout-container">
    <el-container>
      <el-aside width="200px">
        <el-menu
          default-active="1"
          class="el-menu-vertical"
          :router="true"
        >
          <div class="logo">Admin Panel</div>
          <el-menu-item index="/">
            <el-icon><DataBoard /></el-icon>
            <span>Dashboard</span>
          </el-menu-item>
          <el-menu-item index="/health">
            <el-icon><Monitor /></el-icon>
            <span>System Health</span>
          </el-menu-item>
          <el-menu-item index="/users">
            <el-icon><User /></el-icon>
            <span>Users</span>
          </el-menu-item>
          <el-menu-item index="/game-rounds">
            <el-icon><List /></el-icon>
            <span>Game History</span>
          </el-menu-item>
          <el-menu-item index="/transactions">
            <el-icon><Money /></el-icon>
            <span>Transactions</span>
          </el-menu-item>
          <el-menu-item index="/settings">
            <el-icon><Setting /></el-icon>
            <span>Settings</span>
          </el-menu-item>
        </el-menu>
      </el-aside>
      
      <el-container>
        <el-header>
          <div class="header-content">
            <div class="breadcrumb">
              <!-- Breadcrumb could go here -->
            </div>
            <div class="user-profile">
              <el-dropdown @command="handleCommand">
                <span class="el-dropdown-link">
                  Admin User <el-icon class="el-icon--right"><arrow-down /></el-icon>
                </span>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item command="logout">Logout</el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </div>
          </div>
        </el-header>
        
        <el-main>
          <router-view></router-view>
        </el-main>
      </el-container>
    </el-container>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { DataBoard, User, Setting, ArrowDown, Monitor, List, Money } from '@element-plus/icons-vue'
import axios from 'axios'
import { ElMessage } from 'element-plus'

const router = useRouter()

const handleCommand = async (command: string) => {
  if (command === 'logout') {
    try {
      const token = localStorage.getItem('admin_token')
      if (token) {
        await axios.post('/api/admin/logout', {}, {
          headers: {
            Authorization: `Bearer ${token}`
          }
        })
      }
    } catch (error) {
      console.error('Logout error', error)
    } finally {
      localStorage.removeItem('admin_token')
      localStorage.removeItem('admin_user')
      router.push('/login')
      ElMessage.success('Logged out')
    }
  }
}
</script>

<style scoped>
.layout-container {
  height: 100vh;
}

.el-container {
  height: 100%;
}

.el-aside {
  background-color: #fff;
  border-right: 1px solid #dcdfe6;
}

.logo {
  height: 60px;
  line-height: 60px;
  text-align: center;
  font-size: 18px;
  font-weight: bold;
  background-color: #2c3e50;
  color: white;
}

.el-header {
  background-color: #fff;
  border-bottom: 1px solid #dcdfe6;
  display: flex;
  align-items: center;
  justify-content: flex-end; /* Align right */
}

.header-content {
  width: 100%;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 20px;
}

.el-main {
  background-color: #f0f2f5;
  padding: 20px;
}

.el-dropdown-link {
  cursor: pointer;
  display: flex;
  align-items: center;
  color: #606266;
}
</style>
