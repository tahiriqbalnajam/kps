<template>
  <el-drawer 
    :modelValue="addMarksVisible" 
    direction="rtl"
    size="95%"
    :with-header="false" 
    @close="handleClose">
    <span slot="title">Add Marks</span>
      <el-table v-loading="loading" :data="students" style="width: 100%">
        <el-table-column prop="name" label="Student Name" width="180"/>
        <el-table-column v-for="subject in subjects" :key="subject.id" :label="subject.subject.title" width="120">
          <template #default="scope">
            <el-input 
              :model-value="getMarkValue(scope.row.id, subject.id)"
              @update:model-value="updateMark(scope.row.id, subject.id, $event)"
            ></el-input>
          </template>
        </el-table-column>
        <el-table-column label="Total Marks" width="120" align="center">
          <template #default="scope">
            <span>{{ getTotalMarks(scope.row.id) }}/{{ totalPossibleMarks }}</span>
          </template>
        </el-table-column>
      </el-table>
    <template #footer>
      <span class="dialog-footer">
        <el-button @click="handleClose">Cancel</el-button>
        <el-button type="primary" :loading="submitLoading" @click="submitMarks">Submit</el-button>
      </span>
    </template>
  </el-drawer>
</template>

<script>
import Resource from '@/api/resource';
import { fetchExamSubjects, getSubjectsMarksByExamId, examMarks  } from '@/api/exam';
export default {
  name: 'AddMarks',
  props: {
    exam: Object,
    class_id: Number,
    addMarksVisible: Boolean,
  },
  data() {
    return {
      students: [],
      subjects: [],
      marks: {},
      loading: false,
      submitLoading: false,
      query: {
        exam_id: this.exam.id,
        class_id: this.class_id,
        filter: { stdclass: this.class_id },
      },
    };
  },
  computed: {
    totalPossibleMarks() {
      return this.subjects.reduce((sum, subject) => sum + Number(subject.total_marks), 0);
    },
  },
  watch: {
    addMarksVisible(val) {
      if (val) {
        this.fetchData();
      }
    },
  },
  created() {
    this.fetchData();
  },
  methods: {
    async fetchData() {
      this.loading = true;
      const studentRes = new Resource('students');

      const { data: studentData } = await studentRes.list(this.query);
      const { data: subjectData } = await fetchExamSubjects(this.exam.id);
      this.students = studentData.students.data;
      this.subjects = subjectData.subjects;

      // Fetch existing marks if any
      const { data } = await getSubjectsMarksByExamId(this.exam.id);
      const existingMarks = data.exam;
      if (existingMarks && existingMarks.length > 0) {
        this.populateExistingMarks(existingMarks);
      } else {
        this.initializeMarks();
      }

      this.loading = false;
    },
    initializeMarks() {
      const initialMarks = {};
      this.students.forEach(student => {
        initialMarks[student.id] = {};
        this.subjects.forEach(subject => {
          initialMarks[student.id][subject.id] = 0;
        });
      });
      this.marks = initialMarks;
    },
    populateExistingMarks(existingMarks) {
      existingMarks.forEach(mark => {
        if (!this.marks[mark.student_id]) {
          this.marks[mark.student_id] = {};
        }
        this.marks[mark.student_id][mark.exam_subject_id] = mark.obtained_marks;
      });
    },
    getMarkValue(studentId, subjectId) {
      return this.marks?.[studentId]?.[subjectId] || 0;
    },
    updateMark(studentId, subjectId, value) {
      if (!this.marks[studentId]) {
        this.marks[studentId] = {};
      }
      this.marks[studentId][subjectId] = value;
      this.validateMarks(studentId, subjectId, this.subjects.find(s => s.id === subjectId)?.total_marks);
    },
    validateMarks(studentId, subjectId, totalMarks) {
      if (!this.marks[studentId] || !this.marks[studentId][subjectId]) return;
      
      const marks = Number(this.marks[studentId][subjectId]);
      if (marks > totalMarks) {
        this.marks[studentId][subjectId] = totalMarks;
      } else if (marks < 0) {
        this.marks[studentId][subjectId] = 0;
      }
    },
    getTotalMarks(studentId) {
      if (!this.marks[studentId]) return 0;
      return Object.values(this.marks[studentId]).reduce((sum, mark) => sum + Number(mark || 0), 0);
    },
    async submitMarks() {
      this.submitLoading = true;
      const data = {
        exam_id: this.exam.id,
        marks: this.marks,
      };
      console.log(data);
      await examMarks(data);
      this.submitLoading = false;
      this.handleClose();
    },
    handleClose() {
      this.$emit('close');
    },
  },
};
</script>

<style scoped>
.el-table :deep(.el-input) {
  width: 80px;
}
</style>
