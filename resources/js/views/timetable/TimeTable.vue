<template>
    <el-table :data="timetable" border>
      <!-- Column for Period Names -->
      <el-table-column label="Period" width="120">
        <template v-slot="scope">
          <div>{{ periods[scope.$index].name }}</div>
        </template>
      </el-table-column>
  
      <!-- Columns for Weekdays -->
      <el-table-column v-for="(weekday, columnIndex) in weekdays" :key="columnIndex" :label="weekday">
        <template v-slot="scope">
          <div
            class="slot"
            @click="openPopup(scope.$index, columnIndex)"
          >
            {{ getSlotData(scope.$index, columnIndex) }}
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
  import { teachers, subjects, weekdays, periods } from './components/data';
  import SlotPopup from './components/SlotPopup.vue';
  
  export default {
    components: { SlotPopup },
    data() {
      return {
        timetable: periods.map(() => weekdays.map(() => ({}))), // Create a matrix for the timetable
        weekdays,
        periods,
        teachers,
        subjects,
        showPopup: false,
        selectedSlot: null,
        disabledTeachers: [],
        disabledSubjects: []
      };
    },
    methods: {
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

        //this.$set(this.timetable[rowIndex], columnIndex, data);
        this.closePopup();
      },
      getSlotData(rowIndex, columnIndex) {
        const slot = this.timetable[rowIndex][columnIndex];
        return slot.teacher && slot.subject
          ? `${slot.teacher} - ${slot.subject}`
          : '';
      },
      updateDisabledOptions(rowIndex, columnIndex) {
      const selectedTeachers = [];
      const selectedSubjects = [];
      
      // Check the current day (columnIndex) only
      for (let i = 0; i < periods.length; i++) {
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
    display: flex;
    align-items: center;
    justify-content: center;
  }
  </style>
  