<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-row justify="space-between">
          <el-col :span="8">
            <el-row :gutter="20">
              <el-col :span="8">
                <el-form-item>
                  <el-date-picker
                    v-model="query.attendance_date"
                    type="date"
                    format="DD/MM/YYYY"
                    value-format="YYYY-MM-DD"
                    placeholder="Pick a day"
                    :picker-options="pickerOptions"
                  />
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-form-item label="Pef Students">
                  <el-switch
                    v-model="query.pef_admission"
                    inline-prompt
                    :active-icon="Check"
                    :inactive-icon="Close"
                  />
                </el-form-item>
              </el-col>
              <el-col :span="2">
                <el-button type="primary" :loading="loading"  @click="getReport()">{{ loading ? 'Submitting ...' : 'Get report' }}</el-button>
              </el-col>
            </el-row>
          </el-col>
        </el-row>
      </head-controls>   
    </div>
    <el-table
      :loading="loading"
      :data="attendance"
      style="width: 100%"
      :stripe="true"
      :border="true"
      :summary-method="getSummaries"
      show-summary
      empty-text="No data, Try an other date!" 
    >
      <el-table-column label="Class Name" prop="class_title" />
      <el-table-column label="Total Students" prop="total_students" />
      <el-table-column label="Total Boys" prop="total_male" />
      <el-table-column label="Total Girls" prop="total_female" />
      <el-table-column label="Total Present" prop="total_present" />
      <el-table-column label="Total Absent" prop="total_absent" />
      <el-table-column label="Present Boys" prop="total_male_present" />
      <el-table-column label="Absent Boys" prop="total_male_absent" />
      <el-table-column label="Present Girls" prop="total_female_present" />
      <el-table-column label="Absent Girls" prop="total_female_absent" />
      <el-table-column label="Absent %" prop="absent_percentage" />
      <el-table-column label="Present %" prop="present_percentage" />
    </el-table>
  </div>
</template>
<script>
import Resource from '@/api/resource';
import moment from 'moment';
import { debounce } from 'lodash';
import HeadControls from '@/components/HeadControls.vue';
const attendPro = new Resource('attendance');
import { getDailyClasswise } from '@/api/attendance';
import { Check, Close } from '@element-plus/icons-vue'
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
        pef_admission: false,
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
      this.loading = true;
      const { data } = await getDailyClasswise(this.query);
      this.loading = false;
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
        if (index === 10) {
          
          const totalabsentpercent = this.attendance.map(item => parseFloat(item.absent_percentage)).reduce((prev, curr) => prev + curr);
          const present = totalabsentpercent / this.attendance.length;
          sums[index] = Math.round((present + Number.EPSILON) * 100) / 100 + '%';
          return;
        }
        if (index === 11) {
          
          const totalabsentpercent = this.attendance.map(item => parseFloat(item.present_percentage)).reduce((prev, curr) => prev + curr);
          const present = totalabsentpercent / this.attendance.length;
          sums[index] = Math.round((present + Number.EPSILON) * 100) / 100 + '%';
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

