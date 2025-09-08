<template>
  <div class="student-observations">
    <el-card>
      <template #header>
        <div class="card-header">
          <span>Student Observations</span>
          <div class="header-actions">
            <el-input
              v-model="search"
              placeholder="Search"
              prefix-icon="el-icon-search"
              style="width: 300px; margin-right: 16px;"
            />
            <el-button 
              type="primary" 
              @click="$router.push({ name: 'create-student-observation' })"
            >
              <el-icon><Plus /></el-icon> New Observation
            </el-button>
          </div>
        </div>
      </template>

      <el-table
        :data="filteredObservations"
        style="width: 100%"
        v-loading="loading"
      >
        <el-table-column prop="student_name" label="Student Name">
          <template #default="scope">
            {{ scope.row.student ? scope.row.student.name : 'N/A' }}
          </template>
        </el-table-column>
        
        <el-table-column prop="teacher_name" label="Teacher">
          <template #default="scope">
            {{ scope.row.teacher ? scope.row.teacher.name : 'N/A' }}
          </template>
        </el-table-column>
        
        <el-table-column prop="observation_date" label="Date">
          <template #default="scope">
            {{ formatDate(scope.row.observation_date) }}
          </template>
        </el-table-column>
        
        <el-table-column label="Actions">
          <template #default="scope">
            <el-button 
              type="text" 
              size="small" 
              @click="viewObservation(scope.row)"
            >
              <el-icon><View /></el-icon>
            </el-button>
            <el-button 
              type="text" 
              size="small" 
              @click="editObservation(scope.row)"
            >
              <el-icon><Edit /></el-icon>
            </el-button>
            <el-button 
              type="text" 
              size="small" 
              @click="deleteObservation(scope.row)"
            >
              <el-icon><Delete /></el-icon>
            </el-button>
          </template>
        </el-table-column>
      </el-table>
      
      <div class="pagination-container">
        <el-pagination
          layout="prev, pager, next"
          :total="observations.length"
          :page-size="10"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>
  </div>
</template>

<script>
import { Plus, View, Edit, Delete } from '@element-plus/icons-vue'

export default {
  name: 'StudentObservations',
  components: {
    Plus,
    View,
    Edit,
    Delete
  },
  data() {
    return {
      search: '',
      loading: false,
      observations: [],
      currentPage: 1
    };
  },
  computed: {
    filteredObservations() {
      if (!this.search) {
        const start = (this.currentPage - 1) * 10;
        const end = this.currentPage * 10;
        return this.observations.slice(start, end);
      }
      
      const filtered = this.observations.filter(item => {
        const studentName = item.student ? item.student.name.toLowerCase() : '';
        const teacherName = item.teacher ? item.teacher.name.toLowerCase() : '';
        const searchTerm = this.search.toLowerCase();
        
        return studentName.includes(searchTerm) || 
               teacherName.includes(searchTerm) ||
               this.formatDate(item.observation_date).includes(searchTerm);
      });
      
      return filtered;
    }
  },
  created() {
    this.fetchObservations();
  },
  methods: {
    async fetchObservations() {
      this.loading = true;
      try {
        const response = await this.axios.get('/api/student-observations');
        this.observations = response.data;
      } catch (error) {
        this.$message.error('Failed to fetch observations');
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
    formatDate(date) {
      if (!date) return 'N/A';
      return new Date(date).toLocaleDateString();
    },
    viewObservation(item) {
      this.$router.push({ name: 'view-student-observation', params: { id: item.id } });
    },
    editObservation(item) {
      this.$router.push({ name: 'edit-student-observation', params: { id: item.id } });
    },
    async deleteObservation(item) {
      try {
        await this.$confirm('Are you sure you want to delete this observation?', 'Warning', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning'
        });
        
        await this.axios.delete(`/api/student-observations/${item.id}`);
        this.$message.success('Observation deleted successfully');
        this.fetchObservations();
      } catch (error) {
        if (error !== 'cancel') {
          this.$message.error('Failed to delete observation');
          console.error(error);
        }
      }
    },
    handleCurrentChange(val) {
      this.currentPage = val;
    }
  }
};
</script>

<style scoped>
.student-observations {
  margin: 20px;
}
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.header-actions {
  display: flex;
  align-items: center;
}
.pagination-container {
  margin-top: 20px;
  display: flex;
  justify-content: center;
}
</style>
