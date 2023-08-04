<template>
  <div class="app-container">
    <div class="filter-container">
      <el-date-picker
        v-model="query.attendance_date"
        type="date"
        format="dd/MM/yyyy"
        value-format="yyyy-MM-dd"
        placeholder="Pick a day"
        :picker-options="pickerOptions"
      />
      <el-button type="primary" :loading="loading"  @click="getReport()">{{ loading ? 'Submitting ...' : 'Get report' }}</el-button>
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
      <el-table-column label="Absent/Leave" prop="absent" />
      <el-table-column label="Total" prop="total" />
      <el-table-column label="Present %">
        <template slot-scope="scope">
          {{ 100 - Math.round(scope.row.absent/scope.row.total*100) }}%
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>
<script>
import Resource from '@/api/resource';
import moment from 'moment';
const attendPro = new Resource('attendance');
import { getDailyClasswise } from '@/api/student';
export default {
  name: '',
  components: { },
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
    debounceInput: _.debounce(function (e) {
      this.getList();
    }, 500),
    getReport() {
      this.getAttendance();
    },
    async getAttendance() {
      const { data } = await getDailyClasswise(this.query);
      this.attendance = data.attendance;
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
        if (index === 3) {
          const totalabsent = this.attendance.map(item => item.absent).reduce((prev, curr) => prev + curr);
          const total = this.attendance.map(item => item.total).reduce((prev, curr) => prev + curr);
          const present = 100 - Math.round((totalabsent / total) * 100);
          sums[index] = 'Total Present: ' + present + '%';
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

