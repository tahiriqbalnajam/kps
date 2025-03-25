<template>
  <el-drawer
    :modelValue="addExamVisible"
    direction="rtl"
    size="50%"
    :with-header="false"
    @close="closeDrawer"
  >
    <div class="drawer-header">
      <el-form :model="examForm" ref="examForm" label-width="120px" inline>
        <el-form-item label="Class" prop="class_id">
          <el-select v-model="selectedClass" placeholder="Select Class" @change="fetchSubjects" style="width: 200px">
            <el-option
              v-for="classItem in classes"
              :key="classItem.id"
              :label="classItem.name"
              :value="classItem.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="Exam Title" prop="title">
          <el-input v-model="examForm.title" />
        </el-form-item>
      </el-form>
    </div>
    <el-table :data="subjects" style="width: 100%">
      <el-table-column prop="title" label="Subject Name" />
      <el-table-column label="Total Marks">
        <template #default="scope">
          <el-input-number v-model="examForm.subjects[scope.$index].total_marks" :min="0" />
        </template>
      </el-table-column>
      <el-table-column label="Skip in Report">
        <template #default="scope">
          <el-checkbox v-model="examForm.subjects[scope.$index].skip_in_report" />
        </template>
      </el-table-column>
    </el-table>
    <div class="drawer-footer">
      <el-button type="primary" @click="submitExam">Submit</el-button>
      <el-button @click="closeDrawer">Cancel</el-button>
    </div>
  </el-drawer>
</template>

<script>
import { ElMessage } from 'element-plus';
import { fetchClasses, fetchSubjectsByClass, createExam } from '@/api/exam';
import Resource from '@/api/resource';
const subjRes = new Resource('subjects');
const examRes = new Resource('exams');

export default {
  name: 'AddExam',
  props: {
    addExamVisible: {
      type: Boolean,
      required: true,
    },
    examToEdit: {
      type: Object,
      default: null,
    }
  },
  data() {
    return {
      classes: [],
      subjects: [],
      selectedClass: null,
      examForm: {
        title: '',
        class_id: null,
        subjects: [],
      },
      isEditMode: false,
      query: {
        page: 1,
        limit: 10,
        filter: {
          id: '',
        },
      },
    };
  },
  watch: {
    examToEdit: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.initializeEditMode(newVal);
        }
      }
    }
  },
  methods: {
    async fetchClasses() {
      try {
        const response = await fetchClasses();
        this.classes = response.data.classes.data;
      } catch (error) {
        ElMessage.error('Failed to fetch classes');
      }
    },
    async fetchSubjects(classId) {
      try {
        const response = await fetchSubjectsByClass(classId);
        this.subjects = response.data.classubj.subjects;
        this.examForm.subjects = this.subjects.map(subject => ({
          subject_id: subject.id,
          total_marks: 0,
          skip_in_report: false,
        }));
      } catch (error) {
        ElMessage.error('Failed to fetch subjects');
      }
    },
    async initializeEditMode(exam) {
      this.isEditMode = true;
      this.examForm.title = exam.title;
      this.selectedClass = exam.class_id;
      
      // First fetch subjects for the class
      await this.fetchSubjects(exam.class_id);
      
      // Then fetch exam subjects
      try {
        const response = await examRes.get(exam.id);
        const examWithSubjects = response.data.exam.exam_subjects;
        
        // Map existing exam subjects to form
        if (examWithSubjects.length) {
          this.examForm.subjects = this.subjects.map(subject => {
            const existingSubject = examWithSubjects.find(s => s.subject_id === subject.id);
            return {
              subject_id: subject.id,
              total_marks: existingSubject ? existingSubject.total_marks : 0,
              skip_in_report: existingSubject.skip == 1 ? true : false,
            };
          });
        }
      } catch (error) {
        ElMessage.error('Failed to fetch exam details');
      }
    },
    async submitExam() {
      try {
        this.examForm.class_id = this.selectedClass;
        
        if (this.isEditMode && this.examToEdit) {
          await examRes.update(this.examToEdit.id, this.examForm);
          ElMessage.success('Exam updated successfully');
          this.$emit('examUpdated');
        } else {
          await createExam(this.examForm);
          ElMessage.success('Exam created successfully');
          this.$emit('examAdded');
        }
        
        this.closeDrawer();
      } catch (error) {
        ElMessage.error(this.isEditMode ? 'Failed to update exam' : 'Failed to create exam');
      }
    },
    closeDrawer() {
      this.resetForm();
      this.$emit('update:visible', false);
    },
    resetForm() {
      this.examForm = {
        title: '',
        class_id: null,
        subjects: [],
      };
      this.selectedClass = null;
      this.isEditMode = false;
    },
  },
  created() {
    this.fetchClasses();
  },
};
</script>

<style scoped>
.drawer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #ebeef5;
}
.drawer-footer {
  display: flex;
  justify-content: flex-end;
  padding: 20px;
  border-top: 1px solid #ebeef5;
}
</style>
