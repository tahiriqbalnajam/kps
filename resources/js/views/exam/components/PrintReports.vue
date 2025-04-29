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
      </div>
      
      <el-skeleton :loading="loading" animated>
        <template #template>
          <div style="padding: 20px;">
            <el-skeleton-item variant="p" style="width: 100%; height: 600px;" />
          </div>
        </template>
        
        <template #default>
          <div class="reports" ref="reportsRef">
            <div v-for="student in students" :key="student.id" class="report-card">
              <div class="report-header">
                <div class="logo-section">
                  <img v-if="schoolInfo.school_logo" :src="`/${schoolInfo.school_logo}`" alt="School Logo" />
                </div>
                <div class="school-info">
                  <h1 class="school-name">{{ schoolInfo.school_name }}</h1>
                  <p class="school-address">{{ schoolInfo.address }}</p>
                  <p class="school-contact">Phone: {{ schoolInfo.phone }}</p>
                </div>
              </div>

              <div class="student-info-section">
                <div class="info-row">
                  <div class="info-item">
                    <span class="label">Student Name:</span>
                    <span class="value" style="font-size: 26px;"><b>{{ student.name }}</b></span>
                  </div>
                  <div class="info-item">
                    <span class="label">Father's Name:</span>
                    <span class="value" style="font-size: 26px;"><b>{{ student.parents.name }}</b></span>
                  </div>
                </div>
                <div class="info-row">
                  <div class="info-item">
                    <span class="label">Class:</span>
                    <span class="value" style="font-size: 26px;"><b>{{ exam.classes.name }}</b></span>
                  </div>
                  <div class="info-item">
                    <span class="label">Roll Number:</span>
                    <span class="value" style="font-size: 26px;"><b>{{ student.roll_no }}</b></span>
                  </div>
                </div>
              </div>

              <div class="results-section">
                <div class="marks-section">
                  <table class="marks-table">
                    <thead>
                      <tr>
                        <th>Subject</th>
                        <th>Total Marks</th>
                        <th>Obtained Marks</th>
                        <th>Percentage</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="mark in getStudentMarks(student.id)" :key="mark.subject">
                        <td>{{ mark.subject }}</td>
                        <td>{{ mark.total_marks }}</td>
                        <td>{{ mark.obtained_marks }}</td>
                        <td>{{ calculatePercentage(mark.obtained_marks, mark.total_marks) }}%</td>
                      </tr>
                      <tr class="total-row">
                        <td>Total</td>
                        <td>{{ calculateTotal('total_marks', student.id) }}</td>
                        <td>{{ calculateTotal('obtained_marks', student.id) }}</td>
                        <td>{{ calculateOverallPercentage(student.id) }}%</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="results-section">
                <div class="assessment-section">
                  <h3 style="font-size: 26px;">Performance Assessment</h3>
                  <div class="assessment-item">
                    <span class="label">Grade:</span>
                    <span class="line value asses">{{ calculateGrade(calculateOverallPercentage(student.id)) }}</span>
                  </div>
                  <div class="assessment-item">
                    <span class="label">Attendance:</span>
                    <span class="line value asses"></span>
                  </div>
                  <div class="assessment-item">
                    <span class="label">Uniform:</span>
                    <span class="line value asses"></span>
                  </div>
                  <div class="assessment-item">
                    <span class="label">Behavior:</span>
                    <span class="line value asses"></span>
                  </div>
                </div>
              </div>

              <div class="footer-section">
                <div class="remarks">
                  <span class="label">Remarks:</span>
                  <span class="line remarks"></span>
                </div>
                <div class="signatures">
                  <div class="signature-item">
                    <span class="line w60 "></span>
                    <span class="bborder label">Class Teacher</span>
                  </div>
                  <div class="signature-item">
                    <span class="line w60"></span>
                    <span class="label">Principal</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </el-skeleton>
    </div>
  </el-drawer>
</template>

