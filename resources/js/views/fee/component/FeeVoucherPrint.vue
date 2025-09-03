<template>
  <el-dialog
    title="Fee Voucher Print Preview"
    v-model="dialogVisible"
    width="90%"
    top="5vh"
    :before-close="handleClose"
    class="print-dialog"
  >
    <div class="print-toolbar">
      <el-button type="primary" @click="printVouchers" size="large">
        <el-icon><Printer /></el-icon>
        Print All Vouchers
      </el-button>
      <el-button @click="handleClose" size="large">
        <el-icon><Close /></el-icon>
        Close
      </el-button>
    </div>

    <div class="print-container" id="printVouchers">
      <div 
        v-for="(voucher, index) in vouchers" 
        :key="index" 
        class="voucher-page"
        :class="{ 'page-break': index > 0 }"
      >
        <!-- Two Column Layout: Student Copy and Office Copy Side by Side -->
        <div class="voucher-row">
          <!-- Student Copy - Left Column -->
          <div class="voucher-copy student-copy voucher-column">
            <div class="voucher-header">
              <div class="school-info">
                <div class="school-logo">
                  <el-image
                    :src="`/${settings.school_logo || 'images/default-logo.png'}`"
                    fit="contain"
                    style="height: 60px; width: 60px;"
                  />
                </div>
                <div class="school-details">
                  <h2 class="school-name">{{ settings.school_name || 'School Name' }}</h2>
                  <p v-if="settings.school_tagline" class="school-tagline">{{ settings.school_tagline }}</p>
                  <p class="school-address">{{ settings.school_address || 'School Address' }}</p>
                  <p class="school-contact">Phone: {{ settings.school_phone || 'Phone Number' }}</p>
                  <p v-if="settings.school_website" class="school-website">{{ settings.school_website }}</p>
                </div>
              </div>
              <div class="voucher-info">
                <h3 class="voucher-title">FEE VOUCHER</h3>
                <div class="voucher-number">Voucher #: {{ voucher.voucher_number || 'TEMP-' + (index + 1) }}</div>
                <div class="copy-label">Student Copy</div>
                <div class="print-date">{{ formatDate(new Date()) }}</div>
              </div>
            </div>

            <div class="voucher-body">
              <table class="info-table">
                <tr>
                  <td class="label">Student:</td>
                  <td class="value">{{ voucher.student_name }}</td>
                </tr>
                <tr>
                  <td class="label">Admission:</td>
                  <td class="value">{{ voucher.admission_number }}</td>
                </tr>
                <tr>
                  <td class="label">Father:</td>
                  <td class="value">{{ voucher.parent_name }}</td>
                </tr>
                <tr>
                  <td class="label">Class:</td>
                  <td class="value">{{ voucher.class_name }}</td>
                </tr>
                <tr>
                  <td class="label">Due Date:</td>
                  <td class="value due-date">{{ formatDate(voucher.due_date) }}</td>
                </tr>
              </table>

              <div class="fee-section">
                <table class="fee-table">
                  <thead>
                    <tr>
                      <th>Description</th>
                      <th class="amount-col">Amount (Rs.)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Show fee breakdown if available (for multiple fee types) -->
                    <template v-if="voucher.fee_breakdown && voucher.fee_breakdown.length > 0">
                      <tr v-for="(feeItem, feeIndex) in voucher.fee_breakdown" :key="feeIndex">
                        <td>{{ feeItem.fee_type }}</td>
                        <td class="amount">{{ feeItem.amount }}</td>
                      </tr>
                    </template>
                    <!-- Fallback for single fee (monthly/custom) -->
                    <template v-else>
                      <tr>
                        <td>{{ getFeeDescription(voucher.voucher_type) }}</td>
                        <td class="amount">{{ voucher.fee_amount }}</td>
                      </tr>
                    </template>
                    
                    <tr v-if="voucher.fine_amount > 0" class="fine-row">
                      <td>Fine (After Due Date)</td>
                      <td class="amount">{{ voucher.fine_amount }}</td>
                    </tr>
                    
                    <tr class="total-row">
                      <td><strong>Total Amount</strong></td>
                      <td class="amount total"><strong>{{ voucher.total_with_fine }}</strong></td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="payment-info">
                <div class="payment-instruction">
                  <strong>Payment Instructions:</strong>
                  <p>Please pay before the due date to avoid fine charges.</p>
                </div>
              </div>

              <div class="signatures">
                <div class="signature-box">
                  <span>Received By:</span>
                  <div class="signature-line">___________</div>
                  <span>Date: _______</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Office Copy - Right Column -->
          <div class="voucher-copy office-copy voucher-column">
            <div class="voucher-header">
              <div class="school-info">
                <div class="school-logo">
                  <el-image
                    :src="`/${settings.school_logo || 'images/default-logo.png'}`"
                    fit="contain"
                    style="height: 60px; width: 60px;"
                  />
                </div>
                <div class="school-details">
                  <h2 class="school-name">{{ settings.school_name || 'School Name' }}</h2>
                  <p v-if="settings.school_tagline" class="school-tagline">{{ settings.school_tagline }}</p>
                  <p class="school-address">{{ settings.school_address || 'School Address' }}</p>
                  <p class="school-contact">Phone: {{ settings.school_phone || 'Phone Number' }}</p>
                  <p v-if="settings.school_website" class="school-website">{{ settings.school_website }}</p>
                </div>
              </div>
              <div class="voucher-info">
                <h3 class="voucher-title">FEE VOUCHER</h3>
                <div class="voucher-number">Voucher #: {{ voucher.voucher_number || 'TEMP-' + (index + 1) }}</div>
                <div class="copy-label office-label">Office Copy</div>
                <div class="print-date">{{ formatDate(new Date()) }}</div>
              </div>
            </div>

            <div class="voucher-body">
              <table class="info-table">
                <tr>
                  <td class="label">Student:</td>
                  <td class="value">{{ voucher.student_name }}</td>
                </tr>
                <tr>
                  <td class="label">Admission:</td>
                  <td class="value">{{ voucher.admission_number }}</td>
                </tr>
                <tr>
                  <td class="label">Father:</td>
                  <td class="value">{{ voucher.parent_name }}</td>
                </tr>
                <tr>
                  <td class="label">Class:</td>
                  <td class="value">{{ voucher.class_name }}</td>
                </tr>
                <tr>
                  <td class="label">Due Date:</td>
                  <td class="value due-date">{{ formatDate(voucher.due_date) }}</td>
                </tr>
              </table>

              <div class="fee-section">
                <table class="fee-table">
                  <thead>
                    <tr>
                      <th>Description</th>
                      <th class="amount-col">Amount (Rs.)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Show fee breakdown if available (for multiple fee types) -->
                    <template v-if="voucher.fee_breakdown && voucher.fee_breakdown.length > 0">
                      <tr v-for="(feeItem, feeIndex) in voucher.fee_breakdown" :key="feeIndex">
                        <td>{{ feeItem.fee_type }}</td>
                        <td class="amount">{{ feeItem.amount }}</td>
                      </tr>
                    </template>
                    <!-- Fallback for single fee (monthly/custom) -->
                    <template v-else>
                      <tr>
                        <td>{{ getFeeDescription(voucher.voucher_type) }}</td>
                        <td class="amount">{{ voucher.fee_amount }}</td>
                      </tr>
                    </template>
                    
                    <tr v-if="voucher.fine_amount > 0" class="fine-row">
                      <td>Fine (After Due Date)</td>
                      <td class="amount">{{ voucher.fine_amount }}</td>
                    </tr>
                    
                    <tr class="total-row">
                      <td><strong>Total Amount</strong></td>
                      <td class="amount total"><strong>{{ voucher.total_with_fine }}</strong></td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="payment-info">
                <div class="payment-instruction">
                  <strong>Payment Instructions:</strong>
                  <p>Please pay before the due date to avoid fine charges.</p>
                </div>
              </div>

              <div class="signatures">
                <div class="signature-box">
                  <span>Authorized Signature:</span>
                  <div class="signature-line">___________</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
          <div class="voucher-header">
            <div class="school-info">
              <div class="school-logo">
                <el-image
                  :src="`/${settings.school_logo || 'images/default-logo.png'}`"
                  fit="contain"
                  style="height: 80px; width: 80px;"
                />
              </div>
              <div class="school-details">
                <h2 class="school-name">{{ settings.school_name || 'School Name' }}</h2>
                <p v-if="settings.school_tagline" class="school-tagline">{{ settings.school_tagline }}</p>
                <p class="school-address">{{ settings.school_address || 'School Address' }}</p>
                <p class="school-contact">Phone: {{ settings.school_phone || 'Phone Number' }}</p>
                <p v-if="settings.school_website" class="school-website">{{ settings.school_website }}</p>
              </div>
            </div>
            <div class="voucher-info">
              <h3 class="voucher-title">FEE VOUCHER</h3>
              <div class="voucher-number">Voucher #: {{ voucher.voucher_number || 'TEMP-' + (index + 1) }}</div>
              <div class="copy-label">Student Copy</div>
              <div class="print-date">Print Date: {{ formatDate(new Date()) }}</div>
            </div>
          </div>

          <div class="voucher-body">
            <table class="info-table">
              <tr>
                <td class="label">Student Name:</td>
                <td class="value">{{ voucher.student_name }}</td>
                <td class="label">Admission No:</td>
                <td class="value">{{ voucher.admission_number }}</td>
              </tr>
              <tr>
                <td class="label">Father Name:</td>
                <td class="value">{{ voucher.parent_name }}</td>
                <td class="label">Class:</td>
                <td class="value">{{ voucher.class_name }}</td>
              </tr>
              <tr>
                <td class="label">Due Date:</td>
                <td class="value due-date">{{ formatDate(voucher.due_date) }}</td>
                <td class="label">Voucher Type:</td>
                <td class="value">{{ voucher.voucher_type === 'monthly' ? 'Monthly Fee' : 'Custom Fee' }}</td>
              </tr>
            </table>

            <div class="fee-section">
              <table class="fee-table">
                <thead>
                  <tr>
                    <th>Description</th>
                    <th class="amount-col">Amount (Rs.)</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Show fee breakdown if available (for multiple fee types) -->
                  <template v-if="voucher.fee_breakdown && voucher.fee_breakdown.length > 0">
                    <tr v-for="(feeItem, feeIndex) in voucher.fee_breakdown" :key="feeIndex">
                      <td>{{ feeItem.fee_type }}</td>
                      <td class="amount">{{ feeItem.amount }}</td>
                    </tr>
                  </template>
                  <!-- Fallback for single fee (monthly/custom) -->
                  <template v-else>
                    <tr>
                      <td>{{ getFeeDescription(voucher.voucher_type) }}</td>
                      <td class="amount">{{ voucher.fee_amount }}</td>
                    </tr>
                  </template>
                  
                  <tr v-if="voucher.fine_amount > 0" class="fine-row">
                    <td>Fine (After Due Date)</td>
                    <td class="amount">{{ voucher.fine_amount }}</td>
                  </tr>
                  <tr class="total-row">
                    <td><strong>Total Amount (With Fine)</strong></td>
                    <td class="amount"><strong>{{ voucher.total_with_fine }}</strong></td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="payment-info" v-if="voucher.notes">
              <div class="notes-section">
                <strong>Notes:</strong>
                <p>{{ voucher.notes }}</p>
              </div>
            </div>

            <div class="payment-instructions">
              <p><strong>Payment Instructions:</strong></p>
              <ul>
                <li>Fee must be paid before the due date to avoid fine</li>
                <li>Fine of Rs. {{ voucher.fine_amount }} will be charged after due date</li>
                <li>Keep this voucher as payment receipt</li>
                <li>For any queries, contact school office</li>
              </ul>
            </div>

            <div class="voucher-footer">
              <div class="signature-section">
                <div class="signature-box">
                  <span>Received By:</span>
                  <div class="signature-line">_________________</div>
                  <span>Date: ___________</span>
                </div>
                <div class="signature-box">
                  <span>Authorized Signature:</span>
                  <div class="signature-line">_________________</div>
                </div>
              </div>
              
              <div class="footer-text" v-if="settings.invoice_footer">
                <div v-html="settings.invoice_footer"></div>
              </div>
              
              <div class="developer-credit">
                Developed by IDLBridge - 03457050405
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </el-dialog>
</template>

