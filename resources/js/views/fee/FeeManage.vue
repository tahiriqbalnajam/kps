<template>
  <div class="app-container">
    <!-- Compact Filter Header -->
    <div class="compact-filter-header">
      <div class="filter-section">
        <!-- Search Controls -->
        <div class="search-controls">
          <el-select
            v-model="query.searchType"
            placeholder="Search By"
            class="search-type-select"
            size="default"
          >
            <el-option label="Student Name" value="student_name" />
            <el-option label="Parent Name" value="parent_name" />
            <el-option label="Voucher #" value="voucher_number" />
            <el-option label="Roll No" value="roll_no" />
          </el-select>

          <el-input
            v-model="query.search"
            placeholder="Search..."
            class="search-input"
            v-on:input="debounceInput"
            clearable
            size="default"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
        </div>

        <!-- Filter Controls -->
        <div class="filter-controls">
          <el-select
            v-model="query.status"
            placeholder="Status"
            clearable
            class="filter-select"
            @change="handleFilter"
            size="default"
          >
            <el-option label="All" value="" />
            <el-option label="Unpaid" value="unpaid" />
            <el-option label="Partial" value="partially_paid" />
            <el-option label="Paid" value="paid" />
            <el-option label="Overdue" value="overdue" />
            <el-option label="Cancelled" value="cancelled" />
          </el-select>

          <el-select
            v-model="query.class_name"
            placeholder="Class"
            clearable
            class="filter-select"
            @change="handleFilter"
            size="default"
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
            range-separator="-"
            start-placeholder="Start"
            end-placeholder="End"
            class="date-range-picker"
            value-format="YYYY-MM-DD"
            @change="handleDateFilter"
            size="default"
          />
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="action-section">
        <el-button-group class="action-button-group">
          <el-tooltip content="Search" placement="top">
            <el-button 
              type="primary" 
              @click="getVouchers()"
              size="default"
              class="action-btn"
            >
              <el-icon><Search /></el-icon>
            </el-button>
          </el-tooltip>

          <el-tooltip content="Print Selected" placement="top">
            <el-button
              type="success"
              :disabled="selectedVouchers.length === 0"
              @click="printSelected"
              size="default"
              class="action-btn"
            >
              <el-icon><Printer /></el-icon>
              <span v-if="selectedVouchers.length > 0" style="margin-left: 4px">({{ selectedVouchers.length }})</span>
            </el-button>
          </el-tooltip>

          <el-tooltip content="Voucher Statistics" placement="top">
            <el-button 
              type="info" 
              plain 
              @click="openStatsDrawer"
              size="default"
              class="action-btn"
            >
              <el-icon><DataAnalysis /></el-icon>
            </el-button>
          </el-tooltip>
        </el-button-group>
      </div>
    </div>

    <!-- Vouchers Table -->
    <el-table
      :data="vouchersList"
      v-loading="loading"
      style="width: 100%"
      max-height="600"
      size="small"
      @selection-change="handleSelectionChange"
    >
      <el-table-column type="selection" width="45" />

      <el-table-column label="Voucher #" width="130" prop="voucher_number">
        <template #default="scope">
          <div class="voucher-num">
            <span class="vnum">{{ scope.row.voucher_number }}</span>
            <el-tag v-if="scope.row.voucher_type !== 'monthly'" size="small" type="info">{{ scope.row.voucher_type }}</el-tag>
          </div>
        </template>
      </el-table-column>

      <el-table-column label="Student" min-width="200" prop="student_name">
        <template #default="scope">
          <div class="student-cell">
            <div class="student-name">{{ scope.row.student_name }}</div>
            <div class="student-meta">
              <span>{{ scope.row.admission_number }}</span>
              <el-tag type="primary" size="small" effect="plain">{{ scope.row.class_name }}</el-tag>
            </div>
            <div v-if="scope.row.parent_name" class="student-parent">
              {{ scope.row.parent_name }}
            </div>
          </div>
        </template>
      </el-table-column>

      <el-table-column label="Amount" width="140" align="right">
        <template #default="scope">
          <div class="amount-cell">
            <div class="base-amount">Rs. {{ scope.row.fee_amount }}</div>
            <div class="fine-amount" v-if="parseFloat(scope.row.fine_amount) > 0">
              + Rs. {{ scope.row.fine_amount }} fine
            </div>
            <div class="total-amount-val">
              <strong>Rs. {{ scope.row.total_with_fine }}</strong>
            </div>
            <div v-if="scope.row.status === 'partially_paid'" class="partial-info">
              <span class="paid-badge">Paid: Rs. {{ scope.row.paid_amount || 0 }}</span>
              <span class="remaining-badge">Due: Rs. {{ getRemainingAmount(scope.row) }}</span>
            </div>
          </div>
        </template>
      </el-table-column>

      <el-table-column label="Due" width="100">
        <template #default="scope">
          <div class="due-cell" :class="{ 'is-overdue': isOverdue(scope.row) }">
            <div class="due-date-text">{{ formatDate(scope.row.due_date) }}</div>
            <div class="due-diff" v-if="isOverdue(scope.row)">
              {{ getDaysOverdue(scope.row) }}d late
            </div>
          </div>
        </template>
      </el-table-column>

      <el-table-column label="Status" width="110" align="center">
        <template #default="scope">
          <el-tag
            :type="getStatusTagType(scope.row)"
            effect="dark"
            size="small"
            round
          >
            {{ getStatusLabel(scope.row) }}
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column label="Actions" width="180" align="center">
        <template #default="scope">
          <div class="action-btns">
            <el-tooltip content="Mark Paid" placement="top" v-if="scope.row.status === 'unpaid' || scope.row.status === 'partially_paid'">
              <el-button type="success" size="small" circle @click.stop="markAsPaid(scope.row)">
                <el-icon><Money /></el-icon>
              </el-button>
            </el-tooltip>
            <el-tooltip content="Reprint" placement="top">
              <el-button type="primary" size="small" circle @click.stop="reprintVoucher(scope.row)">
                <el-icon><Printer /></el-icon>
              </el-button>
            </el-tooltip>
            <el-tooltip content="Send Reminder" placement="top" v-if="scope.row.status === 'unpaid' || scope.row.status === 'partially_paid'">
              <el-button type="warning" size="small" circle @click.stop="sendReminder(scope.row)">
                <el-icon><Bell /></el-icon>
              </el-button>
            </el-tooltip>
            <el-tooltip content="Cancel" placement="top" v-if="scope.row.status === 'unpaid' || scope.row.status === 'partially_paid'">
              <el-button type="danger" size="small" circle @click.stop="cancelVoucher(scope.row)">
                <el-icon><Close /></el-icon>
              </el-button>
            </el-tooltip>
          </div>
        </template>
      </el-table-column>
    </el-table>

    <!-- Pagination -->
    <el-pagination
      v-show="total > 0"
      v-model:current-page="query.page"
      v-model:page-size="query.limit"
      :page-sizes="[15, 30, 50, 100]"
      background
      layout="total, sizes, prev, pager, next, jumper"
      :total="total"
      @size-change="handleSizeChange"
      @current-change="handleCurrentChange"
    />

    <!-- Stats Drawer (70% width) -->
    <el-drawer
      title="Voucher Statistics"
      v-model="showStatsDrawer"
      direction="rtl"
      size="70%"
    >
      <template #header>
        <div class="drawer-header-row">
          <span class="drawer-header-title">Voucher Statistics</span>
          <el-button type="primary" size="small" @click="printStatsPDF">
            <el-icon class="el-icon--left"><Printer /></el-icon> Download PDF
          </el-button>
        </div>
      </template>

      <div class="stats-drawer-body">
        <!-- Stats Filters -->
        <div class="drawer-section">
          <div class="drawer-section-title">Filters</div>
          <el-row :gutter="16" align="middle">
            <el-col :span="10">
              <el-date-picker
                v-model="statsDateRange"
                type="daterange"
                range-separator="→"
                start-placeholder="From"
                end-placeholder="To"
                style="width: 100%"
                @change="loadStats"
                :shortcuts="dateShortcuts"
                size="default"
              />
            </el-col>
            <el-col :span="5">
              <el-select
                v-model="statsFilters.class_name"
                placeholder="All Classes"
                clearable
                style="width: 100%"
                @change="loadStats"
                size="default"
              >
                <el-option
                  v-for="cls in classes"
                  :key="cls.name"
                  :label="cls.name"
                  :value="cls.name"
                />
              </el-select>
            </el-col>
            <el-col :span="5">
              <el-select
                v-model="statsFilters.voucher_type"
                placeholder="All Types"
                clearable
                style="width: 100%"
                @change="loadStats"
                size="default"
              >
                <el-option label="Monthly" value="monthly" />
                <el-option label="Custom" value="custom" />
                <el-option label="Multiple" value="multiple" />
              </el-select>
            </el-col>
            <el-col :span="4">
              <el-button type="primary" @click="loadStats" style="width: 100%">
                <el-icon class="el-icon--left"><Refresh /></el-icon> Refresh
              </el-button>
            </el-col>
          </el-row>
        </div>

        <!-- Voucher Counts -->
        <div class="drawer-section">
          <div class="drawer-section-title">Voucher Counts</div>
          <el-row :gutter="16">
            <el-col :span="5" v-for="item in statCards" :key="item.key">
              <div class="drawer-stat-card" @click="filterByStatusAndClose(item.status)">
                <div class="stat-card-value" :style="{ color: item.color }">{{ stats[item.key] || 0 }}</div>
                <div class="stat-card-label">{{ item.label }}</div>
              </div>
            </el-col>
          </el-row>
        </div>

        <!-- Financial Summary -->
        <div class="drawer-section">
          <div class="drawer-section-title">Financial Summary</div>
          <el-row :gutter="16">
            <el-col :span="6">
              <div class="finance-card-drawer">
                <div class="fc-label">Total Generated</div>
                <div class="fc-value" style="color: #409eff">Rs. {{ formatAmount(stats.total_amount_generated) }}</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="finance-card-drawer">
                <div class="fc-label">Collected</div>
                <div class="fc-value" style="color: #67c23a">Rs. {{ formatAmount(stats.total_amount_collected) }}</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="finance-card-drawer">
                <div class="fc-label">Pending</div>
                <div class="fc-value" style="color: #f56c6c">Rs. {{ formatAmount(stats.pending_amount) }}</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="finance-card-drawer">
                <div class="fc-label">Collection Rate</div>
                <div class="fc-value" style="color: #e6a23c">{{ getCollectionRate() }}%</div>
                <div class="rate-bar">
                  <div class="rate-fill" :style="{ width: getCollectionRate() + '%' }"></div>
                </div>
              </div>
            </el-col>
          </el-row>
        </div>
      </div>
    </el-drawer>

    <!-- Payment Dialog -->
    <el-dialog
      title="Record Payment"
      v-model="showPaymentDialog"
      width="450px"
    >
      <el-form :model="paymentForm" label-width="130px" size="default">
        <el-form-item label="Voucher">
          <el-input :value="selectedVoucher?.voucher_number" disabled />
        </el-form-item>
        <el-form-item label="Student">
          <el-input :value="selectedVoucher?.student_name" disabled />
        </el-form-item>
        <el-form-item :label="selectedVoucher?.status === 'partially_paid' ? 'Remaining' : 'Total Due'">
          <el-input :value="displayTotalAmount" disabled />
        </el-form-item>
        <el-form-item label="Pay Amount" required>
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
            placeholder="Select date"
            style="width: 100%"
          />
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="showPaymentDialog = false">Cancel</el-button>
        <el-button
          type="primary"
          @click="confirmPayment"
          :loading="paymentLoading"
          :disabled="!paymentForm.paidAmount || !paymentForm.paymentDate"
        >
          Confirm Payment
        </el-button>
      </template>
    </el-dialog>

    <!-- Reminder Dialog -->
    <el-dialog
      title="Send Reminder"
      v-model="showReminderDialog"
      width="500px"
    >
      <el-form :model="reminderForm" label-width="110px">
        <el-form-item label="Student">
          <el-input :value="selectedVoucher?.student_name" disabled />
        </el-form-item>
        <el-form-item label="Template">
          <el-radio-group v-model="reminderForm.template" size="small">
            <el-radio-button label="gentle">Gentle</el-radio-button>
            <el-radio-button label="urgent">Urgent</el-radio-button>
            <el-radio-button label="final">Final</el-radio-button>
          </el-radio-group>
        </el-form-item>
        <el-form-item label="Send Via">
          <el-checkbox-group v-model="reminderForm.channels">
            <el-checkbox label="sms">SMS</el-checkbox>
            <el-checkbox label="whatsapp">WhatsApp</el-checkbox>
          </el-checkbox-group>
        </el-form-item>
      </el-form>

      <template #footer>
        <el-button @click="showReminderDialog = false">Cancel</el-button>
        <el-button
          type="primary"
          @click="confirmReminder"
          :loading="sendingReminder"
          :disabled="reminderForm.channels.length === 0"
        >
          Send Reminder
        </el-button>
      </template>
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
  Search, Files, CircleCheckFilled, Clock, WarningFilled, List, Money,
  Printer, Close, Bell, Refresh, DataAnalysis, SemiSelect, Download
} from '@element-plus/icons-vue'
import moment from 'moment'
import { debounce } from 'lodash'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'
import {
  getFeeVouchers,
  getFeeVoucherStats,
  updateFeeVoucherStatus,
  reprintFeeVoucher,
  sendVoucherReminders
} from '@/api/fee'
import Resource from '@/api/resource'
import FeeVoucherPrint from './component/FeeVoucherPrint.vue'

