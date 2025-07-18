<template>
  <div class="app-container">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <span>Import Student Data</span>
          <el-button 
            type="primary" 
            size="small" 
            @click="downloadExample"
            :loading="downloadLoading"
          >
            Download Example CSV
          </el-button>
        </div>
      </template>
      
      <div class="upload-section">
        <el-upload
          ref="upload"
          class="upload-demo"
          drag
          :auto-upload="false"
          :on-change="handleFileChange"
          :file-list="fileList"
          accept=".csv"
          :limit="1"
        >
          <el-icon class="el-icon--upload"><upload-filled /></el-icon>
          <div class="el-upload__text">
            Drop CSV file here or <em>click to upload</em>
          </div>
          <template #tip>
            <div class="el-upload__tip">
              Only CSV files are allowed, max size 2MB
            </div>
          </template>
        </el-upload>
        
        <div class="upload-actions" v-if="fileList.length > 0">
          <el-button 
            type="primary" 
            @click="uploadFile"
            :loading="uploading"
          >
            {{ uploading ? 'Uploading...' : 'Upload & Process' }}
          </el-button>
          <el-button @click="clearFile">Clear</el-button>
        </div>
      </div>

      <div v-if="importResult" class="import-results">
        <el-alert
          :title="importResult.message"
          :type="importResult.type"
          :description="importResult.description"
          show-icon
          :closable="false"
        />
        
        <div class="results-summary" v-if="importResult.stats">
          <el-row :gutter="20">
            <el-col :span="6">
              <el-statistic title="Total Records" :value="importResult.stats.total" />
            </el-col>
            <el-col :span="6">
              <el-statistic title="Successful" :value="importResult.stats.successful" />
            </el-col>
            <el-col :span="6">
              <el-statistic title="Failed" :value="importResult.stats.failed" />
            </el-col>
            <el-col :span="6">
              <el-statistic title="Success Rate" :value="importResult.stats.successRate" suffix="%" />
            </el-col>
          </el-row>
        </div>

        <div v-if="importResult.errors && importResult.errors.length > 0" class="error-details">
          <h4>Import Errors:</h4>
          <el-table :data="importResult.errors" style="width: 100%">
            <el-table-column prop="row" label="Row" width="80" />
            <el-table-column prop="error" label="Error" />
            <el-table-column prop="data" label="Data" width="200">
              <template #default="scope">
                <el-tooltip :content="JSON.stringify(scope.row.data)" placement="top">
                  <span>{{ Object.values(scope.row.data).join(', ').substring(0, 50) }}...</span>
                </el-tooltip>
              </template>
            </el-table-column>
          </el-table>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script>
import { UploadFilled } from '@element-plus/icons-vue'
import Resource from '@/api/resource'

const importResource = new Resource('import')
import { uploadFile, importData } from '@/api/import'

export default {
  name: 'ImportData',
  components: {
    UploadFilled
  },
  data() {
    return {
      fileList: [],
      uploading: false,
      downloadLoading: false,
      importResult: null
    }
  },
  methods: {
    handleFileChange(file) {
      this.fileList = [file]
      this.importResult = null
    },
    
    clearFile() {
      this.fileList = []
      this.importResult = null
      this.$refs.upload.clearFiles()
    },
    
    async uploadFile() {
      if (this.fileList.length === 0) {
        this.$message.error('Please select a file first')
        return
      }
      
      this.uploading = true
      
      try {
        const formData = new FormData()
        formData.append('file', this.fileList[0].raw)
        
        // Upload file
        console.log('Starting file upload...')
        const uploadResponse = await uploadFile(formData)
        console.log('Upload response:', uploadResponse)
        
        // Check if upload was successful
        if (!uploadResponse.import_id) {
          throw new Error('Upload failed: No import_id received')
        }
        
        // Process import
        console.log('Starting import process with ID:', uploadResponse.import_id)
        const processResponse = await importData({
          import_id: uploadResponse.import_id
        })
        console.log('Process response:', processResponse)
        
        // Fix: Access data directly from response, not from processResponse.data
        const stats = processResponse
        this.importResult = {
          message: 'Import completed successfully',
          type: 'success',
          description: `Processed ${stats.total} records. ${stats.successful} successful, ${stats.failed} failed.`,
          stats: {
            total: stats.total,
            successful: stats.successful,
            failed: stats.failed,
            successRate: stats.total > 0 ? Math.round((stats.successful / stats.total) * 100) : 0
          },
          errors: stats.errors
        }
        
        if (stats.failed > 0) {
          this.importResult.type = 'warning'
          this.importResult.message = 'Import completed with errors'
        }
        
      } catch (error) {
        console.error('Import error:', error)
        console.error('Error response:', error.response)
        
        // Handle different types of errors
        let errorMessage = 'Import failed'
        let errorDescription = 'An error occurred during import'
        let errorDetails = null
        
        if (error.response) {
          // Server responded with error status
          const responseData = error.response.data
          errorMessage = responseData.message || 'Import failed'
          errorDescription = responseData.error || error.message || 'Server error occurred'
          
          // If there are detailed errors from the import process
          if (responseData.errors && responseData.errors.length > 0) {
            errorDetails = responseData.errors
          }
          
          // If it's a validation error or processing error with stats
          if (responseData.total !== undefined) {
            errorDescription = `Processed ${responseData.total} records. ${responseData.successful || 0} successful, ${responseData.failed || responseData.total} failed.`
          }
        } else if (error.request) {
          // Request was made but no response received
          errorMessage = 'Network Error'
          errorDescription = 'Unable to connect to server. Please check your connection and try again.'
        } else {
          // Something else happened
          errorMessage = 'Import failed'
          errorDescription = error.message || 'An unexpected error occurred'
        }
        
        this.importResult = {
          message: errorMessage,
          type: 'error',
          description: errorDescription,
          errors: errorDetails
        }
        
        // If we have stats from a failed import, show them
        if (error.response?.data?.total !== undefined) {
          this.importResult.stats = {
            total: error.response.data.total,
            successful: error.response.data.successful || 0,
            failed: error.response.data.failed || error.response.data.total,
            successRate: 0
          }
        }
      } finally {
        this.uploading = false
      }
    },
    
    async downloadExample() {
      this.downloadLoading = true
      try {
        const response = await fetch('/api/import/example')
        const blob = await response.blob()
        const url = window.URL.createObjectURL(blob)
        const link = document.createElement('a')
        link.href = url
        link.download = 'student_import_example.csv'
        link.click()
        window.URL.revokeObjectURL(url)
      } catch (error) {
        this.$message.error('Failed to download example file')
      } finally {
        this.downloadLoading = false
      }
    }
  }
}
</script>

<style scoped>
.upload-section {
  margin-bottom: 30px;
}

.upload-actions {
  margin-top: 20px;
  text-align: center;
}

.import-results {
  margin-top: 30px;
}

.results-summary {
  margin: 20px 0;
}

.error-details {
  margin-top: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
