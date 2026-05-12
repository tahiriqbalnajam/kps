<template>
  <el-dialog
    v-model="dialogVisible"
    title="Date Sheet"
    width="70%"
    :close-on-click-modal="true"
  >
    <div>
      <div class="print-actions no-print">
        <el-button type="primary" @click="printDateSheet">
          <el-icon><Printer /></el-icon> Print
        </el-button>
        <el-button @click="dialogVisible = false">Close</el-button>
      </div>

      <div id="datesheet-print" class="datesheet-container">
        <div class="datesheet-header">
          <div class="school-info">
            <div class="school-logo" v-if="school.school_logo">
              <img :src="`/${school.school_logo}`" alt="School Logo" style="max-height: 80px;" />
            </div>
            <h2>{{ school.school_name || 'School Name' }}</h2>
            <p v-if="school.address">{{ school.address }}</p>
            <p v-if="school.phone || school.school_email">
              <span v-if="school.phone">Phone: {{ school.phone }}</span>
              <span v-if="school.phone && school.school_email"> | </span>
              <span v-if="school.school_email">Email: {{ school.school_email }}</span>
            </p>
            <p v-if="school.website">Web: {{ school.website }}</p>
            <p v-if="school.tagline" class="tagline">{{ school.tagline }}</p>
          </div>
          <hr />
          <div class="exam-info">
            <h3>Date Sheet: {{ exam.title }}</h3>
            <p>
              Class: {{ exam.classes?.name }}
              <span v-if="exam.section"> | Section: {{ exam.section.name }}</span>
            </p>
            <p v-if="exam.start_date || exam.end_date" class="exam-dates">
              {{ exam.start_date ? formatDate(exam.start_date) : '...' }} — {{ exam.end_date ? formatDate(exam.end_date) : '...' }}
            </p>
          </div>
        </div>

        <table class="datesheet-table">
          <thead>
            <tr>
              <th class="col-sno">S.No</th>
              <th class="col-subject">Subject</th>
              <th class="col-date">Date</th>
              <th class="col-day">Day</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(subject, index) in examSubjects" :key="subject.id">
              <td class="text-center">{{ index + 1 }}</td>
              <td>{{ subject.subject?.title || 'N/A' }}</td>
              <td class="text-center">{{ formatDate(subject.exam_date) }}</td>
              <td class="text-center">{{ formatDay(subject.exam_date) }}</td>
            </tr>
            <tr v-if="examSubjects.length === 0">
              <td colspan="4" class="text-center">No dates have been assigned for this exam.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </el-dialog>
</template>

<script>
import moment from 'moment';

export default {
  name: 'DateSheetPrint',
  props: {
    visible: {
      type: Boolean,
      default: false,
    },
    exam: {
      type: Object,
      default: () => ({}),
    },
    school: {
      type: Object,
      default: () => ({}),
    },
  },
  emits: ['update:visible'],
  computed: {
    dialogVisible: {
      get() { return this.visible; },
      set(val) { this.$emit('update:visible', val); },
    },
    examSubjects() {
      return (this.exam?.exam_subjects || []).filter(s => !s.skip);
    },
  },
  methods: {
    formatDate(date) {
      return date ? moment(date).format('DD/MM/YYYY') : '-';
    },
    formatDay(date) {
      return date ? moment(date).format('dddd') : '-';
    },
    handleClose() {
      this.$emit('update:visible', false);
    },
    printDateSheet() {
      const printContents = document.getElementById('datesheet-print').innerHTML;
      const originalContents = document.body.innerHTML;
      document.body.innerHTML = printContents;
      window.print();
      document.body.innerHTML = originalContents;
      window.location.reload();
    },
  },
};
</script>

<style scoped>
.print-actions {
  margin-bottom: 20px;
  padding-bottom: 10px;
  border-bottom: 1px solid #ebeef5;
}

.datesheet-container {
  font-family: 'Times New Roman', serif;
  max-width: 800px;
  margin: 0 auto;
}

.school-info {
  text-align: center;
  margin-bottom: 10px;
}

.school-info h2 {
  margin: 5px 0;
  font-size: 22px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.school-info p {
  margin: 3px 0;
  font-size: 14px;
  color: #333;
}

.tagline {
  font-style: italic;
  font-weight: bold;
  color: #555;
}

.exam-info {
  text-align: center;
  margin: 15px 0;
}

.exam-info h3 {
  margin: 5px 0;
  font-size: 18px;
  text-decoration: underline;
}

.exam-info p {
  margin: 3px 0;
  font-size: 15px;
  font-weight: bold;
}

.datesheet-table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}

.datesheet-table th,
.datesheet-table td {
  border: 1px solid #333;
  padding: 10px 12px;
  font-size: 15px;
}

.datesheet-table thead th {
  background-color: #e8e8e8;
  font-weight: bold;
  text-align: center;
}

.col-sno {
  width: 8%;
}

.col-subject {
  width: 50%;
  text-align: left;
}

.col-date {
  width: 22%;
}

.col-day {
  width: 20%;
}

.text-center {
  text-align: center;
}
</style>
