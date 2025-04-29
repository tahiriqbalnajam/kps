<template>
  <div class="app-container">
    <div class="filter-container">
      <el-date-picker
        v-model="dateRange"
        type="daterange"
        range-separator="To"
        start-placeholder="Start date"
        end-placeholder="End date"
        :picker-options="pickerOptions"
        @change="fetchData"
      />
      <el-button
        class="filter-item"
        style="margin-left: 10px;"
        type="primary"
        icon="el-icon-refresh"
        @click="fetchData"
      >
        Refresh
      </el-button>
    </div>

    <el-card>
      <div slot="header" class="clearfix">
        <span>Daily Attendance Statistics</span>
      </div>
      <div v-loading="loading" class="chart-container">
        <canvas id="attendanceChart" height="400"></canvas>
      </div>
    </el-card>
  </div>
</template>

<script>
import Chart from 'chart.js/auto';
import { parseTime } from '@/utils';
import { getDailyAttendanceGraph } from '@/api/attendance';

export default {
  name: 'DailyAttendanceGraph',
  data() {
    return {
      loading: false,
      chart: null,
      query: {
        start_date: null,
        end_date: null,
      },
      dateRange: [
        new Date(new Date().setDate(new Date().getDate() - 30)),
        new Date()
      ],
      pickerOptions: {
        shortcuts: [
          {
            text: 'Last week',
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
              picker.$emit('pick', [start, end]);
            }
          },
          {
            text: 'Last month',
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
              picker.$emit('pick', [start, end]);
            }
          },
          {
            text: 'Last 3 months',
            onClick(picker) {
              const end = new Date();
              const start = new Date();
              start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
              picker.$emit('pick', [start, end]);
            }
          }
        ]
      }
    };
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    async fetchData() {
      if (!this.dateRange || this.dateRange.length !== 2) {
        this.$message.error('Please select a date range');
        return;
      }

      this.loading = true;
      try {
        this.query.start_date = parseTime(this.dateRange[0], '{y}-{m}-{d}');
        this.query.end_date = parseTime(this.dateRange[1], '{y}-{m}-{d}');
        
        const response = await getDailyAttendanceGraph(this.query);

        this.renderChart(response.data.attendanceData);
      } catch (error) {
        console.error(error);
        this.$message.error('Failed to fetch attendance data');
      } finally {
        this.loading = false;
      }
    },
    renderChart(data) {
      const ctx = document.getElementById('attendanceChart');
      
      if (this.chart) {
        this.chart.destroy();
      }
      
      this.chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.dates,
          datasets: [
            {
              label: 'Present Students',
              data: data.present,
              backgroundColor: 'rgba(75, 192, 192, 0.2)',
              borderColor: 'rgba(75, 192, 192, 1)',
              borderWidth: 2,
              fill: false,
              tension: 0.1
            },
            {
              label: 'Absent Students',
              data: data.absent,
              backgroundColor: 'rgba(255, 99, 132, 0.2)',
              borderColor: 'rgba(255, 99, 132, 1)',
              borderWidth: 2,
              fill: false,
              tension: 0.1
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true
              },
              scaleLabel: {
                display: true,
                labelString: 'Number of Students'
              }
            }],
            xAxes: [{
              scaleLabel: {
                display: true,
                labelString: 'Date'
              }
            }]
          },
          tooltips: {
            mode: 'index',
            intersect: false,
          }
        }
      });
    }
  }
};
</script>

<style scoped>
.chart-container {
  position: relative;
  height: 400px;
  width: 100%;
}
.filter-container {
  padding-bottom: 10px;
  display: flex;
  align-items: center;
}
</style>
