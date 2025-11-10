<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-row :gutter="20">
          <el-col :span="5">
            <el-form-item>
              <el-tree-select 
                check-strictly
                v-model="query.class" 
                :data="classes"
                placeholder="Select class/group..." 
                clearable 
                class="filter-item"
              />
            </el-form-item>
          </el-col>
          <el-col :span="5">
            <el-form-item>
              <el-date-picker
                v-model="query.month"
                type="month"
                format="MMM"
                value-format="YYYY-MM-DD"
                placeholder="Pick a month" 
              />
             </el-form-item>
          </el-col>
          <el-col :span="5">
            <el-button type="primary" :loading="loading" :disabled="!query.class || !query.month" @click="getReport()">
              {{ loading ? 'Submitting ...' : 'get report' }}
            </el-button>
          </el-col>
        </el-row>
      </head-controls>
    </div>
    
    <el-card class="box-card">
      <el-scrollbar max-height="700px">
        <table class="tblwdborder">
          <tr>
            <th>Student Name</th>
            <th v-for="(dayInfo, index) in dayHeaders" :key="index" 
                :class="{
                  'sunday-header': dayInfo.type === 'sunday',
                  'holiday-header': dayInfo.type === 'holiday'
                }">
              <div v-if="dayInfo.type === 'sunday'" class="vertical-text">Sunday</div>
              <div v-else-if="dayInfo.type === 'holiday'" class="vertical-text">{{ dayInfo.name }}</div>
              <div v-else>{{ dayInfo.day }}</div>
            </th>
            <th>%</th>
          </tr>
          <tr v-for="student in attendance.students" :key="student.id">
            <td>{{ student.name }}</td>
            <td v-for="att in student.attendances" 
                :key="att.id" 
                :class="{
                  'absent': (att === 'absent' || att === 'A'),
                  'present': (att === 'present' || att === 'P'),
                  'leave': (att === 'leave' || att === 'L'),
                  'sunday': (att === 'Sun'),
                  'not-marked': (att === '-'),
                  'holiday': isHoliday(formatAttendance(att))
                }">
              {{ formatAttendance(att) }}
            </td>
            <td class="percentage-cell">
              <strong>{{ getStudentPercentage(student) }}%</strong>
            </td>
          </tr>
        </table>
      </el-scrollbar>
    </el-card>

    <!-- Attendance Summary Section -->
    <el-card v-if="showSummary" class="summary-card">
      <template #header>
        <div class="card-header">
          <span style="font-weight: bold; font-size: 16px;">Attendance Summary</span>
        </div>
      </template>
      
      <el-table v-if="summaryData && summaryData.length > 0" :data="summaryData" :show-header="false" border style="width: 100%">
        <el-table-column prop="metric" label="Metric" width="250">
          <template #default="scope">
            <strong>{{ scope.row.metric }}</strong>
          </template>
        </el-table-column>
        <el-table-column prop="value" label="Value">
          <template #default="scope">
            <div v-if="scope.row.type === 'average'" style="font-size: 18px; color: #409EFF; font-weight: bold;">
              {{ scope.row.value }}
            </div>
            <div v-else-if="scope.row.type === 'highest'" style="color: #67C23A;">
              <div v-for="(student, index) in scope.row.students" :key="index" style="margin: 5px 0;">
                <el-tag type="success" effect="plain">{{ student.name }} - {{ student.percentage }}%</el-tag>
              </div>
            </div>
            <div v-else-if="scope.row.type === 'lowest'" style="color: #F56C6C;">
              <div v-for="(student, index) in scope.row.students" :key="index" style="margin: 5px 0;">
                <el-tag type="danger" effect="plain">{{ student.name }} - {{ student.percentage }}%</el-tag>
              </div>
            </div>
          </template>
        </el-table-column>
      </el-table>
      <div v-else style="padding: 20px; text-align: center; color: #909399;">
        No attendance data available for summary calculation.
      </div>
    </el-card>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import HeadControls from '@/components/HeadControls.vue';
