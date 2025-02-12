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
                  <img v-if="schoolInfo.logo" :src="`/${schoolInfo.logo}`" alt="School Logo" />
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
                    <span class="value">{{ student.name }}</span>
                  </div>
                  <div class="info-item">
                    <span class="label">Father's Name:</span>
                    <span class="value">{{ student.father_name }}</span>
                  </div>
                </div>
                <div class="info-row">
                  <div class="info-item">
                    <span class="label">Class:</span>
                    <span class="value">{{ exam.classes.name }}</span>
                  </div>
                  <div class="info-item">
                    <span class="label">Roll Number:</span>
                    <span class="value">{{ student.roll_no }}</span>
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

                <div class="assessment-section">
                  <h3>Performance Assessment</h3>
                  <div class="assessment-item">
                    <span class="label">Grade:</span>
                    <span class="value">{{ calculateGrade(calculateOverallPercentage(student.id)) }}</span>
                  </div>
                  <div class="assessment-item">
                    <span class="label">Attendance:</span>
                    <span class="value">___________</span>
                  </div>
                  <div class="assessment-item">
                    <span class="label">Uniform:</span>
                    <span class="value">___________</span>
                  </div>
                  <div class="assessment-item">
                    <span class="label">Behavior:</span>
                    <span class="value">___________</span>
                  </div>
                </div>
              </div>

              <div class="footer-section">
                <div class="remarks">
                  <span class="label">Remarks:</span>
                  <span class="value">_________________________________</span>
                </div>
                <div class="signatures">
                  <div class="signature-item">
                    <span class="line">_________________</span>
                    <span class="label">Class Teacher</span>
                  </div>
                  <div class="signature-item">
                    <span class="line">_________________</span>
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
      return ((obtained / total) * 100).toFixed(2);
    },
    calculateTotal(field, studentId) {
      return this.getStudentMarks(studentId).reduce((sum, mark) => sum + mark[field], 0);
    },
    calculateOverallPercentage(studentId) {
      const total = this.calculateTotal('total_marks', studentId);
      const obtained = this.calculateTotal('obtained_marks', studentId);
      return ((obtained / total) * 100).toFixed(2);
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
                margin: 1cm;
              }
              @media print {
                .report-card {
                  page-break-after: always;
                  margin: 0;
                  padding: 20px;
                  min-height: calc(100vh - 2cm);
                  display: flex;
                  flex-direction: column;
                }
                .controls { display: none; }
                body { margin: 0; }
                
                /* Header Styles */
                .report-header {
                  padding: 10px 0;
                  border-bottom: 2px solid #000;
                }
                .logo-section img {
                  height: 80px;
                  max-width: 80px;
                  object-fit: contain;
                }
                .school-name {
                  font-size: 24px;
                  margin: 5px 0;
                }
                
                /* Table Styles */
                .marks-table {
                  width: 100%;
                  border-collapse: collapse;
                  margin: 15px 0;
                }
                .marks-table th, .marks-table td {
                  border: 1px solid #000;
                  padding: 5px;
                  text-align: center;
                }
                
                /* Layout */
                .results-section {
                  display: flex;
                  gap: 20px;
                  flex: 1;
                }
                .marks-section { flex: 2; }
                .assessment-section {
                  flex: 1;
                  padding: 10px;
                  border: 1px solid #000;
                }
                
                /* Footer */
                .footer-section {
                  margin-top: auto;
                  padding-top: 20px;
                }
                .signatures {
                  display: flex;
                  justify-content: space-between;
                  margin-top: 30px;
                }
                .signature-item {
                  text-align: center;
                }
                .signature-item .line {
                  display: block;
                  width: 150px;
                  border-top: 1px solid #000;
                  margin: 40px auto 5px;
                }
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
  padding: 20px;
  margin-bottom: 30px;
  page-break-after: always;
}
.report-header {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 20px;
  border-bottom: 2px solid #333;
}
.logo-section img {
  height: 80px;
  margin-right: 20px;
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
  margin-bottom: 20px;
}
.info-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}
.results-section {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}
.marks-section {
  flex: 2;
}
.assessment-section {
  flex: 1;
  padding: 20px;
  background: #f9f9f9;
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
}
.total-row {
  font-weight: bold;
  background: #f5f5f5;
}
.footer-section {
  margin-top: 40px;
}
.remarks {
  margin-bottom: 30px;
}
.signatures {
  display: flex;
  justify-content: space-between;
  margin-top: 40px;
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
</style>
