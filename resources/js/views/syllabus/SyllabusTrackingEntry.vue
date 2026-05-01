<template>
  <div class="app-container">

    <!-- Filter header -->
    <div class="compact-filter-header">
      <div class="filter-section">
        <el-select v-model="filter.class_id" placeholder="Select Class" clearable @change="onClassChange" class="filter-select">
          <el-option v-for="cls in classes" :key="cls.id" :label="cls.name" :value="cls.id" />
        </el-select>
        <el-select v-model="filter.subject_id" placeholder="Select Subject" clearable :disabled="!filter.class_id" @change="loadChapters" class="filter-select">
          <el-option v-for="sub in subjects" :key="sub.id" :label="sub.title" :value="sub.id" />
        </el-select>
      </div>
      <div class="action-section">
        <div v-if="overallPct !== null" class="overall-bar">
          <span class="overall-label">Overall</span>
          <el-progress
            :percentage="overallPct"
            :color="pctColor(overallPct)"
            :stroke-width="12"
            style="width:160px"
          />
          <span class="overall-count">{{ doneTopics }}/{{ allTopics }}</span>
        </div>
      </div>
    </div>

    <!-- Empty / loading -->
    <el-empty v-if="!filter.subject_id" description="Select a class and subject to track progress" :image-size="160" style="margin-top:60px" />

    <div v-else-if="loading" class="loading-center">
      <el-icon :size="36" class="is-loading"><Loading /></el-icon>
      <p style="color:#909399;margin-top:12px">Loading...</p>
    </div>

    <el-empty v-else-if="chapters.length === 0" description="No syllabus set up for this class/subject yet. Add chapters from the Syllabus Setup page." style="margin-top:40px" />

    <!-- Chapters -->
    <el-collapse v-else v-model="openPanels" class="chapter-collapse">
      <el-collapse-item v-for="(chapter, idx) in chapters" :key="chapter.id" :name="chapter.id">
        <template #title>
          <div class="ch-header">
            <el-tag type="info" effect="dark" size="small" class="ch-num">{{ idx + 1 }}</el-tag>
            <span class="ch-title">{{ chapter.title }}</span>
            <el-progress
              :percentage="chPct(chapter)"
              :color="pctColor(chPct(chapter))"
              :stroke-width="8"
              style="width:110px"
            />
            <span class="ch-count">{{ chDone(chapter) }}/{{ chapter.topics.length }}</span>
            <el-tag size="small" :type="statusType(chapter)" effect="light">
              {{ statusLabel(chapter) }}
            </el-tag>
            <span v-if="chapter.planned_end_date" class="ch-date">
              Due: {{ fmt(chapter.planned_end_date) }}
            </span>
          </div>
        </template>

        <!-- Topics -->
        <div class="topics-wrap">
          <div v-if="chapter.topics.length === 0" style="color:#909399;padding:8px 0">
            No topics defined. Add them in Syllabus Setup.
          </div>

          <div
            v-for="(topic, tIdx) in chapter.topics"
            :key="topic.id"
            class="topic-row"
            :class="{ 'is-done': topic.completed }"
            @click="toggle(topic)"
          >
            <el-checkbox
              :model-value="topic.completed"
              :loading="topic.loading"
              class="topic-cb"
              @click.stop
              @change="toggle(topic)"
            />
            <span class="t-num">{{ idx + 1 }}.{{ tIdx + 1 }}</span>
            <span class="t-title" :class="{ done: topic.completed }">{{ topic.title }}</span>
            <span v-if="topic.completed && topic.completed_date" class="done-date">
              ✓ Completed {{ fmt(topic.completed_date) }}
            </span>
          </div>
        </div>
      </el-collapse-item>
    </el-collapse>

  </div>
</template>

<script>
import syllabusApi from '@/api/syllabus';
import dayjs from 'dayjs';
import { Loading } from '@element-plus/icons-vue';

