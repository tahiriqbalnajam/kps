<template>
  <el-drawer 
    :modelValue="addMarksVisible" 
    direction="rtl"
    size="95%"
    :with-header="false" 
    @close="handleClose">
    <span slot="title">Add Marks</span>
    
    <!-- Keyboard Navigation Helper -->
    <div class="navigation-helper">
      <el-alert type="info" :closable="false" show-icon>
        <template #title>
          <strong>Keyboard Navigation:</strong> 
          Use <kbd>↑</kbd> <kbd>↓</kbd> <kbd>←</kbd> <kbd>→</kbd> arrow keys to navigate | 
          Press <kbd>Enter</kbd> to move down | 
          Type numbers directly to replace values
        </template>
      </el-alert>
    </div>

      <el-table v-loading="loading" :data="students" style="width: 100%">
        <el-table-column label="Student Name" width="220">
          <template #default="scope">
            <div>
              <div style="font-weight: 500;">{{ scope.row.roll_no ? scope.row.roll_no + ' - ' : '' }}{{ scope.row.name }}</div>
              <div style="font-size: 12px; color: #909399;">{{ scope.row.parents?.name || 'N/A' }}</div>
            </div>
          </template>
        </el-table-column>
        <el-table-column v-for="(subject, subjectIndex) in subjects" :key="subject.id" :label="subject.subject.title" width="120">
          <template #default="scope">
            <el-input 
              :ref="`input-${scope.$index}-${subjectIndex}`"
              type="number"
              :model-value="getMarkValue(scope.row.id, subject.id)"
              @update:model-value="updateMark(scope.row.id, subject.id, $event)"
              @keydown="handleKeyDown($event, scope.$index, subjectIndex)"
              :min="0"
              :max="subject.total_marks"
              step="0.5"
              placeholder="0"
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
        filter: {},
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

      // Update query to include section_id if exam has one, otherwise use class_id
      if (this.exam.section_id) {
        this.query.filter.section_id = this.exam.section_id;
      } else {
        this.query.filter.stdclass = this.class_id;
      }

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
      // Convert to number and handle decimal values
      const numericValue = value === '' || value === null ? 0 : parseFloat(value);
      this.marks[studentId][subjectId] = numericValue;
      this.validateMarks(studentId, subjectId, this.subjects.find(s => s.id === subjectId)?.total_marks);
    },
    validateMarks(studentId, subjectId, totalMarks) {
      if (!this.marks[studentId] || this.marks[studentId][subjectId] === undefined) return;
      
      let marks = parseFloat(this.marks[studentId][subjectId]);
      
      // Check if it's a valid number
      if (isNaN(marks)) {
        this.marks[studentId][subjectId] = 0;
        return;
      }
      
      // Validate range
      if (marks > totalMarks) {
        this.marks[studentId][subjectId] = totalMarks;
        this.$message.warning(`Marks cannot exceed ${totalMarks}`);
      } else if (marks < 0) {
        this.marks[studentId][subjectId] = 0;
        this.$message.warning('Marks cannot be negative');
      }
    },
    getTotalMarks(studentId) {
      if (!this.marks[studentId]) return 0;
      return Object.values(this.marks[studentId]).reduce((sum, mark) => sum + Number(mark || 0), 0);
    },
    handleKeyDown(event, rowIndex, colIndex) {
      const key = event.key;
      
      // Arrow keys and Enter navigation
      if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Enter'].includes(key)) {
        event.preventDefault();
        
        let newRowIndex = rowIndex;
        let newColIndex = colIndex;
        
        switch(key) {
          case 'ArrowUp':
            newRowIndex = Math.max(0, rowIndex - 1);
            break;
          case 'ArrowDown':
          case 'Enter':
            newRowIndex = Math.min(this.students.length - 1, rowIndex + 1);
            break;
          case 'ArrowLeft':
            newColIndex = Math.max(0, colIndex - 1);
            break;
          case 'ArrowRight':
            newColIndex = Math.min(this.subjects.length - 1, colIndex + 1);
            break;
        }
        
        // Focus the new input
        this.focusInput(newRowIndex, newColIndex);
      }
    },
    focusInput(rowIndex, colIndex) {
      this.$nextTick(() => {
        const refName = `input-${rowIndex}-${colIndex}`;
        const inputRef = this.$refs[refName];
        
        if (inputRef) {
          // Handle both single ref and array of refs
          const inputElement = Array.isArray(inputRef) ? inputRef[0] : inputRef;
          
          if (inputElement) {
            // Get the actual input element from el-input component
            const input = inputElement.$el?.querySelector('input') || inputElement;
            if (input && input.focus) {
              input.focus();
              // Select all text for easy replacement
              if (input.select) {
                input.select();
              }
            }
          }
        }
      });
    },
    async submitMarks() {
      this.submitLoading = true;
      try {
        const data = {
          exam_id: this.exam.id,
          marks: this.marks,
        };
        console.log(data);
        await examMarks(data);
        this.$message.success('Marks submitted successfully');
        this.handleClose();
      } catch (error) {
        console.error('Error submitting marks:', error);
        this.$message.error(error.response?.data?.message || 'Failed to submit marks. Please check your input and try again.');
      } finally {
        this.submitLoading = false;
      }
    },
    handleClose() {
      this.$emit('close');
    },
  },
};
</script>

<style scoped>
.navigation-helper {
  margin-bottom: 16px;
  padding: 0 16px;
}

.navigation-helper kbd {
  display: inline-block;
  padding: 2px 6px;
  font-size: 11px;
  line-height: 1.4;
  color: #24292e;
  background-color: #fafbfc;
  border: 1px solid #d1d5da;
  border-radius: 3px;
  box-shadow: inset 0 -1px 0 #d1d5da;
  font-family: monospace;
  margin: 0 2px;
}

.el-table :deep(.el-input) {
  width: 80px;
}

.el-table :deep(.el-input input) {
  text-align: center;
}

.el-table :deep(.el-input input:focus) {
  background-color: #f0f9ff;
  border-color: #409eff;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.1);
}

.el-table :deep(.el-table__body-wrapper) {
  /* Smooth scrolling for better keyboard navigation */
  scroll-behavior: smooth;
}
</style>
