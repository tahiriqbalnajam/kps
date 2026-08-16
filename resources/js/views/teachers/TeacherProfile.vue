<template>
  <div class="app-container report-page">
    <div class="report-header">
      <el-button circle size="small" class="back-btn" @click="$router.back()">
        <el-icon><ArrowLeft /></el-icon>
      </el-button>
      <h2>Teacher Profile</h2>
    </div>

    <el-row :gutter="16" class="report-col">
      <el-col :xs="24" :sm="10" :md="8" :lg="6">
        <teacher-info />
      </el-col>
      <el-col :xs="24" :sm="14" :md="16" :lg="18">
        <teacher-tests />
      </el-col>
    </el-row>

    <el-row :gutter="16" class="report-col progress-row">
      <el-col :xs="24" :sm="24" :md="24" :lg="24">
        <teacher-progress :teacher-id="$route.params.id" />
      </el-col>
    </el-row>
  </div>
</template>

<script>
import TeacherTests from './components/TeacherTests.vue'
import TeacherInfo from './components/TeacherInfo.vue'
import TeacherProgress from '@/components/TeacherProgress.vue'
import { ArrowLeft } from '@element-plus/icons-vue'

export default {
  name: 'TeacherProfile',
  components: { TeacherTests, TeacherInfo, TeacherProgress, ArrowLeft }
}
</script>

<style scoped lang="scss">
.report-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;

  h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
  }
}

.back-btn {
  background: #fff;
  color: #4f46e5;
  border: 1px solid #e4e7ed;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);

  &:hover {
    border-color: #4f46e5;
    color: #fff;
    background: #4f46e5;
  }
}

// Consistent card look across the whole report (info, tests, chart)
:deep(.el-card) {
  border: 1px solid #e4e7ed;
  border-radius: 10px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
  height: 100%;
  margin-bottom: 16px;
}

.report-col {
  display: flex;
}

// Gap between the score cards and the chart below: card margins can't do
// this because el-col flex items are sized by the row, so the margin would
// overflow invisibly — the row needs its own margin instead
.progress-row {
  margin-top: 16px;
}
</style>
