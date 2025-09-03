import request from '@/utils/request'

// Get students eligible for fee vouchers
export function getFeeVoucherStudents(query) {
  return request({
    url: '/fee/voucher/students',
    method: 'get',
    params: query
  })
}

// Generate and save fee vouchers
export function generateFeeVouchers(data) {
  return request({
    url: '/fee/voucher/generate',
    method: 'post',
    data
  })
}

// Get list of generated fee vouchers
export function getFeeVouchers(query) {
  return request({
    url: '/fee/voucher/list',
    method: 'get',
    params: query
  })
}

// Get specific fee voucher details
export function getFeeVoucherDetails(voucherId) {
  return request({
    url: `/fee/voucher/${voucherId}`,
    method: 'get'
  })
}

// Update fee voucher status (paid/unpaid)
export function updateFeeVoucherStatus(voucherId, status, paidAmount = null, paymentDate = null) {
  return request({
    url: `/fee/voucher/${voucherId}/status`,
    method: 'put',
    data: {
      status,
      paid_amount: paidAmount,
      payment_date: paymentDate
    }
  })
}

// Delete/Cancel fee voucher
export function deleteFeeVoucher(voucherId) {
  return request({
    url: `/fee/voucher/${voucherId}`,
    method: 'delete'
  })
}

// Get fee voucher templates/settings
export function getFeeVoucherSettings() {
  return request({
    url: '/fee/voucher/settings',
    method: 'get'
  })
}

// Save fee voucher settings
export function saveFeeVoucherSettings(settings) {
  return request({
    url: '/fee/voucher/settings',
    method: 'post',
    data: settings
  })
}

// Get fee voucher statistics
export function getFeeVoucherStats(filters = {}) {
  return request({
    url: '/fee/voucher/statistics',
    method: 'get',
    params: filters
  })
}

// Reprint specific fee voucher - get voucher data for printing
export function reprintFeeVoucher(voucherId) {
  return request({
    url: `/fee/voucher/${voucherId}/reprint`,
    method: 'post'
  })
}

// Save fee voucher
export function saveFeeVoucher(voucherData) {
  return request({
    url: '/fee/voucher/save',
    method: 'post',
    data: voucherData
  })
}

// Print fee voucher
export function printFeeVouchers(voucherIds) {
  return request({
    url: '/fee/voucher/print',
    method: 'post',
    data: { voucher_ids: voucherIds },
    responseType: 'blob'
  })
}

// Get outstanding fee vouchers
export function getOutstandingVouchers(filters = {}) {
  return request({
    url: '/fee/voucher/outstanding',
    method: 'get',
    params: filters
  })
}

// Send voucher reminders
export function sendVoucherReminders(payload) {
  return request({
    url: '/fee/voucher/remind',
    method: 'post',
    data: payload
  })
}

// Check for existing vouchers
export function checkExistingVouchers(data) {
  return request({
    url: '/fee/voucher/check-existing',
    method: 'post',
    data
  })
}
