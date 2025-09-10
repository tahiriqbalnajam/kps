<template>
  <el-table :data="tableData" border stripe max-height="650">
    <!-- Column for Class/Section Names -->
    <el-table-column label="Classes" width="120" fixed>
      <template v-slot="scope">
        {{ scope.row.displayName }}
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
const sectionRes = new Resource('sections');
const timetableSlotRes = new Resource('timetable-slots');

export default {
  name: 'TimeTable',
  components: { SlotPopup },
  data() {
    return {
      classes: [],
      sections: [],
      tableData: [], // Combined classes and sections data for display
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
    this.getSections();
  },
  mounted() {
    // Table initialization will happen through watchers when data is ready
  },
  watch: {
    classes() {
      this.prepareTableData();
    },
    sections() {
      this.prepareTableData();
    },
    periods() {
      if (this.tableData.length && this.periods.length) {
        this.setTimeTable();
      }
    },
    tableData(newTableData) {
      if (newTableData.length && this.periods.length) {
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
      // Only proceed if both classes and sections are loaded
      if (!this.classes.length) return;
      
      let tableData = [];
      
      // Group sections by class_id
      const sectionsByClass = {};
      if (this.sections && this.sections.length) {
        this.sections.forEach(section => {
          if (!sectionsByClass[section.class_id]) {
            sectionsByClass[section.class_id] = [];
          }
          sectionsByClass[section.class_id].push(section);
        });
      }
      
      // Create table rows for each class and its sections
      this.classes.forEach(cls => {
        const classSections = sectionsByClass[cls.id] || [];
        
        if (classSections.length > 0) {
          // Add each section as a row
          classSections.forEach(section => {
            tableData.push({
              id: `section-${section.id}`,
              type: 'section',
              classId: cls.id,
              sectionId: section.id,
              displayName: `${cls.name} - ${section.name}`,
              refData: section
            });
          });
        } else {
          // Add the class itself as a row
          tableData.push({
            id: `class-${cls.id}`,
            type: 'class',
            classId: cls.id,
            sectionId: null,
            displayName: cls.name,
            refData: cls
          });
        }
      });
      
      this.tableData = tableData;
      
      console.log('Table data prepared:', this.tableData.length, 'rows');
      console.log('Periods available:', this.periods.length);
      
      // Initialize timetable after preparing tableData
      if (this.tableData.length && this.periods.length) {
        this.setTimeTable();
      }
    },
    setTimeTable() {
      // Initialize with empty objects to prevent undefined errors
      if (this.tableData.length > 0 && this.periods.length > 0) {
        console.log('Setting up timetable matrix:', this.tableData.length, 'x', this.periods.length);
        this.timetable = this.tableData.map(() => this.periods.map(() => ({})));
        // Load slots after timetable structure is ready
        console.log('Loading timetable slots now...');
        this.loadTimetableSlots();
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
    async saveSlot(data) {
      const { rowIndex, columnIndex } = this.selectedSlot;
      const currentRow = this.tableData[rowIndex];
      const currentPeriod = this.periods[columnIndex];
      
      // Prepare slot data for API
      const requestData = {
        class_id: currentRow.classId,
        section_id: currentRow.sectionId,
        period_id: currentPeriod.id,
        subject_id: data.subject,
        teacher_id: data.teacher,
        day_of_week: 'monday' // For now, using Monday. You might want to add day selection
      };

      try {
        let response;
        
        // Check if slot already exists
        const existingSlot = this.timetable[rowIndex] && this.timetable[rowIndex][columnIndex];
        
        if (existingSlot && existingSlot.id) {
          // Update existing slot
          response = await timetableSlotRes.update(existingSlot.id, requestData);
          console.log('Updated slot response:', response);
        } else {
          // Create new slot
          response = await timetableSlotRes.store(requestData);
          console.log('Created slot response:', response);
        }
        
        // Ensure timetable structure exists
        if (!this.timetable[rowIndex]) {
          this.timetable[rowIndex] = [];
        }
        
        // Update local timetable with the response data
        // Handle different response structures
        const slotData = response.slot || response.data?.slot;
        console.log('Slot data to save:', slotData);
        console.log('Saving to position:', { rowIndex, columnIndex });
        
        this.timetable[rowIndex][columnIndex] = {
          id: slotData.id,
          subject: data.subject,
          teacher: data.teacher,
          subject_id: data.subject,
          teacher_id: data.teacher,
          class_id: currentRow.classId,
          section_id: currentRow.sectionId,
          period_id: currentPeriod.id,
          day_of_week: 'monday'
        };
        
        console.log('Updated timetable[' + rowIndex + '][' + columnIndex + ']:', this.timetable[rowIndex][columnIndex]);
        
        // Force reactivity update
        this.$forceUpdate();
        
        // Close popup and show success message
        this.closePopup();
        this.saveTimetable(); // Save for backward compatibility
        
        this.$message.success('Timetable slot saved successfully');
      } catch (error) {
        console.error('Error saving slot:', error);
        this.$message.error('Failed to save timetable slot');
      }
    },
    async getTimeTable() {
      try {
        // Try to load from new timetable_slots structure first
        await this.loadTimetableSlots();
        
        // Fallback to old structure if needed
        const data = await ttRes.list();
        
        if (data && data.timetable && data.timetable.timetable) {
          const parsedData = JSON.parse(data.timetable.timetable);
          
          // Only use old data if new structure is empty
          if (this.isTimetableEmpty() && Array.isArray(parsedData) && parsedData.length > 0) {
            if (parsedData.length !== this.tableData.length || 
                (parsedData[0] && parsedData[0].length !== this.periods.length)) {
              this.setTimeTable();
            } else {
              this.timetable = parsedData;
            }
          }
        } else if (this.isTimetableEmpty()) {
          this.setTimeTable();
        }
      } catch (error) {
        console.error("Error loading timetable:", error);
        this.setTimeTable();
      }
    },
    
    async loadTimetableSlots() {
      try {
        // Validate that table structure is ready
        if (!this.tableData || this.tableData.length === 0) {
          console.log('TableData not ready, skipping slot loading');
          return;
        }
        
        if (!this.periods || this.periods.length === 0) {
          console.log('Periods not ready, skipping slot loading');
          return;
        }
        
        console.log('Loading slots with tableData:', this.tableData.length, 'periods:', this.periods.length);
        
        // Load all timetable slots
        const response = await timetableSlotRes.list();
        console.log('Timetable slots response:', response); // Debug log
        
        // Handle different possible response structures
        let slots = [];
        console.log('Full response structure:', response); // More detailed debug
        
        if (response && response.slots) {
          slots = response.slots;
          console.log('Found slots in response.slots');
        } else if (response && response.data && response.data.slots) {
          slots = response.data.slots;
          console.log('Found slots in response.data.slots');
        } else if (response && response.data && Array.isArray(response.data)) {
          slots = response.data;
          console.log('Found slots in response.data (array)');
        } else {
          console.log('No slots found in expected locations');
        }
        
        console.log('Processed slots:', slots); // Debug log
        console.log('Current tableData:', this.tableData); // Debug log
        console.log('Current periods:', this.periods); // Debug log
        
        // Populate the timetable matrix with the loaded slots
        slots.forEach((slot, index) => {
          console.log(`Processing slot ${index}:`, slot); // Debug each slot
          
          // Find the row index for this class/section
          const rowIndex = this.tableData.findIndex(row => {
            if (slot.section_id) {
              const match = row.type === 'section' && row.sectionId === slot.section_id;
              console.log(`Checking section match for slot ${index}: row.sectionId=${row.sectionId}, slot.section_id=${slot.section_id}, match=${match}`);
              return match;
            } else {
              const match = row.type === 'class' && row.classId === slot.class_id && !row.sectionId;
              console.log(`Checking class match for slot ${index}: row.classId=${row.classId}, slot.class_id=${slot.class_id}, match=${match}`);
              return match;
            }
          });
          
          // Find the column index for this period
          const columnIndex = this.periods.findIndex(period => period.id === slot.period_id);
          
          console.log(`Slot ${index} mapping: rowIndex=${rowIndex}, columnIndex=${columnIndex}`);
          
          if (rowIndex !== -1 && columnIndex !== -1) {
            console.log(`Setting timetable[${rowIndex}][${columnIndex}] with slot:`, slot);
            this.timetable[rowIndex][columnIndex] = {
              id: slot.id,
              subject: slot.subject_id,
              teacher: slot.teacher_id,
              subject_id: slot.subject_id,
              teacher_id: slot.teacher_id,
              class_id: slot.class_id,
              section_id: slot.section_id,
              period_id: slot.period_id,
              day_of_week: slot.day_of_week
            };
          } else {
            console.log(`Slot ${index} not mapped: rowIndex=${rowIndex}, columnIndex=${columnIndex}`);
          }
        });
      } catch (error) {
        console.error("Error loading timetable slots:", error);
      }
    },
    
    isTimetableEmpty() {
      if (!this.timetable || this.timetable.length === 0) return true;
      
      for (let row of this.timetable) {
        if (row && Array.isArray(row)) {
          for (let slot of row) {
            if (slot && (slot.subject || slot.teacher)) {
              return false;
            }
          }
        }
      }
      return true;
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
      const currentRow = this.tableData[rowIndex];

      // Check horizontally: if a teacher is already assigned for this period
      for (let i = 0; i < this.tableData.length; i++) {
        // Don't disable the currently selected teacher for the same row
        if (i !== rowIndex && this.timetable[i] && this.timetable[i][columnIndex]) {
          const period = this.timetable[i][columnIndex];
          if (period && period.teacher) selectedTeachers.push(period.teacher);
        }
      }
      
      // Check vertically: if a subject is already assigned to this class/section for the day
      // Only check within the same class/section combination
      for (let i = 0; i < this.periods.length; i++) {
        // Don't disable the currently selected subject for the same slot
        if (i !== columnIndex && this.timetable[rowIndex] && this.timetable[rowIndex][i]) {
          const slot = this.timetable[rowIndex][i];
          if (slot && slot.subject) {
            // Check if this is the same class/section combination
            const currentSlot = this.timetable[rowIndex][columnIndex];
            const isSameClassSection = currentRow.type === 'section' 
              ? (slot.section_id === currentRow.sectionId && slot.class_id === currentRow.classId)
              : (slot.class_id === currentRow.classId && !slot.section_id);
            
            if (isSameClassSection) {
              selectedSubjects.push(slot.subject);
            }
          }
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