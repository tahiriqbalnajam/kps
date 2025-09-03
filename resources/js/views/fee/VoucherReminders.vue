<template>
  <div class="app-container">
    <!-- Header -->
    <div class="header-section">
      <div class="header-info">
        <el-icon class="header-icon"><Bell /></el-icon>
        <div>
          <h2 class="header-title">Outstanding Voucher Reminders</h2>
          <p class="header-subtitle">Send reminders for overdue and pending vouchers</p>
        </div>
      </div>
      
      <div class="header-actions">
        <el-button 
          type="primary" 
          @click="sendBulkReminders"
          :disabled="selectedVouchers.length === 0"
          :loading="sendingReminders"
        >
          <el-icon><Message /></el-icon>
          Send Reminders ({{ selectedVouchers.length }})
        </el-button>
        
        <el-button type="success" @click="refreshData">
          <el-icon><Refresh /></el-icon>
          Refresh
        </el-button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
      <el-row :gutter="16">
        <el-col :xs="24" :sm="12" :md="6" :lg="6">
          <el-card class="summary-card overdue">
            <div class="card-content">
              <div class="card-icon">
                <el-icon><WarningFilled /></el-icon>
              </div>
              <div class="card-info">
                <div class="card-value">{{ overdueCount }}</div>
                <div class="card-label">Overdue Vouchers</div>
              </div>
            </div>
          </el-card>
        </el-col>
        
        <el-col :xs="24" :sm="12" :md="6" :lg="6">
          <el-card class="summary-card due-soon">
            <div class="card-content">
              <div class="card-icon">
                <el-icon><Clock /></el-icon>
              </div>
              <div class="card-info">
                <div class="card-value">{{ dueSoonCount }}</div>
                <div class="card-label">Due in 3 Days</div>
              </div>
            </div>
          </el-card>
        </el-col>
        
        <el-col :xs="24" :sm="12" :md="6" :lg="6">
          <el-card class="summary-card pending">
            <div class="card-content">
              <div class="card-icon">
                <el-icon><DocumentRemove /></el-icon>
              </div>
              <div class="card-info">
                <div class="card-value">{{ pendingAmount }}</div>
                <div class="card-label">Pending Amount</div>
              </div>
            </div>
          </el-card>
        </el-col>
        
        <el-col :xs="24" :sm="12" :md="6" :lg="6">
          <el-card class="summary-card reminders">
            <div class="card-content">
              <div class="card-icon">
                <el-icon><Message /></el-icon>
              </div>
              <div class="card-info">
                <div class="card-value">{{ remindersSent }}</div>
                <div class="card-label">Reminders Sent</div>
              </div>
            </div>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- Filters -->
    <div class="filter-section">
      <el-card>
        <el-row :gutter="16" align="middle">
          <el-col :span="4">
            <el-select v-model="filters.urgency" placeholder="All Urgency" @change="loadOutstandingVouchers">
              <el-option label="All" value="" />
              <el-option label="Overdue" value="overdue" />
              <el-option label="Due in 3 days" value="due_soon" />
              <el-option label="Due in 7 days" value="due_week" />
            </el-select>
          </el-col>
          
          <el-col :span="4">
            <el-select v-model="filters.class_name" placeholder="All Classes" @change="loadOutstandingVouchers">
              <el-option label="All Classes" value="" />
              <el-option 
                v-for="cls in classes" 
                :key="cls.name" 
                :label="cls.name" 
                :value="cls.name" 
              />
            </el-select>
          </el-col>
          
          <el-col :span="6">
            <el-input 
              v-model="filters.search" 
              placeholder="Search student name, voucher#..." 
              clearable
              @input="debounceSearch"
            >
              <template #prefix>
                <el-icon><Search /></el-icon>
              </template>
            </el-input>
          </el-col>
          
          <el-col :span="6">
            <el-date-picker
              v-model="filters.due_date_range"
              type="daterange"
              range-separator="To"
              start-placeholder="Due from"
              end-placeholder="Due to"
              @change="loadOutstandingVouchers"
            />
          </el-col>
          
          <el-col :span="4">
            <div class="filter-actions">
              <el-button type="primary" @click="loadOutstandingVouchers">
                <el-icon><Search /></el-icon>
                Search
              </el-button>
            </div>
          </el-col>
        </el-row>
      </el-card>
    </div>

    <!-- Outstanding Vouchers Table -->
    <el-card class="table-card">
      <template #header>
        <div class="table-header">
          <div class="header-left">
            <el-checkbox 
              v-model="selectAll" 
              @change="handleSelectAll"
              :indeterminate="isIndeterminate"
            >
              Select All
            </el-checkbox>
            <span class="total-info">{{ outstandingVouchers.length }} outstanding vouchers</span>
          </div>
          
          <div class="header-right">
            <el-button-group>
              <el-button 
                size="small" 
                @click="selectOverdue"
                :disabled="overdueVouchers.length === 0"
              >
                Select Overdue ({{ overdueVouchers.length }})
              </el-button>
              
              <el-button 
                size="small" 
                @click="selectDueSoon"
                :disabled="dueSoonVouchers.length === 0"
              >
                Select Due Soon ({{ dueSoonVouchers.length }})
              </el-button>
            </el-button-group>
          </div>
        </div>
      </template>

      <el-table
        :data="outstandingVouchers"
        v-loading="loading"
        stripe
        @selection-change="handleSelectionChange"
        class="reminder-table"
      >
        <el-table-column type="selection" width="50" />
        
        <el-table-column label="Urgency" width="100">
          <template #default="scope">
            <el-tag 
              :type="getUrgencyTagType(scope.row)" 
              size="small"
              :effect="getUrgencyEffect(scope.row)"
            >
              {{ getUrgencyLabel(scope.row) }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="Voucher #" width="130">
          <template #default="scope">
            <div class="voucher-number">{{ scope.row.voucher_number }}</div>
          </template>
        </el-table-column>

        <el-table-column label="Student" min-width="180">
          <template #default="scope">
            <div class="student-info">
              <div class="student-name">{{ scope.row.student_name }}</div>
              <div class="class-info">{{ scope.row.class_name }} - {{ scope.row.admission_number }}</div>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Parent Contact" width="150">
          <template #default="scope">
            <div class="contact-info">
              <div class="parent-name">{{ scope.row.parent_name }}</div>
              <div class="phone-number" v-if="scope.row.parent_phone">
                <el-icon><Phone /></el-icon>
                {{ scope.row.parent_phone }}
              </div>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Amount" width="120" align="right">
          <template #default="scope">
            <div class="amount-info">
              <div class="total-amount">Rs. {{ scope.row.total_with_fine }}</div>
              <div class="fine-info" v-if="scope.row.fine_amount > 0">
                <small>+ Rs. {{ scope.row.fine_amount }} fine</small>
              </div>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Due Date" width="110">
          <template #default="scope">
            <div class="due-date" :class="getDueDateClass(scope.row)">
              {{ formatDate(scope.row.due_date) }}
              <div class="days-info">{{ getDaysOverdue(scope.row) }}</div>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Last Reminder" width="120">
          <template #default="scope">
            <div v-if="scope.row.last_reminder_sent" class="reminder-info">
              {{ formatDate(scope.row.last_reminder_sent) }}
            </div>
            <div v-else class="no-reminder">
              <small>No reminders</small>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Actions" width="180" align="center">
          <template #default="scope">
            <div class="action-buttons">
              <el-button 
                type="primary" 
                size="small"
                @click="sendSingleReminder(scope.row)"
                :loading="scope.row.sending"
              >
                <el-icon><Message /></el-icon>
                Remind
              </el-button>
              
              <el-button 
                type="info" 
                size="small"
                @click="viewVoucherDetails(scope.row)"
              >
                <el-icon><View /></el-icon>
                View
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

    <!-- Reminder Template Dialog -->
    <el-dialog
      title="Customize Reminder Message"
      v-model="showReminderDialog"
      width="600px"
      class="reminder-dialog"
    >
      <el-form :model="reminderForm" label-width="120px">
        <el-form-item label="Recipients">
          <el-tag 
            v-for="voucher in selectedVoucherDetails" 
            :key="voucher.id"
            class="recipient-tag"
          >
            {{ voucher.student_name }} ({{ voucher.voucher_number }})
          </el-tag>
        </el-form-item>
        
        <el-form-item label="Message Type">
          <el-radio-group v-model="reminderForm.template">
            <el-radio label="gentle">Gentle Reminder</el-radio>
            <el-radio label="urgent">Urgent Notice</el-radio>
            <el-radio label="final">Final Warning</el-radio>
            <el-radio label="custom">Custom Message</el-radio>
          </el-radio-group>
        </el-form-item>
        
        <el-form-item label="Message" v-if="reminderForm.template === 'custom'">
          <el-input
            v-model="reminderForm.customMessage"
            type="textarea"
            :rows="4"
            placeholder="Enter your custom reminder message..."
          />
        </el-form-item>
        
        <el-form-item label="Preview">
          <div class="message-preview">
            {{ getPreviewMessage() }}
          </div>
        </el-form-item>
        
        <el-form-item label="Send Via">
          <el-checkbox-group v-model="reminderForm.channels">
            <el-checkbox label="sms">SMS</el-checkbox>
            <el-checkbox label="whatsapp">WhatsApp</el-checkbox>
            <el-checkbox label="email">Email</el-checkbox>
          </el-checkbox-group>
        </el-form-item>
      </el-form>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="showReminderDialog = false">Cancel</el-button>
          <el-button 
            type="primary" 
            @click="confirmSendReminders"
            :loading="sendingReminders"
            :disabled="reminderForm.channels.length === 0"
          >
            Send {{ selectedVouchers.length }} Reminders
          </el-button>
        </div>
      </template>
    </el-dialog>
  </div>
</template>

<script>
import {
  Bell,
  Message,
  Refresh,
  WarningFilled,
  Clock,
  DocumentRemove,
  Search,
  Phone,
  View
} from '@element-plus/icons-vue'
import moment from 'moment'
import { debounce } from 'lodash'
import { 
  getOutstandingVouchers,
  sendVoucherReminders,
  getFeeVoucherDetails
} from '@/api/fee'
import Resource from '@/api/resource'

const classesResource = new Resource('classes')

export default {
  name: 'VoucherReminders',
  data() {
    return {
      loading: false,
      sendingReminders: false,
      outstandingVouchers: [],
      classes: [],
      selectedVouchers: [],
      selectAll: false,
      isIndeterminate: false,
      showReminderDialog: false,
      filters: {
        urgency: '',
        class_name: '',
        search: '',
        due_date_range: null
      },
      reminderForm: {
        template: 'gentle',
        customMessage: '',
        channels: ['sms']
      },
      remindersSent: 0
    }
  },
  computed: {
    overdueVouchers() {
      return this.outstandingVouchers.filter(v => this.isOverdue(v))
    },
    
    dueSoonVouchers() {
      return this.outstandingVouchers.filter(v => this.isDueSoon(v))
    },
    
    overdueCount() {
      return this.overdueVouchers.length
    },
    
    dueSoonCount() {
      return this.dueSoonVouchers.length
    },
    
    pendingAmount() {
      const total = this.outstandingVouchers.reduce((sum, v) => sum + parseFloat(v.total_with_fine), 0)
      return `Rs. ${total.toLocaleString()}`
    },
    
    selectedVoucherDetails() {
      return this.outstandingVouchers.filter(v => this.selectedVouchers.includes(v.id))
    }
  },
  created() {
    this.loadOutstandingVouchers()
    this.loadClasses()
  },
  methods: {
    debounceSearch: debounce(function () {
      this.loadOutstandingVouchers()
    }, 500),

    async loadOutstandingVouchers() {
      this.loading = true
      try {
        const params = { ...this.filters }
        
        // Clean up empty parameters
        Object.keys(params).forEach(key => {
          if (params[key] === '' || params[key] === null || params[key] === undefined) {
            delete params[key]
          }
        })
        
        if (params.due_date_range) {
          params.due_from = moment(params.due_date_range[0]).format('YYYY-MM-DD')
          params.due_to = moment(params.due_date_range[1]).format('YYYY-MM-DD')
          delete params.due_date_range
        }
        
        // The request interceptor returns response.data, so no destructuring needed
        const data = await getOutstandingVouchers(params)
        this.outstandingVouchers = data.vouchers || []
        this.remindersSent = data.reminders_sent_today || 0
      } catch (error) {
        console.error('Error loading outstanding vouchers:', error)
        this.$message.error('Failed to load outstanding vouchers')
        this.outstandingVouchers = []
        this.remindersSent = 0
      } finally {
        this.loading = false
      }
    },

    async loadClasses() {
      try {
        const data = await classesResource.list()
        this.classes = data.classes?.data || data.classes || []
      } catch (error) {
        console.error('Error loading classes:', error)
        this.classes = []
      }
    },

    refreshData() {
      this.loadOutstandingVouchers()
      this.$message.success('Data refreshed')
    },

    handleSelectionChange(selection) {
      this.selectedVouchers = selection.map(v => v.id)
      this.updateSelectAllState()
    },

    handleSelectAll(checked) {
      if (checked) {
        this.$refs.table?.toggleAllSelection()
      } else {
        this.$refs.table?.clearSelection()
      }
    },

    updateSelectAllState() {
      const selectedCount = this.selectedVouchers.length
      const totalCount = this.outstandingVouchers.length
      
      this.selectAll = selectedCount === totalCount && totalCount > 0
      this.isIndeterminate = selectedCount > 0 && selectedCount < totalCount
    },

    selectOverdue() {
      this.selectedVouchers = this.overdueVouchers.map(v => v.id)
      this.updateSelectAllState()
    },

    selectDueSoon() {
      this.selectedVouchers = this.dueSoonVouchers.map(v => v.id)
      this.updateSelectAllState()
    },

    sendBulkReminders() {
      if (this.selectedVouchers.length === 0) {
        this.$message.warning('Please select vouchers to send reminders')
        return
      }
      this.showReminderDialog = true
    },

    async sendSingleReminder(voucher) {
      voucher.sending = true
      try {
        await sendVoucherReminders([voucher.id])
        this.$message.success(`Reminder sent for ${voucher.student_name}`)
        this.loadOutstandingVouchers()
      } catch (error) {
        console.error('Error sending reminder:', error)
        this.$message.error('Failed to send reminder')
      } finally {
        voucher.sending = false
      }
    },

    async confirmSendReminders() {
      this.sendingReminders = true
      try {
        const payload = {
          voucher_ids: this.selectedVouchers,
          template: this.reminderForm.template,
          custom_message: this.reminderForm.customMessage,
          channels: this.reminderForm.channels
        }
        
        const { data } = await sendVoucherReminders(payload)
        
        this.$message.success(`${data.sent_count} reminders sent successfully!`)
        this.showReminderDialog = false
        this.selectedVouchers = []
        this.loadOutstandingVouchers()
      } catch (error) {
        console.error('Error sending reminders:', error)
        this.$message.error('Failed to send reminders')
      } finally {
        this.sendingReminders = false
      }
    },

    viewVoucherDetails(voucher) {
      // Navigate to voucher details or show in dialog
      this.$router.push(`/fee/voucher/${voucher.id}`)
    },

    formatDate(date) {
      return moment(date).format('DD MMM')
    },

    isOverdue(voucher) {
      return moment().isAfter(moment(voucher.due_date))
    },

    isDueSoon(voucher) {
      const dueDate = moment(voucher.due_date)
      const today = moment()
      return dueDate.diff(today, 'days') <= 3 && dueDate.diff(today, 'days') >= 0
    },

    getDaysOverdue(voucher) {
      const dueDate = moment(voucher.due_date)
      const today = moment()
      const diff = today.diff(dueDate, 'days')
      
      if (diff > 0) {
        return `${diff} days overdue`
      } else if (diff === 0) {
        return 'Due today'
      } else {
        return `${Math.abs(diff)} days left`
      }
    },

    getUrgencyLabel(voucher) {
      if (this.isOverdue(voucher)) return 'OVERDUE'
      if (this.isDueSoon(voucher)) return 'DUE SOON'
      return 'PENDING'
    },

    getUrgencyTagType(voucher) {
      if (this.isOverdue(voucher)) return 'danger'
      if (this.isDueSoon(voucher)) return 'warning'
      return 'info'
    },

    getUrgencyEffect(voucher) {
      if (this.isOverdue(voucher)) return 'dark'
      return 'plain'
    },

    getDueDateClass(voucher) {
      if (this.isOverdue(voucher)) return 'overdue'
      if (this.isDueSoon(voucher)) return 'due-soon'
      return ''
    },

    getPreviewMessage() {
      const templates = {
        gentle: 'Dear Parent, this is a gentle reminder that the school fee for your child is due. Please make the payment at your earliest convenience.',
        urgent: 'URGENT: The school fee payment for your child is overdue. Please make immediate payment to avoid additional charges.',
        final: 'FINAL NOTICE: This is the final reminder for overdue school fees. Please pay immediately to avoid further action.',
        custom: this.reminderForm.customMessage
      }
      return templates[this.reminderForm.template] || 'Please select a template'
    }
  }
}
</script>

<style scoped>
.app-container {
  padding: 20px;
  background: #f5f7fa;
  min-height: 100vh;
}

/* Header */
.header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 20px 24px;
  border-radius: 12px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  margin-bottom: 20px;
}

