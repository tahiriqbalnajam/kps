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
                <div v-if="voucher.fee_month" class="fee-month"><strong>Fee Month:</strong> {{ formatMonth(voucher.fee_month) }}</div>
                <div class="print-date"><strong>Due Date:</strong> {{ formatDate(voucher.due_date) }}</div>
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
                <div v-else-if="voucher.status === 'unpaid'" class="pending-stamp">PENDING</div>
                <div v-else-if="voucher.status === 'cancelled'" class="cancelled-stamp">CANCELLED</div>
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
                <div v-if="voucher.fee_month" class="fee-month"><strong>Fee Month:</strong> {{ formatMonth(voucher.fee_month) }}</div>
                <div class="print-date"><strong>Due Date:</strong> {{ formatDate(voucher.due_date) }}</div>
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
                <div v-else-if="voucher.status === 'unpaid'" class="pending-stamp">PENDING</div>
                <div v-else-if="voucher.status === 'cancelled'" class="cancelled-stamp">CANCELLED</div>
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

    formatMonth(month) {
      return month ? moment(month).format('MMM YYYY') : ''
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

          /* Inkjet-safe palette: light tones darkened one step so hairlines,
             tints and muted text stay visible on inkjet printouts (ink spreads
             and lightens on absorbent paper). Solid indigo (#4f46e5) prints fine. */
          :root {
            --accent: #4f46e5;
            --accent-soft: #e0e7ff;
            --accent-border: #c7d2fe;
            --ink: #1e293b;
            --muted: #5b6b82;
            --border: #cbd5e1;
            --border-strong: #94a3b8;
            --surface: #f1f5f9;
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
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 6mm;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
          }
           
          .voucher-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--accent);
          }
          
          .school-info {
            display: flex;
            gap: 10px;
            align-items: center;
            flex: 1;
          }
          
          .school-logo img {
            height: 56px;
            width: 56px;
            object-fit: contain;
          }

          .school-details h2 {
            margin: 0 0 3px 0;
            color: var(--ink);
            font-size: 19px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
          }

          .school-details p {
            margin: 1px 0;
            color: var(--muted);
            font-size: 11.5px;
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
            margin: 0 0 6px 0;
            color: var(--ink);
            font-size: 15px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
          }

          .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            background: var(--accent-soft);
            border: 1px solid var(--accent-border);
            color: var(--accent);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.8px;
            margin-left: 6px;
            vertical-align: middle;
          }

          .voucher-number {
            display: block;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--ink);
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 700;
            margin-bottom: 4px;
            font-family: inherit;
          }

          .copy-label {
            display: inline-block;
            background: var(--accent);
            color: white;
            border-radius: 999px;
            padding: 2px 10px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-top: 2px;
          }

          .print-date {
             color: var(--muted);
             font-size: 10.5px;
             margin-top: 3px;
          }

          .fee-month {
             display: inline-block;
             margin-top: 4px;
             background: var(--accent-soft);
             border: 1px solid var(--accent-border);
             color: var(--accent);
             border-radius: 999px;
             padding: 2px 10px;
             font-size: 11px;
             font-weight: 700;
             letter-spacing: 0.5px;
          }
          
          .voucher-body {
            flex: 1;
            display: flex;
            flex-direction: column;
          }

          .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
          }

          .info-table td {
            padding: 6px 8px;
            border: none;
            border-bottom: 1px solid var(--border);
            font-size: 12px;
            color: var(--ink);
          }

          .info-table tr:last-child td {
            border-bottom: none;
          }

          .info-table .label {
            font-weight: 700;
            color: var(--muted);
            width: 14%;
            white-space: nowrap;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
          }

          .info-table .value {
            font-weight: 600;
            width: 36%;
          }
          
          .fee-section {
            margin-bottom: 12px;
            flex: 1;
          }

          .fee-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
          }

          .fee-table th,
          .fee-table td {
            padding: 6px 10px;
            border-right: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            text-align: left;
            font-size: 12px;
            color: var(--ink);
          }

          .fee-table th:last-child,
          .fee-table td:last-child {
            border-right: none;
          }

          .fee-table tr:last-child td {
            border-bottom: none;
          }

          .fee-table th {
            background: var(--accent-soft);
            color: var(--accent);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.8px;
            border-bottom: 1px solid var(--accent-border);
          }

          .fee-table .amount-col,
          .fee-table .amount {
            text-align: right;
            width: 25%;
            font-variant-numeric: tabular-nums;
            font-weight: 600;
            white-space: nowrap;
          }

          .subtotal-row td {
            color: var(--muted);
            font-weight: 600;
            border-top: 1px solid var(--border);
          }

          .paid-row td {
            color: var(--muted);
            border-top: 1px solid var(--border);
          }

          .total-row td {
            border-top: 2px solid var(--accent);
            font-weight: 800;
            background: var(--surface);
            font-size: 12.5px;
            color: var(--ink);
          }
          
          .payment-info {
            margin: 10px 0;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px 10px;
          }

          .paid-stamp, .partial-stamp, .pending-stamp, .cancelled-stamp {
             position: absolute;
             top: 42%;
             left: 50%;
             transform: translate(-50%, -50%) rotate(-15deg);
             font-size: 2.8rem;
             font-weight: 800;
             letter-spacing: 5px;
             border: 4px solid;
             border-radius: 14px;
             padding: 8px 20px;
             text-transform: uppercase;
             z-index: 0;
             pointer-events: none;
             opacity: 0.25; /* Darkened for inkjet legibility */
             mix-blend-mode: multiply;
          }

          .paid-stamp { color: #16a34a; }
          .partial-stamp { color: #d97706; }
          .pending-stamp { color: #5b6b82; }
          .cancelled-stamp { color: #dc2626; }

          .payment-instruction {
            font-size: 11px;
            color: var(--ink);
          }

          .payment-instruction strong {
            display: inline;
            margin-right: 5px;
            color: var(--accent);
            font-weight: 700;
          }

          .signatures {
            margin-top: auto; /* Push to bottom */
            padding-top: 12px;
            border-top: 1px dashed var(--border-strong);
          }

          .signature-row {
             display: flex;
             justify-content: space-between;
             width: 100%;
             font-size: 11px;
             font-weight: 500;
             color: var(--ink);
          }

          .sig-item strong {
            font-weight: 700;
            margin-right: 5px;
            color: var(--muted);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
          }

          .voucher-status {
             font-size: 11px;
             font-weight: 800;
             text-transform: uppercase;
             margin-bottom: 2px;
             padding: 2px 0;
             border-bottom: 1px solid var(--border);
             display: inline-block;
          }

          .voucher-footer {
            margin-top: 10px;
            border-top: 1px solid var(--border);
            padding-top: 6px;
            text-align: center;
            font-size: 10px;
            color: var(--muted);
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
  padding: 18px;
  margin: 0;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
  min-height: 95vh;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
  display: flex;
  flex-direction: column;
  position: relative; /* anchor status stamp to this copy */
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
  border-bottom: 2px solid #4f46e5;
}

.school-info {
  display: flex;
  gap: 15px;
  align-items: center;
}

.school-details h2 {
  margin: 0 0 5px 0;
  color: #1e293b;
  font-size: 20px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.4px;
}

.school-details p {
  margin: 2px 0;
  color: #5b6b82;
  font-size: 14px;
}

.school-tagline {
  font-style: italic;
  color: #5b6b82 !important;
  font-weight: 500;
}

.school-website {
  color: #5b6b82 !important;
  font-size: 12px;
}

.voucher-info {
  text-align: right;
}

.voucher-title {
  margin: 0;
  color: #1e293b;
  font-size: 20px;
  font-weight: 800;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.status-badge {
  display: inline-block;
  padding: 3px 12px;
  border-radius: 999px;
  background: #e0e7ff;
  border: 1px solid #c7d2fe;
  color: #4f46e5;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.8px;
  margin-left: 6px;
  vertical-align: middle;
  text-transform: uppercase;
}

.voucher-number {
  background: #f1f5f9;
  color: #1e293b;
  padding: 4px 10px;
  border-radius: 6px;
  font-size: 12px;
  font-weight: 700;
  margin: 6px 0;
  display: inline-block;
  border: 1px solid #cbd5e1;
}

.copy-label {
  background: #4f46e5;
  color: white;
  padding: 4px 14px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 0.8px;
  margin: 8px 0;
  display: inline-block;
  text-transform: uppercase;
}

.office-label {
  background: #334155;
}

.print-date {
  color: #5b6b82;
  font-size: 12px;
  font-weight: 600;
}

.fee-month {
  display: inline-block;
  margin-top: 5px;
  background: #e0e7ff;
  border: 1px solid #c7d2fe;
  color: #4f46e5;
  border-radius: 999px;
  padding: 3px 12px;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 0.5px;
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
  border: none;
  border-bottom: 1px solid #cbd5e1;
}

.info-table tr:last-child td {
  border-bottom: none;
}

.info-table .label {
  font-weight: 700;
  color: #5b6b82;
  width: 14%;
  white-space: nowrap;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.5px;
}

.info-table .value {
  font-weight: 600;
}

.due-date {
  color: #555;
  font-weight: 700;
}

.fee-section {
  margin: 10px 0;
}

.paid-stamp,
.partial-stamp,
.pending-stamp,
.cancelled-stamp {
  font-size: 26px;
  font-weight: 800;
  letter-spacing: 4px;
  border: 3px solid;
  border-radius: 12px;
  padding: 8px 18px;
  transform: translate(-50%, -50%) rotate(-15deg);
  display: inline-block;
  margin-bottom: 10px;
  position: absolute;
  top: 50%;
  left: 50%;
  opacity: 0.25;
  pointer-events: none;
}

.paid-stamp {
  color: #16a34a;
  border-color: #16a34a;
}

.partial-stamp {
  color: #d97706;
  border-color: #d97706;
}

.pending-stamp {
  color: #5b6b82;
  border-color: #5b6b82;
}

.cancelled-stamp {
  color: #dc2626;
  border-color: #dc2626;
}

.fee-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  overflow: hidden;
}

.fee-table th,
.fee-table td {
  padding: 10px 14px;
  text-align: left;
  border-right: 1px solid #cbd5e1;
  border-bottom: 1px solid #cbd5e1;
  font-size: 14px;
  color: #1e293b;
}

.fee-table th:last-child,
.fee-table td:last-child {
  border-right: none;
}

.fee-table tr:last-child td {
  border-bottom: none;
}

.fee-table th {
  background: #e0e7ff;
  color: #4f46e5;
  font-weight: 700;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 0.8px;
  border-bottom: 1px solid #c7d2fe;
}

.amount-col,
.amount {
  text-align: right;
  font-variant-numeric: tabular-nums;
  font-weight: 600;
  white-space: nowrap;
}

.fine-row {
  color: #5b6b82;
}

.subtotal-row td,
.paid-row td {
  color: #5b6b82;
  font-weight: 600;
}

.total-row td {
  font-size: 15px;
  color: #1e293b;
  font-weight: 800;
  background: #f1f5f9;
  border-top: 2px solid #4f46e5;
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

.payment-info {
  margin: 14px 0;
  padding: 12px 14px;
  background: #f1f5f9;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 13px;
  color: #1e293b;
}

.payment-info strong {
  color: #4f46e5;
  font-weight: 700;
  margin-right: 5px;
}

.signatures {
  margin-top: auto;
  padding-top: 14px;
  border-top: 1px dashed #94a3b8;
}

.signature-row {
  display: flex;
  justify-content: space-between;
  width: 100%;
  font-size: 13px;
  color: #1e293b;
}

.sig-item strong {
  font-weight: 700;
  margin-right: 5px;
  color: #5b6b82;
  text-transform: uppercase;
  font-size: 11px;
  letter-spacing: 0.5px;
}

.voucher-footer {
  margin-top: 24px;
  padding-top: 12px;
  border-top: 1px solid #cbd5e1;
  text-align: center;
  font-size: 12px;
  color: #5b6b82;
  font-style: italic;
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
    border: 1px solid #cbd5e1 !important;
    border-radius: 10px !important;
    padding: 6mm !important;
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
    border-bottom: 2px solid #4f46e5 !important;
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