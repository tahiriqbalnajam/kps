<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-row :gutter="20">
          <el-col :span="5">
            <el-form-item>
                <el-tree-select 
                      check-strictly
                      v-model="query.stdclass" 
                      :data="classes"
                      placeholder="Class" clearable 
                      style="" class="filter-item" 
                      @change="getStudent"
                    />
            </el-form-item>
          </el-col>
          <el-col :span="5">
            <el-date-picker
              v-model="attendance.date"
              type="date"
              format="DD MMM, YYYY"
              value-format="YYYY-MM-DD"
              placeholder="Pick a day" 
              @change="getStudent" />
          </el-col>
          <el-col :span="3">
            <el-button 
              type="primary" 
              :loading="loading" 
              :disabled="attendance.students.length <= 0" 
              @click="submitAttendance"
            >
              {{ loading ? 'Submitting ...' : attendanceAlreadyMarked ? 'Update Attendance' : 'Save Attendance' }}
            </el-button>
          </el-col>
        </el-row>
      </head-controls>
    </div>
    
    <!-- Attendance Summary when already marked -->
    <div v-if="attendanceAlreadyMarked && attendance.students.length > 0" class="attendance-summary">
      <el-card shadow="hover" style="margin-bottom: 20px;">
        <template #header>
          <span>Attendance Summary for {{ attendance.date }}</span>
        </template>
        <el-row :gutter="20">
          <el-col :span="6">
            <el-statistic title="Total Students" :value="attendance.students.length" />
          </el-col>
          <el-col :span="6">
            <el-statistic 
              title="Present" 
              :value="getPresentCount" 
              suffix="students"
              class="green-stat"
            />
          </el-col>
          <el-col :span="6">
            <el-statistic 
              title="Absent" 
              :value="getAbsentCount" 
              suffix="students"
              class="red-stat"
            />
          </el-col>
          <el-col :span="6">
            <el-statistic 
              title="On Leave" 
              :value="getLeaveCount" 
              suffix="students"
              class="orange-stat"
            />
          </el-col>
        </el-row>
      </el-card>
    </div>
    
    <el-table
      :data="filterTableData"
      style="width: 100%"
      max-height="500"
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
      <el-table-column label="Student Name" prop="name" />
      <el-table-column label="Father name" prop="parents.name" />
    </el-table>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import HeadControls from '@/components/HeadControls.vue';
import Resource from '@/api/resource';
const classPro = new Resource('classes');
const studentPro = new Resource('students');
const attendPro = new Resource('attendance');
import {studentAttMarked} from '@/api/attendance';
import { debounce } from 'lodash';
export default {
  name: 'StudentAttendance',
  components: { Pagination, HeadControls },
  directives: { },
  data() {
    return {
      student_loading: false,
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
    filterTableData() {
      return this.attendance.students.filter(
        (data) =>
          !this.search ||
          data.name.toLowerCase().includes(this.search.toLowerCase())
      )
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
  methods: {
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
      
      this.student_loading = true;
      
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
        // Remove stdclass if it exists from previous selection
        delete this.query.filter['stdclass'];
        
        // Find the class ID for this section (needed for attendance table)
        const selectedSection = this.findSectionById(sectionId);
        if (selectedSection) {
          classId = selectedSection.class_id;
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
<style  scoped>
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
  
  /* Statistics styling */
  .attendance-summary {
    margin-bottom: 20px;
  }
  
  .green-stat :deep(.el-statistic__number) {
    color: #67c23a;
  }
  
  .red-stat :deep(.el-statistic__number) {
    color: #f56c6c;
  }
  
  .orange-stat :deep(.el-statistic__number) {
    color: #e6a23c;
  }
</style>

