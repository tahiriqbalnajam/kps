<template>
  <el-drawer
    :modelValue="addExamVisible"
    direction="rtl"
    size="95%"
    :with-header="false"
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

export default {
  name: 'AddExam',
  props: {
    addExamVisible: {
      type: Boolean,
      required: true,
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
        await createExam(this.examForm);
        ElMessage.success('Exam created successfully');
        this.closeDrawer();
        this.$emit('examAdded');
      } catch (error) {
        ElMessage.error('Failed to create exam');
      }
    },
    closeDrawer() {
      this.$emit('update:visible', false);
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
