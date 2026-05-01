<template>
  <div class="app-container">

    <!-- Filter header -->
    <div class="compact-filter-header">
      <div class="filter-section">
        <el-select v-model="filter.class_id" placeholder="All Classes" clearable @change="onClassChange" class="filter-select">
          <el-option v-for="cls in classes" :key="cls.id" :label="cls.name" :value="cls.id" />
        </el-select>
        <el-select v-model="filter.subject_id" placeholder="All Subjects" clearable :disabled="!filter.class_id" class="filter-select">
          <el-option v-for="sub in subjects" :key="sub.id" :label="sub.title" :value="sub.id" />
        </el-select>
      </div>
      <div class="action-section">
        <el-button type="primary" :loading="loading" @click="loadReport">
          <el-icon><DataAnalysis /></el-icon>&nbsp;Generate Report
        </el-button>
      </div>
    </div>

    <!-- Summary strip -->
    <el-card v-if="loaded && report.length > 0" class="summary-card" shadow="never">
      <el-row :gutter="0">
        <el-col :span="6" class="stat-col">
          <div class="stat-num">{{ report.length }}</div>
          <div class="stat-lbl">Subject–Class Entries</div>
        </el-col>
        <el-col :span="6" class="stat-col">
          <div class="stat-num">{{ allTopics }}</div>
          <div class="stat-lbl">Total Topics</div>
        </el-col>
        <el-col :span="6" class="stat-col">
          <div class="stat-num" style="color:#67C23A">{{ doneTopics }}</div>
          <div class="stat-lbl">Topics Completed</div>
        </el-col>
        <el-col :span="6" class="stat-col">
          <div class="stat-num">{{ overallPct }}%</div>
          <div class="stat-lbl">Overall Completion</div>
          <el-progress :percentage="overallPct" :color="pctColor(overallPct)" :show-text="false" :stroke-width="8" style="margin-top:6px" />
        </el-col>
      </el-row>
    </el-card>

    <!-- States -->
    <div v-if="loading" class="loading-center">
      <el-icon :size="36" class="is-loading"><Loading /></el-icon>
      <p style="color:#909399;margin-top:12px">Generating report...</p>
    </div>
    <el-empty v-else-if="!loaded" description="Set filters and click 'Generate Report'" :image-size="160" style="margin-top:40px" />
    <el-empty v-else-if="report.length === 0" description="No syllabus data found for the selected filters." style="margin-top:40px" />

    <!-- Report cards grid -->
    <div v-else class="report-grid">
      <el-card v-for="item in report" :key="`${item.class_id}_${item.subject_id}`" class="report-card" shadow="hover">
        <template #header>
          <div class="card-hdr">
            <div>
              <div class="card-subject">{{ item.subject_title }}</div>
              <div class="card-class">{{ item.class_name }}</div>
            </div>
            <el-tag :type="tagType(item.completion_percent)" size="large" effect="dark">
              {{ item.completion_percent }}%
            </el-tag>
          </div>
        </template>

        <el-progress
          :percentage="item.completion_percent"
          :color="pctColor(item.completion_percent)"
          :stroke-width="12"
          style="margin-bottom:12px"
        />

        <div class="stats-row">
          <span>📚 {{ item.completed_chapters }}/{{ item.total_chapters }} chapters done</span>
          <span>✅ {{ item.completed_topics }}/{{ item.total_topics }} topics done</span>
        </div>

        <!-- Chapter breakdown -->
        <el-collapse accordion class="ch-mini">
          <el-collapse-item name="1">
            <template #title>
              <span style="font-size:13px;color:#606266">View chapter breakdown</span>
            </template>
            <div v-for="ch in item.chapters" :key="ch.id" class="mini-row">
              <div class="mini-top">
                <span class="mini-title">{{ ch.title }}</span>
                <el-tag :type="statusType(ch.status)" size="small" effect="light">
                  {{ statusLabel(ch.status) }}
                </el-tag>
              </div>
              <div class="mini-bot">
                <el-progress
                  :percentage="topicPct(ch)"
                  :color="pctColor(topicPct(ch))"
                  :stroke-width="6"
                  style="flex:1"
                />
                <span class="mini-cnt">{{ ch.completed_topics }}/{{ ch.total_topics }}</span>
                <span v-if="ch.planned_end_date" class="mini-due">Due {{ fmt(ch.planned_end_date) }}</span>
              </div>
            </div>
          </el-collapse-item>
        </el-collapse>
      </el-card>
    </div>

  </div>
