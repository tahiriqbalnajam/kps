<template>
  <div class="app-container">
    <!-- Filter Header -->
    <div class="filter-header">
      <div class="filter-section">
        <el-select 
          v-model="query.status" 
          placeholder="All Status" 
          class="status-select"
          clearable
          @change="handleFilter"
        >
          <el-option label="All Vouchers" value="" />
          <el-option label="Unpaid" value="unpaid" />
          <el-option label="Paid" value="paid" />
          <el-option label="Overdue" value="overdue" />
          <el-option label="Cancelled" value="cancelled" />
        </el-select>

        <el-select 
          v-model="query.class_name" 
          placeholder="All Classes" 
          class="class-select"
          clearable
          @change="handleFilter"
        >
          <el-option 
            v-for="cls in classes" 
            :key="cls.name" 
            :label="cls.name" 
            :value="cls.name" 
          />
        </el-select>

        <el-date-picker
          v-model="dateRange"
          type="daterange"
          range-separator="To"
          start-placeholder="Start date"
          end-placeholder="End date"
          class="date-picker"
          @change="handleDateFilter"
        />

        <el-input 
          v-model="query.search" 
          placeholder="Search voucher#, student name..." 
          class="search-input" 
          v-on:input="debounceInput" 
          clearable
        >
          <template #prefix>
            <el-icon><Search /></el-icon>
          </template>
        </el-input>
      </div>

      <div class="action-section">
        <el-button 
          type="primary" 
          @click="showStatsDialog = true"
          class="stats-btn"
        >
          <el-icon><DataAnalysis /></el-icon>
          Statistics
        </el-button>
        
        <el-button 
          type="warning" 
          @click="loadOutstandingVouchers"
          class="outstanding-btn"
        >
          <el-icon><Warning /></el-icon>
          Outstanding
        </el-button>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-cards">
      <el-row :gutter="16">
        <el-col :xs="12" :sm="6" :md="6" :lg="6">
          <el-card class="stat-card total">
            <div class="stat-content">
              <div class="stat-value">{{ stats.total_vouchers || 0 }}</div>
              <div class="stat-label">Total Vouchers</div>
            </div>
            <el-icon class="stat-icon"><Files /></el-icon>
          </el-card>
        </el-col>
        
        <el-col :xs="12" :sm="6" :md="6" :lg="6">
          <el-card class="stat-card paid">
            <div class="stat-content">
              <div class="stat-value">{{ stats.paid_vouchers || 0 }}</div>
              <div class="stat-label">Paid</div>
            </div>
            <el-icon class="stat-icon"><CircleCheckFilled /></el-icon>
          </el-card>
        </el-col>
        
        <el-col :xs="12" :sm="6" :md="6" :lg="6">
          <el-card class="stat-card unpaid">
            <div class="stat-content">
              <div class="stat-value">{{ stats.unpaid_vouchers || 0 }}</div>
              <div class="stat-label">Unpaid</div>
            </div>
            <el-icon class="stat-icon"><Clock /></el-icon>
          </el-card>
        </el-col>
        
        <el-col :xs="12" :sm="6" :md="6" :lg="6">
          <el-card class="stat-card overdue">
            <div class="stat-content">
              <div class="stat-value">{{ stats.overdue_vouchers || 0 }}</div>
              <div class="stat-label">Overdue</div>
            </div>
            <el-icon class="stat-icon"><WarningFilled /></el-icon>
          </el-card>
        </el-col>
      </el-row>
    </div>

    <!-- Vouchers Table -->
    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <div class="header-info">
            <el-icon class="header-icon"><List /></el-icon>
            <span class="header-title">Fee Voucher Tracking</span>
            <el-tag v-if="total > 0" type="info" class="total-count">{{ total }} vouchers</el-tag>
          </div>
        </div>
      </template>

      <el-table
        :data="vouchersList"
        v-loading="loading"
        stripe
        style="width: 100%"
        class="tracking-table"
        @row-click="handleRowClick"
      >
        <el-table-column label="Voucher #" width="160" fixed="left">
          <template #default="scope">
            <div class="voucher-number">
              {{ scope.row.voucher_number }}
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Student" min-width="200">
          <template #default="scope">
            <div class="student-info">
              <div class="student-name">{{ scope.row.student_name }}</div>
              <div class="admission-number">{{ scope.row.admission_number }}</div>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Parent" width="150">
          <template #default="scope">
            <span class="parent-name">{{ scope.row.parent_name }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Class" width="100">
          <template #default="scope">
            <el-tag type="primary" size="small">{{ scope.row.class_name }}</el-tag>
          </template>
        </el-table-column>

        <el-table-column label="Amount" width="120" align="right">
          <template #default="scope">
            <div class="amount-info">
              <div class="fee-amount">Rs. {{ scope.row.fee_amount }}</div>
              <div class="fine-amount" v-if="scope.row.fine_amount > 0">
                + Rs. {{ scope.row.fine_amount }} fine
              </div>
              <div class="total-amount">
                <strong>Rs. {{ scope.row.total_with_fine }}</strong>
              </div>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Due Date" width="120">
          <template #default="scope">
            <div class="due-date" :class="{ 'overdue': isOverdue(scope.row) }">
              {{ formatDate(scope.row.due_date) }}
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Status" width="100">
          <template #default="scope">
            <el-tag 
              :type="getStatusTagType(scope.row)" 
              effect="light"
              size="small"
            >
              {{ getStatusLabel(scope.row) }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="Actions" width="200" align="center">
          <template #default="scope">
            <div class="action-buttons">
              <el-button 
                v-if="scope.row.status === 'unpaid'"
                type="success" 
                size="small"
                @click.stop="markAsPaid(scope.row)"
              >
                <el-icon><Money /></el-icon>
                Mark Paid
              </el-button>
              
              <el-button 
                type="primary" 
                size="small"
                @click.stop="reprintVoucher(scope.row)"
              >
                <el-icon><Printer /></el-icon>
                Reprint
              </el-button>
              
              <el-button 
                v-if="scope.row.status === 'unpaid'"
                type="danger" 
                size="small"
                @click.stop="cancelVoucher(scope.row)"
              >
                <el-icon><Close /></el-icon>
                Cancel
              </el-button>
            </div>
          </template>
        </el-table-column>
      </el-table>

      <!-- Pagination -->
      <div class="pagination-section">
        <el-pagination
          v-show="total > 0"
          v-model:current-page="query.page"
          v-model:page-size="query.limit"
          :page-sizes="[10, 15, 20, 30, 50]"
          background
          layout="total, sizes, prev, pager, next, jumper"
          :total="total"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>

    <!-- Payment Dialog -->
    <el-dialog
      title="Mark Voucher as Paid"
      v-model="showPaymentDialog"
      width="400px"
      class="payment-dialog"
    >
      <el-form :model="paymentForm" label-width="120px">
        <el-form-item label="Voucher #">
          <el-input :value="selectedVoucher?.voucher_number" disabled />
        </el-form-item>
        
        <el-form-item label="Student">
          <el-input :value="selectedVoucher?.student_name" disabled />
        </el-form-item>
        
        <el-form-item label="Total Amount">
          <el-input :value="displayTotalAmount" disabled />
        </el-form-item>
        
        <el-form-item label="Paid Amount" required>
          <el-input-number
            v-model="paymentForm.paidAmount"
            :min="0"
            :max="correctPaymentAmount"
            :step="50"
            style="width: 100%"
          />
        </el-form-item>
        
        <el-form-item label="Payment Date" required>
          <el-date-picker
            v-model="paymentForm.paymentDate"
            type="date"
            placeholder="Select payment date"
            style="width: 100%"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="showPaymentDialog = false">Cancel</el-button>
          <el-button 
            type="primary" 
            @click="confirmPayment"
            :loading="paymentLoading"
            :disabled="!paymentForm.paidAmount || !paymentForm.paymentDate"
          >
            Mark as Paid
          </el-button>
        </div>
      </template>
    </el-dialog>

    <!-- Statistics Dialog -->
    <el-dialog
      title="Voucher Statistics"
      v-model="showStatsDialog"
      width="600px"
      class="stats-dialog"
    >
      <div class="stats-content">
        <el-row :gutter="20">
          <el-col :span="12">
            <div class="stat-item">
              <span class="stat-label">Total Vouchers:</span>
              <span class="stat-value">{{ stats.total_vouchers }}</span>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="stat-item">
              <span class="stat-label">Total Generated Amount:</span>
              <span class="stat-value">Rs. {{ formatAmount(stats.total_amount_generated) }}</span>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="stat-item">
              <span class="stat-label">Amount Collected:</span>
              <span class="stat-value success">Rs. {{ formatAmount(stats.total_amount_collected) }}</span>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="stat-item">
              <span class="stat-label">Pending Amount:</span>
              <span class="stat-value danger">Rs. {{ formatAmount(stats.pending_amount) }}</span>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="stat-item">
              <span class="stat-label">Collection Rate:</span>
              <span class="stat-value">{{ getCollectionRate() }}%</span>
            </div>
          </el-col>
          <el-col :span="12">
            <div class="stat-item">
              <span class="stat-label">Overdue Vouchers:</span>
              <span class="stat-value warning">{{ stats.overdue_vouchers }}</span>
            </div>
          </el-col>
        </el-row>
      </div>
    </el-dialog>

    <!-- Voucher Print Component -->
    <fee-voucher-print 
      v-if="showPrintDialog" 
      :vouchers="voucherToPrint"
      :show-dialog="showPrintDialog"
      @close="handlePrintClose"
    />
  </div>
</template>

<script>
import {
  Search,
  DataAnalysis,
  Warning,
  Files,
  CircleCheckFilled,
  Clock,
  WarningFilled,
  List,
  Money,
  Printer,
  Close
} from '@element-plus/icons-vue'
import moment from 'moment'
import { debounce } from 'lodash'
import { 
  getFeeVouchers, 
  getFeeVoucherStats, 
  updateFeeVoucherStatus,
  getOutstandingVouchers,
  reprintFeeVoucher 
} from '@/api/fee'
import Resource from '@/api/resource'
import FeeVoucherPrint from './component/FeeVoucherPrint.vue'

const classesResource = new Resource('classes')

export default {
  name: 'FeeVoucherTracking',
  components: {
    FeeVoucherPrint
  },
  data() {
    return {
      loading: false,
      paymentLoading: false,
      vouchersList: [],
      classes: [],
      total: 0,
      stats: {},
      dateRange: null,
      selectedVoucher: null,
      showPaymentDialog: false,
      showStatsDialog: false,
      showPrintDialog: false,
      voucherToPrint: [],
      query: {
        page: 1,
        limit: 15,
        status: '',
        class_name: '',
        search: '',
        date_from: '',
        date_to: ''
      },
      paymentForm: {
        paidAmount: 0,
        paymentDate: null
      }
    }
  },
  computed: {
    // Calculate the correct amount to pay (with or without fine based on current date)
    correctPaymentAmount() {
      if (!this.selectedVoucher) return 0
      
      const today = new Date()
      const dueDate = new Date(this.selectedVoucher.due_date)
      
      // If today is before or equal to due date, don't include fine
      if (today <= dueDate) {
        return parseFloat(this.selectedVoucher.fee_amount)
      } else {
        // If overdue, include fine
        return parseFloat(this.selectedVoucher.total_with_fine)
      }
    },
    
    // Display text for the total amount
    displayTotalAmount() {
      if (!this.selectedVoucher) return 'Rs. 0'
      
      const today = new Date()
      const dueDate = new Date(this.selectedVoucher.due_date)
      
      if (today <= dueDate) {
        return `Rs. ${this.selectedVoucher.fee_amount} (No fine - paid before due date)`
      } else {
        return `Rs. ${this.selectedVoucher.total_with_fine} (Including Rs. ${this.selectedVoucher.fine_amount} fine)`
      }
    }
  },
  created() {
    this.getVouchers()
    this.getClasses()
    this.getStatistics()
  },
  methods: {
    debounceInput: debounce(function () {
      this.getVouchers()
    }, 500),

    async getVouchers() {
      this.loading = true
      try {
        const params = { ...this.query }
        
        // Clean up empty parameters 
        Object.keys(params).forEach(key => {
          if (params[key] === '' || params[key] === null || params[key] === undefined) {
            delete params[key]
          }
        })
        
        if (params.status === 'overdue') {
          // Handle overdue status differently
          params.status = 'unpaid'
          params.overdue_only = true
        }
        
        // The request interceptor returns response.data, so 'data' IS the actual response
        const data = await getFeeVouchers(params)
        
        if (data && data.success && data.vouchers) {
          this.vouchersList = data.vouchers.data || []
          this.total = data.vouchers.total || 0
        } else {
          this.vouchersList = []
          this.total = 0
        }
      } catch (error) {
        console.error('Error fetching vouchers:', error)
        this.$message.error('Failed to load vouchers')
        this.vouchersList = []
        this.total = 0
      } finally {
        this.loading = false
      }
    },

    async getClasses() {
      try {
        const { data } = await classesResource.list()
        this.classes = data.classes?.data || data.classes || []
      } catch (error) {
        console.error('Error fetching classes:', error)
        this.classes = []
      }
    },

    async getStatistics() {
      try {
        const data = await getFeeVoucherStats()
        this.stats = data.statistics || {}
      } catch (error) {
        console.error('Error fetching statistics:', error)
        this.stats = {}
      }
    },

    async loadOutstandingVouchers() {
      this.query.status = 'unpaid'
      await this.getVouchers()
      this.$message.info('Showing outstanding vouchers')
    },

    handleFilter() {
      this.query.page = 1
      this.getVouchers()
    },

    handleDateFilter(dateRange) {
      if (dateRange) {
        this.query.date_from = moment(dateRange[0]).format('YYYY-MM-DD')
        this.query.date_to = moment(dateRange[1]).format('YYYY-MM-DD')
      } else {
        this.query.date_from = ''
        this.query.date_to = ''
      }
      this.handleFilter()
    },

    async handleSizeChange(val) {
      this.query.limit = val
      await this.getVouchers()
    },

    async handleCurrentChange(val) {
      this.query.page = val
      await this.getVouchers()
    },

    handleRowClick(row) {
      // Future: Show voucher details dialog
      console.log('Voucher details:', row)
    },

    markAsPaid(voucher) {
      this.selectedVoucher = voucher
      
      // Calculate correct amount based on current date vs due date
      const today = new Date()
      const dueDate = new Date(voucher.due_date)
      
      if (today <= dueDate) {
        // If paying before or on due date, don't include fine
        this.paymentForm.paidAmount = parseFloat(voucher.fee_amount)
      } else {
        // If paying after due date, include fine
        this.paymentForm.paidAmount = parseFloat(voucher.total_with_fine)
      }
      
      this.paymentForm.paymentDate = new Date()
      this.showPaymentDialog = true
    },

    async confirmPayment() {
      this.paymentLoading = true
      try {
        await updateFeeVoucherStatus(
          this.selectedVoucher.id,
          'paid',
          this.paymentForm.paidAmount,
          this.paymentForm.paymentDate
        )
        
        this.$message.success('Voucher marked as paid successfully!')
        this.showPaymentDialog = false
        this.getVouchers()
        this.getStatistics()
      } catch (error) {
        console.error('Error updating payment:', error)
        this.$message.error('Failed to update payment status')
      } finally {
        this.paymentLoading = false
      }
    },

    async reprintVoucher(voucher) {
      try {
        const response = await reprintFeeVoucher(voucher.id)
        
        // Since axios interceptor returns response.data directly, 
        // the response IS the data object
        if (response && response.success) {
          // Format the voucher data for the print component
          const voucherData = response.voucher
          
          // Validate voucher data
          if (!voucherData || typeof voucherData !== 'object') {
            console.error('Invalid voucher data received:', voucherData)
            this.$message.error('Invalid voucher data received')
            return
          }
          
          this.voucherToPrint = [voucherData]
          this.showPrintDialog = true
          console.log('Reprint: Opening print dialog with voucher:', voucherData)
          console.log('Reprint: showPrintDialog state:', this.showPrintDialog)
          this.$message.success('Voucher ready for printing')
        } else {
          this.$message.error(response?.message || 'Failed to get voucher data')
        }
      } catch (error) {
        console.error('Error reprinting voucher:', error)
        this.$message.error(`Failed to reprint voucher: ${error.message || error}`)
      }
    },

    async cancelVoucher(voucher) {
      try {
        await this.$confirm(
          `Are you sure you want to cancel voucher ${voucher.voucher_number}?`,
          'Cancel Voucher',
          {
            confirmButtonText: 'Yes, Cancel',
            cancelButtonText: 'No',
            type: 'warning'
          }
        )
        
        await updateFeeVoucherStatus(voucher.id, 'cancelled')
        this.$message.success('Voucher cancelled successfully!')
        this.getVouchers()
        this.getStatistics()
      } catch (error) {
        if (error !== 'cancel') {
          console.error('Error cancelling voucher:', error)
          this.$message.error('Failed to cancel voucher')
        }
      }
    },

    handlePrintClose() {
      this.showPrintDialog = false
      this.voucherToPrint = []
    },

    formatDate(date) {
      return moment(date).format('DD MMM, YY')
    },

    formatAmount(amount) {
      return parseFloat(amount || 0).toLocaleString()
    },

    isOverdue(voucher) {
      return voucher.status === 'unpaid' && moment(voucher.due_date).isBefore(moment())
    },

    getStatusTagType(voucher) {
      if (voucher.status === 'paid') return 'success'
      if (voucher.status === 'cancelled') return 'info'
      if (this.isOverdue(voucher)) return 'danger'
      return 'warning'
    },

    getStatusLabel(voucher) {
      if (voucher.status === 'paid') return 'Paid'
      if (voucher.status === 'cancelled') return 'Cancelled'
      if (this.isOverdue(voucher)) return 'Overdue'
      return 'Unpaid'
    },

    getCollectionRate() {
      if (!this.stats.total_amount_generated || this.stats.total_amount_generated === 0) {
        return '0'
      }
      const rate = (this.stats.total_amount_collected / this.stats.total_amount_generated) * 100
      return rate.toFixed(1)
    }
  }
}
</script>

<style scoped>
.app-container {
  padding: 16px;
  background: #f5f7fa;
  min-height: 100vh;
}

/* Filter Header */
.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 16px 20px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 16px;
}

.filter-section {
  display: flex;
  gap: 16px;
  align-items: center;
  flex-wrap: wrap;
}

.status-select,
.class-select {
  width: 150px;
}

.date-picker {
  width: 250px;
}

.search-input {
  width: 300px;
}

/* Statistics Cards */
.stats-cards {
  margin-bottom: 20px;
}

.stat-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 12px;
  overflow: hidden;
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.stat-card :deep(.el-card__body) {
  padding: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.stat-content .stat-value {
  font-size: 32px;
  font-weight: 700;
  line-height: 1;
  margin-bottom: 4px;
}

.stat-content .stat-label {
  font-size: 14px;
  color: #909399;
  font-weight: 500;
}

.stat-icon {
  font-size: 40px;
  opacity: 0.3;
}

.stat-card.total {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.stat-card.paid {
  background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
  color: white;
}

.stat-card.unpaid {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
  color: white;
}

.stat-card.overdue {
  background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
  color: #333;
}

/* Table */
.table-card {
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-info {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-icon {
  font-size: 20px;
  color: #409eff;
}

.header-title {
  font-size: 18px;
  font-weight: 600;
  color: #303133;
}

.tracking-table {
  cursor: pointer;
}

.tracking-table :deep(.el-table__header th) {
  background: #f8fafc;
  color: #606266;
  font-weight: 600;
}

.voucher-number {
  font-family: 'Courier New', monospace;
  font-weight: 600;
  color: #409eff;
  font-size: 14px;
}

.student-info .student-name {
  font-weight: 600;
  color: #303133;
}

.student-info .admission-number {
  font-size: 12px;
  color: #909399;
  margin-top: 2px;
}

.amount-info {
  text-align: right;
}

.fee-amount {
  color: #606266;
  font-size: 13px;
}

.fine-amount {
  color: #f56c6c;
  font-size: 11px;
  margin: 2px 0;
}

.total-amount {
  color: #27ae60;
  font-weight: 600;
  font-family: 'Courier New', monospace;
}

.due-date {
  font-weight: 500;
}

.due-date.overdue {
  color: #f56c6c;
  font-weight: 700;
}

.action-buttons {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  justify-content: center;
}

.action-buttons .el-button {
  margin: 0;
}

/* Pagination */
.pagination-section {
  margin-top: 16px;
  display: flex;
  justify-content: center;
}

/* Dialog Styles */
.payment-dialog :deep(.el-dialog__header),
.stats-dialog :deep(.el-dialog__header) {
  background: linear-gradient(135deg, #409eff, #36a3f7);
  color: white;
  padding: 20px 24px;
  margin: 0;
}

.payment-dialog :deep(.el-dialog__title),
.stats-dialog :deep(.el-dialog__title) {
  color: white;
  font-weight: 600;
}

.stats-content {
  padding: 20px 0;
}

.stat-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #f0f2f5;
}

.stat-item:last-child {
  border-bottom: none;
}

.stat-item .stat-label {
  color: #606266;
  font-weight: 500;
}

.stat-item .stat-value {
  font-weight: 600;
  color: #303133;
}

.stat-item .stat-value.success {
  color: #67c23a;
}

.stat-item .stat-value.danger {
  color: #f56c6c;
}

.stat-item .stat-value.warning {
  color: #e6a23c;
}

/* Responsive Design */
@media (max-width: 768px) {
  .filter-header {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }

  .filter-section {
    justify-content: center;
  }

  .status-select,
  .class-select,
  .date-picker,
  .search-input {
    width: 100%;
  }

  .action-buttons {
    flex-direction: column;
  }

  .action-buttons .el-button {
    width: 100%;
    margin-bottom: 4px;
  }
}
</style>
