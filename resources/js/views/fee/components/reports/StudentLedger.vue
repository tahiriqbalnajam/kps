<template>
  <div>
    <el-row :gutter="16" class="filter-row">
      <el-col :span="8">
        <el-select v-model="selectedStudent" placeholder="Select Student" filterable remote
          :remote-method="searchStudents" :loading="studentLoading"
          @change="fetchReport" class="w-full" clearable>
          <el-option v-for="s in studentList" :key="s.id" :label="`${s.name} (${s.admission_no})`" :value="s.id" />
        </el-select>
      </el-col>
      <el-col :span="5">
        <el-button type="primary" @click="fetchReport" :loading="loading" :disabled="!selectedStudent">
          <el-icon><Search /></el-icon> Load Ledger
        </el-button>
      </el-col>
      <el-col :span="5" v-if="summary.student_name">
        <el-button type="success" @click="printLedger" :disabled="records.length === 0">
          <el-icon><Printer /></el-icon> Print Statement
        </el-button>
      </el-col>
    </el-row>

    <template v-if="summary.student_name">
      <el-row :gutter="16" class="summary-row">
        <el-col :span="6">
          <el-card shadow="never" class="summary-card">
            <div class="stat-label">Student</div>
            <div class="stat-value-sm">{{ summary.student_name }}</div>
          </el-card>
        </el-col>
        <el-col :span="5">
          <el-card shadow="never" class="summary-card">
            <div class="stat-label">Class</div>
            <div class="stat-value-sm">{{ summary.class_name }}</div>
          </el-card>
        </el-col>
        <el-col :span="5">
          <el-card shadow="never" class="summary-card">
            <div class="stat-label">Admission #</div>
            <div class="stat-value-sm">{{ summary.admission_number }}</div>
          </el-card>
        </el-col>
        <el-col :span="8">
          <el-card shadow="never" class="summary-card">
            <div class="stat-label">Parent</div>
            <div class="stat-value-sm">{{ summary.parent_name }}</div>
          </el-card>
        </el-col>
      </el-row>

      <el-row :gutter="16" class="summary-row">
        <el-col :span="6">
          <el-card shadow="never" class="summary-card">
            <div class="stat-value">{{ formatCurrency(summary.total_charged) }}</div>
            <div class="stat-label">Total Charged</div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card shadow="never" class="summary-card paid">
            <div class="stat-value">{{ formatCurrency(summary.total_paid) }}</div>
            <div class="stat-label">Total Paid</div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card shadow="never" :class="['summary-card', summary.balance > 0 ? 'unpaid' : 'settled']">
            <div class="stat-value">{{ formatCurrency(summary.balance) }}</div>
            <div class="stat-label">Balance</div>
          </el-card>
        </el-col>
        <el-col :span="6">
          <el-card shadow="never" class="summary-card">
            <div class="stat-value">{{ summary.paid_vouchers || 0 }} / {{ summary.total_vouchers || 0 }}</div>
            <div class="stat-label">Paid / Total Vouchers</div>
          </el-card>
        </el-col>
      </el-row>

      <el-table :data="records" border stripe v-loading="loading" class="data-table" id="ledger-print">
        <el-table-column prop="voucher_number" label="Voucher #" width="160" />
        <el-table-column prop="fee_month" label="Fee Month" width="100" />
        <el-table-column prop="voucher_type" label="Type" width="90" />
        <el-table-column prop="total_with_fine" label="Charged" width="100" />
        <el-table-column prop="paid_amount" label="Paid" width="100" />
        <el-table-column label="Balance" width="100">
          <template #default="{ row }">{{ formatCurrency(row.total_with_fine - row.paid_amount) }}</template>
        </el-table-column>
        <el-table-column prop="status" label="Status" width="100" />
        <el-table-column label="Due Date" width="100">
          <template #default="{ row }">{{ formatDate(row.due_date) }}</template>
        </el-table-column>
        <el-table-column label="Paid Date" width="100">
          <template #default="{ row }">{{ formatDate(row.payment_date) }}</template>
        </el-table-column>
        <el-table-column label="Generated At" width="150">
          <template #default="{ row }">{{ formatDateTime(row.generated_at) }}</template>
        </el-table-column>
      </el-table>
    </template>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import moment from 'moment'
import { getFeeReports, getFeeVoucherStudents } from '@/api/fee'
import { ElMessage } from 'element-plus'

const loading = ref(false)
const studentLoading = ref(false)
const selectedStudent = ref('')
const studentList = ref([])
const summary = ref({})
const records = ref([])

const searchStudents = async (query) => {
  if (!query || query.length < 2) return
  studentLoading.value = true
  try {
    const response = await getFeeVoucherStudents({ filter: { search: query } })
    if (response.success) {
      studentList.value = response.students.data.map(s => ({
        id: s.id,
        name: s.name,
        admission_no: s.admission_no
      }))
    }
  } catch (e) {
    ElMessage.error('Failed to search students')
  } finally {
    studentLoading.value = false
  }
}

const fetchReport = async () => {
  if (!selectedStudent.value) return
  loading.value = true
  try {
    const response = await getFeeReports({ type: 8, student_id: selectedStudent.value })
    if (response.success) {
      summary.value = response.summary
      records.value = response.records
    }
  } catch (e) {
    ElMessage.error('Failed to load student ledger')
  } finally {
    loading.value = false
  }
}

const printLedger = () => {
  window.print()
}

const formatDate = (d) => d ? moment(d).format('DD/MM/YY') : ''
const formatDateTime = (d) => d ? moment(d).format('DD/MM/YY HH:mm') : ''

const formatCurrency = (val) => {
  return new Intl.NumberFormat('en-PK', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(val || 0)
}
</script>

<style scoped>
.w-full { width: 100%; }
.filter-row { margin-bottom: 16px; }
.summary-row { margin-bottom: 16px; }
.summary-card { text-align: center; }
.summary-card.paid { border-top: 3px solid #67c23a; }
.summary-card.unpaid { border-top: 3px solid #f56c6c; }
.summary-card.settled { border-top: 3px solid #67c23a; }
.stat-value { font-size: 22px; font-weight: 700; color: #303133; }
.stat-value-sm { font-size: 15px; font-weight: 600; color: #303133; }
.stat-label { font-size: 12px; color: #909399; margin-top: 4px; }
.data-table { width: 100%; }
</style>