import Resource from '@/api/resource';
import moment from 'moment';
import { debounce } from 'lodash';
import {studentAttMonthlyReport} from '@/api/attendance';
const classPro = new Resource('classes');
const attendPro = new Resource('attendance');
export default {
  name: '',
  components: { Pagination, HeadControls },
  directives: { },
  filters: {
    dateformat: (date) => {
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    },
  },
  data() {
    return {
      classes: [],
      studentclass: null,
      search: '',
      total: 0,
      loading: false,
      downloading: false,
      editnow: false,
      formLabelWidth: 250,
      attendance: {
        students: [],
        date: this.todayDate(),
      },
      query: {
        page: 1,
        limit: 100,
        class: '',
        month: '',
      },
      classquery: {
        stdclass: '',
      },
    };
  },
  computed: {
    dayHeaders() {
      if (!this.query.month) return [];
      
      const month = moment(this.query.month);
      const daysInMonth = month.daysInMonth();
      const headers = [];
      
      for (let day = 1; day <= daysInMonth; day++) {
        const currentDate = month.clone().date(day);
        const dayOfWeek = currentDate.day(); // 0 = Sunday, 1 = Monday, etc.
        
        if (dayOfWeek === 0) {
          headers.push({
            type: 'sunday',
            day: day,
            name: 'Sunday'
          });
        } else {
          headers.push({
            type: 'regular',
            day: day,
            name: currentDate.format('ddd')
          });
        }
      }
      
      return headers;
    },
    showSummary() {
      return this.attendance.students && Array.isArray(this.attendance.students) && this.attendance.students.length > 0;
    },
    studentRankings() {
      if (!this.attendance.students || this.attendance.students.length === 0) {
        return { top: [], bottom: [] };
      }

      const studentStats = this.attendance.students.map(student => {
        let present = 0, absent = 0, leave = 0;

        if (student.attendances && Array.isArray(student.attendances)) {
          student.attendances.forEach(att => {
            const formatted = this.formatAttendance(att);
            if (formatted !== 'Sun' && formatted !== '-') {
              if (formatted === 'P' || att === 'present') present++;
              else if (formatted === 'A' || att === 'absent') absent++;
              else if (formatted === 'L' || att === 'leave') leave++;
            }
          });
        }

        const total = present + absent + leave;
        const percentage = total > 0 ? ((present / total) * 100).toFixed(1) : 0;

        return {
          id: student.id,
          name: student.name,
          percentage: parseFloat(percentage),
          total
        };
      });

      const validStats = studentStats.filter(s => s.total > 0);
      
      if (validStats.length === 0) {
        return { top: [], bottom: [] };
      }
      
      const sortedStats = [...validStats].sort((a, b) => b.percentage - a.percentage);
      
      // Get top 3 (highest attendance)
      const top = sortedStats.slice(0, Math.min(3, sortedStats.length)).map((s, index) => ({ 
        ...s, 
        rank: index + 1 
      }));
      
      // Get bottom 3 (lowest attendance) - only if we have more than 3 students
      let bottom = [];
      if (sortedStats.length > 3) {
        const bottomStudents = sortedStats.slice(-3);
        bottom = bottomStudents.map((s, index) => ({ 
          ...s, 
          rank: 3 - index  // Reverse rank: worst is 1, third worst is 3
        }));
      }

      return { top, bottom };
    },
    summaryData() {
      if (!this.attendance.students || this.attendance.students.length === 0) {
        return [];
      }

      // Calculate attendance percentage for each student
      const studentStats = this.attendance.students.map(student => {
        let present = 0;
        let absent = 0;
        let leave = 0;

        if (student.attendances && Array.isArray(student.attendances)) {
          student.attendances.forEach(att => {
            const formatted = this.formatAttendance(att);
            // Ignore Sundays and not-marked (-)
            if (formatted !== 'Sun' && formatted !== '-') {
              if (formatted === 'P' || att === 'present') {
                present++;
              } else if (formatted === 'A' || att === 'absent') {
                absent++;
              } else if (formatted === 'L' || att === 'leave') {
                leave++;
              }
            }
          });
        }

        const total = present + absent + leave;
        const percentage = total > 0 ? ((present / total) * 100).toFixed(1) : 0;

        return {
          name: student.name,
          present,
          absent,
          leave,
          total,
          percentage: parseFloat(percentage)
        };
      });

      // Filter out students with no attendance records
      const validStats = studentStats.filter(s => s.total > 0);

      if (validStats.length === 0) {
        return [];
      }

      // Calculate average attendance
      const totalPercentage = validStats.reduce((sum, s) => sum + s.percentage, 0);
      const averageAttendance = validStats.length > 0 
        ? (totalPercentage / validStats.length).toFixed(1) 
        : 0;

      // Sort by percentage and get top 3 and bottom 3
      const sortedStats = [...validStats].sort((a, b) => b.percentage - a.percentage);
      const topThree = sortedStats.slice(0, 3);
      const bottomThree = sortedStats.slice(-3).reverse();

      return [
        {
          metric: 'Average Attendance (Overall)',
          value: `${averageAttendance}%`,
          type: 'average'
        },
        {
          metric: 'Highest Attendance',
          students: topThree,
          type: 'highest'
        },
        {
          metric: 'Lowest Attendance',
          students: bottomThree,
          type: 'lowest'
        }
      ];
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
    async getReport() {
      this.loading = true;
      
      try {
        // Parse the selected class/section value
        let reportQuery = {
          month: this.query.month
        };
        
        const selectedValue = this.query.class.toString();
        
        if (selectedValue.startsWith('class_')) {
          // Extract class ID from class_X format
          const classId = selectedValue.split('_')[1];
          reportQuery.class = classId;
        } else if (selectedValue.startsWith('section_')) {
          // Extract section ID from section_X format  
          const sectionId = selectedValue.split('_')[1];
          reportQuery.section_id = sectionId;
          
          // Find the class ID for this section
          const selectedSection = this.findSectionById(sectionId);
          if (selectedSection) {
            reportQuery.class = selectedSection.class_id;
          }
        }
        
        const { data } = await studentAttMonthlyReport(reportQuery);
        
        // Convert object to array if needed
        let studentsArray = [];
        if (Array.isArray(data.students)) {
          studentsArray = data.students;
        } else if (typeof data.students === 'object' && data.students !== null) {
          // Convert object to array of values
          studentsArray = Object.values(data.students);
        }
        
        this.attendance.students = studentsArray;
      } catch (error) {
        console.error('Error fetching attendance report:', error);
        this.$message.error('Error fetching attendance report. Please try again.');
      }
      
      this.loading = false;
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
    formatAttendance(att) {
      if(att === 'absent') return 'A';
      if(att === 'present') return 'P';
      if(att === 'leave') return 'L';
      return att;
    },
    isHoliday(formattedAtt) {
      // Check if it's a holiday (not a standard attendance status or special day)
      const standardValues = ['-', 'P', 'A', 'L', 'Sun'];
      return !standardValues.includes(formattedAtt);
    },
    getStudentPercentage(student) {
      let present = 0, absent = 0, leave = 0;

      if (student.attendances && Array.isArray(student.attendances)) {
        student.attendances.forEach(att => {
          const formatted = this.formatAttendance(att);
          if (formatted !== 'Sun' && formatted !== '-') {
            if (formatted === 'P' || att === 'present') present++;
            else if (formatted === 'A' || att === 'absent') absent++;
            else if (formatted === 'L' || att === 'leave') leave++;
          }
        });
      }

      const total = present + absent + leave;
      return total > 0 ? ((present / total) * 100).toFixed(1) : 0;
    },
    getStudentRank(studentId) {
      const topStudent = this.studentRankings.top.find(s => s.id === studentId);
      if (topStudent) {
        return { rank: topStudent.rank, type: 'top' };
      }

      const bottomStudent = this.studentRankings.bottom.find(s => s.id === studentId);
      if (bottomStudent) {
        return { rank: bottomStudent.rank, type: 'bottom' };
      }

      return null;
    },
    
    todayDate() {
      var today = new Date();
      var dd = String(today.getDate()).padStart(2, '0');
      var mm = String(today.getMonth() + 1).padStart(2, '0');
      var yyyy = today.getFullYear();
      today = yyyy + '-' + mm + '-' + dd;
      return today;
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
        this.getList();
      } else {
        await resourcePro.store(this.model);
        this.editnow = false;
        this.getList();
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
  .tblwdborder {
  border-collapse: collapse;
  width: 100%;
  font-size: 12px;
}
.tblwdborder th {
  text-align: center;	
  border: 1px solid #0000001a;
  padding: 5px 3px;
  background-color: #f5f7fa;
  font-weight: bold;
  min-width: 25px;
}
.tblwdborder tr td, .tblwdborder tr th {
  border: 1px solid #0000001a;
  padding: 3px;
  text-align: center;
}
.tblwdborder tr:nth-child(odd) {
   background-color: #e1e0e061;
}
.tblwdborder tr td:first-child {
  text-align: left;
  padding-left: 8px;
  font-weight: 500;
  min-width: 150px;
}
.sunday-header {
  background-color: #fef0f0 !important;
  color: #f56c6c;
}
.holiday-header {
  background-color: #f0f2ff !important;
  color: #722ed1;
}
.vertical-text {
  writing-mode: vertical-rl;
  text-orientation: mixed;
  white-space: nowrap;
  font-size: 10px;
}
.absent {
  background: #ff4d4f;
  color: #fff;
  font-weight: bold;
}
.present {
  background: #52c41a;
  color: #fff;
  font-weight: bold;
}
.leave {
  background: #faad14;
  color: #fff;
  font-weight: bold;
}
.sunday {
  background: #d9d9d9;
  color: #666;
  font-style: italic;
}
.not-marked {
  background: #f5f5f5;
  color: #999;
  font-style: italic;
}
.holiday {
  background: #722ed1;
  color: #fff;
  font-size: 11px;
  font-weight: bold;
}

.summary-card {
  margin-top: 20px;
}

.summary-card .card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.summary-card :deep(.el-table) {
  font-size: 14px;
}

.summary-card :deep(.el-table td) {
  padding: 15px 10px;
}

.summary-card :deep(.el-tag) {
  font-size: 13px;
  padding: 8px 12px;
  margin: 3px 5px;
}

.percentage-cell {
  min-width: 80px !important;
  font-weight: bold;
}

.percentage-container {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  flex-wrap: nowrap;
}

.rank-tag {
  font-size: 11px !important;
  padding: 2px 6px !important;
  height: 20px !important;
  line-height: 16px !important;
}
</style>

