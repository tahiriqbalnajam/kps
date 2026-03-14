<template>
  <div class="app-container">

    <!-- Header bar -->
    <div class="filter-container">
      <el-input
        v-model="query.keyword"
        placeholder="Search sessions..."
        style="width:220px;"
        clearable
        @input="debounceSearch"
        @clear="getList"
      >
        <template #prefix><el-icon><Search /></el-icon></template>
      </el-input>

      <el-button type="primary" style="margin-left:10px;" @click="openDrawer()">
        <el-icon class="el-icon--left"><Plus /></el-icon> Add Session
      </el-button>
    </div>

    <!-- Active session banner -->
    <el-alert
      v-if="activeSession"
      :title="`Active Session: ${activeSession.name}` + (activeSession.start_date ? `  (${formatDate(activeSession.start_date)} – ${formatDate(activeSession.end_date)})` : '')"
      type="success"
      :closable="false"
      show-icon
      style="margin-bottom:16px;"
    />
    <el-alert
      v-else
      title="No active session. Please set one as active."
      type="warning"
      :closable="false"
      show-icon
      style="margin-bottom:16px;"
    />

    <!-- Table -->
    <el-table v-loading="listLoading" :data="sessions" border style="width:100%;">

      <el-table-column label="#" prop="id" width="60" align="center" />

      <el-table-column label="Session Name" min-width="160">
        <template #default="{ row }">
          {{ row.name }}
          <el-tag v-if="row.is_active" type="success" size="small" style="margin-left:8px;">Active</el-tag>
        </template>
      </el-table-column>

      <el-table-column label="Start Date" width="130" align="center">
        <template #default="{ row }">{{ row.start_date ? formatDate(row.start_date) : '—' }}</template>
      </el-table-column>

      <el-table-column label="End Date" width="130" align="center">
        <template #default="{ row }">{{ row.end_date ? formatDate(row.end_date) : '—' }}</template>
      </el-table-column>

      <el-table-column label="Description" min-width="180">
        <template #default="{ row }">{{ row.description || '—' }}</template>
      </el-table-column>

      <el-table-column label="Actions" width="240" align="center">
        <template #default="{ row }">
          <!-- Set Active button (hidden when already active) -->
          <el-button
            v-if="!row.is_active"
            size="small"
            type="success"
            @click="handleSetActive(row)"
          >
            <el-icon class="el-icon--left"><CircleCheck /></el-icon>Set Active
          </el-button>
          <el-tag v-else type="success" size="small">Current</el-tag>

          <el-button size="small" style="margin-left:6px;" @click="openDrawer(row)">
            <el-icon><Edit /></el-icon>
          </el-button>

          <el-button
            size="small"
            type="danger"
            style="margin-left:6px;"
            :disabled="row.is_active"
            :title="row.is_active ? 'Cannot delete active session' : 'Delete'"
            @click="handleDelete(row)"
          >
            <el-icon><Delete /></el-icon>
          </el-button>
        </template>
      </el-table-column>

    </el-table>

    <pagination
      v-show="total > 0"
      :total="total"
      :page.sync="query.page"
      :limit.sync="query.limit"
      @pagination="getList"
      style="margin-top:12px;"
    />

    <!-- Add / Edit Drawer -->
    <el-drawer
      :title="form.id ? 'Edit Session' : 'Add Session'"
      :modelValue="drawerVisible"
      direction="rtl"
      size="420px"
      :before-close="closeDrawer"
    >
      <div style="padding:24px;">
        <el-form ref="formRef" :model="form" :rules="rules" label-position="top">

          <el-form-item label="Session Name" prop="name">
            <el-input
              v-model="form.name"
              placeholder="e.g. 2024-2025"
              maxlength="150"
              show-word-limit
            />
          </el-form-item>

          <el-form-item label="Start Date" prop="start_date">
            <el-date-picker
              v-model="form.start_date"
              type="date"
              placeholder="Select start date"
              value-format="YYYY-MM-DD"
              style="width:100%;"
            />
          </el-form-item>

          <el-form-item label="End Date" prop="end_date">
            <el-date-picker
              v-model="form.end_date"
              type="date"
              placeholder="Select end date"
              value-format="YYYY-MM-DD"
              style="width:100%;"
            />
          </el-form-item>

          <el-form-item label="Description" prop="description">
            <el-input
              v-model="form.description"
              type="textarea"
              :rows="3"
              placeholder="Optional notes"
              maxlength="255"
              show-word-limit
            />
          </el-form-item>

        </el-form>

        <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
          <el-button @click="closeDrawer">Cancel</el-button>
          <el-button type="primary" :loading="submitLoading" @click="onSubmit">
            {{ form.id ? 'Update' : 'Create' }}
          </el-button>
        </div>
      </div>
    </el-drawer>

  </div>
</template>

