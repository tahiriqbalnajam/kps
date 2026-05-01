<template>
  <div>
    <el-row :gutter="16" class="filter-row">
      <el-col :span="8">
        <el-select v-model="className" placeholder="All Classes" clearable @change="fetchReport" class="w-full">
          <el-option v-for="c in classes" :key="c" :label="c" :value="c" />
        </el-select>
      </el-col>
      <el-col :span="5">
        <el-select v-model="agingFilter" placeholder="All Aging" clearable @change="fetchReport" class="w-full">
          <el-option label="1-30 Days" value="1_30" />
          <el-option label="31-60 Days" value="31_60" />
          <el-option label="60+ Days" value="60_plus" />
        </el-select>
      </el-col>
      <el-col :span="5">
        <el-button type="primary" @click="fetchReport" :loading="loading">
          <el-icon><Search /></el-icon> Generate
        </el-button>
        <el-button type="success" @click="printReport" :disabled="records.length === 0" class="ml-2">
          <el-icon><Printer /></el-icon> Print
        </el-button>
      </el-col>
    </el-row>

    <el-row :gutter="16" class="summary-row">
      <el-col :span="6">
        <el-card shadow="never" class="summary-card">
          <div class="stat-value">{{ summary.total_defaulters || 0 }}</div>
          <div class="stat-label">Total Defaulters</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="never" class="summary-card">
          <div class="stat-value">{{ formatCurrency(summary.total_outstanding) }}</div>
          <div class="stat-label">Total Outstanding</div>
        </el-card>
      </el-col>
      <el-col :span="4">
        <el-card shadow="never" class="summary-card aging-1">
          <div class="stat-value">{{ summary.aging_1_30 || 0 }}</div>
          <div class="stat-label">1-30 Days</div>
        </el-card>
      </el-col>
      <el-col :span="4">
        <el-card shadow="never" class="summary-card aging-2">
          <div class="stat-value">{{ summary.aging_31_60 || 0 }}</div>
          <div class="stat-label">31-60 Days</div>
        </el-card>
      </el-col>
      <el-col :span="4">
        <el-card shadow="never" class="summary-card aging-3">
          <div class="stat-value">{{ summary.aging_60_plus || 0 }}</div>
          <div class="stat-label">60+ Days</div>
        </el-card>
      </el-col>
    </el-row>

    <el-table :data="records" border stripe v-loading="loading" class="data-table">
      <el-table-column prop="voucher_number" label="Voucher #" width="160" />
      <el-table-column prop="student_name" label="Student" />
      <el-table-column prop="class_name" label="Class" width="100" />
      <el-table-column prop="parent_name" label="Parent" width="130" />
      <el-table-column prop="parent_phone" label="Phone" width="130" />
      <el-table-column label="Outstanding" width="110">
        <template #default="{ row }">{{ formatCurrency(row.total_with_fine - row.paid_amount) }}</template>
      </el-table-column>
      <el-table-column label="Due Date" width="100">
        <template #default="{ row }">{{ formatDate(row.due_date) }}</template>
      </el-table-column>
      <el-table-column label="Days Overdue" width="120">
        <template #default="{ row }">{{ daysOverdue(row.due_date) }} days</template>
      </el-table-column>
      <el-table-column prop="status" label="Status" width="110" />
    </el-table>

    <el-pagination v-if="total > perPage" class="pagination" background
      layout="prev, pager, next, sizes, total"
      :total="total" :page-size="perPage" :current-page="currentPage"
      @current-change="onPageChange" @size-change="onSizeChange"
      :page-sizes="[10, 25, 50, 100]" />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import moment from 'moment'
import { getFeeVoucherStudents, getFeeReports } from '@/api/fee'
import { ElMessage } from 'element-plus'

const loading = ref(false)
const className = ref('')
const agingFilter = ref('')
const classes = ref([])
const summary = ref({})
const records = ref([])
const total = ref(0)
const perPage = ref(25)
const currentPage = ref(1)

const fetchReport = async () => {
  loading.value = true
  try {
    const params = { type: 3, per_page: perPage.value, page: currentPage.value }
    if (className.value) params.class_name = className.value

    const response = await getFeeReports(params)
    if (response.success) {
      summary.value = response.summary
      let rows = response.records.data
      if (agingFilter.value === '1_30') {
        const cutoff = new Date(); cutoff.setDate(cutoff.getDate() - 30)
        rows = rows.filter(r => new Date(r.due_date) >= cutoff)
      } else if (agingFilter.value === '31_60') {
        const from = new Date(); from.setDate(from.getDate() - 60)
        const to = new Date(); to.setDate(to.getDate() - 30)
        rows = rows.filter(r => new Date(r.due_date) >= from && new Date(r.due_date) < to)
      } else if (agingFilter.value === '60_plus') {
        const cutoff = new Date(); cutoff.setDate(cutoff.getDate() - 60)
        rows = rows.filter(r => new Date(r.due_date) < cutoff)
      }
      records.value = rows
      total.value = response.records.total
    }
  } catch (e) {
    ElMessage.error('Failed to load report')
  } finally {
    loading.value = false
  }
}

const loadClasses = async () => {
  try {
    const response = await getFeeVoucherStudents({ limit: 1000 })
    if (response.success) {
      const seen = new Set()
      classes.value = response.students.data.map(s => s.class_name).filter(c => {
        if (seen.has(c)) return false
        seen.add(c)
        return true
      }).sort()
    }
  } catch (e) { /* ignore */ }
}

const daysOverdue = (dueDate) => {
  const due = new Date(dueDate)
  const now = new Date()
  return Math.max(0, Math.floor((now - due) / (1000 * 60 * 60 * 24)))
}

const onPageChange = (page) => { currentPage.value = page; fetchReport() }
const onSizeChange = (size) => { perPage.value = size; currentPage.value = 1; fetchReport() }

const formatDate = (d) => d ? moment(d).format('DD/MM/YY') : ''

const printReport = () => { window.print() }

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-PK', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(val || 0)
}

onMounted(() => { loadClasses() })
</script>

<style scoped>
.w-full { width: 100%; }
.filter-row { margin-bottom: 16px; }
.summary-row { margin-bottom: 16px; }
.summary-card { text-align: center; }
.summary-card.aging-1 { border-top: 3px solid #e6a23c; }
.summary-card.aging-2 { border-top: 3px solid #f56c6c; }
.summary-card.aging-3 { border-top: 3px solid #8b0000; }
.stat-value { font-size: 22px; font-weight: 700; color: #303133; }
.stat-label { font-size: 12px; color: #909399; margin-top: 4px; }
.data-table { width: 100%; }
.pagination { margin-top: 16px; justify-content: flex-end; }

@media print {
  .filter-row, .pagination { display: none !important; }
  :deep(.el-table__body-wrapper) { overflow: visible !important; height: auto !important; }
  :deep(.el-table__inner-wrapper) { height: auto !important; }
  :deep(.el-table) { overflow: visible !important; }
}
</style>
