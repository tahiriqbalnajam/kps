<template>
  <el-card class="chart-card" shadow="never" v-loading="loading">
    <template #header>
      <div class="chart-header">
        <h3>Teacher Performance Analysis — {{ selectedYear }}</h3>
        <div class="chart-controls">
          <el-select v-model="selectedYear" size="small" @change="fetchData" placeholder="Select Year" style="width: 110px">
            <el-option
              v-for="year in availableYears"
              :key="year"
              :label="year"
              :value="year"
            />
          </el-select>
          <el-radio-group v-model="chartType" size="small" @change="updateChart">
            <el-radio-button label="line">Line</el-radio-button>
            <el-radio-button label="bar">Bar</el-radio-button>
          </el-radio-group>
        </div>
      </div>
    </template>

    <el-empty
      v-if="!loading && progressData.length === 0"
      :description="'No progress data for ' + selectedYear"
      :image-size="90"
    />
    <div v-else class="chart-container">
      <canvas ref="chartCanvas" :id="'chart-' + resolvedTeacherId"></canvas>
    </div>

    <div v-if="progressData.length > 0" class="chart-legend">
      <div class="legend-item">
        <span class="legend-color" style="background: rgba(16, 185, 129, 0.6)"></span>
        <span>Test Performance (%)</span>
      </div>
      <div class="legend-item">
        <span class="legend-color" style="background: rgba(75, 192, 192, 0.5)"></span>
        <span>Observation — Excellent (≥4)</span>
      </div>
      <div class="legend-item">
        <span class="legend-color" style="background: rgba(255, 205, 86, 0.5)"></span>
        <span>Observation — Satisfactory (2.5–4)</span>
      </div>
      <div class="legend-item">
        <span class="legend-color" style="background: rgba(255, 99, 132, 0.5)"></span>
        <span>Observation — Needs Improvement (&lt;2.5)</span>
      </div>
    </div>
  </el-card>
</template>

<script>
import Chart from 'chart.js/auto'
import Resource from '@/api/resource'

export default {
  name: 'TeacherProgress',
  props: {
    teacherId: {
      type: [Number, String],
      default: null
    }
  },
  data() {
    return {
      loading: false,
      chart: null,
      chartType: 'line',
      selectedYear: new Date().getFullYear(),
      availableYears: [],
      progressData: []
    }
  },
  computed: {
    // Accept the prop when passed, fall back to the route param (direct visits)
    resolvedTeacherId() {
      return this.teacherId ?? this.$route.params.id
    }
  },
  created() {
    const currentYear = new Date().getFullYear()
    this.availableYears = Array.from({ length: 5 }, (_, i) => currentYear - i)
  },
  methods: {
    async fetchData() {
      this.loading = true
      try {
        const resource = new Resource('teacher-observations')
        const response = await resource.get(`progress/${this.resolvedTeacherId}?year=${this.selectedYear}`)

        if (response && response.data && response.data.progress) {
          this.progressData = response.data.progress
          this.$nextTick(() => {
            this.updateChart()
          })
        } else {
          this.$message.error('Invalid data format received from server')
        }
      } catch (error) {
        this.$message.error('Failed to load progress data')
      } finally {
        this.loading = false
      }
    },
    updateChart() {
      // Destroy the previous instance so chart.js doesn't stack renders
      if (this.chart) {
        this.chart.destroy()
        this.chart = null
      }

      const chartCanvas = this.$refs.chartCanvas
      if (!chartCanvas) return
      const ctx = chartCanvas.getContext('2d')
      if (!ctx) return

      const labels = this.progressData.map((item) => item.month)

      const observationScores = this.progressData.map((item) => {
        const score = parseFloat(item.observation_score)
        return isNaN(score) ? 0 : score
      })

      const testScores = this.progressData.map((item) => {
        const score = parseFloat(item.test_score)
        return isNaN(score) ? 0 : score
      })

      // Bar mode: each observation bar is colored by its rating band
      const observationBackgroundColors = observationScores.map((score) => {
        if (score < 2.5) return 'rgba(255, 99, 132, 0.5)' // Red for low scores
        if (score < 4) return 'rgba(255, 205, 86, 0.5)'   // Yellow for medium scores
        return 'rgba(75, 192, 192, 0.5)'                  // Green for high scores
      })

      const observationBorderColors = observationScores.map((score) => {
        if (score < 2.5) return 'rgb(255, 99, 132)'
        if (score < 4) return 'rgb(255, 205, 86)'
        return 'rgb(75, 192, 192)'
      })

      // Keep the observation axis capped at 5 unless data exceeds it
      const maxObservationScore = Math.max(...observationScores, 5)
      const yMax = maxObservationScore > 5 ? maxObservationScore + 1 : 5

      this.chart = new Chart(ctx, {
        type: this.chartType,
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Observation Score (out of 5)',
              data: observationScores,
              backgroundColor: this.chartType === 'bar' ? observationBackgroundColors : 'rgba(79, 70, 229, 0.15)',
              borderColor: this.chartType === 'bar' ? observationBorderColors : '#4f46e5',
              borderWidth: this.chartType === 'bar' ? 1 : 2,
              tension: 0.4,
              pointRadius: this.chartType === 'bar' ? 0 : 3,
              pointBackgroundColor: '#4f46e5',
              borderRadius: this.chartType === 'bar' ? 6 : 0,
              yAxisID: 'y'
            },
            {
              label: 'Test Performance (%)',
              data: testScores,
              backgroundColor: 'rgba(16, 185, 129, 0.15)',
              borderColor: '#10b981',
              borderWidth: 2,
              tension: 0.4,
              pointRadius: 3,
              pointBackgroundColor: '#10b981',
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: yMax,
              position: 'left',
              title: {
                display: true,
                text: 'Observation Score (out of 5)'
              }
            },
            y1: {
              beginAtZero: true,
              max: 100,
              position: 'right',
              grid: {
                drawOnChartArea: false
              },
              title: {
                display: true,
                text: 'Test Performance (%)'
              }
            }
          },
          plugins: {
            legend: {
              display: false // custom legend below the chart
            },
            tooltip: {
              callbacks: {
                label(context) {
                  let label = context.dataset.label || ''
                  if (label) label += ': '
                  if (context.parsed.y !== null) {
                    if (context.datasetIndex === 0) {
                      const score = context.parsed.y
                      let ratingText = ''
                      if (score < 2.5) ratingText = ' (Needs Improvement)'
                      else if (score < 4) ratingText = ' (Satisfactory)'
                      else ratingText = ' (Excellent)'
                      label += score.toFixed(2) + ' / 5' + ratingText
                    } else {
                      label += context.parsed.y.toFixed(2) + '%'
                    }
                  }
                  return label
                }
              }
            }
          }
        }
      })
    }
  },
  watch: {
    chartType() {
      this.updateChart()
    },
    resolvedTeacherId: {
      handler(id) {
        if (id) {
          this.fetchData()
        }
      },
      immediate: true
    }
  },
  beforeUnmount() {
    if (this.chart) {
      this.chart.destroy()
      this.chart = null
    }
  }
}
</script>

<style scoped lang="scss">
.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;

  h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
  }
}

.chart-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.chart-container {
  position: relative;
  height: 400px;
  width: 100%;
  padding-top: 20px;
}

.chart-legend {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 20px;
  flex-wrap: wrap;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 12px;
  color: #475569;
}

.legend-color {
  width: 14px;
  height: 14px;
  border-radius: 4px;
  flex-shrink: 0;
}

canvas {
  border: 1px solid #e4e7ed;
  border-radius: 8px;
}
</style>
