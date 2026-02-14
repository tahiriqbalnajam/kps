<template>
  <div class="app-container">
    <!-- Filter Header -->
    <div class="filter-header">
      <div class="filter-section">
        <div class="search-group" style="display: flex; gap: 8px;">
          <el-select 
            v-model="query.searchType" 
            placeholder="Search By" 
            class="search-type-select"
            style="width: 140px;"
            size="default"
          >
            <el-option label="Student Name" value="student_name" />
            <el-option label="Parent Name" value="parent_name" />
            <el-option label="Admission #" value="admission_number" />
            <el-option label="Roll No" value="roll_no" />
          </el-select>

          <el-input 
            v-model="query.keyword" 
            placeholder="Search..." 
            class="search-input" 
            v-on:input="debounceInput" 
            clearable
            size="default"
            style="width: 200px;"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
        </div>

        <el-select 
          v-model="query.class_id" 
          placeholder="Select Class..." 
          class="class-select"
          clearable
          @change="handleFilter"
          size="default"
        >
          <el-option 
            v-for="cls in classes" 
            :key="cls.id" 
            :label="cls.name" 
            :value="cls.id" 
          />
        </el-select>
      </div>

      <div class="action-section">
        <el-button 
          type="primary" 
          @click="showVoucherDialog = true"
          :disabled="selectedStudents.length === 0"
          size="default"
          class="generate-btn"
        >
          <el-icon><Document /></el-icon>
          Generate Vouchers ({{ selectedStudents.length }})
        </el-button>
      </div>
    </div>

    <!-- Student List Table -->
    <el-card class="table-card">
      <template #header>
        <div class="card-header">
          <div class="header-info">
            <el-icon class="header-icon"><Files /></el-icon>
            <span class="header-title">Fee Voucher Generation</span>
            <el-tag v-if="total > 0" type="info" class="total-count">{{ total }} students</el-tag>
          </div>
          <div class="selected-info" v-if="selectedStudents.length > 0">
            <el-tag type="primary" effect="dark">
              {{ selectedStudents.length }} selected
            </el-tag>
          </div>
        </div>
      </template>

      <el-table
        :data="studentsList"
        v-loading="loading"
        @selection-change="handleSelectionChange"
        stripe
        style="width: 100%"
        class="voucher-table"
      >
        <el-table-column type="selection" width="55" />
        
        <el-table-column label="Admission #" prop="adminssion_number" width="120">
          <template #default="scope">
            <span class="admission-number">{{ scope.row.adminssion_number }}</span>
          </template>
        </el-table-column>

        <el-table-column label="Student" min-width="200">
          <template #default="scope">
            <div class="student-info">
              <div class="student-details">
                <span class="student-name">{{ scope.row.name }}</span>
                <div class="parent-info">
                  <el-icon class="parent-icon"><User /></el-icon>
                  <span class="parent-name">{{ scope.row.parents?.name || 'N/A' }}</span>
                </div>
              </div>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Class" width="120">
          <template #default="scope">
            <el-tag 
              :type="getClassTagType(scope.row.stdclasses?.name)" 
              effect="light"
            >
              {{ scope.row.stdclasses?.name || 'Unassigned' }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="Monthly Fee" width="120" align="right">
          <template #default="scope">
            <div class="fee-amount">
              <el-icon class="currency-icon"><Money /></el-icon>
              <span class="amount">{{ getStudentFee(scope.row) }}</span>
            </div>
          </template>
        </el-table-column>

        <el-table-column label="Gender" width="100" align="center">
          <template #default="scope">
            <el-tag 
              :type="scope.row.gender === 'Male' ? 'primary' : 'danger'" 
              effect="light"
              size="small"
            >
              {{ scope.row.gender }}
            </el-tag>
          </template>
        </el-table-column>

        <el-table-column label="Status" width="120" align="center">
          <template #default="scope">
            <el-tag 
              :type="scope.row.status === 'active' ? 'success' : 'warning'" 
              effect="light"
              size="small"
            >
              {{ scope.row.status === 'active' ? 'Active' : 'Inactive' }}
            </el-tag>
          </template>
        </el-table-column>
      </el-table>

      <!-- Pagination -->
      <div class="pagination-section">
        <el-pagination
          v-show="total > 0"
          v-model:current-page="query.page"
          v-model:page-size="query.limit"
          :page-sizes="[10, 15, 20, 30, 50, 100]"
          background
          layout="total, sizes, prev, pager, next, jumper"
          :total="total"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
    </el-card>

    <!-- Voucher Generation Dialog -->
    <el-dialog
      title="Generate Fee Vouchers"
      v-model="showVoucherDialog"
      width="700px"
      class="voucher-dialog"
      :before-close="handleDialogClose"
    >
      <div class="voucher-form">
        <el-form :model="voucherForm" label-width="120px">
          <el-form-item label="Due Date" required>
            <el-date-picker
              v-model="voucherForm.dueDate"
              type="date"
              placeholder="Select due date"
              style="width: 100%"
              :disabled-date="disabledDate"
            />
          </el-form-item>

          <el-form-item label="Fine Amount" required>
            <el-input-number
              v-model="voucherForm.fineAmount"
              :min="0"
              :max="10000"
              :step="50"
              placeholder="Fine after due date"
              style="width: 100%"
            />
          </el-form-item>

          <el-form-item label="Voucher Type">
            <el-radio-group v-model="voucherForm.voucherType" @change="handleVoucherTypeChange">
              <el-radio value="monthly">Monthly Fee</el-radio>
              <el-radio value="custom">Custom Amount</el-radio>
              <el-radio value="multiple">Multiple Fee Types</el-radio>
            </el-radio-group>
          </el-form-item>

          <!-- Month Selection for Monthly Fees -->
          <el-form-item 
            v-if="voucherForm.voucherType === 'monthly'" 
            label="Fee Month"
            required
          >
            <el-date-picker
              v-model="voucherForm.feeMonth"
              type="month"
              placeholder="Select fee month"
              style="width: 100%"
              format="MMM YYYY"
              value-format="YYYY-MM"
            />
          </el-form-item>

          <el-form-item 
            v-if="voucherForm.voucherType === 'custom'" 
            label="Custom Amount"
          >
            <el-input-number
              v-model="voucherForm.customAmount"
              :min="0"
              :max="50000"
              :step="100"
              placeholder="Custom fee amount"
              style="width: 100%"
            />
          </el-form-item>

          <!-- Multiple Fee Types Selection -->
          <el-form-item 
            v-if="voucherForm.voucherType === 'multiple'" 
            label="Fee Types"
            required
          >
            <div class="fee-types-grid">
              <el-row :gutter="10">
                <el-col 
                  :span="24" 
                  v-for="feeType in feeTypes" 
                  :key="feeType.id"
                  class="fee-type-item"
                >
                  <div class="fee-type-row">
                    <el-checkbox
                      :model-value="isSelectedFeeType(feeType.id)"
                      @change="handleFeeTypeSelection(feeType.id, $event)"
                      class="fee-type-checkbox"
                    >
                      <span class="fee-type-title">{{ feeType.title }}</span>
                    </el-checkbox>
                    <div class="fee-amount-input">
                      <span class="original-amount">Original: Rs. {{ feeType.amount }}</span>
                      <el-input-number
                        :model-value="getSelectedFeeTypeAmount(feeType.id)"
                        @input="updateFeeTypeAmount(feeType.id, $event)"
                        :min="0"
                        :max="50000"
                        :step="10"
                        :disabled="!isSelectedFeeType(feeType.id)"
                        placeholder="Amount"
                        style="width: 150px"
                      />
                    </div>
                  </div>
                </el-col>
              </el-row>
            </div>
            <div v-if="voucherForm.selectedFeeTypes.length > 0" class="selected-fee-types">
              <strong>Selected Fee Types:</strong>
              <div v-for="feeType in getSelectedFeeTypesDetails()" :key="feeType.id" class="selected-fee-type">
                {{ feeType.title }} - Rs. {{ feeType.amount }}
              </div>
            </div>
          </el-form-item>

          <el-form-item label="Include Pending">
            <el-checkbox
              v-model="voucherForm.includePending"
              @change="handleIncludePendingChange"
            >
              Include previous pending/overdue vouchers
            </el-checkbox>
            <div v-if="voucherForm.includePending" class="pending-warning">
              <el-alert
                v-if="selectedStudents.length === 0"
                title="Select Students First"
                type="warning"
                :closable="false"
                show-icon
              >
                <template #default>
                  <span>Please select students first to check for pending vouchers.</span>
                </template>
              </el-alert>
              <el-alert
                v-else
                title="Pending Vouchers"
                type="info"
                :closable="false"
                show-icon
              >
                <template #default>
                  <span v-if="loadingPending">Loading pending vouchers...</span>
                  <span v-else-if="pendingVouchers.length > 0">
                    {{ pendingVouchers.length }} pending voucher(s) will be added to the new voucher on generation.
                  </span>
                  <span v-else>No pending vouchers found for selected students.</span>
                </template>
              </el-alert>
            </div>
          </el-form-item>

          <el-form-item label="Additional Notes">
            <el-input
              v-model="voucherForm.notes"
              type="textarea"
              :rows="3"
              placeholder="Additional notes for voucher (optional)"
            />
          </el-form-item>
        </el-form>

        <div class="voucher-summary">
          <h4>Voucher Summary</h4>
          <div class="summary-item">
            <span>Students Selected:</span>
            <span class="value">{{ selectedStudents.length }}</span>
          </div>
          <div class="summary-item" v-if="voucherForm.feeMonth && voucherForm.voucherType === 'monthly'">
            <span>Fee Month:</span>
            <span class="value">{{ formatMonth(voucherForm.feeMonth) }}</span>
          </div>
          <div class="summary-item">
            <span>Due Date:</span>
            <span class="value">{{ formatDate(voucherForm.dueDate) || 'Not selected' }}</span>
          </div>
          <div class="summary-item">
            <span>Fee Amount per Student:</span>
            <span class="value">Rs. {{ calculateTotalFeeAmount() }}</span>
          </div>
          <div class="summary-item">
            <span>Fine Amount:</span>
            <span class="value">Rs. {{ voucherForm.fineAmount || 0 }}</span>
          </div>

          <!-- Pending Vouchers Section -->
          <div v-if="voucherForm.includePending && pendingVouchers.length > 0" class="pending-vouchers-section">
            <div class="summary-item">
              <span>Pending Vouchers:</span>
              <span class="value">{{ pendingVouchers.length }} voucher(s)</span>
            </div>
            <div class="pending-voucher-details">
              <div v-for="pending in pendingVouchers" :key="pending.id" class="pending-voucher-item">
                <span class="voucher-number">{{ pending.voucher_number }}</span>
                <span class="voucher-amount">Rs. {{ getPendingVoucherAmount(pending) }}</span>
              </div>
            </div>
            <div class="summary-item">
              <span>Total Pending Amount:</span>
              <span class="value pending-amount">Rs. {{ calculateTotalPendingAmount() }}</span>
            </div>
          </div>

          <div class="summary-item total-summary">
            <span>Total per Student:</span>
            <span class="value">Rs. {{ calculateTotalAmount() }}</span>
          </div>
        </div>

        <!-- Duplicate Check Warning -->
        <div v-if="duplicateWarning.show" class="duplicate-warning-section">
          <el-alert
            :title="duplicateWarning.title"
            type="warning"
            show-icon
            :closable="false"
            class="duplicate-warning"
          >
            <template #default>
              <div class="duplicate-details">
                <p>{{ duplicateWarning.message }}</p>
                <div class="duplicate-actions">
                  <el-button 
                    size="small" 
                    type="primary" 
                    @click="removeDuplicatedStudents"
                  >
                    Remove Duplicated Students
                  </el-button>
                  <el-button 
                    size="small" 
                    @click="duplicateWarning.show = false"
                  >
                    Keep All Students
                  </el-button>
                </div>
              </div>
            </template>
          </el-alert>
        </div>
      </div>

      <template #footer>
        <div class="dialog-footer">
          <el-button @click="handleDialogClose">Cancel</el-button>
          <el-button 
            v-if="duplicateWarning.show"
            type="info"
            @click="checkForDuplicates"
            :loading="checkingDuplicates"
          >
            Re-check Duplicates
          </el-button>
          <el-button 
            type="primary" 
            @click="generateVouchers"
            :loading="generating"
            :disabled="!canGenerateVouchers"
          >
            Generate & Print Vouchers
          </el-button>
        </div>
      </template>
    </el-dialog>

    <!-- Voucher Print Component -->
    <fee-voucher-print 
      v-if="showPrintDialog" 
      :vouchers="generatedVouchers"
      :show-dialog="showPrintDialog"
      @close="handlePrintClose"
    />
  </div>
</template>

<script>
import {
  Search,
  Document,
  Files,
  User,
  Money,
  Calendar,
  Printer
} from '@element-plus/icons-vue'
import moment from 'moment'
import { debounce } from 'lodash'
import Resource from '@/api/resource'
import { getFeeVoucherStudents, generateFeeVouchers, checkExistingVouchers, getOutstandingVouchers } from '@/api/fee'
import FeeVoucherPrint from './component/FeeVoucherPrint.vue'

const studentsResource = new Resource('students')
const classesResource = new Resource('classes')
const feeTypesResource = new Resource('feetypes')

export default {
  name: 'FeeVoucher',
  components: {
    FeeVoucherPrint
  },
  data() {
    return {
      loading: false,
      generating: false,
      checkingDuplicates: false,
      studentsList: [],
      classes: [],
      feeTypes: [],
      selectedStudents: [],
      total: 0,
      showVoucherDialog: false,
      showPrintDialog: false,
      generatedVouchers: [],
      duplicateWarning: {
        show: false,
        count: 0,
        title: '',
        message: '',
        duplicates: []
      },
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        class_id: '',
        group: '',
        include: 'stdclasses,parents',
        searchType: 'student_name'
      },
      voucherForm: {
        dueDate: null,
        fineAmount: 100,
        voucherType: 'monthly',
        feeMonth: null,
        customAmount: 0,
        selectedFeeTypes: [],
        feeTypeAmounts: {}, // Store custom amounts for each fee type
        notes: '',
        includePending: false
      },
      pendingVouchers: [],
      loadingPending: false
    }
  },
  computed: {
    formattedDueDate() {
      return this.voucherForm.dueDate ? moment(this.voucherForm.dueDate).format('DD MMM, YYYY') : ''
    },
    
    canGenerateVouchers() {
      const hasRequiredFields = this.voucherForm.dueDate && this.selectedStudents.length > 0
      
      if (this.voucherForm.voucherType === 'monthly') {
        return hasRequiredFields && this.voucherForm.feeMonth
      } else if (this.voucherForm.voucherType === 'custom') {
        return hasRequiredFields && this.voucherForm.customAmount > 0
      } else if (this.voucherForm.voucherType === 'multiple') {
        return hasRequiredFields && this.voucherForm.selectedFeeTypes.length > 0
      }
      
      return hasRequiredFields
    }
  },
  created() {
    this.getStudents()
    this.getClasses()
    this.getFeeTypes()
  },
  methods: {
    debounceInput: debounce(function () {
      this.getStudents()
    }, 500),

    async getStudents() {
      this.loading = true
      try {
        // Build query filters
        const filters = {}
        
        if (this.query.keyword) {
          if (this.query.searchType) {
            filters[this.query.searchType] = this.query.keyword
          } else {
            filters.search = this.query.keyword
          }
        }
        
        if (this.query.class_id) {
          filters.stdclass = this.query.class_id
        }
        
        if (this.query.group) {
          switch (this.query.group) {
            case 'male':
              filters.gender = 'Male'
              break
            case 'female':
              filters.gender = 'Female'
              break
            case 'pending':
              filters.fee_status = 'pending'
              break
            case 'active':
              filters.status = 'active'
              break
          }
        }

        const queryParams = {
          ...this.query,
          filter: filters
        }

        const { data } = await studentsResource.list(queryParams)
        this.studentsList = data.students.data
        this.total = data.students.total
      } catch (error) {
        console.error('Error fetching students:', error)
        this.$message.error('Failed to load students')
      } finally {
        this.loading = false
      }
    },

    async getClasses() {
      try {
        const { data } = await classesResource.list()
        this.classes = data.classes.data
      } catch (error) {
        console.error('Error fetching classes:', error)
      }
    },

    async getFeeTypes() {
      try {
        const { data } = await feeTypesResource.list()
        this.feeTypes = data.feetypes.data
      } catch (error) {
        console.error('Error fetching fee types:', error)
        this.$message.error('Failed to load fee types')
      }
    },

    handleFilter() {
      this.query.page = 1
      this.getStudents()
    },

    handleSelectionChange(selection) {
      this.selectedStudents = selection
      // Reset duplicate warning when selection changes
      this.duplicateWarning.show = false
      
      // Fetch pending vouchers if include pending is enabled
      if (this.voucherForm.includePending && selection.length > 0) {
        this.fetchPendingVouchers()
      }
    },

    async handleSizeChange(val) {
      this.query.limit = val
      await this.getStudents()
    },

    async handleCurrentChange(val) {
      this.query.page = val
      await this.getStudents()
    },

    getClassTagType(className) {
      if (!className) return 'info'
      const classNumber = parseInt(className.match(/\d+/)?.[0] || '0')
      if (classNumber <= 5) return 'success'
      if (classNumber <= 8) return 'primary'
      if (classNumber <= 10) return 'warning'
      return 'danger'
    },

    getStudentFee(student) {
      // Get fee from student table, otherwise from class
      if (student.monthly_fee && student.monthly_fee > 0) {
        return student.monthly_fee
      }
      // Fallback to class fee if available
      if (student.stdclasses && student.stdclasses.monthly_fee) {
        return student.stdclasses.monthly_fee
      }
      return 'N/A'
    },

    formatDate(date) {
      return date ? moment(date).format('DD MMM, YYYY') : ''
    },

    formatMonth(month) {
      return month ? moment(month).format('MMM YYYY') : ''
    },

    disabledDate(time) {
      // Disable past dates
      return time.getTime() < Date.now() - 8.64e7
    },

    handleDialogClose() {
      this.showVoucherDialog = false
      this.resetVoucherForm()
    },

    resetVoucherForm() {
      this.voucherForm = {
        dueDate: null,
        fineAmount: 100,
        voucherType: 'monthly',
        feeMonth: null,
        customAmount: 0,
        selectedFeeTypes: [],
        feeTypeAmounts: {},
        notes: '',
        includePending: false
      }
      this.pendingVouchers = []
      this.duplicateWarning.show = false
    },

    handleVoucherTypeChange(value) {
      // Reset related fields when type changes
      if (value !== 'monthly') {
        this.voucherForm.feeMonth = null
      }
      if (value !== 'custom') {
        this.voucherForm.customAmount = 0
      }
      if (value !== 'multiple') {
        this.voucherForm.selectedFeeTypes = []
        this.voucherForm.feeTypeAmounts = {}
      }
      this.duplicateWarning.show = false
    },

    async handleIncludePendingChange(checked) {
      if (checked) {
        if (this.selectedStudents.length === 0) {
          this.$message.info('Please select students first to check for pending vouchers')
          return
        }
        await this.fetchPendingVouchers()
      } else {
        this.pendingVouchers = []
      }
    },

    async fetchPendingVouchers() {
      if (this.selectedStudents.length === 0) {
        this.pendingVouchers = []
        return
      }

      this.loadingPending = true
      try {
        const studentIds = this.selectedStudents.map(s => s.id)
        // Use the outstanding vouchers API function with student_ids filter
        const data = await getOutstandingVouchers({
          student_ids: studentIds.join(',')
        })

        // The response interceptor already unwraps response.data
        if (data && data.success && data.vouchers) {
          this.pendingVouchers = data.vouchers
        } else {
          this.pendingVouchers = []
        }
      } catch (error) {
        console.error('Error fetching pending vouchers:', error)
        this.$message.warning('Failed to load pending vouchers')
        this.pendingVouchers = []
      } finally {
        this.loadingPending = false
      }
    },

    isSelectedFeeType(feeTypeId) {
      return this.voucherForm.selectedFeeTypes.includes(feeTypeId)
    },

    handleFeeTypeSelection(feeTypeId, selected) {
      if (selected) {
        this.voucherForm.selectedFeeTypes.push(feeTypeId)
        // Initialize with original amount
        const feeType = this.getFeeTypeById(feeTypeId)
        if (feeType) {
          this.voucherForm.feeTypeAmounts[feeTypeId] = parseFloat(feeType.amount || 0)
        }
      } else {
        const index = this.voucherForm.selectedFeeTypes.indexOf(feeTypeId)
        if (index > -1) {
          this.voucherForm.selectedFeeTypes.splice(index, 1)
        }
        // Remove custom amount
        delete this.voucherForm.feeTypeAmounts[feeTypeId]
      }
    },

    updateFeeTypeAmount(feeTypeId, amount) {
      if (this.isSelectedFeeType(feeTypeId)) {
        this.voucherForm.feeTypeAmounts[feeTypeId] = amount || 0
      }
    },

    getSelectedFeeTypeAmount(feeTypeId) {
      return this.voucherForm.feeTypeAmounts[feeTypeId] || 0
    },

    getSelectedFeeTypesDetails() {
      return this.voucherForm.selectedFeeTypes.map(feeTypeId => {
        const feeType = this.getFeeTypeById(feeTypeId)
        return {
          id: feeTypeId,
          title: feeType?.title || 'Unknown',
          amount: this.voucherForm.feeTypeAmounts[feeTypeId] || 0
        }
      })
    },

    getFeeTypeById(feeTypeId) {
      return this.feeTypes.find(ft => ft.id === feeTypeId)
    },

    calculateTotalFeeAmount() {
      if (this.voucherForm.voucherType === 'custom') {
        return this.voucherForm.customAmount || 0
      } else if (this.voucherForm.voucherType === 'multiple') {
        return this.voucherForm.selectedFeeTypes.reduce((total, feeTypeId) => {
          return total + (this.voucherForm.feeTypeAmounts[feeTypeId] || 0)
        }, 0)
      } else {
        // For monthly fees, we'll use the first selected student's fee as base
        if (this.selectedStudents.length > 0) {
          return parseFloat(this.getStudentFee(this.selectedStudents[0]) || 0)
        }
        return 0
      }
    },

    calculateTotalPendingAmount() {
      return this.pendingVouchers.reduce((total, voucher) => {
        const amount = this.getPendingVoucherAmount(voucher)
        return total + parseFloat(amount || 0)
      }, 0)
    },

    getPendingVoucherAmount(voucher) {
      // Try different amount fields in order of preference
      if (voucher.total_amount_with_fine) {
        return voucher.total_amount_with_fine
      }
      if (voucher.total_with_fine) {
        return voucher.total_with_fine
      }
      if (voucher.amount) {
        return voucher.amount
      }
      if (voucher.fee_amount) {
        // Calculate with fine if overdue
        const feeAmount = parseFloat(voucher.fee_amount || 0)
        const fineAmount = parseFloat(voucher.fine_amount || 0)
        const today = new Date()
        const dueDate = new Date(voucher.due_date)
        
        // Include fine if overdue
        if (today > dueDate) {
          return feeAmount + fineAmount
        }
        return feeAmount
      }
      return 0
    },

    calculateTotalAmount() {
      const feeAmount = this.calculateTotalFeeAmount()
      const fineAmount = this.voucherForm.fineAmount || 0
      const pendingAmount = this.voucherForm.includePending ? this.calculateTotalPendingAmount() : 0
      return feeAmount + fineAmount + pendingAmount
    },

    async checkForDuplicates() {
      if (this.selectedStudents.length === 0 || !this.voucherForm.dueDate) {
        return
      }

      this.checkingDuplicates = true
      try {
        const studentIds = this.selectedStudents.map(s => s.id)
        const checkData = {
          student_ids: studentIds,
          due_date: this.voucherForm.dueDate,
          voucher_type: this.voucherForm.voucherType,
          fee_month: this.voucherForm.feeMonth
        }

        const response = await checkExistingVouchers(checkData)
        
        if (response.data && response.data.existing_vouchers && response.data.existing_vouchers.length > 0) {
          const existingCount = response.data.existing_vouchers.length
          const studentNames = response.data.existing_vouchers.slice(0, 3).map(v => v.student_name).join(', ')
          const moreText = existingCount > 3 ? ` and ${existingCount - 3} more` : ''
          
          this.duplicateWarning = {
            show: true,
            title: 'Duplicate Vouchers Found',
            message: `${existingCount} vouchers already exist for: ${studentNames}${moreText}. Please review your selection or choose different dates.`
          }
        } else {
          this.duplicateWarning.show = false
        }
      } catch (error) {
        console.error('Error checking duplicates:', error)
        // Don't show error message for duplicate check failure
      } finally {
        this.checkingDuplicates = false
      }
    },

    async generateVouchers() {
      if (!this.canGenerateVouchers) {
        this.$message.warning('Please complete all required fields')
        return
      }

      // Check for duplicates first if not already done
      if (!this.duplicateWarning.show) {
        await this.checkForDuplicates()
        
        // If duplicates were found, don't proceed automatically
        if (this.duplicateWarning.show) {
          this.$confirm('Duplicate vouchers detected. Do you want to proceed anyway?', 'Duplicate Warning', {
            confirmButtonText: 'Proceed Anyway',
            cancelButtonText: 'Cancel',
            type: 'warning'
          }).then(() => {
            this.proceedWithGeneration()
          }).catch(() => {
            // User cancelled
          })
          return
        }
      }

      this.proceedWithGeneration()
    },

    async proceedWithGeneration() {
      this.generating = true

      try {
        // Prepare voucher data
        const vouchers = this.selectedStudents.map(student => {
          let baseFee = 0
          let feeBreakdown = []

          if (this.voucherForm.voucherType === 'custom') {
            baseFee = this.voucherForm.customAmount
            feeBreakdown = [{
              fee_type: 'Custom Fee',
              amount: this.voucherForm.customAmount
            }]
          } else if (this.voucherForm.voucherType === 'multiple') {
            this.voucherForm.selectedFeeTypes.forEach(feeTypeId => {
              const feeType = this.getFeeTypeById(feeTypeId)
              const customAmount = this.voucherForm.feeTypeAmounts[feeTypeId] || 0
              if (feeType) {
                baseFee += parseFloat(customAmount)
                feeBreakdown.push({
                  fee_type: feeType.title,
                  amount: parseFloat(customAmount)
                })
              }
            })
          } else {
            // Monthly fee
            baseFee = parseFloat(this.getStudentFee(student) || 0)
            feeBreakdown = [{
              fee_type: 'Monthly Fee',
              amount: baseFee
            }]
          }

          // Include pending vouchers in fee breakdown if enabled
          if (this.voucherForm.includePending) {
            // Filter pending vouchers for this student
            const studentPendingVouchers = this.pendingVouchers.filter(pv => pv.student_id === student.id)
            studentPendingVouchers.forEach(pv => {
              const pendingAmount = this.getPendingVoucherAmount(pv)
              baseFee += parseFloat(pendingAmount) // Add to base fee
              feeBreakdown.push({
                fee_type: `Pending: ${pv.voucher_number}`,
                amount: parseFloat(pendingAmount),
                pending_voucher_id: pv.id
              })
            })
          }

          return {
            student_id: student.id,
            student_name: student.name,
            admission_number: student.adminssion_number,
            parent_name: student.parents?.name || 'N/A',
            parent_phone: student.parents?.phone || null,
            parent_email: student.parents?.email || null,
            class_name: student.stdclasses?.name || 'Unassigned',
            fee_amount: baseFee,
            fee_breakdown: feeBreakdown,
            fine_amount: this.voucherForm.fineAmount,
            due_date: this.voucherForm.dueDate,
            fee_month: this.voucherForm.feeMonth,
            notes: this.voucherForm.notes,
            total_with_fine: parseFloat(baseFee) + parseFloat(this.voucherForm.fineAmount || 0),
            voucher_type: this.voucherForm.voucherType,
            generated_date: new Date()
          }
        })

        // Save vouchers to database
        const voucherData = {
          vouchers: vouchers,
          due_date: this.voucherForm.dueDate,
          fine_amount: this.voucherForm.fineAmount,
          voucher_type: this.voucherForm.voucherType,
          custom_amount: this.voucherForm.customAmount,
          fee_month: this.voucherForm.feeMonth,
          selected_fee_types: this.voucherForm.selectedFeeTypes,
          notes: this.voucherForm.notes,
          generated_by: 'system', // Will be set by backend based on authenticated user
          generated_at: new Date().toISOString()
        }

        // Call API to save vouchers
        const response = await generateFeeVouchers(voucherData)
        
        if (response && response.saved_vouchers) {
          // Update vouchers with database IDs and voucher numbers
          vouchers.forEach((voucher, index) => {
            if (response.saved_vouchers[index]) {
              voucher.id = response.saved_vouchers[index].id
              voucher.voucher_number = response.saved_vouchers[index].voucher_number || `FV-${Date.now()}-${index + 1}`
            }
          })
        }

        // Store generated vouchers for printing
        this.generatedVouchers = vouchers

        // Validate voucher data before showing print dialog
        const validVouchers = vouchers.filter(voucher => 
          voucher && 
          typeof voucher === 'object' && 
          voucher.student_name
        )
        
        if (validVouchers.length !== vouchers.length) {
          console.warn(`${vouchers.length - validVouchers.length} invalid vouchers filtered out`)
          this.generatedVouchers = validVouchers
        }

        this.$message.success(`${validVouchers.length} vouchers generated and saved successfully!`)
        
        // Close generation dialog and open print dialog
        this.showVoucherDialog = false
        this.showPrintDialog = true
        
        console.log('Opening print dialog with vouchers:', validVouchers.length)
        console.log('showPrintDialog state:', this.showPrintDialog)
        console.log('Generated vouchers data:', this.generatedVouchers)

      } catch (error) {
        console.error('Error generating vouchers:', error)
        
        // Handle duplicate vouchers response
        if (error.response?.status === 422 && error.response?.data?.duplicates) {
          const duplicates = error.response.data.duplicates
          const duplicateMessage = `The following students already have vouchers:\n${duplicates.map(dup => 
            `• ${dup.student_name} (${dup.admission_number}) - ${dup.type} voucher: ${dup.existing_voucher}`
          ).join('\n')}`
          
          this.duplicateWarning = {
            show: true,
            title: 'Duplicate Vouchers Detected',
            message: duplicateMessage,
            duplicates: duplicates
          }
          
          this.$message.warning('Some students already have vouchers for this period')
          return
        }
        
        // Show specific error message if available
        const errorMessage = error.response?.data?.message || 'Failed to generate vouchers'
        this.$message.error(errorMessage)
        
        // If database save failed but we have vouchers, still allow printing
        if (this.generatedVouchers.length > 0) {
          this.$confirm('Database save failed, but vouchers are ready. Do you want to print them anyway?', 'Warning', {
            confirmButtonText: 'Print Anyway',
            cancelButtonText: 'Cancel',
            type: 'warning',
          }).then(() => {
            this.showVoucherDialog = false
            this.showPrintDialog = true
          }).catch(() => {
            // User cancelled, do nothing
          })
        }
      } finally {
        this.generating = false
      }
    },

    removeDuplicatedStudents() {
      if (!this.duplicateWarning.show || !this.duplicateWarning.duplicates) {
        return
      }
      
      // Remove students with duplicates from selection
      const duplicateAdmissionNumbers = this.duplicateWarning.duplicates.map(dup => dup.admission_number)
      const originalCount = this.selectedStudents.length
      
      this.selectedStudents = this.selectedStudents.filter(student => 
        !duplicateAdmissionNumbers.includes(student.adminssion_number)
      )
      
      const removedCount = originalCount - this.selectedStudents.length
      
      // Clear duplicate warning
      this.duplicateWarning = {
        show: false,
        title: '',
        message: '',
        duplicates: []
      }
      
      this.$message.success(`${removedCount} duplicated students removed from selection`)
    },

    handlePrintClose() {
      this.showPrintDialog = false
      this.generatedVouchers = []
      this.selectedStudents = []
      this.resetVoucherForm()
      
      // Refresh the table to clear selections
      this.$refs.voucherTable?.clearSelection()
    }
  }
}
</script>

