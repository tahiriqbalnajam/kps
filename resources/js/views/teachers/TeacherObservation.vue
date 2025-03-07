<template>
  <div class="app-container">
    <!-- Add the top button -->
    <div class="filter-container mb-4">
      <el-button
        type="primary"
        @click="showFormDrawer = true"
        class="filter-item"
      >
        <el-icon><Plus /></el-icon>
        Add Observation
      </el-button>
    </div>

    <!-- Move the form to a drawer -->
    <el-drawer
      v-model="showFormDrawer"
      title="Add Teacher Observation"
      direction="rtl"
      size="50%"
    >
      <el-form
        :model="observationForm"
        :rules="rules"
        ref="observationForm"
        label-position="top"
        class="drawer-form"
      >
        <!-- Two columns for teacher and date selection -->
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Select Teacher" prop="teacher_id">
              <el-select v-model="observationForm.teacher_id" placeholder="Select Teacher" style="width: 100%">
                <el-option
                  v-for="teacher in teachers"
                  :key="teacher.id"
                  :label="teacher.name"
                  :value="teacher.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Observation Date" prop="observation_date">
              <el-date-picker
                v-model="observationForm.observation_date"
                type="date"
                placeholder="Select Date"
                style="width: 100%"
                value-format="YYYY-MM-DD"
              />
            </el-form-item>
          </el-col>
        </el-row>

        <el-divider content-position="left">Performance Evaluation</el-divider>

        <!-- Two columns for ratings -->
        <el-row :gutter="20">
          <el-col :span="12" v-for="(item, index) in evaluationCriteria" :key="index">
            <el-card class="rating-card" :class="{ 'mb-4': index < 2 }">
              <template #header>
                <div class="card-header">
                  <h4>{{ item.label }}</h4>
                </div>
              </template>
              <el-form-item :prop="item.prop">
                <el-rate
                  v-model="observationForm[item.prop]"
                  :max="5"
                  show-score
                  :texts="['Poor', 'Fair', 'Good', 'Very Good', 'Excellent']"
                  text-color="#ff9900"
                />
              </el-form-item>
            </el-card>
          </el-col>
        </el-row>

        <!-- Full width for comments -->
        <el-form-item label="Supervisor Comments" prop="supervisor_comments">
          <el-input
            type="textarea"
            v-model="observationForm.supervisor_comments"
            :rows="4"
            placeholder="Enter detailed observations and recommendations"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <div style="flex: auto">
          <el-button @click="showFormDrawer = false">Cancel</el-button>
          <el-button type="primary" @click="submitForm('observationForm')" :loading="loading">
            Submit Report
          </el-button>
        </div>
      </template>
    </el-drawer>

    <!-- Previous Reports Table -->
    <el-card class="box-card mt-4">
      <div slot="header" class="clearfix">
        <span>Previous Observation Reports</span>
      </div>

      <!-- Add Filter Section -->
      <div class="filter-section mb-4">
        <el-row :gutter="20">
          <el-col :span="8">
            <el-form-item label="Filter by Teacher">
              <el-select 
                v-model="query.teacher_id" 
                placeholder="Select Teacher" 
                clearable
                @change="handleFilter"
              >
                <el-option
                  v-for="teacher in teachers"
                  :key="teacher.id"
                  :label="teacher.name"
                  :value="teacher.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="12">
            <el-form-item label="Date Range">
              <el-date-picker
                v-model="dateRange"
                type="daterange"
                range-separator="to"
                start-placeholder="Start date"
                end-placeholder="End date"
                value-format="YYYY-MM-DD"
                @change="handleDateRangeChange"
              />
            </el-form-item>
          </el-col>
          <el-col :span="4" class="filter-buttons">
            <el-button type="primary" @click="handleFilter">Filter</el-button>
            <el-button @click="resetFilters">Reset</el-button>
          </el-col>
        </el-row>
      </div>

      <el-table :data="observations" style="width: 100%" v-loading="tableLoading">
        <el-table-column prop="teacher.name" label="Teacher" />
        <el-table-column prop="observation_date" label="Date" />
        <el-table-column prop="attentiveness_score" label="Attentiveness" />
        <el-table-column prop="syllabus_progress_score" label="Syllabus Progress" />
        <el-table-column prop="tools_usage_score" label="Tools Usage" />
        <el-table-column prop="homework_check_score" label="Homework Check" />
        <el-table-column label="Actions">
          <template #default="scope">
            <el-button size="small" @click="viewDetails(scope.row)">View</el-button>
          </template>
        </el-table-column>
      </el-table>

      <pagination
        v-show="total > 0"
        :total="total"
        v-model:page="query.page"
        v-model:limit="query.limit"
        @pagination="getObservations"
      />
    </el-card>

    <!-- Details Dialog -->
    <el-dialog
      title="Observation Details"
      v-model="dialogVisible"
      width="50%"
    >
      <div v-if="selectedObservation">
        <h3>{{ selectedObservation.teacher?.name }}</h3>
        <p><strong>Date:</strong> {{ selectedObservation.observation_date }}</p>
        <el-divider></el-divider>
        <div class="observation-scores">
          <p><strong>Attentiveness:</strong> {{ selectedObservation.attentiveness_score }}/5</p>
          <p><strong>Syllabus Progress:</strong> {{ selectedObservation.syllabus_progress_score }}/5</p>
          <p><strong>Tools Usage:</strong> {{ selectedObservation.tools_usage_score }}/5</p>
          <p><strong>Homework Check:</strong> {{ selectedObservation.homework_check_score }}/5</p>
        </div>
        <el-divider></el-divider>
        <div class="comments">
          <h4>Supervisor Comments:</h4>
          <p>{{ selectedObservation.supervisor_comments }}</p>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Resource from '@/api/resource';
