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

      // Create comprehensive print styles
      const printStyles = `
        <style>
          @page { 
            size: A4 portrait; 
            margin: 10mm;
            orientation: portrait;
          }
          
          * {
            box-sizing: border-box;
          }
          
          body { 
            font-family: Arial, sans-serif; 
            font-size: 12px; 
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background: white !important;
          }
          
          .print-container {
            background: white;
            min-height: auto;
            padding: 0;
          }
          
          .voucher-page {
            background: white;
            margin: 0;
            padding: 0;
            page-break-after: always;
          }
          
          .voucher-row {
            display: flex;
            gap: 10mm;
            width: 100%;
          }
          
          .voucher-column {
            flex: 1;
            width: 48%;
          }
          
          .voucher-copy {
            background: white;
            border: 1px solid #333;
            border-radius: 4px;
            padding: 8mm;
            margin: 0;
            box-shadow: none;
            page-break-inside: avoid;
          }
          
          .student-copy {
            border-color: #666;
          }
          
          .office-copy {
            border-color: #333;
          }
          
          .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
          }
          
          .school-info {
            display: flex;
            gap: 10px;
            align-items: center;
            flex: 1;
          }
          
          .school-logo img {
            height: 50px;
            width: 50px;
            object-fit: contain;
          }
          
          .school-details h2 {
            margin: 0 0 4px 0;
            color: #303133;
            font-size: 16px;
            font-weight: bold;
          }
          
          .school-details p {
            margin: 2px 0;
            color: #606266;
            font-size: 11px;
          }
          
          .school-tagline {
            font-style: italic;
            color: #555 !important;
            font-weight: 500;
          }
          
          .school-website {
            color: #666 !important;
            font-size: 10px;
          }
          
          .voucher-info {
            text-align: right;
            flex-shrink: 0;
          }
          
          .voucher-title {
            margin: 0;
            color: #333;
            font-size: 18px;
            font-weight: bold;
          }
          
          .voucher-number {
            background: #f5f7fa;
            color: #303133;
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            margin: 3px 0;
            display: inline-block;
            border: 1px solid #ddd;
            font-family: 'Courier New', monospace;
          }
          
          .copy-label {
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: 600;
            margin: 6px 0;
            display: inline-block;
          }
          
          .student-copy .copy-label {
            background: #666;
          }
          
          .office-copy .copy-label {
            background: #333;
          }
          
          .print-date {
            color: #909399;
            font-size: 10px;
          }
          
          .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
          }
          
          .info-table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            font-size: 11px;
          }
          
          .info-table .label {
            background: #f5f7fa;
            font-weight: 600;
            width: 35%;
          }
          
          .info-table .value {
            background: white;
          }
          
          .due-date {
            color: #555;
            font-weight: 600;
          }
          
          .fee-section {
            margin: 15px 0;
          }
          
          .fee-table {
            width: 100%;
            border-collapse: collapse;
          }
          
          .fee-table th,
          .fee-table td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            text-align: left;
            font-size: 11px;
          }
          
          .fee-table th {
            background: #f5f7fa;
            font-weight: 600;
          }
          
          .fee-table .amount-col,
          .fee-table .amount {
            text-align: right;
            width: 25%;
          }
          
          .fine-row td {
            color: #555;
            font-style: italic;
          }
          
          .total-row td {
            background: #f0f0f0;
            font-weight: bold;
            border-top: 2px solid #333;
          }
          
          .payment-info {
            margin: 10px 0;
          }
          
          .payment-instruction {
            font-size: 10px;
            color: #606266;
          }
          
          .payment-instruction p {
            margin: 3px 0;
          }
          
          .signatures {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
            gap: 20px;
          }
          
          .signature-box {
            text-align: center;
            font-size: 10px;
            flex: 1;
          }
          
          .signature-line {
            border-bottom: 1px solid #333;
            height: 25px;
            margin: 8px 0;
            width: 100%;
          }
          
          .page-break {
            page-break-before: always;
          }
          
          /* Hide any remaining elements that shouldn't print */
          .print-toolbar,
          .el-dialog__header,
          .el-dialog__footer {
            display: none !important;
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
  border-color: #333;
  background: #f8f8f8;
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
  color: #555 !important;
  font-weight: 500;
}

.school-website {
  color: #666 !important;
  font-size: 12px;
}

.voucher-info {
  text-align: right;
}

.voucher-title {
  margin: 0;
  color: #333;
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
  background: #666;
  color: white;
  padding: 4px 12px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  margin: 8px 0;
  display: inline-block;
}

.office-label {
  background: #333;
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
  color: #555;
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
  color: #555;
}

.total-row {
  background: #f0f0f0;
  border-top: 2px solid #333;
}

.total-row td {
  font-size: 16px;
  color: #333;
}

.notes-section {
  margin: 15px 0;
  padding: 12px;
  background: #f5f5f5;
  border-left: 4px solid #666;
  border-radius: 4px;
}

.notes-section p {
  margin: 5px 0 0 0;
  color: #606266;
}

.payment-instructions {
  margin: 20px 0;
  padding: 15px;
  background: #f0f0f0;
  border-radius: 6px;
  border: 1px solid #ccc;
}

.payment-instructions p {
  margin: 0 0 8px 0;
  font-weight: 600;
  color: #333;
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
  @page {
    size: A4 portrait;
    margin: 10mm;
    orientation: portrait;
  }
  
  .print-toolbar {
    display: none !important;
  }
  
  .voucher-page {
    page-break-after: always;
    margin: 0;
    padding: 0;
  }
  
  .voucher-row {
    display: flex !important;
    gap: 10mm !important;
    min-height: auto;
    width: 100% !important;
  }
  
  .voucher-column {
    flex: 1 !important;
    width: 48% !important;
  }
  
  .voucher-copy {
    margin: 0 !important;
    border: 1px solid #333 !important;
    padding: 8mm !important;
    box-shadow: none !important;
    min-height: auto !important;
    page-break-inside: avoid !important;
    background: white !important;
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
    padding: 6px 8px !important;
    font-size: 11px !important;
  }
  
  .fee-table th,
  .fee-table td {
    padding: 6px 8px !important;
    font-size: 11px !important;
  }
  
  .voucher-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: flex-start !important;
    margin-bottom: 15px !important;
    padding-bottom: 10px !important;
    border-bottom: 1px solid #ddd !important;
  }
  
  .school-info {
    display: flex !important;
    gap: 10px !important;
    align-items: center !important;
  }
  
  .school-logo img {
    height: 50px !important;
    width: 50px !important;
  }
  
  .voucher-info {
    text-align: right !important;
  }
  
  .signatures {
    display: flex !important;
    justify-content: space-between !important;
    gap: 20px !important;
  }
  
  .signature-box {
    flex: 1 !important;
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
