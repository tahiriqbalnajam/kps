<template>
  <div class="view-student-observation">
    <el-card>
      <template #header>
        <div class="card-header">
          <h2>Student Observation Details</h2>
          <el-button 
            icon="Close" 
            circle 
            @click="$router.push({ name: 'student-observations' })"
          />
        </div>
      </template>

      <div v-if="observation">
        <el-row :gutter="20">
          <el-col :xs="24" :md="12">
            <el-card class="info-card">
              <template #header>
                <div class="card-header">Basic Information</div>
              </template>
              <el-descriptions :column="1" border>
                <el-descriptions-item label="Student">
                  {{ observation.student ? observation.student.name : 'N/A' }}
                </el-descriptions-item>
                <el-descriptions-item label="Teacher">
                  {{ observation.teacher ? observation.teacher.name : 'N/A' }}
                </el-descriptions-item>
                <el-descriptions-item label="Date">
                  {{ formatDate(observation.observation_date) }}
                </el-descriptions-item>
              </el-descriptions>
            </el-card>
          </el-col>
          
          <el-col :xs="24" :md="12">
            <el-card class="info-card">
              <template #header>
                <div class="card-header">Comments</div>
              </template>
              <p>{{ observation.comments || 'No comments provided.' }}</p>
            </el-card>
          </el-col>
        </el-row>

        <el-card class="mt-4">
          <template #header>
            <div class="card-header">Observation Details</div>
          </template>
          <el-tabs v-model="activeTab">
            <el-tab-pane label="Academic" name="academic">
              <el-table :data="getDetailsByCategory('academic')" border>
                <el-table-column prop="parameter" label="Parameter">
                  <template #default="scope">
                    {{ formatParameter(scope.row.parameter) }}
                  </template>
                </el-table-column>
                <el-table-column prop="value" label="Rating">
                  <template #default="scope">
                    <el-tag :type="getRatingType(scope.row.value)">
                      {{ formatValue(scope.row.value) }}
                    </el-tag>
                  </template>
                </el-table-column>
              </el-table>
            </el-tab-pane>
            
            <el-tab-pane label="Social" name="social">
              <el-table :data="getDetailsByCategory('social')" border>
                <el-table-column prop="parameter" label="Parameter">
                  <template #default="scope">
                    {{ formatParameter(scope.row.parameter) }}
                  </template>
                </el-table-column>
                <el-table-column prop="value" label="Rating">
                  <template #default="scope">
                    <el-tag :type="getRatingType(scope.row.value)">
                      {{ formatValue(scope.row.value) }}
                    </el-tag>
                  </template>
                </el-table-column>
              </el-table>
            </el-tab-pane>
            
            <el-tab-pane label="Health" name="health">
              <el-table :data="getDetailsByCategory('health')" border>
                <el-table-column prop="parameter" label="Parameter">
                  <template #default="scope">
                    {{ formatParameter(scope.row.parameter) }}
                  </template>
                </el-table-column>
                <el-table-column prop="value" label="Rating">
                  <template #default="scope">
                    <el-tag :type="getRatingType(scope.row.value)">
                      {{ formatValue(scope.row.value) }}
                    </el-tag>
                  </template>
                </el-table-column>
              </el-table>
            </el-tab-pane>
            
            <el-tab-pane label="General" name="general">
              <el-table :data="getDetailsByCategory('general')" border>
                <el-table-column prop="parameter" label="Parameter">
                  <template #default="scope">
                    {{ formatParameter(scope.row.parameter) }}
                  </template>
                </el-table-column>
                <el-table-column prop="value" label="Rating">
                  <template #default="scope">
                    <el-tag :type="getRatingType(scope.row.value)">
                      {{ formatValue(scope.row.value) }}
                    </el-tag>
                  </template>
                </el-table-column>
              </el-table>
            </el-tab-pane>
          </el-tabs>
        </el-card>

        <div class="action-buttons mt-4">
          <el-button 
            type="primary" 
            @click="$router.push({ name: 'edit-student-observation', params: { id: observation.id } })"
          >
            <el-icon><Edit /></el-icon> Edit
          </el-button>
          <el-button 
            type="info" 
            @click="printObservation"
          >
            <el-icon><Printer /></el-icon> Print
          </el-button>
        </div>
      </div>

      <el-skeleton v-else :rows="10" animated />
    </el-card>
  </div>
</template>

<script>
import { Edit, Printer } from '@element-plus/icons-vue'

export default {
  name: 'ViewStudentObservation',
  components: {
    Edit,
    Printer
  },
  data() {
    return {
      observation: null,
      activeTab: 'academic'
    };
  },
  created() {
    this.fetchObservation();
  },
  methods: {
    async fetchObservation() {
      try {
        const response = await this.axios.get(`/api/student-observations/${this.$route.params.id}`);
        this.observation = response.data;
      } catch (error) {
        this.$message.error('Failed to fetch observation');
        console.error(error);
      }
    },
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString();
    },
    getDetailsByCategory(category) {
      if (!this.observation || !this.observation.details) return [];
      return this.observation.details.filter(detail => detail.category === category);
    },
    formatParameter(param) {
      return param.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    },
    formatValue(value) {
      return value.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
    },
    getRatingType(value) {
      const typeMap = {
        'excellent': 'success',
        'good': 'success',
        'satisfactory': 'warning',
        'needs_improvement': 'warning',
        'poor': 'danger'
      };
      return typeMap[value] || 'info';
    },
    printObservation() {
      window.print();
    }
  }
};
</script>

<style scoped>
.view-student-observation {
  margin: 20px;
}
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-weight: bold;
}
.info-card {
  height: 100%;
  margin-bottom: 20px;
}
.mt-4 {
  margin-top: 20px;
}
.action-buttons {
  display: flex;
  gap: 10px;
}

@media print {
  .el-button {
    display: none !important;
  }
}
</style>
