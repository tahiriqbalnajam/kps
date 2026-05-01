<template>
  <div>
    <el-row :gutter="16" class="filter-row">
      <el-col :span="8">
        <el-date-picker v-model="dateRange" type="daterange" range-separator="To"
          start-placeholder="Start date" end-placeholder="End date"
          @change="fetchReport" class="w-full" />
      </el-col>
      <el-col :span="5">
        <el-select v-model="className" placeholder="All Classes" clearable @change="fetchReport" class="w-full">
          <el-option v-for="c in classes" :key="c" :label="c" :value="c" />
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
          <div class="stat-value">{{ formatCurrency(summary.total_fines_applied) }}</div>
          <div class="stat-label">Total Fines Applied</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="never" class="summary-card paid">
          <div class="stat-value">{{ formatCurrency(summary.fines_paid) }}</div>
          <div class="stat-label">Fines Collected</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="never" class="summary-card unpaid">
          <div class="stat-value">{{ formatCurrency(summary.fines_unpaid) }}</div>
          <div class="stat-label">Fines Outstanding</div>
        </el-card>
      </el-col>
      <el-col :span="6">
        <el-card shadow="never" class="summary-card">
          <div class="stat-value">{{ summary.vouchers_with_fines || 0 }}</div>
          <div class="stat-label">Vouchers with Fines</div>
        </el-card>
      </el-col>
    </el-row>

    <el-row :gutter="16" class="summary-row">
      <el-col :span="24">
        <el-card shadow="never" header="Frequent Late Payers (Top 10)">
          <el-table :data="summary.frequent_late_payers || []" size="small" max-height="300">
            <el-table-column prop="student_name" label="Student" />
            <el-table-column prop="class_name" label="Class" width="100" />
            <el-table-column prop="parent_phone" label="Phone" width="130" />
            <el-table-column prop="late_count" label="Late Count" width="100" />
            <el-table-column label="Total Fines">
              <template #default="{ row }">{{ formatCurrency(row.total_fines) }}</template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-col>
    </el-row>

    <el-table :data="records" border stripe v-loading="loading" class="data-table">
      <el-table-column prop="voucher_number" label="Voucher #" width="160" />
      <el-table-column prop="student_name" label="Student" />
      <el-table-column prop="class_name" label="Class" width="100" />
      <el-table-column prop="fine_amount" label="Fine" width="100" />
      <el-table-column prop="status" label="Status" width="100" />
      <el-table-column label="Due Date" width="100">
        <template #default="{ row }">{{ formatDate(row.due_date) }}</template>
      </el-table-column>
      <el-table-column label="Generated At" width="150">
        <template #default="{ row }">{{ formatDateTime(row.generated_at) }}</template>
      </el-table-column>
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
const now = new Date()
const dateRange = ref([new Date(now.getFullYear(), now.getMonth(), 1), now])
const className = ref('')
const classes = ref([])
const summary = ref({})
const records = ref([])
const total = ref(0)
const perPage = ref(25)
const currentPage = ref(1)

const fetchReport = async () => {
  loading.value = true
  try {
    const params = { type: 7, per_page: perPage.value, page: currentPage.value }
    if (dateRange.value?.length === 2) {
      params.date_from = dateRange.value[0]
      params.date_to = dateRange.value[1]
    }
    if (className.value) params.class_name = className.value

    const response = await getFeeReports(params)
    if (response.success) {
      summary.value = response.summary
      records.value = response.records.data
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

const onPageChange = (page) => { currentPage.value = page; fetchReport() }
const onSizeChange = (size) => { perPage.value = size; currentPage.value = 1; fetchReport() }

const formatDate = (d) => d ? moment(d).format('DD/MM/YY') : ''
const formatDateTime = (d) => d ? moment(d).format('DD/MM/YY HH:mm') : ''

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
.summary-card.paid { border-top: 3px solid #67c23a; }
.summary-card.unpaid { border-top: 3px solid #f56c6c; }
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