</template>

<script>
import syllabusApi from '@/api/syllabus';
import dayjs from 'dayjs';
import { Loading, DataAnalysis } from '@element-plus/icons-vue';

export default {
  name: 'SyllabusReport',
  components: { Loading, DataAnalysis },
  data() {
    return {
      filter: { class_id: null, subject_id: null },
      classes: [],
      subjects: [],
      report: [],
      loading: false,
      loaded: false,
    };
  },
  computed: {
    allTopics()  { return this.report.reduce((s, r) => s + r.total_topics, 0); },
    doneTopics() { return this.report.reduce((s, r) => s + r.completed_topics, 0); },
    overallPct() {
      if (!this.allTopics) return 0;
      return Math.round((this.doneTopics / this.allTopics) * 100);
    },
  },
  created() { this.loadClasses(); },
  methods: {
    fmt(d) { return d ? dayjs(d).format('DD MMM YYYY') : ''; },
    pctColor(p) { return p >= 100 ? '#67C23A' : p >= 60 ? '#E6A23C' : '#F56C6C'; },
    tagType(p)  { return p >= 100 ? 'success' : p >= 60 ? 'warning' : 'danger'; },
    topicPct(ch) {
      if (!ch.total_topics) return 0;
      return Math.round((ch.completed_topics / ch.total_topics) * 100);
    },
    statusType(s) {
      return { completed:'success', behind:'danger', in_progress:'warning', upcoming:'', not_started:'info', empty:'info' }[s] ?? 'info';
    },
    statusLabel(s) {
      return { completed:'✅ Completed', behind:'🔴 Behind Schedule', in_progress:'🔶 In Progress', upcoming:'🕐 Upcoming', not_started:'Not Started', empty:'No Topics' }[s] ?? s;
    },

    async loadClasses() {
      const { data } = await syllabusApi.getClasses();
      this.classes = data.classes.data;
    },
    async onClassChange() {
      this.filter.subject_id = null;
      this.subjects = [];
      if (!this.filter.class_id) return;
      const { data } = await syllabusApi.getSubjects(this.filter.class_id);
      this.subjects = data.subjects;
    },
    async loadReport() {
      this.loading = true;
      this.loaded  = false;
      try {
        const params = {};
        if (this.filter.class_id)   params.class_id   = this.filter.class_id;
        if (this.filter.subject_id) params.subject_id = this.filter.subject_id;
        const { data } = await syllabusApi.getReport(params);
        this.report = data.report;
        this.loaded = true;
      } finally {
        this.loading = false;
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
.summary-card { margin-bottom: 24px; border-radius: 8px; }
.stat-col { text-align: center; padding: 12px 0; border-right: 1px solid #f0f2f5; }
.stat-col:last-child { border-right: none; }
.stat-num { font-size: 30px; font-weight: 700; color: #303133; line-height: 1.2; }
.stat-lbl { font-size: 13px; color: #909399; margin-top: 4px; }
.report-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 20px; }
.report-card { border-radius: 8px; }
.card-hdr { display: flex; justify-content: space-between; align-items: flex-start; }
.card-subject { font-size: 16px; font-weight: 600; color: #303133; }
.card-class { font-size: 13px; color: #909399; margin-top: 3px; }
.stats-row { display: flex; justify-content: space-between; font-size: 13px; color: #606266; margin-bottom: 12px; }
.ch-mini { border: none; }
.mini-row { margin-bottom: 12px; }
.mini-row:last-child { margin-bottom: 0; }
.mini-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px; gap: 8px; }
.mini-title { font-size: 13px; color: #303133; flex: 1; }
.mini-bot { display: flex; align-items: center; gap: 8px; }
.mini-cnt { font-size: 12px; color: #606266; white-space: nowrap; }
.mini-due { font-size: 12px; color: #909399; white-space: nowrap; }
.loading-center { text-align: center; padding: 60px 0; }
</style>
