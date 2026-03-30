<template>
  <div class="app-container">

    <!-- ───────────── Compact Filter Header ───────────── -->
    <div class="compact-filter-header">
      <div class="filter-section">
        <el-select
          v-model="filter.class_id"
          placeholder="All Classes"
          clearable
          filterable
          class="filter-select"
          @change="handleFilter"
        >
          <el-option v-for="c in classes" :key="c.id" :label="c.name" :value="c.id" />
        </el-select>

        <el-select
          v-model="filter.section_id"
          placeholder="All Sections"
          clearable
          filterable
          class="filter-select"
          :disabled="!filter.class_id"
          @change="handleFilter"
        >
          <el-option v-for="s in filterSections" :key="s.id" :label="s.name" :value="s.id" />
        </el-select>

        <el-date-picker
          v-model="filter.diary_date"
          type="date"
          format="DD MMM, YYYY"
          value-format="YYYY-MM-DD"
          placeholder="Filter by date"
          clearable
          class="date-picker"
          @change="handleFilter"
        />

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
          Add Diary
        </el-button>
      </div>
    </div>

    <!-- ───────────── Main Table ───────────── -->
    <el-table
      :data="diaryList"
      v-loading="listLoading"
      style="width:100%"
      stripe
      border
      size="small"
      empty-text="No diary entries found"
    >
      <el-table-column label="#" type="index" width="50" align="center" />

      <el-table-column label="Date" width="130" align="center">
        <template #default="{ row }">
          <span class="date-badge">{{ formatDate(row.diary_date) }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Class" prop="class_name" min-width="120" />

      <el-table-column label="Section" width="110" align="center">
        <template #default="{ row }">
          <el-tag v-if="row.section_name" type="info" size="small">{{ row.section_name }}</el-tag>
          <span v-else class="meta-text">—</span>
        </template>
      </el-table-column>

      <el-table-column label="Subjects" width="110" align="center">
        <template #default="{ row }">
          <el-tag type="success" size="small" round>
            {{ row.subject_count }} subject{{ row.subject_count !== 1 ? 's' : '' }}
          </el-tag>
        </template>
      </el-table-column>

      <el-table-column label="Added On" width="150" align="center">
        <template #default="{ row }">
          <span class="meta-text">{{ formatDateTime(row.created_at) }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Actions" width="150" align="center" fixed="right">
        <template #default="{ row }">
          <div class="action-btns">
            <el-tooltip content="View Diary" placement="top">
              <el-button type="success" size="small" circle @click="openViewDrawer(row)">
                <el-icon><View /></el-icon>
              </el-button>
            </el-tooltip>
            <el-tooltip content="Edit Diary" placement="top">
              <el-button type="primary" size="small" circle @click="openEditDrawer(row)">
                <el-icon><Edit /></el-icon>
              </el-button>
            </el-tooltip>
            <el-tooltip content="Delete Diary" placement="top">
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
      size="90%"
      :destroy-on-close="true"
      :close-on-click-modal="true"
      :with-header="true"
    >
      <template #header>
        <div class="drawer-header">
          <span class="drawer-title">{{ isEdit ? 'Edit Diary' : 'Add New Diary' }}</span>
          <div class="drawer-header-controls">
            <!-- Class -->
            <el-select
              v-model="form.class_id"
              placeholder="Select Class"
              filterable
              clearable
              class="drawer-ctrl-item"
              :disabled="isEdit"
              @change="onFormClassChange"
            >
              <el-option v-for="c in classes" :key="c.id" :label="c.name" :value="c.id" />
            </el-select>

            <!-- Section (hidden if class has no sections) -->
            <el-select
              v-if="formSections.length > 0 || form.section_id"
              v-model="form.section_id"
              placeholder="Select Section"
              filterable
              clearable
              class="drawer-ctrl-item"
              :disabled="isEdit || !form.class_id"
              @change="onFormSectionDateChange"
            >
              <el-option v-for="s in formSections" :key="s.id" :label="s.name" :value="s.id" />
            </el-select>

            <!-- Date -->
            <el-date-picker
              v-model="form.diary_date"
              type="date"
              format="DD MMM, YYYY"
              value-format="YYYY-MM-DD"
              placeholder="Select Date"
              class="drawer-ctrl-item drawer-date"
              :disabled="isEdit"
              @change="onFormSectionDateChange"
            />
          </div>
        </div>
      </template>

      <!-- Drawer body: subject grid -->
      <div class="drawer-body">
        <el-empty
          v-if="showFormEmpty"
          :description="formEmptyHint"
          :image-size="90"
        />
        <el-skeleton v-else-if="formLoading" :rows="4" animated />

        <div v-else>
          <el-alert
            v-if="isEdit"
            :title="`Editing: ${form.class_name}${form.section_name ? ' – ' + form.section_name : ''} · ${formatDate(form.diary_date)}`"
            type="info"
            :closable="false"
            show-icon
            style="margin-bottom:14px"
          />

          <!-- 3-column subject grid -->
          <el-row :gutter="12">
            <el-col
              v-for="entry in form.entries"
              :key="entry.subject_id"
              :xs="24" :sm="12" :md="8"
            >
              <div class="subject-card">
                <div class="subject-card-label">
                  <el-icon class="subject-icon"><Reading /></el-icon>
                  {{ entry.subject_title }}
                </div>
                <el-input
                  v-model="entry.diary_text"
                  type="textarea"
                  :rows="2"
                  placeholder="Diary / homework…"
                  resize="none"
                  class="subject-textarea"
                />
              </div>
            </el-col>
          </el-row>
        </div>
      </div>

      <!-- Sticky footer -->
      <template #footer>
        <div class="drawer-footer">
          <el-button @click="drawerVisible = false">Cancel</el-button>
          <el-button
            type="primary"
            :loading="saving"
            :disabled="!canSave"
            @click="submitDiary"
          >
            {{ saving ? 'Saving…' : isEdit ? 'Update Diary' : 'Save Diary' }}
          </el-button>
        </div>
      </template>
    </el-drawer>

    <!-- ───────────── View Drawer ───────────── -->
    <el-drawer
      v-model="viewDrawerVisible"
      direction="rtl"
      size="90%"
      :destroy-on-close="true"
      :close-on-click-modal="true"
    >
      <template #header>
        <div class="view-drawer-header">
          <el-icon class="view-icon"><Reading /></el-icon>
          <span class="drawer-title">Diary</span>
          <div v-if="viewRow" class="view-meta">
            <el-tag type="primary" effect="plain">{{ viewRow.class_name }}</el-tag>
            <el-tag v-if="viewRow.section_name" type="info" effect="plain">{{ viewRow.section_name }}</el-tag>
            <el-tag type="warning" effect="plain">{{ formatDate(viewRow.diary_date) }}</el-tag>
          </div>
        </div>
      </template>

      <div class="drawer-body">
        <el-skeleton v-if="viewLoading" :rows="5" animated />

        <el-empty
          v-else-if="viewEntries.length === 0"
          description="No diary content found for this date"
          :image-size="90"
        />

        <el-row v-else :gutter="14">
          <el-col
            v-for="entry in viewEntries"
            :key="entry.subject_id"
            :xs="24" :sm="12" :md="8"
          >
            <div class="view-subject-card" :class="{ 'is-empty': !entry.diary_text }">
              <div class="view-subject-label">
                <el-icon><Reading /></el-icon>
                {{ entry.subject_title }}
              </div>
              <div v-if="entry.diary_text" class="view-diary-text">{{ entry.diary_text }}</div>
              <div v-else class="view-no-entry">No entry for this subject</div>
            </div>
          </el-col>
        </el-row>
      </div>

      <template #footer>
        <div class="drawer-footer">
          <el-button @click="viewDrawerVisible = false">Close</el-button>
          <el-button type="primary" @click="openEditFromView">
            <el-icon style="margin-right:4px"><Edit /></el-icon>
            Edit This Diary
          </el-button>
        </div>
      </template>
    </el-drawer>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import { Plus, Edit, Delete, View, Reading } from '@element-plus/icons-vue'
import { getDiaryList, getDiaryByDate, saveDiary, deleteDiaryGroup } from '@/api/diary'
import request from '@/utils/request'

// ─── State ────────────────────────────────────────────────────────────────────
const classes         = ref([])
const filterSections  = ref([])
const formSections    = ref([])
const diaryList       = ref([])
const total           = ref(0)
const listLoading     = ref(false)
const filterDateRange = ref(null)

// Add/Edit drawer
const drawerVisible = ref(false)
const formLoading   = ref(false)
const saving        = ref(false)
const isEdit        = ref(false)

// View drawer
const viewDrawerVisible = ref(false)
const viewLoading       = ref(false)
const viewEntries       = ref([])
const viewRow           = ref(null)

const listQuery = ref({ page: 1, limit: 15 })

const filter = ref({
  class_id: null, section_id: null, diary_date: null, date_from: null, date_to: null,
})

const form = ref({
  class_id: null, section_id: null, diary_date: null,
  class_name: '', section_name: '', entries: [],
})

// ─── Computed ─────────────────────────────────────────────────────────────────
// Section is only required if the class actually has sections
const sectionRequired = computed(() => formSections.value.length > 0)

const canSave = computed(() => {
  if (!form.value.class_id || !form.value.diary_date) return false
  if (sectionRequired.value && !form.value.section_id) return false
  return form.value.entries.length > 0
})

const showFormEmpty = computed(() => {
  if (!form.value.class_id || !form.value.diary_date) return true
  if (sectionRequired.value && !form.value.section_id) return true
  return false
})

const formEmptyHint = computed(() => {
  if (!form.value.class_id) return 'Select a Class to continue'
  if (sectionRequired.value && !form.value.section_id) return 'Select a Section to continue'
  if (!form.value.diary_date) return 'Select a Date to load subjects'
  return ''
})

// ─── Lifecycle ────────────────────────────────────────────────────────────────
onMounted(async () => {
  await loadClasses()
  loadList()
})

watch(() => filter.value.class_id, async (val) => {
  filter.value.section_id = null
  filterSections.value = val ? await fetchSections(val) : []
})

// ─── Data loaders ─────────────────────────────────────────────────────────────
async function loadClasses() {
  try {
    const res = await request({ url: '/classes', method: 'get', params: { limit: 500 } })
    classes.value = res.data?.classes?.data || res.data?.classes || []
  } catch (e) { console.error(e) }
}

async function fetchSections(classId) {
  try {
    const res = await request({ url: '/sections', method: 'get', params: { class_id: classId, limit: 100 } })
    return res.data?.sections?.data || res.data?.sections || []
  } catch (e) { return [] }
}

async function loadList() {
  listLoading.value = true
  try {
    const res  = await getDiaryList({ page: listQuery.value.page, limit: listQuery.value.limit, ...filterNonEmpty(filter.value) })
    const data = res.data?.diaries
    diaryList.value = data?.data || []
    total.value     = data?.total || 0
  } catch (e) {
    ElMessage.error('Failed to load diary list')
  } finally {
    listLoading.value = false
  }
}

// ─── Filter ───────────────────────────────────────────────────────────────────
function handleFilter() {
  listQuery.value.page = 1
  loadList()
}

function handleDateRangeFilter(range) {
  filter.value.date_from  = range ? range[0] : null
  filter.value.date_to    = range ? range[1] : null
  filter.value.diary_date = null
  handleFilter()
}

// ─── Add drawer ───────────────────────────────────────────────────────────────
function openAddDrawer() {
  isEdit.value      = false
  formSections.value = []
  form.value = { class_id: null, section_id: null, diary_date: null, class_name: '', section_name: '', entries: [] }
  drawerVisible.value = true
}

// ─── Edit drawer ──────────────────────────────────────────────────────────────
async function openEditDrawer(row) {
  isEdit.value = true
  formSections.value = await fetchSections(row.class_id)
  form.value = {
    class_id:    row.class_id,
    section_id:  row.section_id,
    diary_date:  row.diary_date ? String(row.diary_date).substring(0, 10) : null,
    class_name:  row.class_name,
    section_name: row.section_name,
    entries:     [],
  }
  drawerVisible.value = true
  await loadFormEntries()
}

// ─── View drawer ──────────────────────────────────────────────────────────────
async function openViewDrawer(row) {
  viewRow.value           = row
  viewEntries.value       = []
  viewDrawerVisible.value = true
  viewLoading.value       = true
  try {
    const res = await getDiaryByDate({
      class_id:   row.class_id,
      section_id: row.section_id,
      diary_date: row.diary_date,
    })
    viewEntries.value = res.data?.diaries || []
  } catch (e) {
    ElMessage.error('Failed to load diary')
  } finally {
    viewLoading.value = false
  }
}

function openEditFromView() {
  viewDrawerVisible.value = false
  if (viewRow.value) openEditDrawer(viewRow.value)
}

// ─── Form helpers ─────────────────────────────────────────────────────────────
async function onFormClassChange(val) {
  form.value.section_id = null
  form.value.entries    = []
  formSections.value    = val ? await fetchSections(val) : []
  // If class has no sections and date is already set → load entries right away
  if (val && !sectionRequired.value && form.value.diary_date) await loadFormEntries()
}

async function onFormSectionDateChange() {
  // Trigger load when: class set + date set + (section set OR class has no sections)
  if (!form.value.class_id || !form.value.diary_date) return
  if (sectionRequired.value && !form.value.section_id) return
  await loadFormEntries()
}

async function loadFormEntries() {
  formLoading.value  = true
  form.value.entries = []
  try {
    const res = await getDiaryByDate({
      class_id:   form.value.class_id,
      section_id: form.value.section_id || 0,
      diary_date: form.value.diary_date,
    })
    form.value.entries = res.data?.diaries || []
  } catch (e) {
    ElMessage.error('Failed to load subjects')
  } finally {
    formLoading.value = false
  }
}

// ─── Submit ───────────────────────────────────────────────────────────────────
async function submitDiary() {
  if (!canSave.value) return
  saving.value = true
  try {
    const res = await saveDiary({
      class_id:   form.value.class_id,
      section_id: form.value.section_id || 0,
      diary_date: form.value.diary_date,
      entries:    form.value.entries.map(e => ({ subject_id: e.subject_id, diary_text: e.diary_text || '' })),
    })

    ElMessage.success(isEdit.value ? 'Diary updated successfully' : 'Diary saved successfully')
    drawerVisible.value = false

    const group = res.data?.group
    if (group) {
      if (isEdit.value) {
        const idx = diaryList.value.findIndex(
          r => r.class_id === group.class_id && r.section_id === group.section_id && r.diary_date === group.diary_date
        )
        if (idx !== -1) diaryList.value[idx] = group
      } else {
        diaryList.value.unshift(group)
        total.value += 1
      }
    }
  } catch (e) {
    ElMessage.error('Failed to save diary')
  } finally {
    saving.value = false
  }
}

// ─── Delete ───────────────────────────────────────────────────────────────────
async function confirmDelete(row) {
  try {
    await ElMessageBox.confirm(
      `Delete diary for ${row.class_name}${row.section_name ? ' – ' + row.section_name : ''} on ${formatDate(row.diary_date)}? All subject entries for that day will be removed.`,
      'Confirm Delete',
      { confirmButtonText: 'Delete', cancelButtonText: 'Cancel', type: 'warning' }
    )
    await deleteDiaryGroup({ class_id: row.class_id, section_id: row.section_id, diary_date: row.diary_date })
    ElMessage.success('Diary deleted')
    diaryList.value = diaryList.value.filter(
      r => !(r.class_id === row.class_id && r.section_id === row.section_id && r.diary_date === row.diary_date)
    )
    total.value = Math.max(0, total.value - 1)
  } catch (e) {
    if (e !== 'cancel') ElMessage.error('Failed to delete diary')
  }
}

// ─── Helpers ──────────────────────────────────────────────────────────────────
function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatDateTime(d) {
  if (!d) return ''
  return new Date(d).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function filterNonEmpty(obj) {
  return Object.fromEntries(Object.entries(obj).filter(([, v]) => v !== null && v !== '' && v !== undefined))
}
</script>

<style scoped>
/* ── Container ── */
.app-container {
  padding: 16px;
  background: #f5f7fa;
  min-height: 100vh;
}

/* ── Filter Header ── */
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
.filter-select      { width: 140px; }
.date-picker        { width: 160px; }
.date-range-picker  { width: 230px; }
.action-section     { display: flex; align-items: center; }

/* ── Table ── */
.date-badge  { font-weight: 600; color: #409eff; font-size: 13px; }
.meta-text   { font-size: 12px; color: #909399; }
.action-btns { display: flex; gap: 6px; justify-content: center; }

/* ── Drawer shared ── */
.drawer-body {
  padding: 0 4px;
  /* no overflow — we want full height use without scroll */
}
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding: 12px 20px;
  border-top: 1px solid #e4e7ed;
  background: #fff;
}

/* ── Add/Edit drawer header ── */
.drawer-header {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  width: 100%;
}
.drawer-title {
  font-size: 16px;
  font-weight: 600;
  color: #303133;
  white-space: nowrap;
}
.drawer-header-controls {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  flex: 1;
}
.drawer-ctrl-item { width: 150px; }
.drawer-date      { width: 165px; }

/* ── Subject grid (add/edit) ── */
.subject-card {
  background: #fff;
  border: 1px solid #e4e7ed;
  border-radius: 8px;
  padding: 10px 12px;
  margin-bottom: 10px;
  transition: box-shadow 0.2s;
}
.subject-card:hover { box-shadow: 0 2px 10px rgba(0,0,0,.1); }
.subject-card-label {
  font-size: 13px;
  font-weight: 600;
  color: #303133;
  margin-bottom: 6px;
  display: flex;
  align-items: center;
  gap: 6px;
}
.subject-icon { color: #409eff; }
.subject-textarea :deep(.el-textarea__inner) {
  font-size: 13px;
  line-height: 1.5;
}

/* ── View drawer header ── */
.view-drawer-header {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  width: 100%;
}
.view-icon { color: #67c23a; font-size: 18px; }
.view-meta { display: flex; gap: 6px; flex-wrap: wrap; }

/* ── View subject cards ── */
.view-subject-card {
  background: #fff;
  border: 1px solid #e4e7ed;
  border-radius: 8px;
  padding: 12px 14px;
  margin-bottom: 10px;
  min-height: 90px;
}
.view-subject-card.is-empty {
  background: #fafafa;
  border-style: dashed;
  opacity: 0.7;
}
.view-subject-label {
  font-size: 13px;
  font-weight: 600;
  color: #409eff;
  margin-bottom: 8px;
  display: flex;
  align-items: center;
  gap: 6px;
  padding-bottom: 6px;
  border-bottom: 1px solid #ebeef5;
}
.view-diary-text {
  font-size: 13px;
  line-height: 1.7;
  color: #303133;
  white-space: pre-wrap;
}
.view-no-entry {
  font-size: 12px;
  color: #c0c4cc;
  font-style: italic;
}

/* ── Responsive ── */
@media (max-width: 768px) {
  .compact-filter-header    { flex-direction: column; align-items: stretch; }
  .filter-section           { flex-direction: column; }
  .filter-select, .date-picker, .date-range-picker { width: 100%; }
  .drawer-header            { flex-direction: column; align-items: flex-start; }
  .drawer-header-controls   { flex-direction: column; width: 100%; }
  .drawer-ctrl-item, .drawer-date { width: 100% !important; }
}
</style>
