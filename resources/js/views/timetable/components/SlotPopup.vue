<template>
    <el-dialog title="Select Teacher and Subject" modelValue="visible" @close="handleClose">
      <el-form>
        <el-form-item label="Teacher">
          <el-select v-model="selectedTeacher" placeholder="Select Teacher">
            <el-option
              v-for="teacher in teachers"
              :key="teacher.id"
              :label="teacher.name"
              :value="teacher.id"
              :disabled="disabledTeachers.some(teach => teach == teacher.id)"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="Subject">
          <el-select v-model="selectedSubject" placeholder="Select Subject">
            <el-option
              v-for="subject in subjects"
              :key="subject.id"
              :label="subject.title"
              :value="subject.id"
              :disabled="disabledSubjects.some(sub => sub == subject.id)"
            />
          </el-select>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="handleClose">Cancel</el-button>
        <el-button type="primary" @click="handleSave">Save</el-button>
      </span>
    </el-dialog>
  </template>
  
  <script>
  export default {
    props: {
      teachers: Array,
      subjects: Array,
      disabledTeachers: Array,
      disabledSubjects: Array
    },
    data() {
      return {
        visible: true,
        selectedTeacher: null,
        selectedSubject: null
      };
    },
    methods: {
      handleClose() {
        this.$emit('close');
      },
      handleSave() {
        this.$emit('save', {
          teacher: this.selectedTeacher,
          subject: this.selectedSubject
        });
      }
    }
  };
  </script>
  