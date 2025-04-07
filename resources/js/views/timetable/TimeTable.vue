<template>
  <el-table :data="timetable" border stripe max-height="650">
    <!-- Column for Class Names -->
    <el-table-column label="Classes" width="120" fixed>
      <template v-slot="scope">
        {{ classes[scope.$index].name }}
      </template>
    </el-table-column>

    <!-- Columns for Periods -->
    <el-table-column v-for="(period, columnIndex) in periods" :key="columnIndex" align="center">
      <template #header="scope">
        {{ period.title }}<br> <span style='font-size:9px'>({{ period.start }} - {{ period.end }})</span>
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
    :selectedSlotData="selectedSlotData"
    @close="closePopup"
    @save="saveSlot"
  />
</template>

<script>
import SlotPopup from './components/SlotPopup.vue';
import Resource from '@/api/resource';

const teacherRes = new Resource('teachers');
const classRes = new Resource('classes');
const subjectRes = new Resource('subjects');
const periodRes = new Resource('periods');
const ttRes = new Resource('timetable');

export default {
  name: 'TimeTable',
  components: { SlotPopup },
  data() {
    return {
      classes: [],
      periods: [],
      teachers: [],
      subjects: [],
      showPopup: false,
      selectedSlot: null,
      selectedSlotData: null,
      disabledTeachers: [],
      disabledSubjects: [],
      timetable: [], // Create a matrix for the timetable
    };
  },
  created() {
    this.getTeachers();
    this.getClasses();
    this.getSubjects();
    this.getPeriods();
    

  },
  mounted() {
    this.getTimeTable();
  },
  watch: {
    classes(newClasses) {
      if (newClasses.length) {
        this.setTimeTable();
      }
    },
    periods(newPeriod) {
      if (newPeriod.length) {
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
    async getPeriods() {
      const { data } = await periodRes.list();
      this.periods = data.periods.data;
    },
    setTimeTable() {
      // Initialize with empty objects to prevent undefined errors
      if (this.classes.length > 0 && this.periods.length > 0) {
        this.timetable = this.classes.map(() => this.periods.map(() => ({})));
      }
    },
    getSummaries(param) {
      const { columns, data } = param
      const sums = [];

      columns.forEach((column, index) => {
        sums[index] = 'N'
      })
      return sums;
    },
    openPopup(rowIndex, columnIndex) {
      this.selectedSlot = { rowIndex, columnIndex };
      // Store current slot data to pass to popup for editing
      this.selectedSlotData = this.timetable[rowIndex][columnIndex] || {};
      
      this.updateDisabledOptions(rowIndex, columnIndex);
      this.showPopup = true;
    },
    closePopup() {
      this.showPopup = false;
      this.selectedSlot = null;
      this.selectedSlotData = null;
    },
    saveSlot(data) {
      const { rowIndex, columnIndex } = this.selectedSlot;
      this.timetable[rowIndex][columnIndex] = data;
      this.closePopup();
      this.saveTimetable();
    },
    async getTimeTable() {
      try {
        const data = await ttRes.list();
        await new Promise(resolve => setTimeout(resolve, 500)); // Add a half-second pause
        
        if (data && data.timetable && data.timetable.timetable) {
          const parsedData = JSON.parse(data.timetable.timetable);
          
          // Ensure timetable structure matches current classes and periods
          if (Array.isArray(parsedData) && parsedData.length > 0) {
            // If timetable structure doesn't match current classes/periods, reinitialize
            if (parsedData.length !== this.classes.length || 
                (parsedData[0] && parsedData[0].length !== this.periods.length)) {
              this.setTimeTable();
            } else {
              this.timetable = parsedData;
            }
          } else {
            this.setTimeTable();
          }
        } else {
          this.setTimeTable();
        }
      } catch (error) {
        console.error("Error loading timetable:", error);
        this.setTimeTable();
      }
    },
    saveTimetable() {
      ttRes.update('1',{timetable: this.timetable});
    },
    getTeacherNameById(id) {
      const teacher = this.teachers.find(teacher => teacher.id === id);
      if (teacher)
       return  teacher?.name;
      
      return '';
    },
    getSubjectNameById(id) {
      const subject = this.subjects.find(subject => subject.id === id);
      if (subject)
        return  subject.title;
     
      return '';
    },
    getSlotData(rowIndex, columnIndex) {
      // Add safety checks
      if (!this.timetable || !this.timetable[rowIndex] || !this.timetable[rowIndex][columnIndex]) {
        return '';
      }
      
      const slot = this.timetable[rowIndex][columnIndex];
      if (!slot) return '';
      
      return slot.teacher && slot.subject && slot.teacher !== '' && slot.subject !== ''
        ? '<b>'+this.getSubjectNameById(slot.subject)+
          '</b><br><span style="font-size:10px">'+
            this.getTeacherNameById(slot.teacher)+
            '</span>'
        : '';
    },
    updateDisabledOptions(rowIndex, columnIndex) {
      // Add safety checks
      if (!this.timetable || !this.timetable[rowIndex]) {
        this.disabledTeachers = [];
        this.disabledSubjects = [];
        return;
      }
      
      const selectedTeachers = [];
      const selectedSubjects = [];

      // Check the current day (columnIndex) only
      for (let i = 0; i < this.classes.length; i++) {
        // Don't disable the currently selected teacher
        if (i !== rowIndex && this.timetable[i] && this.timetable[i][columnIndex]) {
          const period = this.timetable[i][columnIndex];
          if (period && period.teacher) selectedTeachers.push(period.teacher);
        }
      }
      
      for (let i = 0; i < this.periods.length; i++) {
        // Don't disable the currently selected subject
        if (i !== columnIndex && this.timetable[rowIndex] && this.timetable[rowIndex][i]) {
          const clas = this.timetable[rowIndex][i];
          if (clas && clas.subject) selectedSubjects.push(clas.subject);
        }
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