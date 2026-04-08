<template>
  <div>
    <!-- Toolbar -->
    <div style="margin-bottom: 12px; display: flex; gap: 10px; align-items: center;">
      <el-button-group>
        <el-button
          :type="viewMode === 'class' ? 'primary' : 'default'"
          @click="viewMode = 'class'"
          icon="el-icon-s-grid"
        >Class View</el-button>
        <el-button
          :type="viewMode === 'teacher' ? 'primary' : 'default'"
          @click="viewMode = 'teacher'"
          icon="el-icon-user"
        >Teacher View</el-button>
      </el-button-group>
      <el-button type="success" icon="el-icon-printer" @click="printTimetable">Print</el-button>
    </div>

    <!-- Class View -->
    <div v-if="viewMode === 'class'" id="printable-timetable">
      <h3 class="print-title">Timetable</h3>
      <el-table :data="tableData" border stripe max-height="650" class="tt-table">
        <el-table-column label="Classes" width="140" fixed>
          <template v-slot="scope">{{ scope.row.displayName }}</template>
        </el-table-column>
        <el-table-column
          v-for="(period, columnIndex) in periods"
          :key="columnIndex"
          align="center"
          min-width="120"
        >
          <template #header>
            {{ period.title }}<br>
            <span style="font-size:9px">({{ period.start }} - {{ period.end }})</span>
          </template>
          <template v-slot="scope">
            <div
              class="slot"
              @click="openPopup(scope.$index, columnIndex)"
              v-html="getSlotData(scope.$index, columnIndex)"
            ></div>
          </template>
        </el-table-column>
      </el-table>
    </div>

    <!-- Teacher View -->
    <div v-if="viewMode === 'teacher'" id="printable-timetable">
      <h3 class="print-title">Timetable — Teacher View</h3>
      <el-table :data="teacherViewData" border stripe max-height="650" class="tt-table">
        <!-- Teacher name in leftmost column -->
        <el-table-column label="Teacher" width="150" align="center" fixed>
          <template v-slot="scope">
            <b>{{ scope.row.teacherName }}</b>
          </template>
        </el-table-column>
        <el-table-column
          v-for="(period, colIdx) in periods"
          :key="'p-' + colIdx"
          align="center"
          min-width="130"
        >
          <template #header>
            {{ period.title }}<br>
            <span style="font-size:9px">({{ period.start }} - {{ period.end }})</span>
          </template>
          <template v-slot="scope">
            <div class="slot-teacher" v-html="scope.row.periods[colIdx] || ''"></div>
          </template>
        </el-table-column>
      </el-table>
    </div>

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
  </div>
</template>

<script>
import SlotPopup from './components/SlotPopup.vue';
import Resource from '@/api/resource';

const teacherRes = new Resource('teachers');
const classRes = new Resource('classes');
const subjectRes = new Resource('subjects');
const periodRes = new Resource('periods');
const ttRes = new Resource('timetable');
const sectionRes = new Resource('sections');
const timetableSlotRes = new Resource('timetable-slots');