export default {
  name: 'SyllabusProgress',
  components: { Loading },
  data() {
    return {
      filter: { class_id: null, subject_id: null },
      classes: [],
      subjects: [],
      chapters: [],
      openPanels: [],
      loading: false,
    };
  },
  computed: {
    allTopics() { return this.chapters.reduce((s, c) => s + c.topics.length, 0); },
    doneTopics() { return this.chapters.reduce((s, c) => s + c.topics.filter(t => t.completed).length, 0); },
    overallPct() {
      if (!this.filter.subject_id || !this.allTopics) return null;
      return Math.round((this.doneTopics / this.allTopics) * 100);
    },
  },
  created() { this.loadClasses(); },
  methods: {
    fmt(d) { return d ? dayjs(d).format('DD MMM YYYY') : ''; },
    pctColor(p) { return p >= 100 ? '#67C23A' : p >= 60 ? '#E6A23C' : '#F56C6C'; },
    chPct(ch) {
      if (!ch.topics.length) return 0;
      return Math.round((ch.topics.filter(t => t.completed).length / ch.topics.length) * 100);
    },
    chDone(ch) { return ch.topics.filter(t => t.completed).length; },
    statusType(ch) {
      const p = this.chPct(ch);
      if (p === 100) return 'success';
      if (ch.planned_end_date && dayjs().isAfter(dayjs(ch.planned_end_date))) return 'danger';
      if (p > 0) return 'warning';
      if (ch.planned_start_date && dayjs().isBefore(dayjs(ch.planned_start_date))) return '';
      return 'info';
    },
    statusLabel(ch) {
      const p = this.chPct(ch);
      if (p === 100) return '✅ Completed';
      if (ch.planned_end_date && dayjs().isAfter(dayjs(ch.planned_end_date))) return '🔴 Behind Schedule';
      if (p > 0) return '🔶 In Progress';
      if (ch.planned_start_date && dayjs().isBefore(dayjs(ch.planned_start_date))) return '🕐 Upcoming';
      return 'Not Started';
    },

    async loadClasses() {
      const { data } = await syllabusApi.getClasses();
      this.classes = data.classes.data;
    },
    async onClassChange() {
      this.filter.subject_id = null;
      this.subjects = [];
      this.chapters = [];
      if (!this.filter.class_id) return;
      const { data } = await syllabusApi.getSubjects(this.filter.class_id);
      this.subjects = data.subjects;
    },
    async loadChapters() {
      if (!this.filter.subject_id) { this.chapters = []; return; }
      this.loading = true;
      try {
        const { data } = await syllabusApi.getChapters(this.filter);
        this.chapters = data.chapters.map(c => ({
          ...c,
          topics: c.topics.map(t => ({ ...t, loading: false })),
        }));
        this.openPanels = this.chapters.map(c => c.id);
      } finally {
        this.loading = false;
      }
    },
    async toggle(topic) {
      if (topic.loading) return;
      topic.loading = true;
      try {
        const { data } = await syllabusApi.toggleTopic(topic.id);
        topic.completed      = data.topic.completed;
        topic.completed_date = data.topic.completed_date;
      } finally {
        topic.loading = false;
      }
    },
  },
};
</script>

<style scoped>
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
.filter-section { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; flex: 1; }
.filter-select  { width: 160px; }
.action-section { display: flex; align-items: center; }
.overall-bar    { display: flex; align-items: center; gap: 10px; }
.overall-label  { font-size: 13px; color: #606266; white-space: nowrap; }
.overall-count  { font-size: 13px; color: #606266; white-space: nowrap; }
.chapter-collapse { border-radius:8px; }
.ch-header { display:flex; align-items:center; gap:10px; width:100%; padding-right:12px; flex-wrap:wrap; }
.ch-num { min-width:28px; text-align:center; }
.ch-title { font-weight:600; font-size:15px; flex:1; min-width:120px; }
.ch-count { font-size:13px; color:#606266; white-space:nowrap; }
.ch-date { font-size:12px; color:#909399; white-space:nowrap; }
.topics-wrap { padding:8px 52px 20px; }
.topic-row {
  display:flex; align-items:center; gap:10px;
  padding:10px 12px; border-radius:6px; cursor:pointer;
  border-bottom:1px solid #f0f2f5;
  transition:background .15s;
}
.topic-row:hover { background:#f5f7fa; }
.topic-row.is-done { background:#f6ffed; }
.topic-row:last-child { border-bottom:none; }
.t-num { color:#909399; font-size:13px; min-width:36px; }
.t-title { flex:1; font-size:14px; user-select:none; }
.t-title.done { color:#b7b7b7; text-decoration:line-through; }
.done-date { font-size:12px; color:#67C23A; white-space:nowrap; }
.loading-center { text-align:center; padding:60px 0; }
</style>
