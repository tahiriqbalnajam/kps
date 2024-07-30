<template>
  <el-table :data="timetable" border>
    <!-- Column for Class Names -->
    <el-table-column label="Classes" width="120">
      <template v-slot="scope">
        {{ classes[scope.$index].name }}
      </template>
    </el-table-column>

    <!-- Columns for Periods -->
    <el-table-column v-for="(period, columnIndex) in periods" :key="columnIndex" align="center">
      <template #header="scope">
        {{ period.name }}<br> <span style='font-size:9px'>({{ period.start }} - {{ period.end }})</span>
      </template>
      <template v-slot="scope">
        <div
          class="slot"
          @click="openPopup(scope.$index, columnIndex)"
          v-html="getSlotData(scope.$index, columnIndex) "
        >
          
        </div>
      </template>
    </el-table-column>
  </el-table>

  <slot-popup
    v-if="showPopup"
    :teachers="teachers"
    :subjects="subjects"
    :disabledTeachers="disabledTeachers"
    :disabledSubjects="disabledSubjects"
    @close="closePopup"
    @save="saveSlot"
  />
</template>

<script>
import { periods } from './components/data';
import SlotPopup from './components/SlotPopup.vue';
import Resource from '@/api/resource';

const teacherRes = new Resource('teachers');
const classRes = new Resource('classes');
const subjectRes = new Resource('subjects');

export default {
  name: 'TimeTable',
  components: { SlotPopup },
  data() {
    return {
      classes: [],
      periods,
      teachers: [],
      subjects: [],
      showPopup: false,
      selectedSlot: null,
      disabledTeachers: [],
      disabledSubjects: [],
      timetable: [], // Create a matrix for the timetable
    };
  },
  created() {
    this.getTeachers();
    this.getClasses();
    this.getSubjects();
  },
  watch: {
    classes(newClasses) {
      if (newClasses.length) {
        this.setTimeTable();
      }
    }
  },
  methods: {
    async getTeachers() {
      const { data } = await teacherRes.list();
      this.teachers = data.teachers.data;
    },
    async getClasses() {
      const { data } = await classRes.list();
      this.classes = data.classes.data;
    },
    async getSubjects() {
      const { data } = await subjectRes.list();
      this.subjects = data.subjects.data;
    },
    setTimeTable() {
      this.timetable = this.classes.map(() => this.periods.map(() => ({})));
    },
    openPopup(rowIndex, columnIndex) {
      this.selectedSlot = { rowIndex, columnIndex };
      this.showPopup = true;
      this.updateDisabledOptions(rowIndex, columnIndex);
    },
    closePopup() {
      this.showPopup = false;
      this.selectedSlot = null;
    },
    saveSlot(data) {
      const { rowIndex, columnIndex } = this.selectedSlot;
      this.timetable[rowIndex][columnIndex] = data;
      //this.timetable[rowIndex][columnIndex] = data;
      //this.$set(this.timetable[rowIndex], columnIndex, data);
      this.closePopup();
    },
    getTeacherNameById(id) {
      const teacher = this.teachers.find(teacher => teacher.id === id);
      if (teacher)
       return  teacher.name;
      
      return '';
    },
    getSubjectNameById(id) {
      const subject = this.subjects.find(subject => subject.id === id);
      if (subject)
        return  subject.title;
     
      return '';
    },
    getSlotData(rowIndex, columnIndex) {
      const slot = this.timetable[rowIndex][columnIndex];
      return slot.teacher && slot.subject
        ? '<b>'+this.getSubjectNameById(slot.subject)+'</b><br><span style="font-size:10px">'+this.getTeacherNameById(slot.teacher)+'</span>'
        : '';
    },
    updateDisabledOptions(rowIndex, columnIndex) {
      const selectedTeachers = [];
      const selectedSubjects = [];

      // Check the current day (columnIndex) only
      for (let i = 0; i < this.classes.length; i++) {
        const slot = this.timetable[i][columnIndex];
        if (slot.teacher) selectedTeachers.push(slot.teacher);
        if (slot.subject) selectedSubjects.push(slot.subject);
      }

      this.disabledTeachers = selectedTeachers;
      this.disabledSubjects = selectedSubjects;
    }
  }
};
</script>

<style>
.slot {
  cursor: pointer;
  height: 50px;
  text-align: center;
}
</style>