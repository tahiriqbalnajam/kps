<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls class="filter-card">
        <el-row :gutter="16">
            <el-col :xs="24" :sm="8" :md="7" :lg="6">
              <el-tree-select
                check-strictly
                v-model="query.stdclass"
                :data="classes"
                placeholder="Class"
                clearable
                class="filter-item"
                style="width: 100%"
                @change="getStudent"
              />
            </el-col>
            <el-col :xs="24" :sm="8" :md="7" :lg="6">
              <el-date-picker
                v-model="attendance.date"
                type="date"
                format="DD MMM, YYYY"
                value-format="YYYY-MM-DD"
                placeholder="Pick a day"
                style="width: 100%"
                @change="getStudent" />
            </el-col>
            <el-col :xs="24" :sm="8" :md="7" :lg="6">
              <el-button
                type="primary"
                :loading="loading"
                :disabled="attendance.students.length <= 0 || isSelectedDateSunday"
                @click="submitAttendance"
              >
                {{ loading ? 'Submitting ...' : attendanceAlreadyMarked ? 'Update Attendance' : 'Save Attendance' }}
              </el-button>
            </el-col>
        </el-row>
      </head-controls>
    </div>
    
    <!-- Sunday Warning Message -->
    <div v-if="isSelectedDateSunday" class="sunday-warning">
      <el-alert
        title="Sunday Selected"
        type="warning"
        description="Attendance cannot be taken on Sundays. Please select a different date."
        show-icon
        :closable="false"
        style="margin-bottom: 20px;"
      />
    </div>
    
    <!-- Attendance Summary when already marked -->
    <div v-if="attendanceAlreadyMarked && attendance.students.length > 0" class="attendance-summary">
      <el-card shadow="never" class="summary-card">
        <div class="summary-row">
          <div class="summary-title">
            <el-icon class="summary-icon"><Calendar /></el-icon>
            <div>
              <div class="summary-heading">Attendance Summary</div>
              <div class="summary-date">{{ formatSummaryDate(attendance.date) }}</div>
            </div>
          </div>
          <div class="summary-stats">
            <div class="summary-stat">
              <span class="stat-value total">{{ attendance.students.length }}</span>
              <span class="stat-label">Total</span>
            </div>
            <div class="summary-stat">
              <span class="stat-value present">{{ getPresentCount }}</span>
              <span class="stat-label">Present</span>
            </div>
            <div class="summary-stat">
              <span class="stat-value absent">{{ getAbsentCount }}</span>
              <span class="stat-label">Absent</span>
            </div>
            <div class="summary-stat">
              <span class="stat-value leave">{{ getLeaveCount }}</span>
              <span class="stat-label">On Leave</span>
            </div>
          </div>
        </div>
      </el-card>
    </div>
    
    <el-card class="table-card" shadow="never">
    <el-table
      ref="table"
      :data="filterTableData"
      style="width: 100%"
      :max-height="tableMaxHeight"
      :stripe="true"
      :border="true"
      empty-text="Select a class first!"
      size="small"
      v-loading="student_loading"
    >
      <el-table-column label="Attendance Status" width="280">
        <template #header>
          <el-input v-model="search" size="small" placeholder="Type to search" />
        </template>
        <template  #default="scope">
          <el-radio-group 
            v-model="scope.row.attendance" 
            size="small" 
            text-color="" 
            :fill="(scope.row.attendance == 'present') ? '#67c23a' : (scope.row.attendance == 'absent') ? '#f56c6c' : '#909399'"
            :class="{ 'previously-marked': attendanceAlreadyMarked && scope.row.previousAttendance }"
          >
            <el-radio-button 
              label="Present" 
              value="present"
              :class="{ 'was-selected': scope.row.previousAttendance === 'present' }"
            />
            <el-radio-button 
              label="Absent" 
              value="absent"
              :class="{ 'was-selected': scope.row.previousAttendance === 'absent' }"
            />
            <el-radio-button 
              label="Leave" 
              value="leave"
              :class="{ 'was-selected': scope.row.previousAttendance === 'leave' }"
            />
          </el-radio-group>
        </template>
      </el-table-column>
      <el-table-column label="Roll No" prop="roll_no" width="100" />
      <el-table-column label="Student Name" prop="name" />
      <el-table-column label="Father name" prop="parents.name" />
    </el-table>
    </el-card>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import HeadControls from '@/components/HeadControls.vue';
