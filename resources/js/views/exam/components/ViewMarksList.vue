<template>
  <el-dialog
    v-model="visible"
    title="View Marks List"
    width="90%"
    :before-close="handleClose"
  >
    <div v-loading="loading" class="print-area" ref="printArea">
      <!-- Header -->
      <div class="report-header">
        <div class="logo">
          <img :src="schoolLogo" alt="School Logo" />
        </div>
        <div class="school-info">
          <h1>{{ schoolName }}</h1>
          <p>{{ schoolAddress }}</p>
          <p>{{ schoolContact }}</p>
        </div>
      </div>

      <!-- Exam Info -->
      <div class="exam-info">
        <el-row :gutter="20">
          <el-col :span="16">
            <h2><span style="font-size: 21px; font-weight: normal;">{{ exam.title }}</span> - {{ exam.classes.name }}</h2>
          </el-col>
          <el-col :span="8" class="text-right">
            <h3>Date: {{ formatDate(exam.created_at) }}</h3>
          </el-col>
        </el-row>
      </div>

      <!-- Top Positions -->
      <div class="top-positions" v-if="topPositions.length">
        <h3>Top Positions</h3>
        <div class="position-cards">
          <div v-for="(student, index) in topPositions" :key="index" class="position-card">
            <h4 style="margin: 0;">Position {{ index + 1 }}</h4>
            <p class="student-name">{{ student.student_name }}</p>
            <p class="student-marks">{{ student.total_obtained }} (<span class="student-percentage">{{ student.percentage }}%</span>)</p>
          </div>
        </div>
      </div>

      <!-- Marks List -->
      <el-table :data="studentMarks" border style="width: 100%" stripe >
        <el-table-column prop="student_name" label="Student Name" width="180" />
        <el-table-column prop="father_name" label="Father Name" width="180" />
        <el-table-column 
          v-for="subject in subjects" 
          :key="subject.id"
          :label="subject.subject.title"
        >
          <template #default="scope">
            {{ scope.row.marks[subject.id] || 0 }}
          </template>
        </el-table-column>
        <el-table-column prop="total_obtained" label="Total" />
        <el-table-column prop="percentage" label="Percentage">
          <template #default="scope">
            {{ scope.row.percentage }}%
          </template>
        </el-table-column>
        <el-table-column prop="grade" label="Grade" />
      </el-table>
    </div>
    
    <template #footer>
      <span class="dialog-footer">
        <el-button type="primary" @click="printList">
          Print
        </el-button>
        <el-button @click="handleClose">Close</el-button>
      </span>
    </template>
  </el-dialog>
</template>

<script>
import Resource from '@/api/resource';
import moment from 'moment';
import { fetchExamSubjects, getSubjectsMarksByExamId } from '@/api/exam';

const studentRes = new Resource('students');
const settingsResource = new Resource('settings');