export default {
  name: 'TimeTable',
  components: { SlotPopup },
  data() {
    return {
      viewMode: 'class', // 'class' or 'teacher'
      classes: [],
      sections: [],
      tableData: [],
      periods: [],
      teachers: [],
      subjects: [],
      showPopup: false,
      selectedSlot: null,
      selectedSlotData: null,
      disabledTeachers: [],
      disabledSubjects: [],
      timetable: [],
    };
  },
  computed: {
    teacherViewData() {
      // Build teacher-indexed rows: each row = one teacher, columns = periods
      const map = {}; // teacherId -> { teacherName, periods: [html per period] }

      this.tableData.forEach((row, rowIndex) => {
        if (!this.timetable[rowIndex]) return;
        this.periods.forEach((period, colIndex) => {
          const slot = this.timetable[rowIndex][colIndex];
          if (!slot || !slot.teacher) return;

          const tid = slot.teacher;
          if (!map[tid]) {
            map[tid] = {
              teacherName: this.getTeacherNameById(tid),
              periods: this.periods.map(() => ''),
            };
          }
          const className = row.displayName;
          const subjectName = this.getSubjectNameById(slot.subject);
          map[tid].periods[colIndex] =
            '<b>' + subjectName + '</b><br><span style="font-size:10px">' + className + '</span>';
        });
      });

      return Object.values(map).sort((a, b) => a.teacherName.localeCompare(b.teacherName));
    },
  },
  created() {
    this.getTeachers();
    this.getClasses();
    this.getSubjects();
    this.getPeriods();
    this.getSections();
  },
  watch: {
    classes() { this.prepareTableData(); },
    sections() { this.prepareTableData(); },
    periods() {
      if (this.tableData.length && this.periods.length) this.setTimeTable();
    },
    tableData(newData) {
      if (newData.length && this.periods.length) this.setTimeTable();
    },
  },
  methods: {
    printTimetable() {
      const content = document.getElementById('printable-timetable').innerHTML;
      const title = this.viewMode === 'teacher' ? 'Timetable — Teacher View' : 'Timetable';
      const win = window.open('', '_blank');
      win.document.write(`<!DOCTYPE html>
<html>
<head>
  <title>${title}</title>
  <style>
    @page { size: A4 landscape; margin: 10mm; }
    body { font-family: Arial, sans-serif; font-size: 14px; }
    h3 { text-align: center; font-size: 20px; margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #333; padding: 8px 6px; text-align: center; font-size: 13px; }
    th { background: #f0f0f0; font-size: 13px; font-weight: bold; }
    b { font-size: 13px; }
    span { font-size: 11px; }
    .slot, .slot-teacher { min-height: 40px; }
    /* Hide Element UI wrappers, keep table content */
    .el-table__body-wrapper, .el-table__header-wrapper { overflow: visible !important; }
  </style>
</head>
<body>${content}</body>
</html>`);
      win.document.close();
      win.focus();
      setTimeout(() => { win.print(); win.close(); }, 400);
    },

    async getTeachers() {
      const { data } = await teacherRes.list();
      this.teachers = data.teachers.data;
    },
    async getClasses() {
      const { data } = await classRes.list();
      this.classes = data.classes.data;
      this.prepareTableData();
    },
    async getSubjects() {
      const { data } = await subjectRes.list();
      this.subjects = data.subjects.data;
    },
    async getPeriods() {
      const { data } = await periodRes.list();
      this.periods = data.periods.data;
    },
    async getSections() {
      const { data } = await sectionRes.list();
      this.sections = data.sections.data || [];
      this.prepareTableData();
    },
    prepareTableData() {
      if (!this.classes.length) return;

      const sectionsByClass = {};
      if (this.sections && this.sections.length) {
        this.sections.forEach(section => {
          if (!sectionsByClass[section.class_id]) sectionsByClass[section.class_id] = [];
          sectionsByClass[section.class_id].push(section);
        });
      }

      const tableData = [];
      this.classes.forEach(cls => {
        const classSections = sectionsByClass[cls.id] || [];
        if (classSections.length > 0) {
          classSections.forEach(section => {
            tableData.push({
              id: `section-${section.id}`,
              type: 'section',
              classId: cls.id,
              sectionId: section.id,
              displayName: `${cls.name} - ${section.name}`,
              refData: section,
            });
          });
        } else {
          tableData.push({
            id: `class-${cls.id}`,
            type: 'class',
            classId: cls.id,
            sectionId: null,
            displayName: cls.name,
            refData: cls,
          });
        }
      });

      this.tableData = tableData;
      if (this.tableData.length && this.periods.length) this.setTimeTable();
    },
    setTimeTable() {
      if (this.tableData.length > 0 && this.periods.length > 0) {
        this.timetable = this.tableData.map(() => this.periods.map(() => ({})));
        this.loadTimetableSlots();
      }
    },
    openPopup(rowIndex, columnIndex) {
      this.selectedSlot = { rowIndex, columnIndex };
      this.selectedSlotData = this.timetable[rowIndex][columnIndex] || {};
      this.updateDisabledOptions(rowIndex, columnIndex);
      this.showPopup = true;
    },
    closePopup() {
      this.showPopup = false;
      this.selectedSlot = null;
      this.selectedSlotData = null;
    },
    async saveSlot(data) {
      const { rowIndex, columnIndex } = this.selectedSlot;
      const currentRow = this.tableData[rowIndex];
      const currentPeriod = this.periods[columnIndex];

      const requestData = {
        class_id: currentRow.classId,
        section_id: currentRow.sectionId,
        period_id: currentPeriod.id,
        subject_id: data.subject,
        teacher_id: data.teacher,
        day_of_week: 'monday',
      };

      try {
        let response;
        const existingSlot = this.timetable[rowIndex] && this.timetable[rowIndex][columnIndex];

        if (existingSlot && existingSlot.id) {
          response = await timetableSlotRes.update(existingSlot.id, requestData);
        } else {
          response = await timetableSlotRes.store(requestData);
        }

        if (!this.timetable[rowIndex]) this.timetable[rowIndex] = [];

        const slotData = response.slot || response.data?.slot;
        this.timetable[rowIndex][columnIndex] = {
          id: slotData.id,
          subject: data.subject,
          teacher: data.teacher,
          subject_id: data.subject,
          teacher_id: data.teacher,
          class_id: currentRow.classId,
          section_id: currentRow.sectionId,
          period_id: currentPeriod.id,
          day_of_week: 'monday',
        };

        this.$forceUpdate();
        this.closePopup();
        this.saveTimetable();
        this.$message.success('Timetable slot saved successfully');
      } catch (error) {
        console.error('Error saving slot:', error);
        this.$message.error('Failed to save timetable slot');
      }
    },
    async loadTimetableSlots() {
      try {
        if (!this.tableData || this.tableData.length === 0) return;
        if (!this.periods || this.periods.length === 0) return;

        const response = await timetableSlotRes.list();
        let slots = [];

        if (response && response.slots) {
          slots = response.slots;
        } else if (response && response.data && response.data.slots) {
          slots = response.data.slots;
        } else if (response && response.data && Array.isArray(response.data)) {
          slots = response.data;
        }

        slots.forEach(slot => {
          const rowIndex = this.tableData.findIndex(row => {
            if (slot.section_id) {
              return row.type === 'section' && row.sectionId === slot.section_id;
            } else {
              return row.type === 'class' && row.classId === slot.class_id && !row.sectionId;
            }
          });
          const columnIndex = this.periods.findIndex(p => p.id === slot.period_id);

          if (rowIndex !== -1 && columnIndex !== -1) {
            this.timetable[rowIndex][columnIndex] = {
              id: slot.id,
              subject: slot.subject_id,
              teacher: slot.teacher_id,
              subject_id: slot.subject_id,
              teacher_id: slot.teacher_id,
              class_id: slot.class_id,
              section_id: slot.section_id,
              period_id: slot.period_id,
              day_of_week: slot.day_of_week,
            };
          }
        });
      } catch (error) {
        console.error('Error loading timetable slots:', error);
      }
    },
    isTimetableEmpty() {
      if (!this.timetable || this.timetable.length === 0) return true;
      for (const row of this.timetable) {
        if (row && Array.isArray(row)) {
          for (const slot of row) {
            if (slot && (slot.subject || slot.teacher)) return false;
          }
        }
      }
      return true;
    },
    saveTimetable() {
      ttRes.update('1', { timetable: this.timetable });
    },
    getTeacherNameById(id) {
      const teacher = this.teachers.find(t => t.id === id);
      return teacher ? teacher.name : '';
    },
    getSubjectNameById(id) {
      const subject = this.subjects.find(s => s.id === id);
      return subject ? subject.title : '';
    },
    getSlotData(rowIndex, columnIndex) {
      if (!this.timetable || !this.timetable[rowIndex] || !this.timetable[rowIndex][columnIndex]) return '';
      const slot = this.timetable[rowIndex][columnIndex];
      if (!slot || !slot.teacher || !slot.subject) return '';
      return '<b>' + this.getSubjectNameById(slot.subject) +
        '</b><br><span style="font-size:10px">' + this.getTeacherNameById(slot.teacher) + '</span>';
    },
    updateDisabledOptions(rowIndex, columnIndex) {
      if (!this.timetable || !this.timetable[rowIndex]) {
        this.disabledTeachers = [];
        this.disabledSubjects = [];
        return;
      }

      const selectedTeachers = [];
      const selectedSubjects = [];
      const currentRow = this.tableData[rowIndex];

      for (let i = 0; i < this.tableData.length; i++) {
        if (i !== rowIndex && this.timetable[i] && this.timetable[i][columnIndex]) {
          const period = this.timetable[i][columnIndex];
          if (period && period.teacher) selectedTeachers.push(period.teacher);
        }
      }

      for (let i = 0; i < this.periods.length; i++) {
        if (i !== columnIndex && this.timetable[rowIndex] && this.timetable[rowIndex][i]) {
          const slot = this.timetable[rowIndex][i];
          if (slot && slot.subject) {
            const isSameClassSection = currentRow.type === 'section'
              ? (slot.section_id === currentRow.sectionId && slot.class_id === currentRow.classId)
              : (slot.class_id === currentRow.classId && !slot.section_id);
            if (isSameClassSection) selectedSubjects.push(slot.subject);
          }
        }
      }

      this.disabledTeachers = selectedTeachers;
      this.disabledSubjects = selectedSubjects;
    },
  },
};
</script>

<style>
.slot {
  cursor: pointer;
  height: 50px;
  text-align: center;
}
.slot-teacher {
  min-height: 40px;
  text-align: center;
}
.print-title {
  display: none;
}
@media print {
  .print-title {
    display: block;
    text-align: center;
    font-size: 22px;
    margin-bottom: 12px;
  }
}
</style>