import { Calendar } from '@element-plus/icons-vue';
import Resource from '@/api/resource';
const classPro = new Resource('classes');
const studentPro = new Resource('students');
const attendPro = new Resource('attendance');
import {studentAttMarked} from '@/api/attendance';
import { debounce } from 'lodash';
import { sessionStore } from '@/store/session'
export default {
  name: 'StudentAttendance',
  components: { Pagination, HeadControls, Calendar },
  directives: { },
  data() {
    return {
      student_loading: false,
      tableMaxHeight: 500,
      classes: [],
      attendance_day: 'Week day',
      search: '',
      total: 0,
      loading: false,
      downloading: false,
      editnow: false,
      formLabelWidth: 250,
      attendanceAlreadyMarked: false,
      attendance: {
        students: [],
        stdclass: '',
        date: this.todayDate(),
      },
      query: {
        page: 1,
        limit: 1000,
        keyword: '',
        role: '',
        filter: {},
      },
      classquery: {
        stdclass: '',
      },
      attenquery: {
        stdclass: '',
        month: '',
      },
      search: '',
    };
  },
  computed: {
    currentSessionId() { return sessionStore().currentSessionId },
    filterTableData() {
      return this.attendance.students.filter(
        (data) =>
          !this.search ||
          data.name.toLowerCase().includes(this.search.toLowerCase())
      )
    },
    
    isSelectedDateSunday() {
      if (!this.attendance.date) return false;
      const selectedDate = new Date(this.attendance.date);
      return selectedDate.getDay() === 0; // 0 = Sunday
    },
    
    getPresentCount() {
      return this.attendance.students.filter(student => 
        student.previousAttendance === 'present'
      ).length;
    },
    
    getAbsentCount() {
      return this.attendance.students.filter(student => 
        student.previousAttendance === 'absent'
      ).length;
    },
    
    getLeaveCount() {
      return this.attendance.students.filter(student => 
        student.previousAttendance === 'leave'
      ).length;
    }
  },
  created() {
    this.getList();
  },
  mounted() {
    this.updateTableHeight();
    window.addEventListener('resize', this.updateTableHeight);
  },
  beforeUnmount() {
    window.removeEventListener('resize', this.updateTableHeight);
  },
  methods: {
    // Fit the table to the remaining viewport below it (filter bar, summary
    // card and alerts above change height, so measure from the live position)
    updateTableHeight() {
      this.$nextTick(() => {
        const tableEl = this.$refs.table?.$el;
        if (!tableEl) return;
        const top = tableEl.getBoundingClientRect().top;
        this.tableMaxHeight = Math.max(200, Math.floor(window.innerHeight - top - 24));
      });
    },
    debounceInput: debounce(function (e) {
      this.getList();
    }, 500),
    async getList() {
      let query = {
        include: 'sections'
      };
      const{ data } = await classPro.list(query);
      // Transform the classes data to include class and section hierarchy for tree select
      this.classes = data.classes.data.map(classItem => {
        // Create the parent class node
        const classNode = {
          value: `class_${classItem.id}`,
          label: `${classItem.name}`,
          id: classItem.id,
          type: 'class',
          name: classItem.name,
          students_count: classItem.students_count,
          males_count: classItem.males_count,
          females_count: classItem.females_count
        };
        
        // Add children if there are sections
        if (classItem.sections && classItem.sections.length > 0) {
          classNode.children = classItem.sections.map(section => ({
            value: `section_${section.id}`,
            label: `${section.name}`,
            id: section.id,
            type: 'section',
            class_id: classItem.id,
            name: section.name,
            students_count: section.students_count,
            males_count: section.males_count,
            females_count: section.females_count
          }));
        }
        
        return classNode;
      });
      
    },
    todayDate() {
      var today = new Date();
      var dd = String(today.getDate()).padStart(2, '0');
      var mm = String(today.getMonth() + 1).padStart(2, '0');
      var yyyy = today.getFullYear();
      today = yyyy + '-' + mm + '-' + dd;
      return today;
    },
    async getStudent() {
      // Reset attendance marked flag when getting new students
      this.attendanceAlreadyMarked = false;
      
      if (!this.query.stdclass) {
        // Clear students if no class is selected
        this.attendance.students = [];
        this.attendance.stdclass = '';
        return;
      }
      
      // Check if selected date is Sunday
      if(this.isSelectedDateSunday) {
        this.$notify({
          title: 'Sunday Selected',
          message: 'Attendance cannot be taken on Sundays. Please select a different date.',
          type: 'warning',
          duration: 5000
        });
        this.attendance.students = [];
        this.attendance.stdclass = '';
        return;
      }
      
      this.student_loading = true;
      if (this.currentSessionId) this.query.filter['session_id'] = this.currentSessionId
      else delete this.query.filter['session_id']

      const selectedValue = this.query.stdclass.toString();
      let classId = null;
      
      if (selectedValue.startsWith('class_')) {
        // Extract class ID from class_X format
        classId = selectedValue.split('_')[1];
        this.query.filter['stdclass'] = classId;
        // Remove section_id if it exists from previous selection
        delete this.query.filter['section_id'];
      } else if (selectedValue.startsWith('section_')) {
        // Extract section ID from section_X format
        const sectionId = selectedValue.split('_')[1];
        this.query.filter['section_id'] = sectionId;

        // Find the class ID for this section (needed for attendance table)
        const selectedSection = this.findSectionById(sectionId);
        if (selectedSection) {
          classId = selectedSection.class_id;
          // Also require the section's class so students whose section_id is
          // stale (not updated after a class promotion) don't show up under
          // the wrong class's section list.
          this.query.filter['stdclass'] = classId;
        } else {
          // No stdclass from a previous selection should leak through
          delete this.query.filter['stdclass'];
        }
      }
      
      // Set the class ID for attendance (always store class ID, not section ID)
      this.attendance.stdclass = classId;
      this.attenquery.stdclass = classId;
      
      if (!classId) {
        this.$message.error('Unable to determine class ID. Please select a valid class or section.');
        this.student_loading = false;
        return;
      }
      
      this.query.filter.status = 'enable';
      this.query.fields = 'id,name,roll_no,class_id,parent_id';
      
      try {
        const { data } = await studentPro.list(this.query);
        this.attenquery.month = this.attendance.date;
        // Scope the already-marked check to exactly the displayed students so
        // a group selection only flags records for its own students
        this.attenquery.student_ids = data.students.data.map((s) => s.id);
        const attenDD = await studentAttMarked(this.attenquery);
        const hasrec = Object.keys(attenDD.data.attendance).length;
        
        // Check if attendance is already marked
        this.attendanceAlreadyMarked = hasrec > 0;
        
        if(this.attendanceAlreadyMarked) {
          // Show more informative message
          this.$notify({
            title: 'Attendance Already Marked',
            message: `Attendance for the selected class/section on ${this.attendance.date} has already been recorded. You can view and modify the existing attendance records below.`,
            type: 'warning',
            duration: 5000
          });
        }
        
        this.attendance.students = data.students.data.map(std => {
          const atten = attenDD.data.attendance.find(att => att.student_id == std.id);
          if(atten) {
            return { 
              ...std, 
              'attendance': atten.status[0] + atten.status.slice(1),
              'previousAttendance': atten.status[0] + atten.status.slice(1)
            };
          }
          return { 
            ...std, 
            'attendance': 'present',
            'previousAttendance': null
          };
        });
      } catch (error) {
        console.error('Error fetching student data:', error);
        this.$message.error('Error fetching student data. Please try again.');
      }
      
      this.student_loading = false;
      // Summary card / alert may have appeared above the table — re-fit
      this.updateTableHeight();
    },

    formatSummaryDate(d) {
      if (!d) return '';
      const dt = new Date(d + 'T00:00:00');
      return dt.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    },

    // Helper method to find section by ID and get its class_id
    findSectionById(sectionId) {
      for (const classItem of this.classes) {
        if (classItem.children) {
          const section = classItem.children.find(section => section.id == sectionId);
          if (section) {
            return section;
          }
        }
      }
      return null;
    },
    
    async search_data() {
      await this.getList();
    },
    async handleEdit(id, name) {
      const { data } = await resourcePro.get(id);
      this.model = data.model;
      this.editnow = true;
    },
    async handleDelete(id, name) {
      this.confirm('Do you really want to delete?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
    }).then(async () => {
        await resourcePro.destroy(id);
        this.getList();
        this.message({
          type: 'success',
          message: name+' Delete successfully'
        });
      })
    },
    async onSubmit() {
      if(this.model.id != '') {
        await resourcePro.update(this.model.id, this.model);
        this.editnow = false;
        this.getreport();
      } else {
        await resourcePro.store(this.model);
        this.editnow = false;
        this.getList();
      }
    },
    async submitAttendance(){
      if(this.attendance.students.length <= 0) {
        this.$message.error('Kindly select a class first.');
        return;
      }
      
      if(!this.attendance.stdclass) {
        this.$message.error('Class ID is missing. Please reselect the class.');
        return;
      }
      
      // Check if selected date is Sunday
      if(this.isSelectedDateSunday) {
        this.$message.error('Attendance cannot be taken on Sundays. Please select a different date.');
        return;
      }

      this.loading = true;
      
      try {
        // Show different messages for new vs existing attendance
        const action = this.attendanceAlreadyMarked ? 'updated' : 'added';
        
        await attendPro.store(this.attendance);
        this.$message.success(`Attendance ${action} successfully.`);
        
        // Reset attendance marked flag and refresh data
        this.attendanceAlreadyMarked = false;
        this.getStudent(); // Refresh to show updated status
      } catch (error) {
        console.error('Error submitting attendance:', error);
        this.$message.error('Error submitting attendance. Please try again.');
      }
      
      this.loading = false;
    },
    
    // Helper method to get tag type for status
    getStatusTagType(status) {
      switch(status?.toLowerCase()) {
        case 'present':
          return 'success';
        case 'absent':
          return 'danger';
        case 'leave':
          return 'warning';
        default:
          return 'info';
      }
    },
  },
};
</script>
<style scoped lang="scss">
  .table-card {
    border: 1px solid #e4e7ed;
    border-radius: 8px;

    :deep(.el-card__body) {
      padding: 12px 16px;
    }
  }

  .filter-card {
    margin-bottom: 16px;
    border: 1px solid #e4e7ed;
    border-radius: 8px;

    // HeadControls renders an el-card whose body is padded via an inline
    // style (padding: 15px 0 0 15px) that no normal rule can override, so the
    // el-row gutter's negative right margin overflows it and the body's
    // baked-in overflow:auto paints a horizontal scrollbar. Force the right
    // padding past the inline style and clip as a backstop.
    :deep(.el-card__body) {
      padding-right: 16px !important;
      overflow-x: hidden;
    }
  }
  .el-drawer__body {
    flex: 1;
    padding: 20px;
  }
  .demo-drawer__content {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 20px;
  }
  .weekday.is-active {
    border-color: #42b983;
    &.el-radio__label{
      color:#42b983;
    } 
  } 
  .sunday.is-active {
    border-color: #f56c6c;
    &.el-radio__label{
      color:#f56c6c;
    } 
  } 
  .holliday.is-active {
    border-color: #909399;
    &.el-radio__label{
      color:#909399;
    } 
  }
  
  /* Styles for previously marked attendance */
  .previously-marked {
    border: 1px dashed #e6a23c;
    border-radius: 4px;
    padding: 2px;
    background-color: #fdf6ec;
  }
  
  .was-selected {
    position: relative;
  }
  
  .was-selected::before {
    content: "✓";
    position: absolute;
    top: -5px;
    right: -5px;
    color: #e6a23c;
    font-size: 12px;
    font-weight: bold;
  }
  
  /* Attendance summary — compact single row matching the filter card above */
  .attendance-summary {
    margin-bottom: 16px;
  }

  .summary-card {
    border: 1px solid #e4e7ed;
    border-radius: 8px;

    :deep(.el-card__body) {
      padding: 10px 16px;
    }
  }

  .summary-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px 16px;
  }

  .summary-title {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .summary-icon {
    font-size: 18px;
    color: #4f46e5;
    background: #eef2ff;
    border-radius: 8px;
    padding: 5px;
  }

  .summary-heading {
    font-size: 14px;
    font-weight: 600;
    color: #1f2937;
    line-height: 1.2;
  }

  .summary-date {
    font-size: 12px;
    color: #6b7280;
    line-height: 1.4;
  }

  .summary-stats {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }

  .summary-stat {
    display: flex;
    align-items: baseline;
    gap: 6px;
    background: #f9fafb;
    border: 1px solid #eef0f4;
    border-radius: 6px;
    padding: 4px 10px;
  }

  .stat-value {
    font-size: 16px;
    font-weight: 700;
    line-height: 1;
  }

  .stat-label {
    font-size: 12px;
    color: #6b7280;
  }

  .stat-value.total { color: #4f46e5; }
  .stat-value.present { color: #10b981; }
  .stat-value.absent { color: #f43f5e; }
  .stat-value.leave { color: #f59e0b; }
</style>

