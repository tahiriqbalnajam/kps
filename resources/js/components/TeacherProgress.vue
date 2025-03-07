<template>
  <div class="progress-container">
    <div class="chart-filters mb-4">
      <el-select v-model="selectedYear" @change="fetchData" placeholder="Select Year">
        <el-option
          v-for="year in availableYears"
          :key="year"
          :label="year"
          :value="year"
        />
      </el-select>
    </div>

    <el-card class="chart-card" v-loading="loading">
      <template #header>
        <div class="chart-header">
          <h3>Teacher Performance Analysis - {{ selectedYear }}</h3>
          <div class="chart-type-toggle">
            <el-radio-group v-model="chartType" size="small" @change="updateChart">
              <el-radio-button label="bar">Bar Chart</el-radio-button>
              <el-radio-button label="line">Line Chart</el-radio-button>
            </el-radio-group>
          </div>
        </div>
      </template>
      
      <div class="chart-container">
        <canvas ref="chartCanvas" :id="'chart-' + teacherId"></canvas>
      </div>

      <!-- Debug Info - Can be removed in production -->
      <div v-if="debugInfo" class="debug-info">
        <h4>Debug Info:</h4>
        <pre>{{ JSON.stringify(progressData, null, 2) }}</pre>
      </div>

      <div class="chart-legend-container">
        <div class="chart-legend">
          <div class="legend-item">
            <span class="legend-color" style="background: rgba(75, 192, 192, 0.5)"></span>
            <span>Excellent (≥4)</span>
          </div>
          <div class="legend-item">
            <span class="legend-color" style="background: rgba(255, 205, 86, 0.5)"></span>
            <span>Satisfactory (2.5-4)</span>
          </div>
          <div class="legend-item">
            <span class="legend-color" style="background: rgba(255, 99, 132, 0.5)"></span>
            <span>Needs Improvement (<2.5)</span>
          </div>
          <div class="legend-item">
            <span class="legend-color" style="background: rgba(54, 162, 235, 0.2)"></span>
            <span>Test Performance</span>
          </div>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script>
import Chart from 'chart.js/auto';
import Resource from '@/api/resource';

