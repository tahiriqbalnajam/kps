<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-form-item>
          <el-col :span="4">
            <el-date-picker
              v-model="query.attendance_date"
              type="date"
              format="DD/MM/YYYY"
              value-format="YYYY-MM-DD"
              placeholder="Pick a day"
              :picker-options="pickerOptions"
            />
          </el-col>
          <el-col :span="2">
            <el-button type="primary" :loading="loading"  @click="getReport()">{{ loading ? 'Submitting ...' : 'Get report' }}</el-button>
          </el-col>
        </el-form-item>
      </head-controls>   
    </div>
    <el-table
      :data="attendance"
      style="width: 100%"
      :stripe="true"
      :border="true"
      :summary-method="getSummaries"
      show-summary
      empty-text="No data, Try an other date!" 
    >
      <el-table-column label="Class Name" prop="name" />
      <el-table-column label="Register student" prop="total_student" />
      <el-table-column label="Register girls" prop="total_female" />
      <el-table-column label="Register boys" prop="total_male" />
      <el-table-column label="Present student" prop="total_present" />
      <el-table-column label="Present girls" prop="female_present" />
      <el-table-column label="Present boys" prop="male_present" />
      <el-table-column label="Present boys" prop="male_present" />
      <el-table-column label="Absent student" prop="total_absent" />
      <el-table-column label="Absent boys" prop="male_absent" />
      <el-table-column label="Absent girls" prop="female_absent" />
      <el-table-column label="Class Attendance%">
        <template #default="scope">
          {{ scope.row.total_present ? (100 - Math.round(scope.row.total_absent / scope.row.total_student * 100)) + '%' : '' }}
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>
<script>
import Resource from '@/api/resource';
import moment from 'moment';
import { debounce } from 'lodash';
import HeadControls from '@/components/HeadControls.vue';
const attendPro = new Resource('attendance');
import { getDailyClasswise } from '@/api/student';
export default {
  name: '',
  components: { HeadControls},
  directives: { },
  filters: {
    dateformat: (date) => {
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    },
  },
  data() {
    return {
      pickerOptions: {
        disabledDate(time) {
          return time.getTime() > Date.now();
        },
        shortcuts: [{
          text: 'Today',
          onClick(picker) {
            picker.$emit('pick', new Date());
          },
        }, {
          text: 'Yesterday',
          onClick(picker) {
            const date = new Date();
            date.setTime(date.getTime() - 3600 * 1000 * 24);
            picker.$emit('pick', date);
          },
        }, {
          text: 'A week ago',
          onClick(picker) {
            const date = new Date();
            date.setTime(date.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', date);
          },

        }],
      },
      attendance: null,
      loading: false,
      formLabelWidth: 250,
      query: {
        attendance_date: this.todayDate(),
      },
    };
  },
  created() {
    this.getAttendance();
  },
  methods: {
    debounceInput: debounce(function (e) {
      this.getList();
    }, 500),
    getReport() {
      this.getAttendance();
    },
    async getAttendance() {
      const { data } = await getDailyClasswise(this.query);
      this.attendance = data.attendance;
      //console.log( this.attendance);
    },
    todayDate() {
      var today = new Date();
      var dd = String(today.getDate()).padStart(2, '0');
      var mm = String(today.getMonth() + 1).padStart(2, '0');
      var yyyy = today.getFullYear();
      today = yyyy + '-' + mm + '-' + dd;
      return today;
    },
    getSummaries(param) {
      const { columns, data } = param;
      const sums = [];
      columns.forEach((column, index) => {
        if (index === 0) {
          sums[index] = 'Total';
          return;
        }
        if (index === 11) {
          console.log( this.attendance);
          const totalabsent = this.attendance.map(item => item.total_absent).reduce((prev, curr) => prev + curr);
          const total = this.attendance.map(item => item.total_student).reduce((prev, curr) => prev + curr);
          const present = 100 - Math.round((totalabsent / total) * 100);
          sums[index] = present + '%';
          return;
        }
        const values = data.map(item => Number(item[column.property]));
        if (!values.every(value => isNaN(value))) {
          sums[index] = values.reduce((prev, curr) => {
            const value = Number(curr);
            if (!isNaN(value)) {
              return prev + curr;
            } else {
              return prev;
            }
          }, 0);
        } else {
          sums[index] = '';
        }
      });
      return sums;
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
}
.tblwdborder th {
  text-align: left;	
  border: 1px solid #0000001a;
  padding: 3px;
}
.tblwdborder tr td, .tblwdborder tr  th {
  border: 1px solid #0000001a;
  padding: 3px;
}
.tblwdborder tr:nth-child(odd) {
   background-color: #e1e0e061;
}
.absent {
  background: red;
  color:#fff;
}
</style>

