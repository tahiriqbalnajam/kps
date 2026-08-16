<template>
  <div v-loading="loading">
    <el-row :gutter="16">
      <el-col :span="12">
        <div ref="chart" class="chart" :style="{ height: height, width: width }" />
      </el-col>
      <el-col :span="6">
        <el-progress
          type="dashboard"
          :percentage="percentPresent"
          :color="'#4f46e5'"
          :stroke-width="8"
        >
          <template #default="{ percentage }">
            <span class="percentage-value">{{ percentage }}%</span>
            <span class="percentage-label">Overall</span>
          </template>
        </el-progress>
        <div class="today-badge">
          <el-tag :type="(attendance.today_status == 'present') ? 'success' : 'danger'" effect="light">
            Today: {{ attendance.today_status || '—' }}
          </el-tag>
        </div>
      </el-col>
      <el-col :span="6">
        <el-progress
          type="dashboard"
          :percentage="averagePresent"
          :color="'#10b981'"
          :stroke-width="8"
        >
          <template #default="{ percentage }">
            <span class="percentage-value">{{ percentage }}%</span>
            <span class="percentage-label">Class Average</span>
          </template>
        </el-progress>
        <div class="today-badge">
          <el-tag type="success" effect="light">
            Yesterday: {{ attendance.yesterday_status || '—' }}
          </el-tag>
        </div>
      </el-col>
    </el-row>

    <el-row :gutter="16" class="stat-row">
      <el-col :span="8">
        <div class="stat-card present">
          <div class="stat-header"><el-icon><Check /></el-icon><span>Presents</span></div>
          <div class="stat-total">{{ attendance.total_present ?? 0 }}</div>
          <div class="stat-sub">This month: {{ attendance.this_month_present ?? 0 }}</div>
        </div>
      </el-col>
      <el-col :span="8">
        <div class="stat-card absent">
          <div class="stat-header"><el-icon><Close /></el-icon><span>Absents</span></div>
          <div class="stat-total">{{ attendance.total_absent ?? 0 }}</div>
          <div class="stat-sub">This month: {{ attendance.this_month_absent ?? 0 }}</div>
        </div>
      </el-col>
      <el-col :span="8">
        <div class="stat-card leave">
          <div class="stat-header"><el-icon><Calendar /></el-icon><span>Leaves</span></div>
          <div class="stat-total">{{ attendance.total_leave ?? 0 }}</div>
          <div class="stat-sub">This month: {{ attendance.this_month_leave ?? 0 }}</div>
        </div>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import * as echarts from 'echarts'
import { getStudentAttTotals } from '@/api/attendance'
import { Check, Close, Calendar } from '@element-plus/icons-vue'

export default {
  name: 'AttendanceInfo',
  components: {
    Check,
    Close,
    Calendar
  },
  data() {
    return {
      loading: false,
      className: 'chart',
      width: '100%',
      height: '300px',
      chart: null,
      attendance: {}
    }
  },
  computed: {
    percentPresent() {
      return Math.round(this.attendance.percent_present || 0)
    },
    averagePresent() {
      return Math.round(this.attendance.average_present || 0)
    }
  },
  mounted() {
    nextTick(() => this.initChart())
    this.getAttendance(this.$route.params.id)
  },
  beforeUnmount() {
    if (this.chart) {
      this.chart.dispose()
      this.chart = null
    }
  },
  methods: {
    initChart() {
      this.chart = echarts.init(this.$refs.chart, 'macarons')
    },
    async getAttendance(stdid) {
      this.loading = true
      try {
        const { data } = await getStudentAttTotals(stdid)
        this.attendance = data.attendance
        this.chart?.setOption({
          tooltip: {
            trigger: 'item',
            formatter: '{b} : {c} ({d}%)'
          },
          toolbox: {
            show: true,
            feature: {
              saveAsImage: { show: true }
            }
          },
          legend: {
            left: 'center',
            top: 'bottom',
            data: ['P', 'A', 'L']
          },
          series: [
            {
              name: 'Attendance Details',
              type: 'pie',
              roseType: 'area',
              radius: [10, 60],
              data: [
                { value: this.attendance.total_present || 0, name: 'P' },
                { value: this.attendance.total_absent || 0, name: 'A' },
                { value: this.attendance.total_leave || 0, name: 'L' }
              ],
              animationEasing: 'cubicInOut',
              animationDuration: 2600
            }
          ]
        })
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.chart {
  width: 100%;
}

.percentage-value {
  display: block;
  font-size: 18px;
  font-weight: 700;
  color: #1e293b;
}

.percentage-label {
  display: block;
  margin-top: 2px;
  font-size: 12px;
  color: #64748b;
}

.today-badge {
  text-align: center;
  margin-top: 12px;
}

.stat-row {
  margin-top: 16px;
}

.stat-card {
  display: flex;
  flex-direction: column;
  gap: 6px;
  border-radius: 10px;
  padding: 14px 16px;
  color: #fff;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.1);
}

.stat-header {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.6px;
  text-transform: uppercase;
  opacity: 0.95;
}

.stat-total {
  font-size: 26px;
  font-weight: 800;
  line-height: 1;
}

.stat-sub {
  font-size: 12px;
  opacity: 0.85;
}

.present {
  background: linear-gradient(135deg, #4f46e5, #6366f1);
}

.absent {
  background: linear-gradient(135deg, #e11d48, #f43f5e);
}

.leave {
  background: linear-gradient(135deg, #7c3aed, #a78bfa);
}
</style>