<script>
import { getExamReports } from '@/api/exam';
import Resource from '@/api/resource';

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
    };
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
        const [reportsData, settingsData] = await Promise.all([
          getExamReports(this.exam.id),
          this.settingsResource.list()
        ]);
        
        this.students = reportsData.data.students;
        this.examResults = reportsData.data.results;
        this.subjects = reportsData.data.subjects;
        this.schoolInfo = settingsData.data.settings;
      } catch (error) {
        console.error('Error fetching data:', error);
      } finally {
        this.loading = false;
      }
    },
    getStudentMarks(studentId) {
      return this.subjects.map(subject => ({
        subject: subject.subject.title,
        total_marks: subject.total_marks,
        obtained_marks: this.findMark(studentId, subject.id),
      }));
    },
    findMark(studentId, subjectId) {
      const result = this.examResults.find(
        r => r.student_id === studentId && r.exam_subject_id === subjectId
      );
      return result ? result.obtained_marks : 0;
    },
    calculatePercentage(obtained, total) {
      return Math.ceil((obtained / total) * 100);
    },
    calculateTotal(field, studentId) {
      return this.getStudentMarks(studentId).reduce((sum, mark) => sum + mark[field], 0);
    },
    calculateOverallPercentage(studentId) {
      const total = this.calculateTotal('total_marks', studentId);
      const obtained = this.calculateTotal('obtained_marks', studentId);
      return Math.ceil((obtained / total) * 100);
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
      const printContent = this.$refs.reportsRef.innerHTML;
      const windowPrint = window.open('', '', 'height=800,width=800');
      windowPrint.document.write(`
        <html>
          <head>
            <title>Report Cards</title>
            <style>
              @page {
                size: A4;
                margin: 0.5cm;  /* Reduced margins */
              }
              @media print {
.report-container {
  padding: 20px;
}
.controls {
  margin-bottom: 20px;
}
.report-card {
  padding: 15px;
  margin-bottom: 20px;
  page-break-inside: avoid;
  min-height: auto;
  max-height: 297mm;
  display: flex;
  flex-direction: column;
}
.report-header {
  display: flex;
  align-items: center;
  margin-bottom: 15px;
  padding-bottom: 15px;
  border-bottom: 2px solid #333;
}
.logo-section img {
  height: 100px;
  margin-right: 15px;
}
.school-info {
  flex-grow: 1;
  text-align: center;
}
.school-name {
  font-size: 46px;
  font-weight: bold;
  margin-bottom: 5px;
}
.student-info-section {
  margin-bottom: 15px;
}
.info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}
.results-section {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
}
.marks-section {
  flex: 2;
}
.assessment-section {
  flex: 1;
  padding: 15px;
  background: #f9f9f9;
  max-height: 200px;
}
.marks-table {
  width: 100%;
  border-collapse: collapse;
}
.marks-table th,
.marks-table td {
  border: 1px solid #ddd;
  padding: 4px 0;
  text-align: center;
  font-size: 26px !important;
}
.total-row {
  font-weight: bold;
  background: #f5f5f5;
}
.footer-section {
  margin-top: 15px;
}
.remarks {
  margin-bottom: 30px;
}
.signatures {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;
}
.signature-item {
  text-align: center;
}
.signature-item .line {
  display: block;
  margin-bottom: 5px;
}
@media print {
  .controls {
    display: none;
  }
  
  .report-card {
    page-break-after: always;
  }
}

/* Additional styles for better print preview */
.report-card {
  border: 1px solid #eee;
  margin-bottom: 30px;
  min-height: calc(100vh - 40px);
  display: flex;
  flex-direction: column;
}

.results-section {
  flex: 1;
}

.footer-section {
  margin-top: auto;
  padding-top: 20px;
}

@media screen {
  .reports {
    max-width: 21cm;
    margin: 0 auto;
    background: white;
  }
  
  .report-card {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
  }
}
.label {
  margin-right: 5px;
  font-size: 18px;
}
.value {

}
.line {
  border-bottom: 1px solid #ccc;
}
.school-address{
  margin: 0;
}
.school-contact {
  margin: 0;
}
.remarks {
  width: 90%;
}
.asses {
  width: 50%;
  font-size: 18px;
  font-weight: bold;
}
.assessment-item {
    margin-bottom: 10px;
}
            </style>
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
}
.report-card {
  padding: 15px;
  margin-bottom: 20px;
  page-break-inside: avoid;
  min-height: auto;  /* Changed from calc(100vh - 40px) */
  display: flex;
  flex-direction: column;
  max-height: 297mm; /* A4 height */
  border: 1px solid #eee;
}
.report-header {
  display: flex;
  align-items: center;
  margin-bottom: 15px;  /* Reduced from 20px */
  padding-bottom: 15px;
  border-bottom: 2px solid #333;
}
.logo-section img {
  height: 100px;  /* Reduced from 80px */
  margin-right: 15px;
}
.school-info {
  flex-grow: 1;
  text-align: center;
}
.school-name {
  font-size: 24px;
  font-weight: bold;
  margin-bottom: 5px;
}
.student-info-section {
  margin-bottom: 15px;  /* Reduced from 20px */
}
.info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}
.results-section {
  display: flex;
  gap: 15px;  /* Reduced from 20px */
  margin-bottom: 15px;  /* Reduced from 20px */
}
.marks-section {
  flex: 2;
}
.assessment-section {
  flex: 1;
  padding: 15px;
  background: #f9f9f9;
  max-height: 200px;  /* Reduced from 250px */
}
.marks-table {
  width: 100%;
  border-collapse: collapse;
}
.marks-table th,
.marks-table td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: center;
  font-size: 18px;
}
.total-row {
  font-weight: bold;
  background: #f5f5f5;
}
.footer-section {
  margin-top: 15px;  /* Reduced from 40px */
}
.remarks {
  margin-bottom: 30px;
}
.signatures {
  display: flex;
  justify-content: space-between;
  margin-top: 20px;  /* Reduced from 40px */
}
.signature-item {
  text-align: center;
}
.signature-item .line {
  display: block;
  margin-bottom: 5px;
}
@media print {
  .controls {
    display: none;
  }
  
  .report-card {
    page-break-after: always;
  }
}

/* Additional styles for better print preview */
.report-card {
  border: 1px solid #eee;
  margin-bottom: 30px;
  min-height: calc(100vh - 40px);
  display: flex;
  flex-direction: column;
}

.results-section {
  flex: 1;
}

.footer-section {
  margin-top: auto;
  padding-top: 20px;
}

@media screen {
  .reports {
    max-width: 21cm;
    margin: 0 auto;
    background: white;
  }
  
  .report-card {
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
  }
}
.label {
  margin-right: 5px;
  font-size: 18px;
}
.value {

}
.line {
  border-bottom: 1px solid #ccc;
}
.school-address{
  margin: 0;
}
.school-contact {
  margin: 0;
}
.remarks {
  width: 90%;
}
.asses {
  width: 50%;
  font-size: 18px;
  font-weight: bold;
}
.assessment-item {
    margin-bottom: 10px;
}
</style>