<script>
import Pagination from '@/components/Pagination/index.vue'
import Resource from '@/api/resource'
import request from '@/utils/request'
import { debounce } from 'lodash'
import { Plus, Edit, Delete, Search, CircleCheck } from '@element-plus/icons-vue'
import { ElMessage, ElMessageBox } from 'element-plus'

const sessionApi = new Resource('academic-sessions')

export default {
  name: 'AcademicSessions',
  components: { Pagination, Plus, Edit, Delete, Search, CircleCheck },

  data() {
    return {
      sessions:      [],
      activeSession: null,
      total:         0,
      listLoading:   false,
      submitLoading: false,
      drawerVisible: false,

      form: {
        id:          null,
        name:        '',
        start_date:  null,
        end_date:    null,
        description: '',
      },

      rules: {
        name: [
          { required: true, message: 'Session name is required', trigger: 'blur' },
          { min: 2, max: 150, message: 'Length must be between 2 and 150', trigger: 'blur' },
        ],
        end_date: [
          {
            validator: (rule, value, callback) => {
              if (value && this.form.start_date && value < this.form.start_date) {
                callback(new Error('End date must be on or after start date'))
              } else {
                callback()
              }
            },
            trigger: 'change',
          },
        ],
      },

      query: { page: 1, limit: 20, keyword: '' },
    }
  },

  created() {
    this.getList()
    this.loadActiveSession()
  },

  methods: {
    debounceSearch: debounce(function () {
      this.query.page = 1
      this.getList()
    }, 400),

    async getList() {
      this.listLoading = true
      try {
        const { data } = await sessionApi.list(this.query)
        this.sessions = data.sessions.data
        this.total    = data.sessions.total
        // Sync active banner from the loaded page
        const active = this.sessions.find(s => s.is_active)
        if (active) this.activeSession = active
      } finally {
        this.listLoading = false
      }
    },

    async loadActiveSession() {
      try {
        const { data } = await request({ url: '/academic-sessions/active', method: 'get' })
        this.activeSession = data.session || null
      } catch (_) {
        this.activeSession = null
      }
    },

    openDrawer(row = null) {
      if (row) {
        this.form = {
          id:          row.id,
          name:        row.name,
          start_date:  row.start_date ? String(row.start_date).substring(0, 10) : null,
          end_date:    row.end_date   ? String(row.end_date).substring(0, 10)   : null,
          description: row.description || '',
        }
      } else {
        this.resetForm()
      }
      this.drawerVisible = true
    },

    closeDrawer() {
      this.drawerVisible = false
      this.$nextTick(() => {
        this.resetForm()
        this.$refs.formRef?.clearValidate()
      })
    },

    resetForm() {
      this.form = { id: null, name: '', start_date: null, end_date: null, description: '' }
    },

    async onSubmit() {
      const valid = await this.$refs.formRef.validate().catch(() => false)
      if (!valid) return

      this.submitLoading = true
      try {
        if (this.form.id) {
          await sessionApi.update(this.form.id, this.form)
          ElMessage.success('Session updated successfully')
        } else {
          await sessionApi.store(this.form)
          ElMessage.success('Session created successfully')
        }
        this.closeDrawer()
        this.getList()
        this.loadActiveSession()
      } catch (err) {
        const msg = err?.response?.data?.data?.error
          || err?.response?.data?.errors?.name?.[0]
          || 'Failed to save session'
        ElMessage.error(msg)
      } finally {
        this.submitLoading = false
      }
    },

    async handleSetActive(row) {
      try {
        await ElMessageBox.confirm(
          `Set "${row.name}" as the active session? The current active session will be deactivated.`,
          'Set Active Session',
          { confirmButtonText: 'Yes, set active', cancelButtonText: 'Cancel', type: 'warning' }
        )
      } catch (_) { return }

      try {
        await request({ url: `/academic-sessions/${row.id}/set-active`, method: 'post' })
        ElMessage.success(`"${row.name}" is now the active session`)
        this.getList()
        this.loadActiveSession()
      } catch (_) {
        ElMessage.error('Failed to set active session')
      }
    },

    async handleDelete(row) {
      try {
        await ElMessageBox.confirm(
          `Delete session "${row.name}"? This cannot be undone.`,
          'Delete Session',
          { confirmButtonText: 'Delete', cancelButtonText: 'Cancel', type: 'warning' }
        )
      } catch (_) { return }

      try {
        await sessionApi.destroy(row.id)
        ElMessage.success('Session deleted')
        this.getList()
        this.loadActiveSession()
      } catch (err) {
        const msg = err?.response?.data?.data?.error || 'Failed to delete session'
        ElMessage.error(msg)
      }
    },

    formatDate(val) {
      if (!val) return '—'
      return new Date(val).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
    },
  },
}
</script>

<style scoped>
.filter-container {
  display: flex;
  align-items: center;
  margin-bottom: 16px;
}
</style>