.header-info {
  display: flex;
  align-items: center;
  gap: 16px;
}

.header-icon {
  font-size: 32px;
  color: #f56c6c;
}

.header-title {
  margin: 0 0 4px 0;
  color: #303133;
  font-size: 24px;
  font-weight: 600;
}

.header-subtitle {
  margin: 0;
  color: #909399;
  font-size: 14px;
}

/* Summary Cards */
.summary-cards {
  margin-bottom: 20px;
}

.summary-card {
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.summary-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.card-content {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
}

.card-icon {
  font-size: 32px;
  color: rgba(255, 255, 255, 0.9);
}

.card-value {
  font-size: 28px;
  font-weight: 700;
  color: white;
  line-height: 1;
}

.card-label {
  font-size: 13px;
  color: rgba(255, 255, 255, 0.8);
  margin-top: 4px;
}

.summary-card.overdue {
  background: linear-gradient(135deg, #f56c6c, #e85d5d);
}

.summary-card.due-soon {
  background: linear-gradient(135deg, #e6a23c, #d4941f);
}

.summary-card.pending {
  background: linear-gradient(135deg, #409eff, #3a8ee6);
}

.summary-card.reminders {
  background: linear-gradient(135deg, #67c23a, #5cb230);
}

/* Filter Section */
.filter-section {
  margin-bottom: 20px;
}

.filter-actions {
  display: flex;
  justify-content: flex-end;
}

/* Table */
.table-card {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.table-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.total-info {
  color: #909399;
  font-size: 14px;
}

.voucher-number {
  font-family: 'Courier New', monospace;
  font-weight: 600;
  color: #409eff;
}

.student-info .student-name {
  font-weight: 600;
  color: #303133;
  margin-bottom: 2px;
}

.student-info .class-info {
  font-size: 12px;
  color: #909399;
}

.contact-info .parent-name {
  font-weight: 500;
  color: #606266;
}

.contact-info .phone-number {
  font-size: 12px;
  color: #909399;
  display: flex;
  align-items: center;
  gap: 4px;
  margin-top: 2px;
}

.amount-info {
  text-align: right;
}

.total-amount {
  font-weight: 600;
  color: #27ae60;
  font-family: 'Courier New', monospace;
}

.fine-info {
  color: #f56c6c;
  margin-top: 2px;
}

.due-date {
  font-weight: 500;
  text-align: center;
}

.due-date .days-info {
  font-size: 11px;
  margin-top: 2px;
}

.due-date.overdue {
  color: #f56c6c;
  font-weight: 700;
}

.due-date.due-soon {
  color: #e6a23c;
  font-weight: 600;
}

.reminder-info {
  font-size: 12px;
  color: #606266;
  text-align: center;
}

.no-reminder {
  text-align: center;
  color: #c0c4cc;
}

.action-buttons {
  display: flex;
  gap: 8px;
  justify-content: center;
}

/* Dialog */
.reminder-dialog :deep(.el-dialog__header) {
  background: linear-gradient(135deg, #f56c6c, #e85d5d);
  color: white;
  padding: 20px 24px;
  margin: 0;
}

.reminder-dialog :deep(.el-dialog__title) {
  color: white;
  font-weight: 600;
}

.recipient-tag {
  margin: 2px 4px;
}

.message-preview {
  background: #f5f7fa;
  padding: 12px;
  border-radius: 6px;
  border-left: 4px solid #409eff;
  font-style: italic;
  color: #606266;
  line-height: 1.6;
}

/* Responsive */
@media (max-width: 768px) {
  .header-section {
    flex-direction: column;
    gap: 16px;
    align-items: stretch;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .header-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
  }
}
</style>
