<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-row :gutter="20">
          <el-col :span="5">
            <el-form-item>
              <el-select v-model="query.class" placeholder="Select class">
                <el-option
                  v-for="stdclass in classes"
                  :key="stdclass.id"
                  :label="stdclass.name"
                  :value="stdclass.id"
                />
              </el-select>
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
            <el-button type="primary" :loading="loading"  @click="getReport()">
              {{ loading ? 'Submitting ...' : 'get report' }}
            </el-button>
          </el-col>
        </el-row>
      </head-controls>
    </div>
    <div class="table-container">
      <el-table 
        :data="tableData" 
        :span-method="spanMethod"
        border
        size="small"
        class="attendance-table">
        <el-table-column prop="studentName" label="Student Name" width="150" fixed="left"/>
        <el-table-column 
          v-for="day in 31" 
          :key="day"
          :prop="`day${day}`"
          :label="day.toString()"
          width="35"
          align="center"
          :class-name="getDayColumnClass(day)">
          <template #default="scope">
            <div 
              :data-status="getAttendanceStatus(scope.row[`day${day}`])"
              :class="{
                'absent': scope.row[`day${day}`] === 'A',
                'leave': scope.row[`day${day}`] === 'L',
                'not-marked': scope.row[`day${day}`] === '-',
                'sunday': scope.row[`day${day}`] === 'Sun',
                'holiday': isHoliday(scope.row[`day${day}`])
              }">
              <div 
                v-if="scope.row[`day${day}`] && (scope.row[`day${day}`] === 'Sun' || isHoliday(scope.row[`day${day}`]))"
                class="vertical-text">
                {{ scope.row[`day${day}`] === 'Sun' ? 'Sunday' : scope.row[`day${day}`] }}
              </div>
              <div v-else>{{ scope.row[`day${day}`] || '-' }}</div>
            </div>
          </template>
        </el-table-column>
      </el-table>
    </div>
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
    attend(att) {
      if(att === 'absent') return 'A';
      if(att === 'present') return 'P';
      if(att === 'leave') return 'L';
      return att;
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
        calendar: []
      },
      year: new Date().getFullYear(),
      month: new Date().getMonth() + 1,
      query: {
        page: 1,
        limit: 15,
        class: '',
        month: '',
      },
      classquery: {
        stdclass: '',
      },
    };
  },
  computed: {
    tableData() {
      if (!this.attendance.students || Object.keys(this.attendance.students).length === 0) {
        return [];
      }
      
      return Object.values(this.attendance.students).map(student => {
        const row = {
          id: student.id,
          studentName: student.name
        };
        
        // Add attendance data for each day (1-31)
        for (let day = 1; day <= 31; day++) {
          const attendanceIndex = day - 1;
          const att = student.attendances[attendanceIndex];
          row[`day${day}`] = this.formatAttendance(att);
        }
        
        return row;
      });
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
      const { data } = await classPro.list(this.query);
      this.classes = data.classes.data;
    },
    async getReport() {
      const { data } = await studentAttMonthlyReport(this.query);
      this.attendance.students = data.students;
      this.attendance.calendar = data.calendar || [];
      
      // Update year and month from query
      if (this.query.month) {
        const queryDate = new Date(this.query.month);
        this.year = queryDate.getFullYear();
        this.month = queryDate.getMonth() + 1;
      }
    },
    
    formatAttendance(att) {
      // Handle null, undefined, empty string, or similar cases
      if (att === null || att === undefined || att === '' || att === 'null' || att === 0 || att === '0') {
        return '-';
      }
      
      // Convert to string and trim whitespace
      const attendance = String(att).trim();
      
      // Handle empty strings after trimming
      if (attendance === '') {
        return '-';
      }
      
      // Handle specific cases
      if (attendance === '-') return '-';
      if (attendance === 'Sunday') return 'Sun';
      
      // Handle holidays (any text that's not a standard attendance status)
      if (attendance !== 'P' && attendance !== 'A' && attendance !== 'L' && 
          attendance !== 'present' && attendance !== 'absent' && attendance !== 'leave' &&
          attendance !== 'p' && attendance !== 'a' && attendance !== 'l') {
        // This is likely a holiday description, truncate if too long
        return attendance.length > 15 ? attendance.substring(0, 15) + '...' : attendance;
      }
      
      // Convert attendance status to display format (case-insensitive)
      const lowerAtt = attendance.toLowerCase();
      if (lowerAtt === 'absent' || lowerAtt === 'a') return 'A';
      if (lowerAtt === 'present' || lowerAtt === 'p') return 'P';
      if (lowerAtt === 'leave' || lowerAtt === 'l') return 'L';
      
      // For any other case, return as is (likely holiday or special day)
      return attendance;
    },
    
    isHoliday(formattedAtt) {
      // Check if it's a holiday (not a standard attendance status or special day)
      const standardValues = ['-', 'P', 'A', 'L', 'Sun'];
      return !standardValues.includes(formattedAtt);
    },
    
    spanMethod({ row, column, rowIndex, columnIndex }) {
      // Skip the first column (student name)
      if (columnIndex === 0) {
        return [1, 1];
      }
      
      // Get the day number from column property (e.g., "day1" -> 1)
      const dayNumber = parseInt(column.property.replace('day', ''));
      const cellValue = row[column.property];
      
      // Check if this is a Sunday or holiday
      if (cellValue === 'Sun' || this.isHoliday(cellValue)) {
        // Only span for the first row, hide for other rows
        if (rowIndex === 0) {
          return [this.tableData.length, 1]; // Span across all rows
        } else {
          return [0, 0]; // Hide this cell
        }
      }
      
      return [1, 1]; // Normal cell
    },
    
    getDayColumnClass(day) {
      // Add classes for styling Sunday and holiday columns
      const dayNumber = day;
      const currentDate = new Date(this.year, this.month - 1, dayNumber);
      const isSunday = currentDate.getDay() === 0;
      
      if (isSunday) {
        return 'sunday-column';
      }
      
      // Check if it's a holiday from calendar data
      const isHoliday = this.attendance.calendar && 
                       this.attendance.calendar.some(cal => 
                         new Date(cal.date).getDate() === dayNumber && cal.is_holiday === 1
                       );
      
      if (isHoliday) {
        return 'holiday-column';
      }
      
      return '';
    },
    
    getAttendanceStatus(value) {
      if (value === 'A') return 'absent';
      if (value === 'L') return 'leave';
      if (value === '-') return 'not-marked';
      if (value === 'Sun') return 'sunday';
      if (this.isHoliday(value)) return 'holiday';
      return 'present';
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
  
  /* Table container */
  .table-container {
    width: 100%;
    overflow-x: auto;
    margin-top: 10px;
  }
  
  /* Element Plus table styling */
  .attendance-table {
    width: 100%;
  }
  
  .attendance-table .el-table__cell {
    padding: 1px !important;
    height: 24px !important;
    line-height: 22px !important;
    font-size: 12px !important;
  }
  
  .attendance-table .el-table__header .el-table__cell {
    padding: 2px !important;
    height: 28px !important;
    line-height: 24px !important;
    font-size: 11px !important;
    font-weight: bold !important;
  }
  
  .attendance-table .el-table__body-wrapper {
    max-height: none !important;
  }
  
  /* Column-based styling for Sunday and holidays */
  .sunday-column .el-table__cell {
    background: #e6f3ff !important;
  }
  
  .holiday-column .el-table__cell {
    background: #fff2e6 !important;
  }
  
  /* Cell content styling based on attendance status */
  .attendance-table .el-table__row .el-table__cell:has([data-status="absent"]) {
    background: red !important;
    color: #fff !important;
  }
  
  .attendance-table .el-table__row .el-table__cell:has([data-status="leave"]) {
    background: orange !important;
    color: #fff !important;
  }
  
  .attendance-table .el-table__row .el-table__cell:has([data-status="not-marked"]) {
    background: #f0f0f0 !important;
    color: #999 !important;
    font-style: italic;
  }
  
  .attendance-table .el-table__row .el-table__cell:has([data-status="sunday"]) {
    background: #e6f3ff !important;
    color: #0066cc !important;
    font-weight: bold;
  }
  
  .attendance-table .el-table__row .el-table__cell:has([data-status="holiday"]) {
    background: #fff2e6 !important;
    color: #cc6600 !important;
    font-weight: bold;
  }

  /* Vertical text styling */
  .vertical-text {
    writing-mode: vertical-rl;
    text-orientation: mixed;
    height: 50px !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px !important;
    font-weight: bold;
    line-height: 1 !important;
    padding: 1px !important;
  }

  /* Sunday and holiday cell styling */
  .sunday {
    background: #e6f3ff;
    color: #0066cc;
    font-weight: bold;
    text-align: center;
    vertical-align: middle;
    font-size: 10px !important;
    padding: 1px !important;
  }

  .holiday {
    background: #fff2e6;
    color: #cc6600;
    font-weight: bold;
    text-align: center;
    font-size: 9px !important;
    vertical-align: middle;
    padding: 1px !important;
  }
  
  /* Absent cells */
  .absent {
    background: red;
    color: #fff;
    font-size: 11px !important;
    font-weight: bold;
    padding: 1px !important;
  }
  
  /* Leave cells */
  .leave {
    background: orange;
    color: #fff;
    font-size: 11px !important;
    font-weight: bold;
    padding: 1px !important;
  }
  
  /* Not marked cells */
  .not-marked {
    background: #f0f0f0;
    color: #999;
    font-style: italic;
    font-size: 10px !important;
    padding: 1px !important;
  }
</style>

