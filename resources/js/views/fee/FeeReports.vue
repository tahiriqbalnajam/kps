<template>
  <div class="app-container">
    <el-card shadow="never" class="report-tabs-card">
      <el-tabs v-model="activeTab" @tab-change="onTabChange" type="border-card">
        <el-tab-pane label="Voucher Generation" name="1">
          <template #label>
            <span class="tab-label"><el-icon><Document /></el-icon> Voucher Generation</span>
          </template>
        </el-tab-pane>
        <el-tab-pane label="Due vs Paid" name="2">
          <template #label>
            <span class="tab-label"><el-icon><PieChart /></el-icon> Due vs Paid</span>
          </template>
        </el-tab-pane>
        <el-tab-pane label="Overdue Defaulters" name="3">
          <template #label>
            <span class="tab-label"><el-icon><Warning /></el-icon> Overdue Defaulters</span>
          </template>
        </el-tab-pane>
        <el-tab-pane label="Collections" name="4">
          <template #label>
            <span class="tab-label"><el-icon><Money /></el-icon> Collections</span>
          </template>
        </el-tab-pane>
        <el-tab-pane label="Cancellations" name="5">
          <template #label>
            <span class="tab-label"><el-icon><CircleClose /></el-icon> Cancellations</span>
          </template>
        </el-tab-pane>
        <el-tab-pane label="Discount / Concession" name="6">
          <template #label>
            <span class="tab-label"><el-icon><Discount /></el-icon> Discount &amp; Concession</span>
          </template>
        </el-tab-pane>
        <el-tab-pane label="Fine / Late Fee" name="7">
          <template #label>
            <span class="tab-label"><el-icon><Coin /></el-icon> Fine / Late Fee</span>
          </template>
        </el-tab-pane>
        <el-tab-pane label="Student Ledger" name="8">
          <template #label>
            <span class="tab-label"><el-icon><UserFilled /></el-icon> Student Ledger</span>
          </template>
        </el-tab-pane>
      </el-tabs>

      <div class="tab-content">
        <keep-alive>
          <VoucherGeneration v-if="activeTab === '1'" />
          <DueVsPaid v-if="activeTab === '2'" />
          <OverdueDefaulters v-if="activeTab === '3'" />
          <CollectionReport v-if="activeTab === '4'" />
          <CancellationReport v-if="activeTab === '5'" />
          <DiscountConcession v-if="activeTab === '6'" />
          <FineReport v-if="activeTab === '7'" />
          <StudentLedger v-if="activeTab === '8'" />
        </keep-alive>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref } from 'vue'

import VoucherGeneration from './components/reports/VoucherGeneration.vue'
import DueVsPaid from './components/reports/DueVsPaid.vue'
import OverdueDefaulters from './components/reports/OverdueDefaulters.vue'
import CollectionReport from './components/reports/CollectionReport.vue'
import CancellationReport from './components/reports/CancellationReport.vue'
import DiscountConcession from './components/reports/DiscountConcession.vue'
import FineReport from './components/reports/FineReport.vue'
import StudentLedger from './components/reports/StudentLedger.vue'

const activeTab = ref('1')

const onTabChange = () => {
  // Tab switch, keep-alive preserves state
}
</script>

<style scoped>
.app-container { padding: 16px; }
.report-tabs-card { min-height: calc(100vh - 120px); }
.tab-content { padding-top: 16px; }
.tab-label { display: flex; align-items: center; gap: 6px; font-size: 13px; }
</style>
