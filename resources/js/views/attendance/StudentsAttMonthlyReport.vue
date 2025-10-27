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
    <el-scrollbar height="700px">
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
        </tr>
      </table>
    </el-scrollbar>
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
</style>