<script>
import { Printer, Close } from '@element-plus/icons-vue'
import moment from 'moment'
import { getFeeVoucherSettings } from '@/api/fee'

export default {
  name: 'FeeVoucherPrint',
  props: {
    vouchers: {
      type: Array,
      required: true,
      default: () => []
    },
    showDialog: {
      type: Boolean,
      default: false
    }
  },
  emits: ['close'],
  data() {
    return {
      dialogVisible: false,
      settings: {
        school_name: 'School Management System',
        school_address: 'School Address',
        school_phone: 'Phone Number',
        school_logo: 'images/default-logo.png',
        school_email: '',
        school_website: '',
        invoice_footer: 'Thank you for choosing our school!'
      }
    }
  },
  watch: {
    showDialog: {
      immediate: true,
      handler(newVal) {
        this.dialogVisible = newVal
      }
    }
  },
  mounted() {
    // Try to get settings from store or API
    this.loadSettings()
  },
  methods: {
    formatDate(date) {
      return moment(date).format('DD MMM, YYYY')
    },

    async loadSettings() {
      try {
        const response = await getFeeVoucherSettings()
        
        // Handle the axios interceptor response structure
        if (response && response.success && response.settings) {
          this.settings = {
            ...this.settings, // Keep defaults as fallback
            ...response.settings // Override with API data
          }
          console.log('School settings loaded:', this.settings)
        } else {
          console.warn('Settings API returned unexpected format:', response)
        }
      } catch (error) {
        console.log('Failed to load school settings, using defaults:', error)
        // Keep default settings if API call fails
      }
    },

    printVouchers() {
      const printContent = document.getElementById('printVouchers')
      const originalContent = document.body.innerHTML

      // Create print styles
      const printStyles = `
        <style>
          @media print {
            @page { 
              size: A4; 
              margin: 0.5cm; 
            }
            body { 
              font-family: Arial, sans-serif; 
              font-size: 12px; 
              line-height: 1.4;
              margin: 0;
              padding: 0;
            }
            .page-break { 
              page-break-before: always; 
            }
            .voucher-copy { 
              margin-bottom: 2cm; 
            }
            .voucher-copy:last-child { 
              margin-bottom: 0; 
            }
          }
        </style>
      `

      const printWindow = window.open('', '_blank')
      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>Fee Vouchers - Print</title>
          ${printStyles}
        </head>
        <body>
          ${printContent.innerHTML}
        </body>
        </html>
      `)

      printWindow.document.close()
      printWindow.focus()

      // Wait a moment for images to load, then print
      setTimeout(() => {
        printWindow.print()
        printWindow.close()
      }, 1000)
    },

    handleClose() {
      this.dialogVisible = false
      this.$emit('close')
    },

    getFeeDescription(voucherType) {
      switch(voucherType) {
        case 'monthly':
          return 'Monthly Fee'
        case 'custom':
          return 'Custom Fee'
        case 'multiple':
          return 'Multiple Fee Types'
        default:
          return 'Fee'
      }
    }
  }
}
</script>

<style scoped>
.print-dialog :deep(.el-dialog__body) {
  padding: 0;
}

.print-toolbar {
  padding: 16px 20px;
  background: #f5f7fa;
  border-bottom: 1px solid #e4e7ed;
  display: flex;
  gap: 12px;
  align-items: center;
}

.print-container {
  background: white;
  min-height: 400px;
  padding: 10px;
}

.voucher-page {
  background: white;
  margin: 0;
  padding: 0;
}

.voucher-row {
  display: flex;
  gap: 15px;
  width: 100%;
  min-height: calc(100vh - 100px);
}

.voucher-column {
  flex: 1;
  width: 48%;
}

.page-break {
  page-break-before: always;
}

.voucher-copy {
  padding: 15px;
  margin: 0;
  border: 2px solid #ddd;
  border-radius: 8px;
  min-height: 95vh;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.office-copy {
  border-color: #409eff;
  background: #fafbff;
}

.voucher-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 2px solid #e4e7ed;
}

.school-info {
  display: flex;
  gap: 15px;
  align-items: center;
}

.school-details h2 {
  margin: 0 0 5px 0;
  color: #303133;
  font-size: 20px;
}

.school-details p {
  margin: 2px 0;
  color: #606266;
  font-size: 14px;
}

.school-tagline {
  font-style: italic;
  color: #409eff !important;
  font-weight: 500;
}

.school-website {
  color: #67c23a !important;
  font-size: 12px;
}

.voucher-info {
  text-align: right;
}

.voucher-title {
  margin: 0;
  color: #409eff;
  font-size: 24px;
  font-weight: bold;
}

.voucher-number {
  background: #f5f7fa;
  color: #303133;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  margin: 4px 0;
  display: inline-block;
  border: 1px solid #e4e7ed;
  font-family: 'Courier New', monospace;
}

.copy-label {
  background: #67c23a;
  color: white;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  margin: 8px 0;
  display: inline-block;
}

.office-label {
  background: #409eff;
}

.print-date {
  color: #909399;
  font-size: 12px;
}

.voucher-body {
  color: #303133;
}

.info-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 20px;
}

.info-table td {
  padding: 8px 12px;
  border: 1px solid #e4e7ed;
}

.info-table .label {
  background: #f5f7fa;
  font-weight: 600;
  width: 25%;
}

.info-table .value {
  font-weight: 500;
}

.due-date {
  color: #e6a23c;
  font-weight: 700;
}

.fee-section {
  margin: 20px 0;
}

.fee-table {
  width: 100%;
  border-collapse: collapse;
  border: 2px solid #303133;
}

.fee-table th,
.fee-table td {
  padding: 12px;
  text-align: left;
  border: 1px solid #303133;
}

.fee-table th {
  background: #f5f7fa;
  font-weight: 600;
  color: #303133;
}

.amount-col,
.amount {
  text-align: right;
  font-family: 'Courier New', monospace;
  font-weight: 600;
}

.fine-row {
  color: #f56c6c;
}

.total-row {
  background: #f0f9ff;
  border-top: 2px solid #303133;
}

.total-row td {
  font-size: 16px;
  color: #409eff;
}

.notes-section {
  margin: 15px 0;
  padding: 12px;
  background: #fef7e0;
  border-left: 4px solid #e6a23c;
  border-radius: 4px;
}

.notes-section p {
  margin: 5px 0 0 0;
  color: #606266;
}

.payment-instructions {
  margin: 20px 0;
  padding: 15px;
  background: #f0f9ff;
  border-radius: 6px;
  border: 1px solid #b3d8ff;
}

.payment-instructions p {
  margin: 0 0 8px 0;
  font-weight: 600;
  color: #409eff;
}

.payment-instructions ul {
  margin: 8px 0 0 0;
  padding-left: 20px;
}

.payment-instructions li {
  margin-bottom: 4px;
  color: #606266;
}

.voucher-footer {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 2px solid #e4e7ed;
}

.signature-section {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
}

.signature-box {
  text-align: center;
  width: 45%;
}

.signature-line {
  border-bottom: 1px solid #303133;
  height: 30px;
  margin: 10px 0;
  width: 100%;
}

.footer-text {
  text-align: center;
  margin: 15px 0;
  color: #606266;
  font-size: 12px;
}

.developer-credit {
  text-align: center;
  font-size: 10px;
  color: #c0c4cc;
  border-top: 1px solid #e4e7ed;
  padding-top: 10px;
  margin-top: 15px;
}

/* Print Specific Styles */
@media print {
  .print-toolbar {
    display: none !important;
  }
  
  .voucher-page {
    page-break-after: always;
    margin: 0;
    padding: 5mm;
  }
  
  .voucher-row {
    display: flex;
    gap: 5mm;
    min-height: auto;
  }
  
  .voucher-column {
    flex: 1;
    width: 48%;
  }
  
  .voucher-copy {
    margin: 0;
    border: 1px solid #333;
    padding: 3mm;
    box-shadow: none;
    min-height: auto;
    page-break-inside: avoid;
  }
  
  .page-break {
    page-break-before: always;
  }
  
  body {
    background: white !important;
  }
  
  /* Adjust font sizes for print */
  .school-name {
    font-size: 16px !important;
  }
  
  .voucher-title {
    font-size: 18px !important;
  }
  
  .info-table td {
    padding: 4px 8px !important;
    font-size: 12px !important;
  }
  
  .fee-table th,
  .fee-table td {
    padding: 4px 8px !important;
    font-size: 12px !important;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .voucher-row {
    flex-direction: column;
    gap: 20px;
  }
  
  .voucher-column {
    width: 100%;
  }
  
  .voucher-header {
    flex-direction: column;
    text-align: center;
  }
  
  .school-info {
    justify-content: center;
    margin-bottom: 15px;
  }
  
  .voucher-info {
    text-align: center;
  }
  
  .signature-section {
    flex-direction: column;
    gap: 20px;
  }
  
  .signature-box {
    width: 100%;
  }
}
</style>
