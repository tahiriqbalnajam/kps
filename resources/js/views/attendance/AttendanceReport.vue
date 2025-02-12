<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-row :gutter="20">
          <el-col :span="6">
            <el-date-picker
              v-model="selectedMonth"
              type="month"
              format="YYYY-MM"
              value-format="YYYY-MM"
              placeholder="Select month"
              @change="fetchAttendanceReport"
            />
          </el-col>
        </el-row>
      </head-controls>
    </div>

    <!-- Top Punctual Students Card -->
    <el-card class="box-card" v-loading="loading">
      <template #header>
        <div class="card-header">
          <span>Top 3 Most Punctual Students</span>
        </div>
      </template>
      <el-row :gutter="20">
        <el-col :span="8" v-for="(student, index) in topPunctual" :key="student.student_id">
          <el-card :class="['rank-card', getRankClass(index)]">
            <div class="rank-number">#{{ index + 1 }}</div>
            <h3>{{ student.students.name }}</h3>
            <p>Class: {{ student.students.stdclasses.name }}</p>
            <p>Roll No: {{ student.students.roll_no }}</p>
            <p>Present Days: {{ student.present_count }}</p>
          </el-card>
        </el-col>
      </el-row>
    </el-card>

    <!-- Class-wise Reports -->
    <el-row :gutter="20" style="margin-top: 20px">
      <el-col :span="12" v-for="(classData, className) in classReports" :key="className">
        <el-card class="box-card" v-loading="loading">
          <template #header>
            <div class="card-header">
              <span>Class: {{ className }}</span>
            </div>
          </template>

          <!-- Most Absent Students -->
          <div class="section">
            <h4>Most Absent Students</h4>
            <el-table :data="classData.absentees" size="small">
              <el-table-column prop="students.name" label="Name" />
              <el-table-column prop="students.roll_no" label="Roll No" width="100" />
              <el-table-column prop="absent_count" label="Absent Days" width="120" />
            </el-table>
          </div>

          <el-divider />

          <!-- Most Present Students -->
          <div class="section">
            <h4>Most Present Students</h4>
            <el-table :data="classData.punctual" size="small">
              <el-table-column prop="students.name" label="Name" />
              <el-table-column prop="students.roll_no" label="Roll No" width="100" />
              <el-table-column prop="present_count" label="Present Days" width="120" />
            </el-table>
          </div>
        </el-card>
      </el-col>
    </el-row>
  </div>
</template>

<script>
import HeadControls from '@/components/HeadControls.vue';
import { attendanceSummary } from '@/api/attendance';

export default {
  name: 'AttendanceReport',
  components: { HeadControls },
  data() {
    return {
      loading: false,
      selectedMonth: new Date().toISOString().slice(0, 7),
      topPunctual: [],
      classReports: {},
    }
  },
  methods: {
    async fetchAttendanceReport() {
      try {
        this.loading = true;
        const { data } = await attendanceSummary({ month: this.selectedMonth });
        
        // Process top punctual students
        this.topPunctual = data.summary.top_punctual;
        
        // Process class-wise reports
        this.classReports = {};
        Object.entries(data.summary.absentees).forEach(([classId, absentees]) => {
          const className = absentees[0]?.classes?.name || `Class ${classId}`;
          this.classReports[className] = {
            absentees: absentees,
            punctual: data.summary.punctual[classId] || []
          };
        });
      } catch (error) {
        console.error('Error fetching attendance report:', error);
      } finally {
        this.loading = false;
      }
    },
    getRankClass(index) {
      const classes = ['gold', 'silver', 'bronze'];
      return classes[index] || '';
    }
  },
  created() {
    this.fetchAttendanceReport();
  }
}
</script>

<style scoped>
.rank-card {
  text-align: center;
  position: relative;
  margin-bottom: 20px;
}

.rank-number {
  position: absolute;
  top: 5px;
  left: 5px;
  background: #409EFF;
  color: white;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

.gold {
  background: #fdf6e7;
  border: 1px solid #f5dab1;
}

.silver {
  background: #f8f9fa;
  border: 1px solid #dee2e6;
}

.bronze {
  background: #fff5f2;
  border: 1px solid #ffe0d3;
}

.section {
  margin-bottom: 20px;
}

.section h4 {
  margin-bottom: 15px;
  color: #606266;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