const classesResource = new Resource('classes')

export default {
  name: 'FeeManage',
  components: { FeeVoucherPrint },
  data() {
    return {
      loading: false,
      paymentLoading: false,
      sendingReminder: false,
      vouchersList: [],
      classes: [],
      total: 0,
      stats: {},
      dateRange: null,
      statsDateRange: null,
      selectedVoucher: null,
      selectedVouchers: [],
      showPaymentDialog: false,
      showReminderDialog: false,
      showPrintDialog: false,
      showStatsDrawer: false,
      voucherToPrint: [],
      query: {
        page: 1,
        limit: 15,
        status: '',
        class_name: '',
        search: '',
        searchType: 'student_name',
        date_from: '',
        date_to: ''
      },
      statsFilters: {
        class_name: '',
        voucher_type: '',
        date_from: '',
        date_to: ''
      },
      paymentForm: {
        paidAmount: 0,
        paymentDate: null
      },
      reminderForm: {
        template: 'gentle',
        channels: ['sms']
      },
      statCards: [
        { key: 'total_vouchers', label: 'Total', status: '', color: '#409eff' },
        { key: 'paid_vouchers', label: 'Paid', status: 'paid', color: '#67c23a' },
        { key: 'partially_paid_vouchers', label: 'Partial', status: 'partially_paid', color: '#e6a23c' },
        { key: 'unpaid_vouchers', label: 'Unpaid', status: 'unpaid', color: '#909399' },
        { key: 'overdue_vouchers', label: 'Overdue', status: 'overdue', color: '#f56c6c' }
      ],
      dateShortcuts: [
        {
          text: 'This Month',
          value: () => {
            const start = moment().startOf('month').toDate()
            const end = moment().endOf('month').toDate()
            return [start, end]
          }
        },
        {
          text: 'Last Month',
          value: () => {
            const start = moment().subtract(1, 'month').startOf('month').toDate()
            const end = moment().subtract(1, 'month').endOf('month').toDate()
            return [start, end]
          }
        },
        {
          text: 'Last 3 Months',
          value: () => {
            const start = moment().subtract(3, 'months').startOf('month').toDate()
            const end = moment().endOf('month').toDate()
            return [start, end]
          }
        },
        {
          text: 'This Year',
          value: () => {
            const start = moment().startOf('year').toDate()
            const end = moment().endOf('year').toDate()
            return [start, end]
          }
        },
        {
          text: 'Last Year',
          value: () => {
            const start = moment().subtract(1, 'year').startOf('year').toDate()
            const end = moment().subtract(1, 'year').endOf('year').toDate()
            return [start, end]
          }
        }
      ],
      Refresh,
      DataAnalysis
    }
  },
  computed: {
    correctPaymentAmount() {
      if (!this.selectedVoucher) return 0
      const today = new Date()
      const dueDate = new Date(this.selectedVoucher.due_date)
      const paidAmount = parseFloat(this.selectedVoucher.paid_amount || 0)
      if (today <= dueDate) {
        return parseFloat(this.selectedVoucher.fee_amount) - paidAmount
      } else {
        return parseFloat(this.selectedVoucher.total_with_fine) - paidAmount
      }
    },
    displayTotalAmount() {
      if (!this.selectedVoucher) return 'Rs. 0'
      const today = new Date()
      const dueDate = new Date(this.selectedVoucher.due_date)
      const paidAmount = parseFloat(this.selectedVoucher.paid_amount || 0)
      const isPartial = this.selectedVoucher.status === 'partially_paid'

      if (today <= dueDate) {
        const total = parseFloat(this.selectedVoucher.fee_amount)
        const remaining = total - paidAmount
        return isPartial
          ? `Rs. ${remaining} remaining (Rs. ${paidAmount} paid)`
          : `Rs. ${this.selectedVoucher.fee_amount} (no fine)`
      } else {
        const total = parseFloat(this.selectedVoucher.total_with_fine)
        const remaining = total - paidAmount
        return isPartial
          ? `Rs. ${remaining} remaining (incl. Rs. ${this.selectedVoucher.fine_amount} fine)`
          : `Rs. ${this.selectedVoucher.total_with_fine} (incl. Rs. ${this.selectedVoucher.fine_amount} fine)`
      }
    }
  },
  created() {
    this.getVouchers()
    this.getClasses()
    this.loadStats()
  },
  methods: {
    debounceInput: debounce(function () {
      this.getVouchers()
    }, 500),

    /**
     * Build params in Spatie QueryBuilder format: filter[key]=value
     */
    buildSpatieParams(filters, extras = {}) {
      const params = { ...extras }
      Object.keys(filters).forEach(key => {
        const val = filters[key]
        if (val !== '' && val !== null && val !== undefined) {
          params[`filter[${key}]`] = val
        }
      })
      return params
    },

    async getVouchers() {
      this.loading = true
      try {
        const filters = { ...this.query }
        if (filters.search && this.query.searchType) {
          const type = this.query.searchType
          filters[type] = filters.search
          delete filters.search
        }
        // Remove searchType from filters to avoid sending it as a filter param
        if (filters.searchType) {
          delete filters.searchType
        }

        const page = filters.page
        const limit = filters.limit
        delete filters.page
        delete filters.limit

        // Handle overdue special case
        if (filters.status === 'overdue') {
          delete filters.status
          filters.overdue_only = true
        }

        const params = this.buildSpatieParams(filters, { page, limit })
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

    async loadStats() {
      try {
        const filters = { ...this.statsFilters }
        if (this.statsDateRange) {
          filters.date_from = moment(this.statsDateRange[0]).format('YYYY-MM-DD')
          filters.date_to = moment(this.statsDateRange[1]).format('YYYY-MM-DD')
        }
        const params = this.buildSpatieParams(filters)
        const data = await getFeeVoucherStats(params)
        this.stats = data.statistics || {}
      } catch (error) {
        console.error('Error fetching stats:', error)
        this.stats = {}
      }
    },

    openStatsDrawer() {
      this.loadStats()
      this.showStatsDrawer = true
    },

    filterByStatus(status) {
      this.query.status = status
      this.query.page = 1
      this.getVouchers()
    },

    filterByStatusAndClose(status) {
      this.filterByStatus(status)
      this.showStatsDrawer = false
    },

    handleFilter() {
      this.query.page = 1
      this.getVouchers()
    },

    handleDateFilter(dateRange) {
      if (dateRange) {
        this.query.date_from = dateRange[0]
        this.query.date_to = dateRange[1]
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

    handleSelectionChange(selection) {
      this.selectedVouchers = selection
    },

    async printSelected() {
      if (this.selectedVouchers.length === 0) return
      try {
        const printVouchers = []
        for (const voucher of this.selectedVouchers) {
          try {
            const response = await reprintFeeVoucher(voucher.id)
            if (response && response.success && response.voucher) {
              printVouchers.push(response.voucher)
            }
          } catch (e) {
            console.warn('Could not load voucher for print:', voucher.voucher_number, e)
          }
        }
        if (printVouchers.length > 0) {
          this.voucherToPrint = printVouchers
          this.showPrintDialog = true
        } else {
          this.$message.error('Could not load any vouchers for printing')
        }
      } catch (error) {
        console.error('Error printing selected vouchers:', error)
        this.$message.error('Failed to print vouchers')
      }
    },

    printStatsPDF() {
      const doc = new jsPDF()
      const pageWidth = doc.internal.pageSize.getWidth()

      // Title
      doc.setFontSize(18)
      doc.setFont('helvetica', 'bold')
      doc.text('Fee Voucher Statistics Report', pageWidth / 2, 20, { align: 'center' })

      // Date
      doc.setFontSize(10)
      doc.setFont('helvetica', 'normal')
      doc.text(`Generated: ${moment().format('DD MMM, YYYY hh:mm A')}`, pageWidth / 2, 28, { align: 'center' })

      // Filter info
      let filterText = ''
      if (this.statsDateRange) {
        filterText += `Date: ${moment(this.statsDateRange[0]).format('DD MMM YY')} - ${moment(this.statsDateRange[1]).format('DD MMM YY')}`
      }
      if (this.statsFilters.class_name) {
        filterText += `  |  Class: ${this.statsFilters.class_name}`
      }
      if (this.statsFilters.voucher_type) {
        filterText += `  |  Type: ${this.statsFilters.voucher_type}`
      }
      if (filterText) {
        doc.setFontSize(9)
        doc.text(filterText, pageWidth / 2, 34, { align: 'center' })
      }

      // Voucher Counts Table
      autoTable(doc, {
        startY: 42,
        head: [['Metric', 'Count']],
        body: [
          ['Total Vouchers', String(this.stats.total_vouchers || 0)],
          ['Paid', String(this.stats.paid_vouchers || 0)],
          ['Partially Paid', String(this.stats.partially_paid_vouchers || 0)],
          ['Unpaid', String(this.stats.unpaid_vouchers || 0)],
          ['Overdue', String(this.stats.overdue_vouchers || 0)]
        ],
        theme: 'grid',
        headStyles: { fillColor: [64, 158, 255] },
        styles: { fontSize: 11 },
        columnStyles: {
          0: { cellWidth: 80 },
          1: { cellWidth: 40, halign: 'right', fontStyle: 'bold' }
        }
      })

      // Financial Summary Table
      const financialY = doc.lastAutoTable.finalY + 12
      doc.setFontSize(13)
      doc.setFont('helvetica', 'bold')
      doc.text('Financial Summary', 14, financialY)

      autoTable(doc, {
        startY: financialY + 6,
        head: [['Metric', 'Amount']],
        body: [
          ['Total Generated', `Rs. ${this.formatAmount(this.stats.total_amount_generated)}`],
          ['Collected', `Rs. ${this.formatAmount(this.stats.total_amount_collected)}`],
          ['Pending', `Rs. ${this.formatAmount(this.stats.pending_amount)}`],
          ['Collection Rate', `${this.getCollectionRate()}%`]
        ],
        theme: 'grid',
        headStyles: { fillColor: [103, 194, 58] },
        styles: { fontSize: 11 },
        columnStyles: {
          0: { cellWidth: 80 },
          1: { cellWidth: 60, halign: 'right', fontStyle: 'bold' }
        }
      })

      // Footer
      const footerY = doc.internal.pageSize.getHeight() - 10
      doc.setFontSize(8)
      doc.setFont('helvetica', 'italic')
      doc.text('This is a system generated report and does not require a signature.', pageWidth / 2, footerY, { align: 'center' })

      doc.setProperties({
        title: 'Fee Voucher Statistics Report',
        subject: 'Fee Statistics',
        creator: 'School Management System'
      })

      doc.save(`fee-stats-${moment().format('YYYY-MM-DD')}.pdf`)
      this.$message.success('PDF downloaded successfully')
    },

    markAsPaid(voucher) {
      this.selectedVoucher = voucher
      const today = new Date()
      const dueDate = new Date(voucher.due_date)
      const paidAmount = parseFloat(voucher.paid_amount || 0)
      if (today <= dueDate) {
        this.paymentForm.paidAmount = parseFloat(voucher.fee_amount) - paidAmount
      } else {
        this.paymentForm.paidAmount = parseFloat(voucher.total_with_fine) - paidAmount
      }
      this.paymentForm.paymentDate = new Date()
      this.showPaymentDialog = true
    },

    async confirmPayment() {
      this.paymentLoading = true
      try {
        const pendingVoucherIds = []
        if (this.selectedVoucher.fee_breakdown && Array.isArray(this.selectedVoucher.fee_breakdown)) {
          this.selectedVoucher.fee_breakdown.forEach(item => {
            if (item.pending_voucher_id) {
              pendingVoucherIds.push(item.pending_voucher_id)
            }
          })
        }
        await updateFeeVoucherStatus(
          this.selectedVoucher.id,
          'paid',
          this.paymentForm.paidAmount,
          moment(this.paymentForm.paymentDate).format('YYYY-MM-DD HH:mm:ss'),
          pendingVoucherIds.length > 0 ? pendingVoucherIds : null
        )
        this.$message.success('Payment recorded successfully!')
        this.showPaymentDialog = false
        this.getVouchers()
        this.loadStats()
      } catch (error) {
        console.error('Error updating payment:', error)
        this.$message.error('Failed to update payment')
      } finally {
        this.paymentLoading = false
      }
    },

    async reprintVoucher(voucher) {
      try {
        const response = await reprintFeeVoucher(voucher.id)
        if (response && response.success) {
          const voucherData = response.voucher
          if (!voucherData || typeof voucherData !== 'object') {
            this.$message.error('Invalid voucher data')
            return
          }
          this.voucherToPrint = [voucherData]
          this.showPrintDialog = true
        } else {
          this.$message.error(response?.message || 'Failed to get voucher data')
        }
      } catch (error) {
        console.error('Error reprinting voucher:', error)
        this.$message.error('Failed to reprint voucher')
      }
    },

    sendReminder(voucher) {
      this.selectedVoucher = voucher
      this.reminderForm = { template: 'gentle', channels: ['sms'] }
      this.showReminderDialog = true
    },

    async confirmReminder() {
      this.sendingReminder = true
      try {
        const payload = {
          voucher_ids: [this.selectedVoucher.id],
          template: this.reminderForm.template,
          channels: this.reminderForm.channels
        }
        await sendVoucherReminders(payload)
        this.$message.success('Reminder sent successfully!')
        this.showReminderDialog = false
        this.getVouchers()
      } catch (error) {
        console.error('Error sending reminder:', error)
        this.$message.error('Failed to send reminder')
      } finally {
        this.sendingReminder = false
      }
    },

    async cancelVoucher(voucher) {
      try {
        await this.$confirm(
          `Cancel voucher ${voucher.voucher_number}?`,
          'Confirm Cancel',
          { confirmButtonText: 'Yes, Cancel', cancelButtonText: 'No', type: 'warning' }
        )
        await updateFeeVoucherStatus(voucher.id, 'cancelled')
        this.$message.success('Voucher cancelled')
        this.getVouchers()
        this.loadStats()
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

    getRemainingAmount(voucher) {
      const total = parseFloat(voucher.total_with_fine || 0)
      const paid = parseFloat(voucher.paid_amount || 0)
      return (total - paid).toFixed(0)
    },

    getDaysOverdue(voucher) {
      const dueDate = moment(voucher.due_date)
      return moment().diff(dueDate, 'days')
    },

    isOverdue(voucher) {
      return (voucher.status === 'unpaid' || voucher.status === 'partially_paid') && moment(voucher.due_date).isBefore(moment())
    },

    getStatusTagType(voucher) {
      if (voucher.status === 'paid') return 'success'
      if (voucher.status === 'partially_paid') return 'warning'
      if (voucher.status === 'cancelled') return 'info'
      if (this.isOverdue(voucher)) return 'danger'
      return 'warning'
    },

    getStatusLabel(voucher) {
      if (voucher.status === 'paid') return 'Paid'
      if (voucher.status === 'cancelled') return 'Cancelled'
      if (voucher.status === 'partially_paid') {
        return this.isOverdue(voucher) ? 'Overdue (P)' : 'Partial'
      }
      if (this.isOverdue(voucher)) return 'Overdue'
      return 'Unpaid'
    },

    getCollectionRate() {
      if (!this.stats.total_amount_generated || this.stats.total_amount_generated === 0) return '0'
      return ((this.stats.total_amount_collected / this.stats.total_amount_generated) * 100).toFixed(1)
    }
  }
}
</script>

<style scoped>
/* Main Container - same as StudentList */
.app-container {
  padding: 16px;
  background: #f5f7fa;
  min-height: 100vh;
}

/* Compact Filter Header - Adopted from StudentList */
.compact-filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 12px 20px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 16px;
  border: 1px solid #e4e7ed;
}

.filter-section {
  display: flex;
  gap: 16px;
  align-items: center;
  flex: 1;
  flex-wrap: wrap;
}

.search-controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-type-select {
  width: 140px;
  min-width: 140px;
}

.search-input {
  width: 250px;
  min-width: 200px;
}

.filter-controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

.filter-select {
  width: 130px;
  min-width: 130px;
}

.date-range-picker {
  width: 240px;
  min-width: 240px;
}

/* Action Section */
.action-section {
  display: flex;
  align-items: center;
  margin-left: 16px;
}

.action-button-group {
  display: flex;
  gap: 0;
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.action-btn {
  border-radius: 0;
  border-right: 1px solid rgba(255, 255, 255, 0.3);
  font-weight: 500;
  transition: all 0.3s ease;
  min-width: 44px;
  height: 36px;
}

.action-btn:last-child {
  border-right: none;
}

.action-btn:hover {
  transform: translateY(-1px);
  z-index: 1;
  position: relative;
}

/* Element Plus Deep Overrides for rounded look matching StudentList */
.search-input :deep(.el-input__wrapper),
.filter-select :deep(.el-select__wrapper),
.date-range-picker {
  border-radius: 6px;
  transition: all 0.3s ease;
  box-shadow: none !important;
  border: 1px solid #dcdfe6;
}

.search-input :deep(.el-input__wrapper):hover,
.filter-select :deep(.el-select__wrapper):hover,
.date-range-picker:hover {
  border-color: #409eff;
}

.search-input :deep(.el-input__wrapper.is-focus),
.filter-select :deep(.el-select__wrapper.is-focused),
.date-range-picker.is-active {
  border-color: #409eff;
  box-shadow: 0 0 0 1px #409eff !important;
}

/* ─── Table Cells ─── */
.voucher-num {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.vnum {
  font-weight: 600;
  color: #409eff;
  font-size: 13px;
}

.student-cell {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.student-name {
  font-weight: 600;
  color: #303133;
}

.student-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #909399;
}

.student-parent {
  font-size: 11px;
  color: #909399;
  font-style: italic;
}

.amount-cell {
  text-align: right;
  line-height: 1.4;
}

.base-amount {
  color: #606266;
  font-size: 12px;
}

.fine-amount {
  color: #f56c6c;
  font-size: 11px;
}

.total-amount-val {
  color: #67c23a;
  font-weight: 600;
}

.partial-info {
  margin-top: 4px;
  padding-top: 4px;
  border-top: 1px dashed #e4e7ed;
  font-size: 11px;
}

.paid-badge {
  color: #67c23a;
  display: block;
}

.remaining-badge {
  color: #f56c6c;
  font-weight: 600;
  display: block;
}

.due-cell.is-overdue .due-date-text {
  color: #f56c6c;
  font-weight: 600;
}

.due-diff {
  font-size: 11px;
  color: #f56c6c;
  font-weight: 600;
}

.action-btns {
  display: flex;
  gap: 4px;
  justify-content: center;
}

/* ─── Stats Drawer ─── */
.drawer-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.drawer-header-title {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
}

.stats-drawer-body {
  padding: 0;
}

.drawer-section {
  margin-bottom: 24px;
  padding-bottom: 20px;
  border-bottom: 1px solid #ebeef5;
}

.drawer-section:last-child {
  border-bottom: none;
}

.drawer-section-title {
  font-size: 14px;
  font-weight: 600;
  color: #303133;
  margin-bottom: 14px;
}

/* Stat cards in drawer */
.drawer-stat-card {
  background: #f5f7fa;
  border: 1px solid #e4e7ed;
  border-radius: 4px;
  padding: 12px 8px;
  text-align: center;
  cursor: pointer;
  transition: border-color 0.2s;
}

.drawer-stat-card:hover {
  border-color: #409eff;
}

.stat-card-value {
  font-size: 22px;
  font-weight: 700;
  line-height: 1.2;
}

.stat-card-label {
  font-size: 11px;
  color: #909399;
  margin-top: 4px;
}

/* Finance cards in drawer */
.finance-card-drawer {
  background: #f5f7fa;
  border: 1px solid #e4e7ed;
  border-radius: 4px;
  padding: 12px;
}

.fc-label {
  font-size: 12px;
  color: #909399;
  margin-bottom: 6px;
}

.fc-value {
  font-size: 16px;
  font-weight: 700;
}

.rate-bar {
  height: 4px;
  background: #ebeef5;
  border-radius: 2px;
  margin-top: 8px;
  overflow: hidden;
}

.rate-fill {
  height: 100%;
  background: linear-gradient(90deg, #e6a23c, #67c23a);
  border-radius: 2px;
  transition: width 0.6s ease;
}

/* ─── Responsive ─── */
@media (max-width: 992px) {
  .compact-filter-header {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }
  
  .filter-section {
    justify-content: center;
  }
  
  .action-section {
    margin-left: 0;
    justify-content: center;
  }
}

@media (max-width: 768px) {
  .compact-filter-header {
    padding: 16px;
  }
  
  .filter-section {
    flex-direction: column;
    gap: 12px;
  }
  
  .search-controls, .filter-controls {
    width: 100%;
    flex-direction: column;
    gap: 8px;
  }
  
  .search-input, .filter-select, .date-range-picker {
    width: 100%;
    min-width: unset;
  }
}
</style>