<style scoped>
.app-container {
  padding: 16px;
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

.class-select,
.group-select {
  width: 200px;
  min-width: 200px;
}

.search-input {
  width: 300px;
  min-width: 250px;
}

.action-section {
  margin-left: 16px;
}

.generate-btn {
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(64, 158, 255, 0.3);
}

.generate-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(64, 158, 255, 0.4);
}

/* Table Card */
.table-card {
  border-radius: 4px;
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

.total-count {
  background: linear-gradient(135deg, #409eff, #36a3f7);
  color: white;
  border: none;
  font-weight: 500;
}

/* Table Styles */
.voucher-table :deep(.el-table__header th) {
  background: #f8fafc;
  color: #606266;
  font-weight: 600;
}

.admission-number {
  font-family: 'Courier New', monospace;
  font-weight: 600;
  color: #606266;
}

.student-info {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.student-name {
  font-weight: 600;
  color: #303133;
}

.parent-info {
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 12px;
  color: #909399;
}

.parent-icon {
  font-size: 14px;
  color: #409eff;
}

.fee-amount {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 6px;
  font-weight: 600;
  color: #27ae60;
  font-family: 'Courier New', monospace;
}

.currency-icon {
  color: #f39c12;
}

/* Pagination */
.pagination-section {
  margin-top: 16px;
  display: flex;
  justify-content: center;
}

/* Voucher Dialog */
.voucher-form {
  padding: 20px 0;
}

.fee-types-grid {
  border: 1px solid #e4e7ed;
  border-radius: 6px;
  padding: 16px;
  background-color: #fafbfc;
}

.fee-type-item {
  margin-bottom: 16px;
  padding-bottom: 12px;
  border-bottom: 1px solid #f0f0f0;
}

.fee-type-item:last-child {
  border-bottom: none;
  margin-bottom: 0;
}

.fee-type-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  width: 100%;
}

.fee-type-checkbox {
  flex: 1;
  margin-right: 16px;
}

.fee-type-title {
  font-weight: 600;
  color: #303133;
  font-size: 14px;
}

.fee-amount-input {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 4px;
}

.original-amount {
  font-size: 12px;
  color: #909399;
  font-style: italic;
}

.fee-type-details {
  display: flex;
  flex-direction: column;
  margin-left: 8px;
}

.fee-type-amount {
  color: #67c23a;
  font-weight: 500;
  font-size: 12px;
  margin-top: 2px;
}

.selected-fee-types {
  margin-top: 12px;
  padding: 12px;
  background-color: #f0f9ff;
  border: 1px solid #409eff;
  border-radius: 6px;
}

.selected-fee-type {
  display: flex;
  justify-content: space-between;
  padding: 4px 0;
  color: #409eff;
  font-size: 13px;
}

.duplicate-warning {
  margin-top: 16px;
}

.voucher-summary {
  margin-top: 20px;
  padding: 16px;
  background: #f8faff;
  border-radius: 8px;
  border-left: 4px solid #409eff;
}

.voucher-summary h4 {
  margin: 0 0 12px 0;
  color: #303133;
  font-weight: 600;
}

.summary-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
  padding: 4px 0;
}

.summary-item .value {
  font-weight: 600;
  color: #409eff;
}

.total-summary {
  border-top: 1px solid #e4e7ed;
  margin-top: 12px;
  padding-top: 12px;
  font-size: 16px;
  font-weight: 600;
}

.total-summary .value {
  color: #67c23a;
  font-size: 18px;
}

/* Duplicate Warning */
.duplicate-warning-section {
  margin-top: 20px;
}

.duplicate-warning {
  margin-bottom: 16px;
}

.duplicate-details {
  margin-top: 8px;
}

.duplicate-details p {
  white-space: pre-line;
  margin-bottom: 12px;
  font-size: 14px;
  line-height: 1.6;
}

.duplicate-actions {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

/* Pending Vouchers Styles */
.pending-warning {
  margin-top: 8px;
}

.pending-warning :deep(.el-alert) {
  margin-top: 8px;
}

.pending-vouchers-section {
  margin: 12px 0;
  padding: 12px;
  background: #fff3cd;
  border: 1px solid #ffeaa7;
  border-radius: 6px;
}

.pending-voucher-details {
  margin: 8px 0;
  max-height: 120px;
  overflow-y: auto;
}

.pending-voucher-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 4px 8px;
  margin-bottom: 4px;
  background: #fff;
  border-radius: 4px;
  border: 1px solid #e4e7ed;
  font-size: 13px;
}

.voucher-number {
  font-weight: 600;
  color: #e6a23c;
  font-family: 'Courier New', monospace;
}

.voucher-amount {
  font-weight: 600;
  color: #f56c6c;
}

.pending-amount {
  color: #f56c6c !important;
  font-weight: 600;
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

  .action-section {
    margin-left: 0;
    text-align: center;
  }

  .class-select,
  .group-select,
  .search-input {
    width: 100%;
    min-width: unset;
  }

  .voucher-dialog {
    width: 95% !important;
  }

  .fee-types-grid .el-col {
    margin-bottom: 8px;
  }
}
</style>
