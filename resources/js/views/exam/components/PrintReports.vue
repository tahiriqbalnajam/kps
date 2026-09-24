<template>
  <el-drawer
    v-if="exam"
    :modelValue="printReportsVisible"
    direction="rtl"
    size="95%"
    :with-header="false"
    @close="handleClose"
  >
    <div class="report-container">
      <div class="controls">
        <el-button type="primary" @click="printReports" :loading="loading">Print All</el-button>
        <el-button @click="handleClose">Close</el-button>
        <div class="layout-picker">
          <span class="layout-label">Layout:</span>
          <el-radio-group v-model="layout" size="small" @change="onLayoutChange">
            <el-radio-button
              v-for="option in layoutOptions"
              :key="option.key"
              :value="option.key"
            >
              {{ option.label }}
            </el-radio-button>
          </el-radio-group>
        </div>
      </div>

      <el-skeleton :loading="loading" animated>
        <template #template>
          <div style="padding: 20px;">
            <el-skeleton-item variant="p" style="width: 100%; height: 600px;" />
          </div>
        </template>

        <template #default>
          <div
            class="reports"
            ref="reportsRef"
            :class="{ 'reports-wide': layout === 'wide_marks_sheet' }"
          >
            <component
              :is="currentLayout.component"
              v-for="student in students"
              :key="`${layout}-${student.id}`"
              :student="student"
              :school-info="schoolInfo"
              :exam="exam"
              :session-name="sessionName"
              :rows="getStudentMarks(student.id)"
              :total-marks="calculateTotal('total_marks', student.id)"
              :obtained-marks="calculateTotal('obtained_marks', student.id)"
              :percentage="calculateOverallPercentage(student.id)"
              :grade="calculateGrade(calculateOverallPercentage(student.id))"
            />
          </div>
        </template>
      </el-skeleton>
    </div>
  </el-drawer>
</template>

<script>
import { getExamReports } from '@/api/exam';
import Resource from '@/api/resource';
import { sessionStore } from '@/store/session';
import {
  DEFAULT_RESULT_CARD_LAYOUT,
  RESULT_CARD_LAYOUTS,
  RESULT_CARD_LAYOUT_STORAGE_KEY,
  getResultCardLayout,
} from './result-card/layouts';

export default {
  name: 'PrintReports',
  props: {
    exam: {
      type: Object,
      required: true
    },
    printReportsVisible: Boolean,
  },
  data() {
    return {
      loading: false,
      students: [],
      examResults: [],
      subjects: [],
      schoolInfo: {},
      settingsResource: new Resource('settings'),
      layout: DEFAULT_RESULT_CARD_LAYOUT,
      layoutOptions: RESULT_CARD_LAYOUTS.map(({ key, label }) => ({ key, label })),
    };
  },
  computed: {
    currentSessionId() {
      return sessionStore().currentSessionId;
    },
    sessionName() {
      return sessionStore().sessionName;
    },
    currentLayout() {
      return getResultCardLayout(this.layout);
    },
  },
  created() {
    if (this.printReportsVisible && this.exam) {
      this.fetchData();
    }
  },
  methods: {
    async fetchData() {
      try {
        this.loading = true;
        const params = {};
        if (this.currentSessionId) params.session_id = this.currentSessionId;
        const [reportsData, settingsData] = await Promise.all([
          getExamReports(this.exam.id, params),
          this.settingsResource.list()
        ]);

        this.students = reportsData.data.students;
        this.examResults = reportsData.data.results;
        this.subjects = reportsData.data.subjects;
        this.schoolInfo = settingsData.data.settings;
        // Print-time choice wins, then the school's saved default, then 'standard'.
        this.layout = getResultCardLayout(
          localStorage.getItem(RESULT_CARD_LAYOUT_STORAGE_KEY)
          || this.schoolInfo.result_card_layout
          || DEFAULT_RESULT_CARD_LAYOUT
        ).key;
      } catch (error) {
        console.error('Error fetching data:', error);
      } finally {
        this.loading = false;
      }
    },
    getStudentMarks(studentId) {
      return this.subjects.map(subject => {
        const obtained = this.findMark(studentId, subject.id);
        return {
          subject: subject.subject?.title || '',
          total_marks: subject.total_marks,
          obtained_marks: obtained,
          percentage: this.calculatePercentage(obtained, subject.total_marks),
        };
      });
    },
    findMark(studentId, subjectId) {
      const result = this.examResults.find(
        r => r.student_id === studentId && r.exam_subject_id === subjectId
      );
      return result ? result.obtained_marks : 0;
    },
    calculatePercentage(obtained, total) {
      if (!total) return 0;
      return Math.ceil((obtained / total) * 100);
    },
    calculateTotal(field, studentId) {
      return this.getStudentMarks(studentId).reduce((sum, mark) => sum + mark[field], 0);
    },
    calculateOverallPercentage(studentId) {
      const total = this.calculateTotal('total_marks', studentId);
      const obtained = this.calculateTotal('obtained_marks', studentId);
      return this.calculatePercentage(obtained, total);
    },
    calculateGrade(percentage) {
      if (percentage >= 90) return 'A+';
      if (percentage >= 80) return 'A';
      if (percentage >= 70) return 'B';
      if (percentage >= 60) return 'C';
      if (percentage >= 50) return 'D';
      return 'F';
    },
    printReports() {
      // el-skeleton renders the reports ref only once loading finishes
      if (!this.$refs.reportsRef) return;
      const printContent = this.$refs.reportsRef.innerHTML;
      const windowPrint = window.open('', '', 'height=800,width=800');
      windowPrint.document.write(`
        <html>
          <head>
            <title>Report Cards</title>
            <style>${this.currentLayout.printCss}</style>
          </head>
          <body>${printContent}</body>
        </html>
      `);
      windowPrint.document.close();
      setTimeout(() => {
        windowPrint.focus();
        windowPrint.print();
        windowPrint.close();
      }, 250);
    },
    handleClose() {
      this.$emit('close');
    },
    /**
     * Persist only a layout the user actually picked in this toolbar. `change` fires on user
     * input alone, so merely opening the drawer leaves no override behind — otherwise the
     * school's default would be pinned forever in that browser after one visit.
     */
    onLayoutChange(val) {
      try {
        localStorage.setItem(RESULT_CARD_LAYOUT_STORAGE_KEY, val);
      } catch (error) {
        // localStorage may be unavailable; the saved settings default still applies
        console.warn('Could not persist result card layout choice:', error);
      }
    },
  },
  watch: {
    printReportsVisible(val) {
      if (val) {
        this.fetchData();
      }
    },
  },
};
</script>

<style scoped>
.report-container {
  padding: 20px;
}
.controls {
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}
.layout-picker {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: auto;
}
.layout-label {
  font-weight: bold;
  color: #333;
}
@media print {
  .controls {
    display: none;
  }
}
@media screen {
  .reports {
    max-width: 21cm;
    margin: 0 auto;
    background: white;
  }
  .reports-wide {
    max-width: 297mm;
  }
}
</style>