export default {
  name: 'ViewMarksList',
  props: {
    viewMarksListVisible: {
      type: Boolean,
      required: true
    },
    exam: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      visible: this.viewMarksListVisible,
      studentMarks: [],
      subjects: [],
      topPositions: [],
      schoolName: '',
      schoolAddress: '',
      schoolContact: '',
      schoolLogo: '',
      loading: true,
      query: {
        exam_id: null,
        class_id: null,
        filter: {},
      },
    }
  },
  watch: {
    viewMarksListVisible(val) {
      this.visible = val;
    },
    visible(val) {
      if (!val) {
        this.$emit('close');
      }
    }
  },
  async created() {
    this.initializeQuery();
    await this.fetchSettings();
    await this.fetchData();
  },
  methods: {
    formatDate(date) {
      return moment(date).format('DD/MM/YYYY');
    },
    async fetchSettings() {
      try {
        const { data } = await settingsResource.list();
        const settings = data.settings;
        this.schoolName = settings.school_name;
        this.schoolAddress = settings.address;
        this.schoolContact = settings.contact;
        this.schoolLogo = settings.school_logo || '/images/logo.png';
      } catch (error) {
        console.error('Error fetching settings:', error);
      }
    },
    initializeQuery() {
      this.query = {
        exam_id: this.exam.id,
        class_id: this.exam.class_id,
        filter: this.exam.section_id 
          ? { section_id: this.exam.section_id } 
          : { stdclass: this.exam.class_id },
      };
    },
    async fetchData() {
      try {
        this.loading = true;
        // Fetch students
        const { data: studentData } = await studentRes.list(this.query);
        // Fetch subjects
        const { data: subjectData } = await fetchExamSubjects(this.exam.id);
        // Fetch marks
        const { data: marksData } = await getSubjectsMarksByExamId(this.exam.id);

        this.subjects = subjectData.subjects;
        
        // Process student marks data
        this.studentMarks = studentData.students.data.map(student => {
          const studentMarks = marksData.exam.filter(mark => mark.student_id === student.id);
          const total_obtained = studentMarks.reduce((sum, mark) => sum + Number(mark.obtained_marks || 0), 0);
          const total_marks = this.subjects.reduce((sum, subject) => sum + Number(subject.total_marks), 0);
          const percentage = ((total_obtained / total_marks) * 100).toFixed(2);

          return {
            student_name: student.name,
            father_name: student.father_name,
            marks: this.processStudentMarks(studentMarks),
            total_obtained: `${total_obtained}/${total_marks}`,
            percentage: percentage,
            grade: this.calculateGrade(percentage)
          };
        });

        this.calculateTopPositions();
        this.loading = false;
      } catch (error) {
        console.error('Error fetching data:', error);
        this.loading = false;
      }
    },
    processStudentMarks(studentMarks) {
      const marksMap = {};
      studentMarks.forEach(mark => {
        marksMap[mark.exam_subject_id] = mark.obtained_marks;
      });
      return marksMap;
    },
    calculatePercentage(mark) {
      // Implement percentage calculation logic
      return ((mark.total_obtained / mark.total_marks) * 100).toFixed(2);
    },
    calculateGrade(percentage) {
      // Implement grade calculation logic
      if (percentage >= 90) return 'A+';
      if (percentage >= 80) return 'A';
      if (percentage >= 70) return 'B';
      if (percentage >= 60) return 'C';
      if (percentage >= 50) return 'D';
      return 'F';
    },
    calculateTopPositions() {
      this.topPositions = [...this.studentMarks]
        .sort((a, b) => parseFloat(b.percentage) - parseFloat(a.percentage))
        .slice(0, 4);
    },
    handleClose() {
      this.visible = false;
    },
    printList() {
      const printContents = this.$refs.printArea.cloneNode(true);
      
      // Remove any Vue-specific attributes and classes
      const elementPlusClasses = printContents.querySelectorAll('[class^="el-"]');
      elementPlusClasses.forEach(el => {
        el.removeAttribute('class');
      });

      const printWindow = window.open('', '_blank');
      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <meta charset="utf-8">
          <title>Exam Marks List</title>
          <style>
            @page { 
              size: A4 landscape; 
              margin: 1cm; 
            }
            body { 
              font-family: Arial, sans-serif; 
              font-size: 11px;
              margin: 0;
              padding: 10px;
            }
            .report-header {
              display: flex;
              align-items: center;
              margin-bottom: 10px;
            }
            .logo {
              width: 60px;
              margin-right: 15px;
            }
            .logo img {
              max-width: 100%;
              height: auto;
            }
            .school-info {
              flex: 1;
              text-align: right;
            }
            .school-info h1 {
              font-size: 18px;
              margin: 0 0 5px 0;
            }
            .school-info p {
              margin: 2px 0;
            }
            .exam-info {
              margin: 10px 0;
              padding: 5px 0;
              border-bottom: 1px solid #ddd;
            }
            .position-cards {
              display: flex;
              gap: 10px;
              margin: 10px 0;
              justify-content: space-around;
            }
            .position-card {
              border: 1px solid #ddd;
              padding: 5px 10px;
              text-align: center;
            }
            table {
              width: 100%;
              border-collapse: collapse;
              margin-top: 10px;
              font-size: 10px;
            }
            th, td {
              border: 1px solid #ddd;
              padding: 4px;
              text-align: left;
            }
            th {
              background: #f5f7fa;
            }
          </style>
        </head>
        <body onload="window.print();window.close()">
          ${printContents.innerHTML}
        </body>
        </html>
      `);
      
      printWindow.document.close();
    }
  }
}
</script>

<style scoped>
.print-area {
  padding: 20px;
}
.report-header {
  display: flex;
  align-items: center;
  margin-bottom: 20px;
}
.logo {
  width: 100px;
  margin-right: 20px;
}
.logo img {
  width: 100%;
}
.school-info {
  flex-grow: 1;
  text-align: right;
}
.exam-info {
  margin: 20px 0;
  padding: 10px 0;
  border-bottom: 2px solid #eee;
}
.exam-info h2 {
  margin: 0;
  color: #303133;
}
.exam-info h3 {
  margin: 0;
  color: #606266;
}
.text-right {
  text-align: right;
}
.top-positions {
  margin: 20px 0;
}
.position-cards {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
}
.position-card {
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  text-align: center;
  min-width: 150px;
}
.student-name {
  font-weight: bold;
  margin: 5px 0;
}
.student-marks {
  color: #666;
  font-size: 0.9em;
}
.student-percentage {
  color: #409EFF;
  font-weight: bold;
}
@media screen {
  .print-area {
    max-height: 80vh;
    overflow-y: auto;
  }
}
</style>
