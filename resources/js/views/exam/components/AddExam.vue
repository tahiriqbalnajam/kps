<template>
  <el-drawer
    :modelValue="addExamVisible"
    direction="rtl"
    size="95%"
    :with-header="false"
  >
    <div class="drawer-header">
      <h3>{{ isEditMode ? 'Edit Exam' : 'Add New Exam' }}</h3>
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
    </el-table>
    <div class="drawer-footer">
      <el-button type="primary" @click="submitExam">{{ isEditMode ? 'Update' : 'Submit' }}</el-button>
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
    },
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
      query: {
        page: 1,
        limit: 10,
        filter: {
          id: '',
        },
      },
    };
  },
  computed: {
    isEditMode() {
      return !!this.examToEdit;
    },
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
        }));
      } catch (error) {
        ElMessage.error('Failed to fetch subjects');
      }
    },
    async submitExam() {
      try {
        this.examForm.class_id = this.selectedClass;
        
        if (this.isEditMode) {
          await examRes.update(this.examToEdit.id, this.examForm);
          ElMessage.success('Exam updated successfully');
          this.$emit('examUpdated');
        } else {
          await examRes.store(this.examForm);
          ElMessage.success('Exam created successfully');
          this.$emit('examAdded');
        }
        
        this.closeDrawer();
      } catch (error) {
        ElMessage.error(this.isEditMode ? 'Failed to update exam' : 'Failed to create exam');
      }
    },
    populateEditForm() {
      if (this.examToEdit) {
        this.examForm.title = this.examToEdit.title;
        this.selectedClass = this.examToEdit.class_id;
        this.fetchSubjects(this.examToEdit.class_id);
        
        // Populate subject marks after subjects are fetched
        if (this.examToEdit.subjects) {
          this.$nextTick(() => {
            this.examForm.subjects = this.examToEdit.subjects.map(subject => ({
              subject_id: subject.id,
              total_marks: subject.pivot.total_marks,
            }));
          });
        }
      }
    },
    closeDrawer() {
      this.$emit('update:visible', false);
    },
  },
  watch: {
    examToEdit: {
      immediate: true,
      handler(newVal) {
        if (newVal) {
          this.populateEditForm();
        } else {
          // Reset form for new exam
          this.examForm = {
            title: '',
            class_id: null,
            subjects: [],
          };
          this.selectedClass = null;
          this.subjects = [];
        }
      },
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