const teacherResource = new Resource('teachers');
const observationResource = new Resource('teacher-observations');

export default {
  name: 'TeacherObservation',
  data() {
    return {
      showFormDrawer: false, // Add this line
      teachers: [],
      observations: [],
      loading: false,
      tableLoading: false,
      dialogVisible: false,
      selectedObservation: null,
      total: 0,
      query: {
        page: 1,
        limit: 10,
        teacher_id: null,
        date_from: null,
        date_to: null
      },
      dateRange: null,
      observationForm: {
        teacher_id: null,
        observation_date: '',
        attentiveness_score: 0,
        syllabus_progress_score: 0,
        tools_usage_score: 0,
        homework_check_score: 0,
        supervisor_comments: ''
      },
      evaluationCriteria: [
        { label: 'Teacher Attentiveness in Class', prop: 'attentiveness_score' },
        { label: 'Syllabus Progress as per Schedule', prop: 'syllabus_progress_score' },
        { label: 'Use of Teaching Tools', prop: 'tools_usage_score' },
        { label: 'Homework Check Effectiveness', prop: 'homework_check_score' }
      ],
      rules: {
        teacher_id: [{ required: true, message: 'Please select a teacher', trigger: 'change' }],
        observation_date: [{ required: true, message: 'Please select a date', trigger: 'change' }],
        supervisor_comments: [{ required: true, message: 'Please enter comments', trigger: 'blur' }]
      }
    };
  },
  async created() {
    await this.getTeachers();
    await this.getObservations();
  },
  methods: {
    async getTeachers() {
      const { data } = await teacherResource.list();
      this.teachers = data.teachers.data;
    },
    handleDateRangeChange(dates) {
      if (dates) {
        [this.query.date_from, this.query.date_to] = dates;
      } else {
        this.query.date_from = null;
        this.query.date_to = null;
      }
    },

    handleFilter() {
      this.query.page = 1; // Reset to first page when filtering
      this.getObservations();
    },

    resetFilters() {
      this.query.teacher_id = null;
      this.query.date_from = null;
      this.query.date_to = null;
      this.dateRange = null;
      this.handleFilter();
    },
    async getObservations() {
      this.tableLoading = true;
      try {
        const { data } = await observationResource.list(this.query);
        this.observations = data.observations.data;
        this.total = data.observations.total;
      } finally {
        this.tableLoading = false;
      }
    },
    async submitForm(formName) {
      this.$refs[formName].validate(async (valid) => {
        if (valid) {
          this.loading = true;
          try {
            await observationResource.store(this.observationForm);
            this.$message.success('Observation report submitted successfully');
            this.resetForm(formName);
            this.showFormDrawer = false; // Close drawer after success
            await this.getObservations();
          } catch (error) {
            this.$message.error('Failed to submit observation report');
          } finally {
            this.loading = false;
          }
        }
      });
    },
    resetForm(formName) {
      this.$refs[formName].resetFields();
    },
    viewDetails(row) {
      this.selectedObservation = row;
      this.dialogVisible = true;
    }
  }
};
</script>

<style scoped>
.mt-4 {
  margin-top: 1rem;
}

.observation-scores {
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 4px;
}

.comments {
  margin-top: 1rem;
}

.filter-section {
  padding: 16px;
  border-bottom: 1px solid #ebeef5;
}

.filter-buttons {
  display: flex;
  align-items: flex-end;
  gap: 10px;
}

.mb-4 {
  margin-bottom: 1rem;
}

.drawer-form {
  padding: 20px;
  height: calc(100vh - 150px);
  overflow-y: auto;
}

.filter-container {
  padding-bottom: 16px;
}

.el-drawer__body {
  padding: 20px;
}

.el-drawer__footer {
  padding: 20px;
  border-top: 1px solid #dcdfe6;
}

.rating-card {
  margin-bottom: 20px;
  border: 1px solid #ebeef5;
  transition: all 0.3s;
}

.rating-card:hover {
  box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.card-header h4 {
  margin: 0;
  font-size: 14px;
  color: #606266;
}

.el-rate {
  margin-top: 8px;
  display: flex;
  justify-content: center;
}
</style>
