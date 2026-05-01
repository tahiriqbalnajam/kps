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
        <el-button type="primary" :disabled="!filter.subject_id" @click="openChapterForm()">
          <el-icon><Plus /></el-icon>&nbsp;Add Chapter
        </el-button>
      </div>
    </div>

    <!-- No filter selected -->
    <el-empty v-if="!filter.subject_id" description="Select a class and subject to manage its syllabus" :image-size="160" style="margin-top:60px" />

    <!-- Loading -->
    <div v-else-if="loading" class="loading-center">
      <el-icon :size="36" class="is-loading"><Loading /></el-icon>
      <p style="color:#909399;margin-top:12px">Loading chapters...</p>
    </div>

    <!-- Chapters -->
    <template v-else>
      <el-empty v-if="chapters.length === 0" :image-size="130">
        <template #description>
          <p>No chapters yet.</p>
          <el-button type="primary" @click="openChapterForm()">Add First Chapter</el-button>
        </template>
      </el-empty>

      <el-collapse v-else v-model="openPanels" class="chapter-collapse">
        <el-collapse-item v-for="(chapter, idx) in chapters" :key="chapter.id" :name="chapter.id">
          <template #title>
            <div class="chapter-header">
              <el-tag type="info" effect="dark" size="small" class="ch-num">{{ idx + 1 }}</el-tag>
              <span class="ch-title">{{ chapter.title }}</span>
              <el-tag v-if="chapter.planned_start_date" size="small" type="info" class="date-tag">
                📅 {{ fmt(chapter.planned_start_date) }} → {{ fmt(chapter.planned_end_date) }}
              </el-tag>
              <el-tag size="small" :type="chapter.topics.length ? 'success' : 'warning'">
                {{ chapter.topics.length }} topic{{ chapter.topics.length !== 1 ? 's' : '' }}
              </el-tag>
              <div class="ch-actions" @click.stop>
                <el-button size="small" @click="openChapterForm(chapter)">Edit</el-button>
                <el-button size="small" type="danger" plain @click="deleteChapter(chapter)">Delete</el-button>
              </div>
            </div>
          </template>

          <!-- Topic list -->
          <div class="topics-wrap">
            <el-empty v-if="chapter.topics.length === 0" :image-size="60" description="No topics yet." />

            <div v-for="(topic, tIdx) in chapter.topics" :key="topic.id" class="topic-row">
              <span class="t-num">{{ idx + 1 }}.{{ tIdx + 1 }}</span>
              <span class="t-title">{{ topic.title }}</span>
              <div class="t-actions">
                <el-button size="small" link @click="openTopicForm(chapter, topic)">Edit</el-button>
                <el-button size="small" link type="danger" @click="deleteTopic(chapter, topic)">Delete</el-button>
              </div>
            </div>

            <div style="margin-top:14px">
              <el-button size="small" type="primary" plain @click="openTopicForm(chapter)">
                <el-icon><Plus /></el-icon>&nbsp;Add Topic
              </el-button>
            </div>
          </div>
        </el-collapse-item>
      </el-collapse>
    </template>

    <!-- Chapter drawer -->
    <el-drawer
      v-model="chDr.visible"
      :title="chDr.id ? 'Edit Chapter' : 'Add Chapter'"
      size="440px"
      destroy-on-close
    >
      <el-form :model="chForm" label-position="top" style="padding:0 8px">
        <el-form-item label="Chapter Title" required>
          <el-input v-model="chForm.title" placeholder="e.g. Chapter 1: Introduction" maxlength="255" show-word-limit />
        </el-form-item>
        <el-form-item label="Planned Dates (optional)">
          <el-date-picker
            v-model="chDates"
            type="daterange"
            range-separator="to"
            start-placeholder="Start"
            end-placeholder="End"
            format="DD MMM YYYY"
            value-format="YYYY-MM-DD"
            style="width:100%"
          />
        </el-form-item>
        <el-alert type="info" :closable="false" show-icon style="margin-top:4px">
          Planned dates enable behind-schedule detection in the report.
        </el-alert>
      </el-form>
      <template #footer>
        <el-button @click="chDr.visible = false">Cancel</el-button>
        <el-button type="primary" :loading="saving" @click="saveChapter">
          {{ chDr.id ? 'Update' : 'Add Chapter' }}
        </el-button>
      </template>
    </el-drawer>

    <!-- Topic drawer -->
    <el-drawer
      v-model="tpDr.visible"
      :title="tpDr.id ? 'Edit Topic' : 'Add Topic'"
      size="440px"
      destroy-on-close
    >
      <el-form :model="tpForm" label-position="top" style="padding:0 8px">
        <el-form-item label="Topic Title" required>
          <el-input v-model="tpForm.title" placeholder="e.g. Solving Linear Equations" maxlength="255" show-word-limit />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="tpDr.visible = false">Cancel</el-button>
        <el-button type="primary" :loading="saving" @click="saveTopic">
          {{ tpDr.id ? 'Update' : 'Add Topic' }}
        </el-button>
      </template>
    </el-drawer>

  </div>
</template>

<script>
import syllabusApi from '@/api/syllabus';
import dayjs from 'dayjs';
import { Plus, Loading } from '@element-plus/icons-vue';