export default {
  name: 'TeacherProgress',
  props: {
    teacherId: {
      type: [Number, String],
      required: true
    }
  },
  data() {
    return {
      loading: false,
      chart: null,
      chartType: 'bar',
      selectedYear: new Date().getFullYear(),
      availableYears: [],
      progressData: [],
      debugInfo: false // For debugging - set to false in production
    }
  },
  created() {
    // Generate last 5 years for selection
    const currentYear = new Date().getFullYear();
    this.availableYears = Array.from({length: 5}, (_, i) => currentYear - i);
  },
  mounted() {
    // Add some delay to ensure DOM is ready
    setTimeout(() => {
      const teacherId = this.$route.params.id;
      this.fetchData(teacherId);
    }, 100);
  },
  methods: {
    async fetchData(teacherId) {
      this.loading = true;
      try {
        const resource = new Resource('teacher-observations');
        const response = await resource.get(`progress/${teacherId}?year=${this.selectedYear}`);
        
        if (response && response.data && response.data.progress) {
          this.progressData = response.data.progress;
          console.log("Progress Data:", this.progressData);
          
          // Give DOM time to update before rendering chart
          this.$nextTick(() => {
            this.updateChart();
          });
        } else {
          console.error('Invalid response format:', response);
          this.$message.error('Invalid data format received from server');
        }
      } catch (error) {
        console.error('Failed to fetch progress data:', error);
        this.$message.error('Failed to load progress data');
      } finally {
        this.loading = false;
      }
    },
    updateChart() {
      console.log("Updating chart with data:", this.progressData);
      
      // Completely remove any existing chart instance
      if (this.chart) {
        this.chart.destroy();
        this.chart = null;
      }

      // Clear the canvas content
      this.$nextTick(() => {
        const chartCanvas = this.$refs.chartCanvas;
        if (!chartCanvas) {
          console.error('Chart canvas reference not found');
          return;
        }

        // Get the canvas context and clear it
        const ctx = chartCanvas.getContext('2d');
        if (!ctx) {
          console.error('Failed to get 2D context from canvas');
          return;
        }
        
        // Clear the entire canvas
        ctx.clearRect(0, 0, chartCanvas.width, chartCanvas.height);
        
        // Create a new chart instance after a short delay to ensure proper cleanup
        setTimeout(() => {
          try {
            const labels = this.progressData.map(item => item.month);
            
            // Convert string values to numbers and ensure we have valid numbers
            const observationScores = this.progressData.map(item => {
              const score = parseFloat(item.observation_score);
              return isNaN(score) ? 0 : score;
            });
            
            const testScores = this.progressData.map(item => {
              const score = parseFloat(item.test_score);
              return isNaN(score) ? 0 : score;
            });

            // Generate colors based on score values
            const observationBackgroundColors = observationScores.map(score => {
              if (score < 2.5) return 'rgba(255, 99, 132, 0.5)';  // Red for low scores
              if (score < 4) return 'rgba(255, 205, 86, 0.5)';    // Yellow for medium scores
              return 'rgba(75, 192, 192, 0.5)';                   // Green for high scores
            });
            
            const observationBorderColors = observationScores.map(score => {
              if (score < 2.5) return 'rgb(255, 99, 132)';        // Red border
              if (score < 4) return 'rgb(255, 205, 86)';          // Yellow border
              return 'rgb(75, 192, 192)';                         // Green border
            });

            // Calculate appropriate max value for Y axis
            const maxObservationScore = Math.max(...observationScores, 5);
            const yMax = maxObservationScore > 5 ? maxObservationScore + 1 : 5;

            // Create a new chart with the current type
            this.chart = new Chart(ctx, {
              type: this.chartType,
              data: {
                labels: labels,
                datasets: [
                  {
                    label: 'Observation Score (out of 5)',
                    data: observationScores,
                    backgroundColor: this.chartType === 'bar' ? observationBackgroundColors : 'rgba(75, 192, 192, 0.2)',
                    borderColor: this.chartType === 'bar' ? observationBorderColors : 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    tension: 0.4,
                    yAxisID: 'y',
                    borderRadius: this.chartType === 'bar' ? 6 : 0
                  },
                  {
                    label: 'Test Performance (%)',
                    data: testScores,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    tension: 0.4,
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
                    display: false // We're using custom legend
                  },
                  tooltip: {
                    callbacks: {
                      label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                          label += ': ';
                        }
                        if (context.parsed.y !== null) {
                          if (context.datasetIndex === 0) {
                            const score = context.parsed.y;
                            let ratingText = '';
                            if (score < 2.5) ratingText = ' (Needs Improvement)';
                            else if (score < 4) ratingText = ' (Satisfactory)';
                            else ratingText = ' (Excellent)';
                            
                            label += score.toFixed(2) + ' / 5' + ratingText;
                          } else {
                            label += context.parsed.y.toFixed(2) + '%';
                          }
                        }
                        return label;
                      }
                    }
                  }
                }
              }
            });
            console.log("Chart successfully created");
          } catch (error) {
            console.error('Error creating chart:', error);
          }
        }, 50); // Short delay for cleanup
      });
    }
  },
  watch: {
    chartType() {
      // Implement more robust chart recreation for type changes
      if (this.chart) {
        this.chart.destroy();
        this.chart = null;
      }
      this.$nextTick(() => {
        this.updateChart();
      });
    },
    teacherId: {
      handler(newVal) {
        if (newVal) {
          this.fetchData(newVal);
        }
      },
      immediate: true
    }
  },
  beforeUnmount() {
    if (this.chart) {
      this.chart.destroy();
      this.chart = null;
    }
  }
}
</script>

<style scoped>
.progress-container {
  padding: 20px;
}

.chart-card {
  margin-top: 20px;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.chart-header h3 {
  margin: 0;
  font-size: 16px;
  color: #606266;
}

.chart-container {
  position: relative;
  height: 400px;
  width: 100%;
  margin: 20px 0;
}

.chart-legend-container {
  margin-top: 20px;
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
  margin: 5px;
}

.legend-color {
  width: 20px;
  height: 20px;
  border-radius: 4px;
}

.mb-4 {
  margin-bottom: 1rem;
}

.debug-info {
  margin-top: 20px;
  padding: 10px;
  background-color: #f8f9fa;
  border-radius: 4px;
  font-size: 12px;
  overflow-x: auto;
}

/* Ensure the canvas is visible */
canvas {
  border: 1px solid #ebeef5;
}
</style>
