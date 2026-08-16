<template>
  <div class="app-container report-page">
    <div class="report-header">
      <el-button circle size="small" class="back-btn" @click="$router.back()">
        <el-icon><ArrowLeft /></el-icon>
      </el-button>
      <h2>Student Report</h2>
    </div>

    <el-row :gutter="16" class="report-row">
      <el-col :xs="24" :sm="12" :md="8" :lg="6" class="report-col">
        <student-info />
      </el-col>
      <el-col :xs="24" :sm="12" :md="16" :lg="9" class="report-col">
        <el-card class="report-card" shadow="never">
          <template #header>
            <div class="card-header"><strong>Attendance Report</strong></div>
          </template>
          <attendance-info />
          <attendance-comments />
        </el-card>
      </el-col>
      <el-col :xs="24" :sm="24" :md="24" :lg="9" class="report-col">
        <test-info />
      </el-col>
    </el-row>
  </div>
</template>

<script>
import { ArrowLeft } from '@element-plus/icons-vue'
import StudentInfo from './components/StudentInfo.vue'
import AttendanceInfo from './components/AttendanceInfo.vue'
import AttendanceComments from './components/AttendanceComments.vue'
import TestInfo from './components/TestInfo.vue'

export default {
  name: 'StudentReport',
  components: {
    ArrowLeft,
    StudentInfo,
    AttendanceInfo,
    AttendanceComments,
    TestInfo
  }
}
</script>

<style scoped>
.report-page {
  /* Consistent card look across all three columns */
  :deep(.el-card) {
    border: 1px solid #e4e7ed;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    height: 100%;
  }

  :deep(.card-header) {
    display: flex;
    align-items: center;
    justify-content: space-between;

    strong {
      font-size: 15px;
      color: #1e293b;
    }
  }
}

.report-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 16px;

  h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
  }
}

.back-btn {
  background: #fff;
  border: 1px solid #e4e7ed;
}

/* Equal-height columns */
.report-col {
  display: flex;

  > * {
    flex: 1;
    width: 100%;
  }
}
</style>
