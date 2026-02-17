<template>
  <el-dialog
    title="Voucher Statistics"
    v-model="dialogVisible"
    width="900px" 
    class="stats-dialog compact-dialog"
    top="5vh"
    @close="handleClose"
  >
    <div class="stats-content" v-loading="loading">
      <!-- Filters -->
      <div class="stats-section">
        <div class="filter-row">
            <el-date-picker
              v-model="dateRange"
              type="daterange"
              range-separator="to"
              start-placeholder="Start date"
              end-placeholder="End date"
              size="small"
              style="width: 250px; margin-right: 10px;"
              @change="fetchData"
              :clearable="false"
            />
            <el-button size="small" type="primary" @click="fetchData" :icon="Refresh">Refresh</el-button>
            <el-button size="small" type="success" :loading="downloading" @click="downloadPdf" :icon="Download">Download PDF</el-button>
        </div>
      </div>

      <!-- Summary Sections Grid -->
      <div class="summary-grid">
         <!-- Today -->
         <div class="summary-card">
            <div class="card-header">{{ todayLabel }}</div>
            <div class="card-body">
                <div class="summary-row">
                    <span class="label">Total / Paid</span>
                    <span class="value">{{ stats.today.total_vouchers || 0 }} / <span class="text-success">{{ stats.today.paid_vouchers || 0 }}</span></span>
                </div>
                <div class="summary-row">
                    <span class="label">Amount Gen.</span>
                    <span class="value">Rs. {{ formatAmount(stats.today.total_amount_generated) }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Collected</span>
                    <span class="value text-success">Rs. {{ formatAmount(stats.today.total_amount_collected) }}</span>
                </div>
            </div>
         </div>

         <!-- This Month -->
         <div class="summary-card">
            <div class="card-header">This Month</div>
             <div class="card-body">
                <div class="summary-row">
                    <span class="label">Total / Paid</span>
                    <span class="value">{{ stats.month.total_vouchers || 0 }} / <span class="text-success">{{ stats.month.paid_vouchers || 0 }}</span></span>
                </div>
                <div class="summary-row">
                    <span class="label">Amount Gen.</span>
                    <span class="value">Rs. {{ formatAmount(stats.month.total_amount_generated) }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Collected</span>
                    <span class="value text-success">Rs. {{ formatAmount(stats.month.total_amount_collected) }}</span>
                </div>
                 <div class="summary-row">
                    <span class="label">Pending</span>
                    <span class="value text-danger">Rs. {{ formatAmount(stats.month.pending_amount) }}</span>
                </div>
            </div>
         </div>

         <!-- Total -->
         <div class="summary-card">
            <div class="card-header">Total (All Time)</div>
             <div class="card-body">
                <div class="summary-row">
                    <span class="label">Total / Paid</span>
                    <span class="value">{{ stats.all.total_vouchers || 0 }} / <span class="text-success">{{ stats.all.paid_vouchers || 0 }}</span></span>
                </div>
                <!-- Extra details for Total -->
                <div class="summary-row">
                     <span class="label">Unpaid / Overdue</span>
                     <span class="value">{{ stats.all.unpaid_vouchers || 0 }} / <span class="text-danger">{{ stats.all.overdue_vouchers || 0 }}</span></span>
                </div>
                <div class="summary-row">
                    <span class="label">Amount Gen.</span>
                    <span class="value">Rs. {{ formatAmount(stats.all.total_amount_generated) }}</span>
                </div>
                <div class="summary-row">
                    <span class="label">Collected</span>
                    <span class="value text-success">Rs. {{ formatAmount(stats.all.total_amount_collected) }}</span>
                </div>
                 <div class="summary-row">
                    <span class="label">Pending</span>
                    <span class="value text-danger">Rs. {{ formatAmount(stats.all.pending_amount) }}</span>
                </div>
            </div>
         </div>
      </div>

      <!-- Paid Vouchers List -->
      <div class="stats-section" style="margin-top: 20px;">
        <h4 class="section-title">Recent Paid Vouchers (Filtered)</h4>
        <el-table :data="paidVouchers" height="300" style="width: 100%" size="small" border stripe v-loading="loading">
          <el-table-column prop="voucher_number" label="Voucher #" width="120" />
          <el-table-column prop="student_name" label="Student Name" min-width="150" />
          <el-table-column prop="parent_name" label="Father Name" min-width="150" />
          <el-table-column prop="class_name" label="Class" width="80" />
          <el-table-column label="Paid Amount" width="120" align="right">
            <template #default="scope">
              Rs. {{ formatAmount(scope.row.paid_amount) }}
            </template>
          </el-table-column>
          <el-table-column label="Date" width="100">
            <template #default="scope">
              {{ formatDate(scope.row.payment_date || scope.row.updated_at) }}
            </template>
          </el-table-column>
        </el-table>
      </div>
    </div>
    
    <template #footer>
      <div class="dialog-footer-stats">
        <div class="total-collected">
          <strong>Total Collected Amount: </strong>
          <span class="amount">Rs. {{ formatAmount(stats.all.total_amount_collected) }}</span>
        </div>
        <el-button @click="handleClose">Close</el-button>
      </div>
    </template>
  </el-dialog>
</template>

<script>
import moment from 'moment'
import { getFeeVoucherStats, getFeeVouchers } from '@/api/fee'
import { Refresh, Download } from '@element-plus/icons-vue'
import jsPDF from 'jspdf'
import autoTable from 'jspdf-autotable'

export default {
  name: 'FeeVoucherStatistics',
  props: {
    visible: {
      type: Boolean,
      default: false
    },
    defaultFilters: {
        type: Object,
        default: () => ({})
    }
  },
  emits: ['update:visible', 'close'],
  data() {
    return {
      currentVisible: false,
      loading: false,
      loading: false,
      stats: {
        today: {},
        month: {},
        all: {}
      },
      paidVouchers: [],
      dateRange: [],
      Refresh,
      Download,
      downloading: false
    }
  },
  computed: {
    dialogVisible: {
      get() {
        return this.visible
      },
      set(val) {
        this.$emit('update:visible', val)
      }
    },
    todayLabel() {
        if (this.dateRange && this.dateRange.length === 2) {
            const pattern = 'MMM DD, YYYY'
            const target = moment(this.dateRange[1])
            if(target.isSame(moment(), 'day')) return "Today's Overview"
            return `Overview (${target.format(pattern)})`
        }
        return "Today's Overview"
    }
  },
  watch: {
    visible(val) {
        if(val) {
            this.initData();
        }
    }
  },
  methods: {
    initData() {
        // Default to current month if no date range provided in props (though props usually override)
        // Check if defaultFilters has date range, otherwise set default
        if (this.defaultFilters.date_from && this.defaultFilters.date_to) {
            this.dateRange = [new Date(this.defaultFilters.date_from), new Date(this.defaultFilters.date_to)]
        } else {
             const startOfMonth = moment().startOf('month')
             const today = moment()
             this.dateRange = [startOfMonth.toDate(), today.toDate()]
        }
        this.fetchData()
    },

    async fetchData() {
      this.loading = true
      try {
        const baseParams = { ...this.defaultFilters }
        
        // Clean up empty parameters
        Object.keys(baseParams).forEach(key => {
          if (baseParams[key] === '' || baseParams[key] === null || baseParams[key] === undefined) {
            delete baseParams[key]
          }
        })
        
        // Remove date filters from base params (for "All Time" stats)
        const allParams = { ...baseParams }
        delete allParams.date_from
        delete allParams.date_to

        // Prepare Month Params
        const monthParams = { 
            ...baseParams,
            date_from: moment().startOf('month').format('YYYY-MM-DD'),
            date_to: moment().endOf('month').format('YYYY-MM-DD'),
            paid_from: moment().startOf('month').format('YYYY-MM-DD'),
            paid_to: moment().endOf('month').format('YYYY-MM-DD')
        }

        // Prepare Today Params - Uses the LAST DATE of the selected range, or Today
        let todayTarget = moment();
        if (this.dateRange && this.dateRange.length === 2) {
             todayTarget = moment(this.dateRange[1]);
        }

        const todayParams = {
            ...baseParams,
            date_from: todayTarget.format('YYYY-MM-DD'),
            date_to: todayTarget.format('YYYY-MM-DD'),
            paid_from: todayTarget.format('YYYY-MM-DD'),
            paid_to: todayTarget.format('YYYY-MM-DD')
        }

        // Helper to convert to Spatie Params
        const toSpatieParams = (obj) => {
            const newObj = {}
            Object.keys(obj).forEach(key => {
                // If key is already formatted or special, leave it (though here we assume raw inputs)
                if (obj[key] !== null && obj[key] !== undefined && obj[key] !== '') {
                    newObj[`filter[${key}]`] = obj[key]
                }
            })
            return newObj
        }

        // Fetch Stats in parallel - Convert params to filter[...] format
        const [statsAll, statsMonth, statsToday] = await Promise.all([
             getFeeVoucherStats(toSpatieParams(allParams)),
             getFeeVoucherStats(toSpatieParams(monthParams)),
             getFeeVoucherStats(toSpatieParams(todayParams))
        ])

        this.stats = {
            all: statsAll.statistics || {},
            month: statsMonth.statistics || {},
            today: statsToday.statistics || {}
        }

        // Fetch Recent Paid Vouchers - Uses the USER SELECTED date range from helper vars
        const listParams = {
            limit: 100,
            'filter[status]': 'paid'
        }
        
        // Apply Base Filters (class etc)
        Object.keys(baseParams).forEach(key => {
             if (key !== 'date_from' && key !== 'date_to' && key !== 'status') {
                 listParams[`filter[${key}]`] = baseParams[key]
             }
        })
        
        // Apply Selected Date Range for the LIST Only - Use PAID DATE filters
        if (this.dateRange && this.dateRange.length === 2) {
             listParams['filter[paid_from]'] = moment(this.dateRange[0]).format('YYYY-MM-DD')
             listParams['filter[paid_to]'] = moment(this.dateRange[1]).format('YYYY-MM-DD')
        }

        const paidData = await getFeeVouchers(listParams)
        if (paidData && paidData.success && paidData.vouchers) {
          this.paidVouchers = paidData.vouchers.data || []
        } else {
          this.paidVouchers = []
        }

      } catch (error) {
        console.error('Error fetching statistics:', error)
      } finally {
        this.loading = false
      }
    },
    
    async downloadPdf() {
      this.downloading = true
      try {
        // Fetch LIST data (use existing logic)
        const listParams = {
            limit: 10000,
            'filter[status]': 'paid'
        }
        
        // Apply Base Filters
        Object.keys(this.defaultFilters).forEach(key => {
             if (this.defaultFilters[key] && key !== 'date_from' && key !== 'date_to' && key !== 'status') {
                 listParams[`filter[${key}]`] = this.defaultFilters[key]
             }
        })
        
        // Apply Selected Date Range - Use PAID DATE filters
        if (this.dateRange && this.dateRange.length === 2) {
             listParams['filter[paid_from]'] = moment(this.dateRange[0]).format('YYYY-MM-DD')
             listParams['filter[paid_to]'] = moment(this.dateRange[1]).format('YYYY-MM-DD')
        }

        const response = await getFeeVouchers(listParams)
        const vouchers = response.vouchers?.data || []
        
        const doc = new jsPDF()
        
        // -- Title --
        doc.setFontSize(16)
        doc.text('Fee Statistics Report', 14, 15)
        doc.setFontSize(10)
        doc.text(`Generated: ${moment().format('DD MMM, YYYY HH:mm')}`, 14, 22)
        
        let yPos = 30
        
        // -- Summary Tables --
        // We'll create a single table for the summary with columns: Metric, Today, This Month, Total
        
        const summaryBody = [
            ['Total Vouchers', 
             this.stats.today.total_vouchers || 0,
             this.stats.month.total_vouchers || 0,
             this.stats.all.total_vouchers || 0],
             
            ['Paid Vouchers',
             this.stats.today.paid_vouchers || 0,
             this.stats.month.paid_vouchers || 0,
             this.stats.all.paid_vouchers || 0],
             
            ['Amount Generated',
             `Rs. ${this.formatAmount(this.stats.today.total_amount_generated)}`,
             `Rs. ${this.formatAmount(this.stats.month.total_amount_generated)}`,
             `Rs. ${this.formatAmount(this.stats.all.total_amount_generated)}`],
             
            ['Amount Collected',
             `Rs. ${this.formatAmount(this.stats.today.total_amount_collected)}`,
             `Rs. ${this.formatAmount(this.stats.month.total_amount_collected)}`,
             `Rs. ${this.formatAmount(this.stats.all.total_amount_collected)}`],
             
             ['Pending Amount',
             `Rs. ${this.formatAmount(this.stats.today.pending_amount)}`,
             `Rs. ${this.formatAmount(this.stats.month.pending_amount)}`,
             `Rs. ${this.formatAmount(this.stats.all.pending_amount)}`]
        ]
        
        autoTable(doc, {
            startY: yPos,
            head: [['Metric', 'Today', 'This Month', 'Total (All Time)']],
            body: summaryBody,
            theme: 'striped',
            headStyles: { fillColor: [41, 128, 185], halign: 'left' },
            styles: { fontSize: 9, cellPadding: 3, halign: 'left' },
            columnStyles: {
                0: { fontStyle: 'bold' }
            }
        })
        
        yPos = doc.lastAutoTable.finalY + 15
        
        // -- Vouchers List --
        doc.setFontSize(14)
        doc.text('Paid Vouchers List', 14, yPos)
        
        if (this.dateRange && this.dateRange.length === 2) {
             doc.setFontSize(10)
             doc.text(`Period: ${moment(this.dateRange[0]).format('DD MMM')} - ${moment(this.dateRange[1]).format('DD MMM, YYYY')}`, 14, yPos + 6)
             yPos += 12
        } else {
            yPos += 8
        }
        
        if (vouchers.length === 0) {
            doc.text('No paid vouchers found for selected filters.', 14, yPos + 5)
        } else {
             const listBody = vouchers.map(v => [
                v.voucher_number || '-',
                v.student_name || '-',
                v.parent_name || '-',
                v.class_name || '-',
                `Rs. ${this.formatAmount(v.paid_amount)}`,
                this.formatDate(v.payment_date || v.updated_at)
            ])
            
            autoTable(doc, {
                startY: yPos,
                head: [['Voucher #', 'Student Name', 'Father Name', 'Class', 'Paid Amount', 'Date']],
                body: listBody,
                theme: 'grid',
                headStyles: { fillColor: [64, 158, 255], halign: 'left' },
                styles: { fontSize: 8, halign: 'left' }
            })
        }
        
        doc.save(`Fee_Report_${moment().format('YYYYMMDD')}.pdf`)
        
      } catch (error) {
        console.error('Error generating PDF:', error)
        this.$message.error('Failed to generate PDF')
      } finally {
        this.downloading = false
      }
    },

    handleClose() {
      this.$emit('update:visible', false)
      this.$emit('close')
    },

    formatAmount(amount) {
      if (!amount) return '0'
      return parseFloat(amount).toLocaleString()
    },

    formatDate(date) {
      if (!date) return '-'
      return moment(date).format('DD MMM, YY')
    },

    getCollectionRate() {
      // Use All Time Rate for footer? Or maybe remove this method if unused in new template
      // But preserving it for safety or if I use it later
      if (!this.stats.all || !this.stats.all.total_amount_generated) return '0'
      const rate = (this.stats.all.total_amount_collected / this.stats.all.total_amount_generated) * 100
      return rate.toFixed(1)
    }
  }
}
</script>

<style scoped>
.summary-grid {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
}

.summary-card {
  flex: 1;
  background: #fff;
  border-radius: 4px;
  border: 1px solid #e4e7ed;
  box-shadow: 0 1px 3px rgba(0,0,0,0.05);
  display: flex;
  flex-direction: column;
}

.card-header {
  background: #f5f7fa;
  padding: 10px 15px;
  font-weight: 600;
  color: #606266;
  border-bottom: 1px solid #e4e7ed;
  font-size: 14px;
  text-align: center;
}

.card-body {
  padding: 15px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  font-size: 13px;
}

.summary-row:last-child {
  margin-bottom: 0;
}

.summary-row .label {
  color: #909399;
}

.summary-row .value {
  font-weight: 700;
  color: #303133;
}

.text-success { color: #67c23a !important; }
.text-danger { color: #f56c6c !important; }
.text-warning { color: #e6a23c !important; }
</style>
