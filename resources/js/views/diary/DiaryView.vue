<template>
  <div class="app-container">
    <!-- Filter Header -->
    <div class="filter-container">
      <el-row :gutter="16" align="middle">
        <el-col :xs="24" :sm="6">
          <el-select
            v-model="query.class_id"
            placeholder="Select Class"
            clearable
            filterable
            style="width: 100%"
            @change="onClassChange"
          >
            <el-option
              v-for="cls in classes"
              :key="cls.id"
              :label="cls.name"
              :value="cls.id"
            />
          </el-select>
        </el-col>

        <el-col :xs="24" :sm="6">
          <el-select
            v-model="query.section_id"
            placeholder="Select Section"
            clearable
            filterable
            style="width: 100%"
            :disabled="!query.class_id"
            @change="loadDiary"
          >
            <el-option
              v-for="sec in sections"
              :key="sec.id"
              :label="sec.name"
              :value="sec.id"
            />
          </el-select>
        </el-col>

        <el-col :xs="24" :sm="6">
          <el-date-picker
            v-model="query.diary_date"
            type="date"
            format="DD MMM, YYYY"
            value-format="YYYY-MM-DD"
            placeholder="Select Date"
            style="width: 100%"
            @change="loadDiary"
          />
        </el-col>
      </el-row>
    </div>

    <!-- Empty state -->
    <el-empty
      v-if="!query.class_id || !query.section_id || !query.diary_date"
      description="Select Class, Section and Date to view diary"
      :image-size="120"
      style="margin-top: 40px"
    />

    <!-- Loading -->
    <div v-else-if="loading" style="margin-top: 20px">
      <el-skeleton :rows="5" animated />
    </div>

    <!-- No diary -->
    <el-empty
      v-else-if="!loading && diaries.length === 0"
      description="No diary entries found for the selected date"
      :image-size="100"
      style="margin-top: 40px"
    />

    <!-- Diary Cards -->
    <div v-else style="margin-top: 16px">
      <el-alert
        :title="`Diary for ${formattedDate}`"
        type="info"
        :closable="false"
        style="margin-bottom: 16px"
        show-icon
      />

      <el-card
        v-for="item in diaries"
        :key="item.subject_id"
        shadow="hover"
        class="diary-card"
      >
        <template #header>
          <div class="card-header">
            <el-tag type="primary" size="large">{{ item.subject_title }}</el-tag>
          </div>
        </template>
        <div class="diary-text" v-html="formatText(item.diary_text)" />
      </el-card>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import { getStudentDiary } from '@/api/diary'
import request from '@/utils/request'

const classes  = ref([])
const sections = ref([])
const diaries  = ref([])
const loading  = ref(false)

const query = ref({
  class_id:   null,
  section_id: null,
  diary_date: null,
})

const formattedDate = computed(() => {
  if (!query.value.diary_date) return ''
  const d = new Date(query.value.diary_date)
  return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
})

onMounted(() => {
  loadClasses()
})

async function loadClasses() {
  try {
    const res = await request({ url: '/classes', method: 'get', params: { limit: 500 } })
    classes.value = res.data?.classes?.data || res.data?.classes || []
  } catch (e) {
    console.error(e)
  }
}

async function onClassChange() {
  query.value.section_id = null
  diaries.value = []
  sections.value = []

  if (!query.value.class_id) return

  try {
    const res = await request({
      url: '/sections',
      method: 'get',
      params: { class_id: query.value.class_id, limit: 100 },
    })
    sections.value = res.data?.sections?.data || res.data?.sections || []
  } catch (e) {
    console.error(e)
  }
}

async function loadDiary() {
  if (!query.value.class_id || !query.value.section_id || !query.value.diary_date) return

  loading.value = true
  diaries.value = []

  try {
    const res = await getStudentDiary({
      class_id:   query.value.class_id,
      section_id: query.value.section_id,
      diary_date: query.value.diary_date,
    })
    diaries.value = res.data?.diaries || []
  } catch (e) {
    console.error(e)
    ElMessage.error('Failed to load diary')
  } finally {
    loading.value = false
  }
}

function formatText(text) {
  if (!text) return ''
  return text.replace(/\n/g, '<br>')
}
</script>

<style scoped>
.filter-container {
  padding: 16px 0;
}
.diary-card {
  margin-bottom: 16px;
}
.card-header {
  display: flex;
  align-items: center;
}
.diary-text {
  font-size: 14px;
  line-height: 1.7;
  color: #303133;
  white-space: pre-wrap;
}
</style>
