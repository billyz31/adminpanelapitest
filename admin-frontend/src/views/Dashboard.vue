<template>
  <div class="dashboard-container">
    <h1>Dashboard</h1>
    
    <!-- System Overview -->
    <h2 class="section-title">System Overview</h2>
    <el-row :gutter="20">
      <el-col :span="6">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>Total Users</span>
            </div>
          </template>
          <div class="card-value">{{ stats.system.total_users }}</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>System Float</span>
            </div>
          </template>
          <div class="card-value">${{ formatMoney(stats.system.total_balance) }}</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>System Status</span>
            </div>
          </template>
          <div class="card-value status-ok">Healthy</div>
        </el-card>
      </el-col>
    </el-row>

    <!-- Today's Statistics -->
    <h2 class="section-title">Today's Statistics</h2>
    <el-row :gutter="20" v-loading="loading">
      <el-col :span="6">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>Total Bet</span>
            </div>
          </template>
          <div class="card-value">${{ formatMoney(stats.today.total_bet) }}</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>Total Payout</span>
            </div>
          </template>
          <div class="card-value">${{ formatMoney(stats.today.total_payout) }}</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>GGR</span>
            </div>
          </template>
          <div class="card-value" :class="getGGRClass(stats.today.ggr)">
            ${{ formatMoney(stats.today.ggr) }}
          </div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="hover">
          <template #header>
            <div class="card-header">
              <span>DAU</span>
            </div>
          </template>
          <div class="card-value">{{ stats.today.dau }}</div>
        </el-card>
      </el-col>
    </el-row>

    <!-- Charts -->
    <el-row :gutter="20" style="margin-top: 20px;">
      <el-col :span="16">
        <h2 class="section-title">Revenue Trends (30 Days)</h2>
        <el-card shadow="hover" v-loading="loadingTrends">
          <div style="height: 400px">
            <v-chart class="chart" :option="chartOption" autoresize />
          </div>
        </el-card>
      </el-col>
      <el-col :span="8">
        <h2 class="section-title">Game Distribution</h2>
        <el-card shadow="hover" v-loading="loadingGames">
          <div style="height: 400px">
            <v-chart class="chart" :option="pieOption" autoresize />
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, reactive } from 'vue'
import axios from '../config/axios'
import { ElMessage } from 'element-plus'

// ECharts
import VChart from "vue-echarts";
import { use } from "echarts/core";
import { CanvasRenderer } from "echarts/renderers";
import { LineChart, BarChart, PieChart } from "echarts/charts";
import {
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent
} from "echarts/components";

use([
  CanvasRenderer,
  LineChart,
  BarChart,
  PieChart,
  TitleComponent,
  TooltipComponent,
  LegendComponent,
  GridComponent
]);

const loading = ref(false)
const loadingTrends = ref(false)
const loadingGames = ref(false)
const chartOption = ref({})
const pieOption = ref({})

const stats = reactive({
  today: {
    total_bet: 0,
    total_payout: 0,
    ggr: 0,
    dau: 0
  },
  system: {
    total_users: 0,
    total_balance: 0
  }
})

const fetchStats = async () => {
  loading.value = true
  try {
    const token = localStorage.getItem('admin_token')
    const response = await axios.get('/api/admin/stats', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })
    
    // Update stats with response data
    if (response.data) {
      stats.today = response.data.today
      stats.system = response.data.system
    }
  } catch (error) {
    console.error('Error fetching stats:', error)
    ElMessage.error('Failed to load dashboard statistics')
  } finally {
    loading.value = false
  }
}

const fetchTrends = async () => {
  loadingTrends.value = true
  try {
    const token = localStorage.getItem('admin_token')
    const response = await axios.get('http://localhost:8001/api/admin/stats/trends', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })

    const data = response.data
    const dates = data.map((item: any) => item.date)
    const bets = data.map((item: any) => item.total_bet)
    const payouts = data.map((item: any) => item.total_payout)
    const ggrs = data.map((item: any) => item.ggr)

    chartOption.value = {
      title: {
        text: 'Betting & Revenue'
      },
      tooltip: {
        trigger: 'axis'
      },
      legend: {
        data: ['Total Bet', 'Total Payout', 'GGR']
      },
      grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
      },
      xAxis: {
        type: 'category',
        boundaryGap: false,
        data: dates
      },
      yAxis: {
        type: 'value'
      },
      series: [
        {
          name: 'Total Bet',
          type: 'line',
          data: bets,
          smooth: true,
          itemStyle: { color: '#409EFF' }
        },
        {
          name: 'Total Payout',
          type: 'line',
          data: payouts,
          smooth: true,
          itemStyle: { color: '#E6A23C' }
        },
        {
          name: 'GGR',
          type: 'bar',
          data: ggrs,
          itemStyle: { 
             color: (params: any) => {
                return params.value >= 0 ? '#67C23A' : '#F56C6C'
             }
          }
        }
      ]
    }
  } catch (error) {
    console.error('Error fetching trends:', error)
    ElMessage.error('Failed to load trends')
  } finally {
    loadingTrends.value = false
  }
}

const fetchGameStats = async () => {
  loadingGames.value = true
  try {
    const response = await axios.get('/admin/stats/games')

    const data = response.data
    const pieData = data.map((item: any) => ({
      name: item.game_type,
      value: item.total_bet
    }))

    pieOption.value = {
      title: {
        text: 'Total Bet by Game',
        left: 'center'
      },
      tooltip: {
        trigger: 'item',
        formatter: '{a} <br/>{b} : ${c} ({d}%)'
      },
      legend: {
        orient: 'vertical',
        left: 'left'
      },
      series: [
        {
          name: 'Total Bet',
          type: 'pie',
          radius: '50%',
          data: pieData,
          emphasis: {
            itemStyle: {
              shadowBlur: 10,
              shadowOffsetX: 0,
              shadowColor: 'rgba(0, 0, 0, 0.5)'
            }
          }
        }
      ]
    }
  } catch (error) {
    console.error('Error fetching game stats:', error)
    ElMessage.error('Failed to load game stats')
  } finally {
    loadingGames.value = false
  }
}

const formatMoney = (amount: number | string) => {
  return Number(amount).toFixed(2)
}

const getGGRClass = (ggr: number) => {
  return ggr >= 0 ? 'text-success' : 'text-danger'
}

onMounted(() => {
  fetchStats()
  fetchTrends()
  fetchGameStats()
})
</script>

<style scoped>
.dashboard-container {
  padding: 20px;
}

.section-title {
  margin: 20px 0;
  font-size: 18px;
  color: #606266;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.card-value {
  font-size: 24px;
  font-weight: bold;
  text-align: center;
  padding: 10px 0;
}

.status-ok {
  color: #67c23a;
}

.text-success {
  color: #67c23a;
}

.text-danger {
  color: #f56c6c;
}
</style>