export default {
  name: 'SyllabusSetup',
  components: { Plus, Loading },
  data() {
    return {
      filter: { class_id: null, subject_id: null },
      classes: [],
      subjects: [],
      chapters: [],
      openPanels: [],
      loading: false,
      saving: false,
      chDr: { visible: false, id: null },
      chForm: { title: '', planned_start_date: null, planned_end_date: null },
      chDates: [],
      tpDr: { visible: false, id: null, chapterId: null },
      tpForm: { title: '', chapter_id: null },
    };
  },
  created() {
    this.loadClasses();
  },
  methods: {
    fmt(d) { return d ? dayjs(d).format('DD MMM YYYY') : '—'; },

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
        this.chapters = data.chapters;
        this.openPanels = this.chapters.map(c => c.id);
      } finally {
        this.loading = false;
      }
    },

    openChapterForm(chapter = null) {
      this.chDr.id = chapter?.id ?? null;
      this.chForm = {
        title: chapter?.title ?? '',
        planned_start_date: chapter?.planned_start_date ?? null,
        planned_end_date:   chapter?.planned_end_date ?? null,
      };
      this.chDates = chapter?.planned_start_date
        ? [chapter.planned_start_date, chapter.planned_end_date]
        : [];
      this.chDr.visible = true;
    },
    async saveChapter() {
      if (!this.chForm.title.trim()) { this.$message.warning('Chapter title is required.'); return; }
      this.saving = true;
      const payload = {
        title:               this.chForm.title,
        class_id:            this.filter.class_id,
        subject_id:          this.filter.subject_id,
        planned_start_date:  this.chDates?.[0] ?? null,
        planned_end_date:    this.chDates?.[1] ?? null,
      };
      try {
        if (this.chDr.id) {
          const { data } = await syllabusApi.updateChapter(this.chDr.id, payload);
          const i = this.chapters.findIndex(c => c.id === this.chDr.id);
          if (i !== -1) this.chapters[i] = data.chapter;
          this.$message.success('Chapter updated.');
        } else {
          const { data } = await syllabusApi.createChapter(payload);
          this.chapters.push(data.chapter);
          this.openPanels.push(data.chapter.id);
          this.$message.success('Chapter added.');
        }
        this.chDr.visible = false;
      } finally {
        this.saving = false;
      }
    },
    async deleteChapter(chapter) {
      try {
        await this.$confirm(
          `Delete "${chapter.title}" and all its ${chapter.topics.length} topic(s)? This cannot be undone.`,
          'Delete Chapter',
          { type: 'warning', confirmButtonText: 'Delete', confirmButtonClass: 'el-button--danger' }
        );
      } catch { return; }
      await syllabusApi.deleteChapter(chapter.id);
      this.chapters = this.chapters.filter(c => c.id !== chapter.id);
      this.$message.success('Chapter deleted.');
    },

    openTopicForm(chapter, topic = null) {
      this.tpDr.id        = topic?.id ?? null;
      this.tpDr.chapterId = chapter.id;
      this.tpForm = { title: topic?.title ?? '', chapter_id: chapter.id };
      this.tpDr.visible = true;
    },
    async saveTopic() {
      if (!this.tpForm.title.trim()) { this.$message.warning('Topic title is required.'); return; }
      this.saving = true;
      try {
        if (this.tpDr.id) {
          const { data } = await syllabusApi.updateTopic(this.tpDr.id, this.tpForm);
          const ch = this.chapters.find(c => c.id === this.tpDr.chapterId);
          if (ch) { const i = ch.topics.findIndex(t => t.id === this.tpDr.id); if (i !== -1) ch.topics[i] = data.topic; }
          this.$message.success('Topic updated.');
        } else {
          const { data } = await syllabusApi.createTopic(this.tpForm);
          const ch = this.chapters.find(c => c.id === this.tpDr.chapterId);
          if (ch) ch.topics.push(data.topic);
          this.$message.success('Topic added.');
        }
        this.tpDr.visible = false;
      } finally {
        this.saving = false;
      }
    },
    async deleteTopic(chapter, topic) {
      try {
        await this.$confirm(`Delete topic "${topic.title}"?`, 'Delete Topic', {
          type: 'warning', confirmButtonText: 'Delete', confirmButtonClass: 'el-button--danger',
        });
      } catch { return; }
      await syllabusApi.deleteTopic(topic.id);
      chapter.topics = chapter.topics.filter(t => t.id !== topic.id);
      this.$message.success('Topic deleted.');
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
.chapter-collapse { border-radius: 8px; }
.chapter-header {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding-right: 12px;
  flex-wrap: wrap;
}
.ch-num { min-width: 28px; text-align: center; }
.ch-title { font-weight: 600; font-size: 15px; flex: 1; min-width: 120px; }
.date-tag { }
.ch-actions { display: flex; gap: 6px; margin-left: auto; }
.topics-wrap { padding: 8px 52px 20px; }
.topic-row {
  display: flex;
  align-items: center;
  padding: 9px 0;
  border-bottom: 1px solid #f0f2f5;
  gap: 10px;
}
.topic-row:last-of-type { border-bottom: none; }
.t-num { color: #909399; font-size: 13px; min-width: 36px; }
.t-title { flex: 1; font-size: 14px; }
.t-actions { display: flex; gap: 2px; }
.loading-center { text-align: center; padding: 60px 0; }
</style>
