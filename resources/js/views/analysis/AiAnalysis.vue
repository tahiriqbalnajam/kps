<template>
  <div class="app-container">
    <el-card class="analysis-form">
      <template #header>
        <div class="card-header">
          <h3>AI Analysis Dashboard</h3>
          <el-button type="primary" @click="showNewAnalysisForm = true">
            New Analysis
          </el-button>
        </div>
      </template>

      <!-- Reports List -->
      <el-table :data="reports" v-loading="loading" style="width: 100%">
        <el-table-column prop="title" label="Report Title" />
        <el-table-column prop="created_at" label="Date" width="180">
          <template #default="scope">
            {{ formatDate(scope.row.created_at) }}
          </template>
        </el-table-column>
        <el-table-column prop="status" label="Status" width="120">
          <template #default="scope">
            <el-tag :type="getStatusType(scope.row.status)">
              {{ scope.row.status }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column align="right" width="200">
          <template #default="scope">
            <el-button size="small" @click="viewReport(scope.row)">
              View
            </el-button>
            <el-button 
              size="small" 
              type="danger" 
              @click="deleteReport(scope.row)"
              :disabled="scope.row.status === 'processing'"
            >
              Delete
            </el-button>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- New Analysis Dialog -->
    <el-dialog 
      v-model="showNewAnalysisForm" 
      title="New Analysis"
      width="60%"
    >
      <el-form 
        ref="analysisForm"
        :model="analysisForm"
        :rules="formRules"
        label-position="top"
      >
        <el-form-item label="Analysis Title" prop="title">
          <el-input v-model="analysisForm.title" placeholder="Enter a descriptive title" />
        </el-form-item>

        <el-form-item label="Select Data Sources" prop="data_sources">
          <el-select
            v-model="analysisForm.data_sources"
            multiple
            placeholder="Select data sources"
            style="width: 100%"
          >
            <el-option
              v-for="source in dataSources"
              :key="source.id"
              :label="source.name"
              :value="source.id"
            >
              <span>{{ source.name }}</span>
              <small style="color: #8492a6; margin-left: 5px">
                ({{ source.fields.join(', ') }})
              </small>
            </el-option>
          </el-select>
        </el-form-item>

        <el-form-item label="Analysis Prompt" prop="prompt">
          <el-input
            v-model="analysisForm.prompt"
            type="textarea"
            :rows="4"
            placeholder="Describe what you want to analyze..."
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <span class="dialog-footer">
          <el-button @click="showNewAnalysisForm = false">Cancel</el-button>
          <el-button type="primary" @click="submitAnalysis" :loading="submitting">
            Start Analysis
          </el-button>
        </span>
      </template>
    </el-dialog>

    <!-- View Report Dialog -->
    <el-dialog
      v-model="showReportDialog"
      :title="selectedReport?.title"
      width="70%"
    >
      <div v-if="selectedReport" class="report-view">
        <div class="report-metadata">
          <el-descriptions :column="2" border>
            <el-descriptions-item label="Status">
              <el-tag :type="getStatusType(selectedReport.status)">
                {{ selectedReport.status }}
              </el-tag>
            </el-descriptions-item>
            <el-descriptions-item label="Created">
              {{ formatDate(selectedReport.created_at) }}
            </el-descriptions-item>
            <el-descriptions-item label="Data Sources" :span="2">
              {{ selectedReport.data_sources.join(', ') }}
            </el-descriptions-item>
            <el-descriptions-item label="Prompt" :span="2">
              {{ selectedReport.prompt }}
            </el-descriptions-item>
          </el-descriptions>
        </div>

        <div class="report-content">
          <h4>Analysis Results</h4>
          <div class="analysis-result" v-html="formattedAnalysis"></div>
        </div>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import Resource from '@/api/resource'
import { ElMessage } from 'element-plus'
import moment from 'moment'

const aiResource = new Resource('ai-analysis')

export default {
  name: 'AiAnalysis',
  data() {
    return {
      loading: false,
      submitting: false,
      reports: [],
      dataSources: [],
      showNewAnalysisForm: false,
      showReportDialog: false,
      selectedReport: null,
      analysisForm: {
        title: '',
        data_sources: [],
        prompt: ''
      },
      formRules: {
        title: [
          { required: true, message: 'Please enter a title', trigger: 'blur' }
        ],
        data_sources: [
          { required: true, message: 'Please select at least one data source', trigger: 'change' }
        ],
        prompt: [
          { required: true, message: 'Please enter an analysis prompt', trigger: 'blur' }
        ]
      }
    }
  },
  computed: {
    formattedAnalysis() {
      if (!this.selectedReport?.analysis_result) return ''
      return this.selectedReport.analysis_result.replace(/\n/g, '<br>')
    }
  },
  created() {
    this.getReports()
    this.getDataSources()
  },
  methods: {
    formatDate(date) {
      return moment(date).format('YYYY-MM-DD HH:mm')
    },
    getStatusType(status) {
      const types = {
        pending: 'info',
        processing: 'warning',
        completed: 'success',
        failed: 'danger'
      }
      return types[status] || 'info'
    },
    async getReports() {
      this.loading = true
      try {
        const { data } = await aiResource.list()
        this.reports = data.reports.data
      } catch (error) {
        ElMessage.error('Failed to load reports')
      } finally {
        this.loading = false
      }
    },
    async getDataSources() {
      try {
        const { data } = await aiResource.get('sources')
        this.dataSources = data.data_sources
      } catch (error) {
        ElMessage.error('Failed to load data sources')
      }
    },
    async submitAnalysis() {
      try {
        await this.$refs.analysisForm.validate()
      } catch (error) {
        return
      }

      this.submitting = true
      try {
        await aiResource.store(this.analysisForm)
        ElMessage.success('Analysis started successfully')
        this.showNewAnalysisForm = false
        this.getReports()
      } catch (error) {
        ElMessage.error('Failed to start analysis')
      } finally {
        this.submitting = false
      }
    },
    viewReport(report) {
      this.selectedReport = report
      this.showReportDialog = true
    },
    async deleteReport(report) {
      try {
        await this.$confirm(
          'Are you sure you want to delete this report?',
          'Warning',
          {
            confirmButtonText: 'OK',
            cancelButtonText: 'Cancel',
            type: 'warning'
          }
        )
        await aiResource.destroy(report.id)
        ElMessage.success('Report deleted successfully')
        this.getReports()
      } catch (error) {
        if (error !== 'cancel') {
          ElMessage.error('Failed to delete report')
        }
      }
    }
  }
}
</script>

<style scoped>
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.report-view {
  padding: 20px;
}

.report-metadata {
  margin-bottom: 20px;
}

.report-content {
  margin-top: 30px;
}

.analysis-result {
  background: #f8f9fa;
  padding: 20px;
  border-radius: 4px;
  white-space: pre-line;
}
</style>
