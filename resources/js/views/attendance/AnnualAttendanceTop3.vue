<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-row :gutter="20" align="middle">
          <el-col :span="6">
            <el-date-picker
              v-model="selectedYear"
              type="year"
              format="YYYY"
              value-format="YYYY"
              placeholder="Select year"
              @change="fetchReport"
              style="width: 100%"
            />
          </el-col>
          <el-col :span="4">
            <el-button type="primary" @click="fetchReport" :loading="loading">
              <i class="bi bi-search me-1"></i> Generate
            </el-button>
          </el-col>
          <el-col :span="4">
            <el-button type="success" @click="printReport" :disabled="!hasData">
              <i class="bi bi-printer me-1"></i> Print
            </el-button>
          </el-col>
        </el-row>
      </head-controls>
    </div>

    <div v-if="loading" v-loading="loading" style="min-height: 200px"></div>

    <div v-else-if="!hasData && fetched" class="text-center" style="padding: 40px">
      <el-empty description="No attendance data found for the selected year." />
    </div>

    <div v-else id="printable-area">
      <div class="print-header" style="display:none">
        <h2 style="text-align:center; margin-bottom:4px">Annual Attendance Report - Top 3 Per Class</h2>
        <p style="text-align:center; margin:0">Year: {{ selectedYear }}</p>
      </div>

      <el-card
        v-for="(students, className) in report"
        :key="className"
        class="box-card"
        style="margin-bottom: 20px"
      >
        <template #header>
          <div class="card-header">
            <span style="font-weight: bold; font-size: 16px">
              <i class="bi bi-mortarboard-fill me-2"></i>Class: {{ className }}
            </span>
            <el-tag type="success" size="small">Top 4 Students</el-tag>
          </div>
        </template>

        <el-table :data="students" border stripe size="default" style="width: 100%">
          <el-table-column label="#" width="50" align="center">
            <template #default="scope">
              <span :class="['rank-badge', getRankClass(scope.$index)]">
                {{ scope.$index + 1 }}
              </span>
            </template>
          </el-table-column>
          <el-table-column prop="student_name" label="Student Name" min-width="160" />
          <el-table-column prop="parent_name" label="Parent Name" min-width="140" />
          <el-table-column label="Class - Section" min-width="130">
            <template #default="scope">
              {{ scope.row.class_name }}
              <span v-if="scope.row.section_name"> - {{ scope.row.section_name }}</span>
            </template>
          </el-table-column>
          <el-table-column prop="total_present" label="Total Present" width="130" align="center">
            <template #default="scope">
              <el-tag type="success" size="small">{{ scope.row.total_present }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column prop="total_absent" label="Total Absent" width="120" align="center">
            <template #default="scope">
              <el-tag type="danger" size="small">{{ scope.row.total_absent }}</el-tag>
            </template>
          </el-table-column>
          <el-table-column label="Attendance %" width="160" align="center">
            <template #default="scope">
              <el-progress
                :percentage="Number(scope.row.attendance_percentage) || 0"
                :color="getProgressColor(scope.row.attendance_percentage)"
                :stroke-width="12"
                :format="(p) => p + '%'"
              />
            </template>
          </el-table-column>
        </el-table>
      </el-card>
    </div>
  </div>
</template>

<script>
import HeadControls from '@/components/HeadControls.vue';
import { getAnnualAttendanceTop3 } from '@/api/attendance';

export default {
  name: 'AnnualAttendanceTop3',
  components: { HeadControls },
  data() {
    return {
      loading: false,
      fetched: false,
      selectedYear: new Date().getFullYear().toString(),
      report: {},
    };
  },
  computed: {
    hasData() {
      return Object.keys(this.report).length > 0;
    },
  },
  methods: {
    async fetchReport() {
      try {
        this.loading = true;
        this.fetched = false;
        const { data } = await getAnnualAttendanceTop3({ year: this.selectedYear });
        this.report = data.report || {};
        this.fetched = true;
      } catch (error) {
        console.error('Error fetching annual attendance report:', error);
        this.$message.error('Failed to load report. Please try again.');
      } finally {
        this.loading = false;
      }
    },
    getRankClass(index) {
      return ['gold', 'silver', 'bronze'][index] || '';
    },
    getProgressColor(percentage) {
      const p = Number(percentage) || 0;
      if (p >= 90) return '#67c23a';
      if (p >= 75) return '#e6a23c';
      return '#f56c6c';
    },
    printReport() {
      window.print();
    },
  },
  created() {
    this.fetchReport();
  },
};
</script>

<style scoped>
.rank-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  font-weight: bold;
  font-size: 13px;
  color: #fff;
  background: #909399;
}

.rank-badge.gold   { background: #f5a623; }
.rank-badge.silver { background: #8c8c8c; }
.rank-badge.bronze { background: #cd7f32; }

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

@media print {
  .filter-container { display: none !important; }
  .print-header     { display: block !important; }
  .el-card          { break-inside: avoid; page-break-inside: avoid; }
}
</style>
