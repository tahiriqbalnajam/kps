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
        Print
      </el-button>
      
      <div style="margin-left: 20px; display: flex; align-items: center;">
        <span style="margin-right: 10px; font-weight: bold; color: #333;">Orientation:</span>
        <el-radio-group v-model="printSettings.orientation" @change="savePrintSettings">
          <el-radio label="landscape">Landscape (Side-by-Side)</el-radio>
          <el-radio label="portrait">Portrait (Top-Bottom)</el-radio>
        </el-radio-group>
      </div>
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
                <h3 class="voucher-title">
                  STUDENT COPY 
                  <span class="status-badge">({{ getStatusLabel(voucher.status) }})</span>
                </h3>
                <div class="voucher-number">Voucher #: {{ voucher.voucher_number || 'TEMP-' + (index + 1) }}</div>
                <div class="print-date"><strong>Due Date: {{ formatDate(voucher.due_date) }}</strong></div>
              </div>
            </div>

            <div class="voucher-body">
              <table class="info-table">
                <tr>
                  <td class="label">Student:</td>
                  <td class="value">{{ voucher.student_name }}</td>
                  <td class="label">Father:</td>
                  <td class="value">{{ voucher.parent_name }}</td>
                </tr>
                <tr>
                  <td class="label">Class:</td>
                  <td class="value">{{ voucher.class_name }}</td>
                  <td class="label">Admission:</td>
                  <td class="value">{{ voucher.admission_number }}</td>
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
                    
                    <template v-if="hasPayment(voucher)">
                      <tr class="subtotal-row">
                        <td><strong>Total Amount</strong></td>
                        <td class="amount"><strong>{{ voucher.total_with_fine }}</strong></td>
                      </tr>
                      <tr class="paid-row">
                        <td>Less: Paid Amount</td>
                        <td class="amount">{{ voucher.paid_amount }}</td>
                      </tr>
                      <tr class="total-row">
                        <td><strong>Balance Due</strong></td>
                        <td class="amount total"><strong>{{ getBalanceAmount(voucher) }}</strong></td>
                      </tr>
                    </template>
                    <template v-else>
                      <tr class="total-row">
                        <td><strong>Total Amount</strong></td>
                        <td class="amount total"><strong>{{ voucher.total_with_fine }}</strong></td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>

              <div class="payment-info">
                <div v-if="voucher.status === 'paid'" class="paid-stamp">PAID</div>
                <div v-else-if="voucher.status === 'partially_paid'" class="partial-stamp">PARTIAL</div>
                <div class="payment-instruction">
                  <strong>Payment Instructions:</strong> Please pay before the due date to avoid fine charges.
                </div>
              </div>

              <div class="signatures">
                <div class="signature-row">
                  <div class="sig-item"><strong>Received By:</strong> {{ name }}</div>
                  <div class="sig-item"><strong>Date:</strong> {{ currentTimestamp }}</div>
                </div>
              </div>

              <!-- Footer -->
              <div class="voucher-footer" v-if="settings.invoice_footer">
                {{ settings.invoice_footer }}
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
                <h3 class="voucher-title">
                  OFFICE COPY 
                  <span class="status-badge">({{ getStatusLabel(voucher.status) }})</span>
                </h3>
                <div class="voucher-number">Voucher #: {{ voucher.voucher_number || 'TEMP-' + (index + 1) }}</div>
                <div class="print-date"><strong>Due Date: {{ formatDate(voucher.due_date) }}</strong></div>
              </div>
            </div>

            <div class="voucher-body">
              <table class="info-table">
                <tr>
                  <td class="label">Student:</td>
                  <td class="value">{{ voucher.student_name }}</td>
                  <td class="label">Father:</td>
                  <td class="value">{{ voucher.parent_name }}</td>
                </tr>
                <tr>
                  <td class="label">Class:</td>
                  <td class="value">{{ voucher.class_name }}</td>
                  <td class="label">Admission:</td>
                  <td class="value">{{ voucher.admission_number }}</td>
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
                    
                    <template v-if="hasPayment(voucher)">
                      <tr class="subtotal-row">
                        <td><strong>Total Amount</strong></td>
                        <td class="amount"><strong>{{ voucher.total_with_fine }}</strong></td>
                      </tr>
                      <tr class="paid-row">
                        <td>Less: Paid Amount</td>
                        <td class="amount">{{ voucher.paid_amount }}</td>
                      </tr>
                      <tr class="total-row">
                        <td><strong>Balance Due</strong></td>
                        <td class="amount total"><strong>{{ getBalanceAmount(voucher) }}</strong></td>
                      </tr>
                    </template>
                    <template v-else>
                      <tr class="total-row">
                        <td><strong>Total Amount</strong></td>
                        <td class="amount total"><strong>{{ voucher.total_with_fine }}</strong></td>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </div>

              <div class="payment-info">
                <div v-if="voucher.status === 'paid'" class="paid-stamp">PAID</div>
                <div v-else-if="voucher.status === 'partially_paid'" class="partial-stamp">PARTIAL</div>
                <div class="payment-instruction">
                  <strong>Payment Instructions:</strong> Please pay before the due date to avoid fine charges.
                </div>
              </div>

              <div class="signatures">
                <div class="signature-row">
                  <div class="sig-item"><strong>Received By:</strong> {{ name }}</div>
                  <div class="sig-item"><strong>Date:</strong> {{ currentTimestamp }}</div>
                </div>
              </div>

              <!-- Footer -->
              <div class="voucher-footer" v-if="settings.invoice_footer">
                {{ settings.invoice_footer }}
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
import { mapState } from 'pinia'
import { userStore } from '@/store/user'

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
        invoice_footer: 'Developed by IDLSchool (03217050405)'
      },
      printSettings: {
        orientation: 'landscape'
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
    
    // Load persisted print settings
    const savedOrientation = localStorage.getItem('fee_print_orientation')
    if (savedOrientation) {
      this.printSettings.orientation = savedOrientation
    }
  },
  computed: {
    ...mapState(userStore, ['name']),
    currentTimestamp() {
      return moment().format('DD-MMM-YYYY h:mm A')
    }
  },
  methods: {
    getStatusLabel(status) {
      if (!status) return 'UNPAID'
      return status.replace(/_/g, ' ').toUpperCase()
    },

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

    savePrintSettings() {
      localStorage.setItem('fee_print_orientation', this.printSettings.orientation)
    },

    printVouchers() {
      const printContent = document.getElementById('printVouchers')
      const originalContent = document.body.innerHTML

      // Create comprehensive print styles
      const printStyles = `
        <style>
          @page {  
            size: A4 ${this.printSettings.orientation}; 
            margin: 5mm;
            orientation: ${this.printSettings.orientation};
          }
          
          * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
          }
          
          body { 
            font-family: 'Segoe UI', 'Roboto', 'Helvetica Neue', Arial, sans-serif; 
            font-size: 13px; /* Increased base size for inkjet clarity */
            line-height: 1.4; /* More breathing room */
            margin: 0;
            padding: 0;
            background: white !important;
            color: #000 !important;
            -webkit-font-smoothing: antialiased;
          }
          
          .print-container {
            background: white;
            width: 100%;
            padding: 0;
          }
          
          .voucher-page {
            width: 100%;
            height: 100vh; /* Force full height per page */
            page-break-after: always;
            padding: 5mm;
            display: flex;
            align-items: center; /* Center vertically if needed */
          }
          
          .voucher-row {
            display: flex;
            flex-direction: ${this.printSettings.orientation === 'landscape' ? 'row' : 'column'};
            gap: ${this.printSettings.orientation === 'landscape' ? '10mm' : '0'}; /* Removed gap for portrait to save space */
            width: 100%;
            height: 100%;
          }
          
          .voucher-column {
            flex: ${this.printSettings.orientation === 'landscape' ? '1' : '0 0 50%'}; /* Use flex basis for 50% height */
            width: ${this.printSettings.orientation === 'landscape' ? '48%' : '100%'};
            height: ${this.printSettings.orientation === 'landscape' ? '100%' : '50%'}; /* Enforce 50% split */
            padding-bottom: ${this.printSettings.orientation === 'landscape' ? '0' : '5mm'};
            display: flex;
            flex-direction: column;
            box-sizing: border-box; /* Crucial for padding/border calculation */
          }
          
          .voucher-copy {
            background: white;
            border: 2px solid #000;
            border-radius: 0; /* Sharp corners for professional look */
            padding: 5mm;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
          }
           
          .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #000;
          }
          
          .school-info {
            display: flex;
            gap: 10px;
            align-items: center;
            flex: 1;
          }
          
          .school-logo img {
            height: 60px;
            width: 60px;
            object-fit: contain;
            filter: grayscale(100%) contrast(120%); /* Optimize logo for B&W */
          }
          
          .school-details h2 {
            margin: 0 0 2px 0;
            color: #000;
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
          }
          
          .school-details p {
            margin: 1px 0;
            color: #000;
            font-size: 12px; /* Bumped from 11px */
          }
          
          .school-tagline {
            font-style: italic;
            font-weight: 600;
            margin-bottom: 3px !important;
          }
          
          .voucher-info {
            text-align: right;
            flex-shrink: 0;
          }
          
          .voucher-title {
            margin: 0 0 5px 0;
            color: #000;
            font-size: 16px; /* Adjusted for inline status */
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
          }
          
          .status-badge {
            font-size: 12px;
            font-weight: 700;
            margin-left: 5px;
            vertical-align: middle;
          }
          
          .voucher-number {
            border: 2px solid #000; /* Thicker border */
            color: #000;
            padding: 2px 4px;
            font-size: 12px;
            font-weight: 800;
            display: block;
            margin-bottom: 4px;
            font-family: inherit; /* Removed Courier New */
            background: white;
          }
          
          .copy-label {
            color: #000;
            border: 2px solid #000;
            padding: 2px 6px;
            font-size: 11px; /* Bumped from 10px */
            font-weight: 800;
            text-transform: uppercase;
            display: inline-block;
            margin-top: 2px;
          }
          
          .print-date {
             color: #000;
             font-size: 10px; /* Bumped from 9px */
             margin-top: 2px;
          }
          
          .voucher-body {
            flex: 1;
            display: flex;
            flex-direction: column;
          }

          .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #000;
          }
          
          .info-table td {
            padding: 5px 8px; /* More padding */
            border: 1px solid #000;
            font-size: 12px; /* Bumped from 11px */
            color: #000;
          }
          
          
          .info-table .label {
            background: white; /* Removed gray background */
            font-weight: 800;
            width: 15%; /* Optimized for name space */
            white-space: nowrap; /* Prevent label wrapping */
          }
          
          .info-table .value {
            font-weight: 600; /* Slightly bolder for names */
            width: 35%;
          }
          
          .fee-section {
            margin-bottom: 10px;
            flex: 1; 
          }
          
          .fee-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
          }
          
          .fee-table th,
          .fee-table td {
            padding: 5px 8px; /* More padding */
            border: 1px solid #000;
            text-align: left;
            font-size: 12px; /* Bumped from 11px */
            color: #000;
          }
          
          .fee-table th {
            background: white; /* Removed gray background */
            font-weight: 800;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
          }
          
          .fee-table .amount-col,
          .fee-table .amount {
            text-align: right;
            width: 25%;
            font-family: inherit; /* Removed Consolas/Monaco */
          }
          
          .subtotal-row td,
          .paid-row td,
          .total-row td {
            border-top: 1px solid #000;
          }

          .total-row td {
            border-top: 2px solid #000;
            font-weight: 900;
            background: white; /* Removed gray background */
            font-size: 13px;
          }
          
          .payment-info {
            margin: 10px 0;
            border: 1px dashed #000;
            padding: 5px;
          }

          .paid-stamp, .partial-stamp {
             position: absolute;
             top: 40%;
             left: 50%;
             transform: translate(-50%, -50%) rotate(-15deg);
             font-size: 3.5rem;
             font-weight: 900;
             color: #000; /* Pure black text */
             border: 6px double #000; /* Distinct double border */
             padding: 10px 20px;
             text-transform: uppercase;
             z-index: 0;
             pointer-events: none;
             opacity: 0.15; /* Transparency for watermark effect */
             mix-blend-mode: multiply; /* Better blending on print */
          }
          
          .payment-instruction {
            font-size: 11px;
            color: #000;
          }
           
          .payment-instruction strong {
            display: inline; /* Make inline */
            margin-right: 5px;
            text-decoration: underline;
          }

          .signatures {
            margin-top: auto; /* Push to bottom */
            padding-top: 15px;
            border-top: 1px dashed #000;
          }
          
          .signature-row {
             display: flex;
             justify-content: space-between;
             width: 100%;
             font-size: 11px;
             font-weight: 500;
             color: #000;
          }
          
          .sig-item strong {
            font-weight: 800;
            margin-right: 5px;
          }
          
          .voucher-status {
             font-size: 11px;
             font-weight: 800;
             text-transform: uppercase;
             margin-bottom: 2px;
             padding: 2px 0;
             border-bottom: 1px solid #000;
             display: inline-block;
          }

          .voucher-footer {
            margin-top: 10px;
            border-top: 1px solid #000;
            padding-top: 5px;
            text-align: center;
            font-size: 10px; /* Bumped from 9px */
            color: #000;
            font-style: italic;
          }

          /* Hide elements */
          .print-toolbar { display: none !important; }
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
    },

    getBalanceAmount(voucher) {
      const total = parseFloat(voucher.total_with_fine || 0)
      const paid = parseFloat(voucher.paid_amount || 0)
      return (total - paid).toFixed(2)
    },

    hasPayment(voucher) {
      if (voucher.status === 'paid' || voucher.status === 'partially_paid') return true
      return parseFloat(voucher.paid_amount || 0) > 0
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
  display: flex;
  flex-direction: column;
}

.page-break {
  page-break-before: always;
}

.voucher-copy {
  padding: 15px;
  margin: 0;
  border: 2px solid #000;
  border-radius: 0;
  min-height: 95vh;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  display: flex;
  flex-direction: column;
}

.office-copy {
  background: #fff;
}

.voucher-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 2px solid #000;
}

.school-info {
  display: flex;
  gap: 15px;
  align-items: center;
}

.school-details h2 {
  margin: 0 0 5px 0;
  color: #000;
  font-size: 20px;
  font-weight: 800;
}

.school-details p {
  margin: 2px 0;
  color: #000;
  font-size: 14px;
}

.school-tagline {
  font-style: italic;
  color: #000 !important;
  font-weight: 500;
}

.school-website {
  color: #000 !important;
  font-size: 12px;
}

.voucher-info {
  text-align: right;
}

.voucher-title {
  margin: 0;
  color: #000;
  font-size: 24px;
  font-weight: bold;
}

.voucher-number {
  background: #fff;
  color: #000;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 12px;
  font-weight: 600;
  margin: 4px 0;
  display: inline-block;
  border: 1px solid #000;
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
  margin: 10px 0;
}

.paid-stamp,
.partial-stamp {
  font-size: 24px;
  font-weight: bold;
  color: green;
  border: 2px solid green;
  padding: 5px 10px;
  border-radius: 5px;
  transform: rotate(-15deg);
  display: inline-block;
  margin-bottom: 10px;
  position: absolute;
  top: 50%;
  left: 50%;
  opacity: 0.3;
}

.partial-stamp {
  color: orange;
  border-color: orange;
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


.subtotal-row td,
.paid-row td {
  font-weight: 600;
  background-color: #fafafa;
}

.total-row {
  background: #f0f0f0;
  border-top: 2px solid #333;
}

.total-row td {
  font-size: 16px;
  color: #333;
  font-weight: bold;
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
    size: A4 landscape;
    margin: 10mm;
    orientation: landscape;
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