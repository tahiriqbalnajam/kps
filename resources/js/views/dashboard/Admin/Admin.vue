<template>
  <div class="dashboard-editor-container">
    <PanelGroup @handleSetLineChartData="handleSetLineChartData" />
  </div>
</template>

<script setup>
import { toRefs, reactive } from 'vue'
import PanelGroup from './components/PanelGroup.vue'
import TransactionTable from './components/TransactionTable.vue'
import TodoList from './components/TodoList/index.vue'

</script>

<style lang="scss" scoped>
.dashboard-editor-container {
  padding: 32px;
  background-color: rgb(240, 242, 245);
  position: relative;

  .github-corner {
    position: absolute;
    top: 0;
    border: 0;
    right: 0;
  }

  .chart-wrapper {
    background: #fff;
    padding: 16px 16px 0;
    margin-bottom: 32px;
  }
}

@media (max-width: 1024px) {
  .chart-wrapper {
    padding: 8px;
  }
}
</style>
