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
      
      <div v-if="conflictMessage" class="conflict-warning">
        <el-alert
          :title="conflictMessage"
          type="warning"
          :closable="false"
          show-icon>
        </el-alert>
      </div>
      
      <span slot="footer" class="dialog-footer">
        <el-button @click="handleClose">Cancel</el-button>
        <el-button type="primary" @click="handleSave" :disabled="hasConflict">Save</el-button>
      </span>
    </el-dialog>
  </template>
  
  <script>
  export default {
    props: {
      teachers: Array,
      subjects: Array,
      disabledTeachers: Array,
      disabledSubjects: Array,
      selectedSlotData: Object
    },
    data() {
      return {
        visible: true,
        selectedTeacher: null,
        selectedSubject: null,
        conflictMessage: ''
      };
    },
    computed: {
      hasConflict() {
        return this.conflictMessage !== '';
      }
    },
    watch: {
      selectedTeacher() {
        this.checkConflicts();
      },
      selectedSubject() {
        this.checkConflicts();
      },
      selectedSlotData: {
        handler(newVal) {
          if (newVal) {
            this.selectedTeacher = newVal.teacher || null;
            this.selectedSubject = newVal.subject || null;
          }
        },
        immediate: true
      }
    },
    methods: {
      checkConflicts() {
        this.conflictMessage = '';
        
        if (this.selectedTeacher && this.disabledTeachers.includes(this.selectedTeacher)) {
          this.conflictMessage = 'This teacher is already assigned to another class during this period.';
          return;
        }
        
        if (this.selectedSubject && this.disabledSubjects.includes(this.selectedSubject)) {
          this.conflictMessage = 'This subject is already assigned to this class/section for today.';
          return;
        }
      },
      handleClose() {
        this.$emit('close');
      },
      handleSave() {
        if (!this.selectedTeacher || !this.selectedSubject) {
          this.$message.error('Please select a teacher and subject.');
          return;
        }

        if (this.hasConflict) {
          this.$message.error('Please resolve conflicts before saving.');
          return;
        }

        this.$emit('save', {
          teacher: this.selectedTeacher,
          subject: this.selectedSubject
        });
      }
    }
  };
  </script>
  
  <style scoped>
  .conflict-warning {
    margin: 15px 0;
  }
  </style>