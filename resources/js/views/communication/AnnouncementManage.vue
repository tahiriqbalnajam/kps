<template>
  <div class="app-container">

    <!-- ───────────── Top Bar ───────────── -->
    <div class="compact-filter-header">
      <div class="filter-section">
        <el-input
          v-model="filter.search"
          placeholder="Search announcements…"
          clearable
          class="search-input"
          @input="handleFilter"
        >
          <template #prefix><el-icon><Search /></el-icon></template>
        </el-input>

        <el-select
          v-model="filter.type"
          placeholder="All Types"
          clearable
          class="filter-select"
          @change="handleFilter"
        >
          <el-option
            v-for="t in typeOptions"
            :key="t.value"
            :label="t.label"
            :value="t.value"
          />
        </el-select>

        <el-select
          v-model="filter.target_audience"
          placeholder="All Audiences"
          clearable
          class="filter-select"
          @change="handleFilter"
        >
          <el-option
            v-for="a in audienceOptions"
            :key="a.value"
            :label="a.label"
            :value="a.value"
          />
        </el-select>

        <el-date-picker
          v-model="filterDateRange"
          type="daterange"
          range-separator="–"
          start-placeholder="From"
          end-placeholder="To"
          value-format="YYYY-MM-DD"
          clearable
          class="date-range-picker"
          @change="handleDateRangeFilter"
        />
      </div>

      <div class="action-section">
        <el-button type="primary" @click="openAddDrawer">
          <el-icon style="margin-right:4px"><Plus /></el-icon>
          Add Announcement
        </el-button>
      </div>
    </div>

    <!-- ───────────── Table ───────────── -->
    <el-table
      :data="list"
      v-loading="listLoading"
      style="width:100%"
      stripe
      border
      size="small"
      empty-text="No announcements found"
    >
      <el-table-column label="#" type="index" width="50" align="center" />

      <el-table-column label="Title" min-width="200">
        <template #default="{ row }">
          <span class="title-text">{{ row.title }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Type" width="120" align="center">
        <template #default="{ row }">
          <el-tag :type="typeTagMap[row.type]?.color" size="small" round>
            {{ typeTagMap[row.type]?.label || row.type }}
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column label="Audience" width="110" align="center">
        <template #default="{ row }">
          <el-tag type="info" size="small" effect="plain">
            {{ audienceLabelMap[row.target_audience] || row.target_audience }}
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column label="Publish Date" width="130" align="center">
        <template #default="{ row }">
          <span class="date-badge">{{ formatDate(row.publish_date) }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Expiry" width="120" align="center">
        <template #default="{ row }">
          <span v-if="row.expiry_date" class="meta-text">{{ formatDate(row.expiry_date) }}</span>
          <span v-else class="meta-text">—</span>
        </template>
      </el-table-column>

      <el-table-column label="Status" width="90" align="center">
        <template #default="{ row }">
          <el-tag :type="row.is_active ? 'success' : 'danger'" size="small" effect="light">
            {{ row.is_active ? 'Active' : 'Inactive' }}
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column label="Actions" width="145" align="center" fixed="right">
        <template #default="{ row }">
          <div class="action-btns">
            <el-tooltip content="View" placement="top">
              <el-button type="success" size="small" circle @click="openViewDrawer(row)">
                <el-icon><View /></el-icon>
              </el-button>
            </el-tooltip>
            <el-tooltip content="Edit" placement="top">
              <el-button type="primary" size="small" circle @click="openEditDrawer(row)">
                <el-icon><Edit /></el-icon>
              </el-button>
            </el-tooltip>
            <el-tooltip :content="row.is_active ? 'Deactivate' : 'Activate'" placement="top">
              <el-button
                :type="row.is_active ? 'warning' : 'info'"
                size="small"
                circle
                @click="handleToggle(row)"
              >
                <el-icon><Switch /></el-icon>
              </el-button>
            </el-tooltip>
            <el-tooltip content="Delete" placement="top">
              <el-button type="danger" size="small" circle @click="confirmDelete(row)">
                <el-icon><Delete /></el-icon>
              </el-button>
            </el-tooltip>
          </div>
        </template>
      </el-table-column>
    </el-table>

    <!-- Pagination -->
    <el-pagination
      v-show="total > 0"
      v-model:current-page="listQuery.page"
      v-model:page-size="listQuery.limit"
      :page-sizes="[15, 30, 50, 100]"
      background
      layout="total, sizes, prev, pager, next"
      :total="total"
      style="margin-top:14px; justify-content:flex-end"
      @size-change="loadList"
      @current-change="loadList"
    />

    <!-- ───────────── Add / Edit Drawer ───────────── -->
    <el-drawer
      v-model="drawerVisible"
      direction="rtl"
      size="520px"
      :destroy-on-close="true"
      :close-on-click-modal="true"
    >
      <template #header>
        <div class="drawer-title">
          <el-icon class="drawer-icon"><Bell /></el-icon>
          {{ isEdit ? 'Edit Announcement' : 'New Announcement' }}
        </div>
      </template>

      <div class="drawer-body">
        <el-form
          ref="formRef"
          :model="form"
          :rules="rules"
          label-position="top"
          size="default"
        >
          <el-form-item label="Title" prop="title">
            <el-input v-model="form.title" placeholder="Announcement title" maxlength="255" show-word-limit />
          </el-form-item>

          <el-row :gutter="12">
            <el-col :span="12">
              <el-form-item label="Type" prop="type">
                <el-select v-model="form.type" placeholder="Select type" style="width:100%">
                  <el-option
                    v-for="t in typeOptions"
                    :key="t.value"
                    :label="t.label"
                    :value="t.value"
                  />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="Target Audience" prop="target_audience">
                <el-select v-model="form.target_audience" placeholder="Select audience" style="width:100%">
                  <el-option
                    v-for="a in audienceOptions"
                    :key="a.value"
                    :label="a.label"
                    :value="a.value"
                  />
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>

          <el-row :gutter="12">
            <el-col :span="12">
              <el-form-item label="Publish Date" prop="publish_date">
                <el-date-picker
                  v-model="form.publish_date"
                  type="date"
                  format="DD MMM, YYYY"
                  value-format="YYYY-MM-DD"
                  placeholder="Publish date"
                  style="width:100%"
                />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="Expiry Date">
                <el-date-picker
                  v-model="form.expiry_date"
                  type="date"
                  format="DD MMM, YYYY"
                  value-format="YYYY-MM-DD"
                  placeholder="Optional"
                  clearable
                  style="width:100%"
                />
              </el-form-item>
            </el-col>
          </el-row>

          <el-form-item label="Content" prop="content">
            <el-input
              v-model="form.content"
              type="textarea"
              :rows="6"
              placeholder="Write the announcement details here…"
              resize="vertical"
            />
          </el-form-item>

          <el-form-item label="Status">
            <el-switch
              v-model="form.is_active"
              active-text="Active"
              inactive-text="Inactive"
            />
          </el-form-item>
        </el-form>
      </div>

      <template #footer>
        <div class="drawer-footer">
          <el-button @click="drawerVisible = false">Cancel</el-button>
          <el-button type="primary" :loading="saving" @click="submitForm">
            {{ saving ? 'Saving…' : isEdit ? 'Update' : 'Save' }}
          </el-button>
        </div>
      </template>
    </el-drawer>

    <!-- ───────────── View Drawer ───────────── -->
    <el-drawer
      v-model="viewDrawerVisible"
      direction="rtl"
      size="520px"
      :destroy-on-close="true"
      :close-on-click-modal="true"
    >
      <template #header>
        <div class="view-drawer-header" v-if="viewRow">
          <el-tag :type="typeTagMap[viewRow.type]?.color" size="default" round>
            {{ typeTagMap[viewRow.type]?.label }}
          </el-tag>
          <el-tag type="info" effect="plain" size="default">
            {{ audienceLabelMap[viewRow.target_audience] }}
          </el-tag>
          <el-tag :type="viewRow.is_active ? 'success' : 'danger'" effect="light" size="default">
            {{ viewRow.is_active ? 'Active' : 'Inactive' }}
          </el-tag>
        </div>
      </template>

      <div class="drawer-body" v-if="viewRow">
        <h2 class="view-title">{{ viewRow.title }}</h2>
        <div class="view-dates">
          <span><strong>Published:</strong> {{ formatDate(viewRow.publish_date) }}</span>
          <span v-if="viewRow.expiry_date"><strong>Expires:</strong> {{ formatDate(viewRow.expiry_date) }}</span>
        </div>
        <el-divider />
        <div class="view-content">{{ viewRow.content }}</div>
        <div class="view-meta-footer">
          <span class="meta-text">Added: {{ formatDateTime(viewRow.created_at) }}</span>
        </div>
      </div>

      <template #footer>
        <div class="drawer-footer">
          <el-button @click="viewDrawerVisible = false">Close</el-button>
          <el-button type="primary" @click="openEditFromView">
            <el-icon style="margin-right:4px"><Edit /></el-icon>
            Edit
          </el-button>
        </div>
      </template>
    </el-drawer>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Edit, Delete, View, Search, Switch, Bell } from '@element-plus/icons-vue'
import {
  getAnnouncementList,
  createAnnouncement,
  updateAnnouncement,
  deleteAnnouncement,
  toggleAnnouncementStatus,
} from '@/api/announcement'
import moment from 'moment'

// ─── Config ───────────────────────────────────────────────────────────────────
const typeOptions = [
  { value: 'event',    label: 'Event' },
  { value: 'activity', label: 'Activity' },
  { value: 'notice',   label: 'Notice' },
  { value: 'news',     label: 'News' },
  { value: 'holiday',  label: 'Holiday' },
  { value: 'alert',    label: 'Alert' },
  { value: 'circular', label: 'Circular' },
]

const audienceOptions = [
  { value: 'all',      label: 'Everyone' },
  { value: 'students', label: 'Students' },
  { value: 'parents',  label: 'Parents' },
  { value: 'teachers', label: 'Teachers' },
]

const typeTagMap = {
  event:    { label: 'Event',    color: 'primary' },
  activity: { label: 'Activity', color: 'success' },
  notice:   { label: 'Notice',   color: 'warning' },
  news:     { label: 'News',     color: '' },
  holiday:  { label: 'Holiday',  color: 'danger' },
  alert:    { label: 'Alert',    color: 'danger' },
  circular: { label: 'Circular', color: 'info' },
}

const audienceLabelMap = {
  all:      'Everyone',
  students: 'Students',
  parents:  'Parents',
  teachers: 'Teachers',
}

// ─── State ────────────────────────────────────────────────────────────────────
const list        = ref([])
const total       = ref(0)
const listLoading = ref(false)
const filterDateRange = ref(null)

const listQuery = ref({ page: 1, limit: 15 })
const filter    = ref({ search: '', type: '', target_audience: '', date_from: null, date_to: null })

// Add/Edit drawer
const drawerVisible = ref(false)
const saving        = ref(false)
const isEdit        = ref(false)
const formRef       = ref(null)
const form          = ref(defaultForm())

// View drawer
const viewDrawerVisible = ref(false)
const viewRow           = ref(null)

const rules = {
  title:           [{ required: true, message: 'Title is required', trigger: 'blur' }],
  type:            [{ required: true, message: 'Select a type', trigger: 'change' }],
  target_audience: [{ required: true, message: 'Select audience', trigger: 'change' }],
  publish_date:    [{ required: true, message: 'Publish date is required', trigger: 'change' }],
  content:         [{ required: true, message: 'Content is required', trigger: 'blur' }],
}

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(() => loadList())

// ─── Loaders ──────────────────────────────────────────────────────────────────
async function loadList() {
  listLoading.value = true
  try {
    const res  = await getAnnouncementList({ page: listQuery.value.page, limit: listQuery.value.limit, ...filterNonEmpty(filter.value) })
    const data = res.data?.announcements
    list.value  = data?.data || []
    total.value = data?.total || 0
  } catch (e) {
    ElMessage.error('Failed to load announcements')
  } finally {
    listLoading.value = false
  }
}

// ─── Filters ──────────────────────────────────────────────────────────────────
function handleFilter() {
  listQuery.value.page = 1
  loadList()
}

function handleDateRangeFilter(range) {
  filter.value.date_from = range ? range[0] : null
  filter.value.date_to   = range ? range[1] : null
  handleFilter()
}

// ─── Add drawer ───────────────────────────────────────────────────────────────
function openAddDrawer() {
  isEdit.value        = false
  form.value          = defaultForm()
  drawerVisible.value = true
}

// ─── Edit drawer ──────────────────────────────────────────────────────────────
function openEditDrawer(row) {
  isEdit.value = true
  form.value   = {
    id:              row.id,
    title:           row.title,
    type:            row.type,
    content:         row.content,
    target_audience: row.target_audience,
    publish_date:    row.publish_date ? moment(row.publish_date).format('YYYY-MM-DD') : null,
    expiry_date:     row.expiry_date  ? moment(row.expiry_date).format('YYYY-MM-DD')  : null,
    is_active:       row.is_active,
  }
  drawerVisible.value = true
}

// ─── View drawer ──────────────────────────────────────────────────────────────
function openViewDrawer(row) {
  viewRow.value           = row
  viewDrawerVisible.value = true
}

function openEditFromView() {
  viewDrawerVisible.value = false
  if (viewRow.value) openEditDrawer(viewRow.value)
}

// ─── Submit ───────────────────────────────────────────────────────────────────
async function submitForm() {
  await formRef.value.validate(async (valid) => {
    if (!valid) return
    saving.value = true
    try {
      if (isEdit.value) {
        const res = await updateAnnouncement(form.value.id, form.value)
        const updated = res.data?.announcement
        if (updated) {
          const idx = list.value.findIndex(r => r.id === updated.id)
          if (idx !== -1) list.value[idx] = updated
        }
        ElMessage.success('Announcement updated')
      } else {
        const res = await createAnnouncement(form.value)
        const created = res.data?.announcement
        if (created) { list.value.unshift(created); total.value += 1 }
        ElMessage.success('Announcement created')
      }
      drawerVisible.value = false
    } catch (e) {
      ElMessage.error('Failed to save announcement')
    } finally {
      saving.value = false
    }
  })
}

// ─── Toggle ───────────────────────────────────────────────────────────────────
async function handleToggle(row) {
  try {
    const res     = await toggleAnnouncementStatus(row.id)
    const updated = res.data?.announcement
    if (updated) {
      const idx = list.value.findIndex(r => r.id === updated.id)
      if (idx !== -1) list.value[idx] = updated
    }
    ElMessage.success(`Announcement ${updated?.is_active ? 'activated' : 'deactivated'}`)
  } catch (e) {
    ElMessage.error('Failed to update status')
  }
}

// ─── Delete ───────────────────────────────────────────────────────────────────
async function confirmDelete(row) {
  try {
    await ElMessageBox.confirm(
      `Delete announcement "${row.title}"? This cannot be undone.`,
      'Confirm Delete',
      { confirmButtonText: 'Delete', cancelButtonText: 'Cancel', type: 'warning' }
    )
    await deleteAnnouncement(row.id)
    list.value  = list.value.filter(r => r.id !== row.id)
    total.value = Math.max(0, total.value - 1)
    ElMessage.success('Deleted successfully')
  } catch (e) {
    if (e !== 'cancel') ElMessage.error('Failed to delete')
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function defaultForm() {
  return {
    id: null, title: '', type: '', content: '',
    target_audience: 'all', publish_date: moment().format('YYYY-MM-DD'),
    expiry_date: null, is_active: true,
  }
}

function formatDate(d) {
  if (!d) return ''
  return moment(d).format('DD MMM, YYYY')
}

function formatDateTime(d) {
  if (!d) return ''
  return moment(d).format('DD MMM, YYYY HH:mm')
}

function filterNonEmpty(obj) {
  return Object.fromEntries(Object.entries(obj).filter(([, v]) => v !== null && v !== '' && v !== undefined))
}
</script>

<style scoped>
.app-container {
  padding: 16px;
  background: #f5f7fa;
  min-height: 100vh;
}

/* ── Top Bar ── */
.compact-filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: #fff;
  padding: 12px 20px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,.08);
  margin-bottom: 16px;
  border: 1px solid #e4e7ed;
  gap: 12px;
  flex-wrap: wrap;
}
.filter-section {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
  flex: 1;
}
.search-input      { width: 200px; }
.filter-select     { width: 140px; }
.date-range-picker { width: 230px; }
.action-section    { display: flex; align-items: center; }

/* ── Table ── */
.title-text  { font-weight: 500; color: #303133; }
.date-badge  { font-weight: 600; color: #409eff; font-size: 13px; }
.meta-text   { font-size: 12px; color: #909399; }
.action-btns { display: flex; gap: 4px; justify-content: center; }

/* ── Drawer shared ── */
.drawer-body {
  padding: 4px 4px 16px;
}
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 12px 20px;
  border-top: 1px solid #e4e7ed;
  background: #fff;
}
.drawer-title {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 16px;
  font-weight: 600;
  color: #303133;
}
.drawer-icon { color: #409eff; font-size: 18px; }

/* ── View drawer ── */
.view-drawer-header {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  align-items: center;
}
.view-title {
  font-size: 18px;
  font-weight: 700;
  color: #303133;
  margin: 0 0 10px;
  line-height: 1.4;
}
.view-dates {
  display: flex;
  gap: 20px;
  font-size: 13px;
  color: #606266;
}
.view-content {
  font-size: 14px;
  line-height: 1.8;
  color: #303133;
  white-space: pre-wrap;
}
.view-meta-footer {
  margin-top: 20px;
  padding-top: 10px;
  border-top: 1px solid #ebeef5;
}

/* ── Responsive ── */
@media (max-width: 768px) {
  .compact-filter-header { flex-direction: column; align-items: stretch; }
  .filter-section        { flex-direction: column; }
  .search-input, .filter-select, .date-range-picker { width: 100%; }
}
</style>
