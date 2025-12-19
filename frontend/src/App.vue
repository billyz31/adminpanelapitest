<script setup lang="ts">
import ApiTester from './components/ApiTester.vue'
</script>

<template>
  <router-view></router-view>
  <ApiTester />
</template>

<style>
body {
  margin: 0;
  padding: 0;
  font-family: 'Helvetica Neue', Helvetica, 'PingFang SC', 'Hiragino Sans GB', 'Microsoft YaHei', '微软雅黑', Arial, sans-serif;
}
</style>
