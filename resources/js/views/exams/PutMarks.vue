<template>
  <div class="app-container">
    <div class="filter-container">
      <el-select v-model="studentclass" placeholder="Select class" @change="getStudent">
        <el-option
          v-for="stdclass in classes"
          :key="stdclass.id"
          :label="stdclass.name"
          :value="stdclass.id"
        />
      </el-select>
      <el-date-picker
        v-model="attendance.date"
        type="date"
        format="dd MMM, yyyy"
        value-format="yyyy-MM-dd"
        placeholder="Pick a day" />
      <el-button type="primary" :loading="loading" :disabled="attendance.students.length <= 0" @click="submitAttendance">{{ loading ? 'Submitting ...' : 'Save Attendance' }}</el-button>
    </div>
    <div style="margin-top: 20px; margin-bottom: 20px">
      <el-radio-group v-model="attendance_day" size="medium">
        <el-radio-button label="Week day" class="weekday" fill="#ff4949" />
        <el-radio-button label="Sunday" class="sunday" />
        <el-radio-button label="Holliday" class="holliday" />
      </el-radio-group>
    </div>
    <el-table
      :data="attendance.students"
      style="width: 100%"
      :stripe="true"
      :border="true"
      empty-text="Select a class first!"
    >
      <el-table-column label="Roll No." prop="roll_no" />
      <el-table-column label="Student Name" prop="name" />
      <el-table-column>
        <template slot-scope="scope">
          <el-radio-group v-model="scope.row.attendance" size="small" text-color="" :fill="(scope.row.attendance == 'Present') ? '#67c23a' : (scope.row.attendance == 'Absent') ? '#f56c6c' : '#909399'">
            <el-radio-button label="Present" />
            <el-radio-button label="Absent" />
            <el-radio-button label="Leave" />
          </el-radio-group>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import Resource from '@/api/resource';
const classPro = new Resource('classes');
const studentPro = new Resource('students');
const attendPro = new Resource('attendance');
import { addAttendance } from '@/api/student';
export default {
  name: '',
  components: { Pagination },
  directives: { },
  data() {
    return {
      classes: [],
      attendance_day: 'Week day',
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
        limit: 15,
        keyword: '',
        role: '',
      },
      classquery: {
        stdclass: '',
      },
    };
  },
  created() {
    this.getList();
  },
  methods: {
    debounceInput: _.debounce(function (e) {
      this.getList();
    }, 500),
    async getList() {
      const { data } = await classPro.list(this.query);
      this.classes = data.classes.data;
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
      this.classquery.stdclass = this.studentclass;
      const { data } = await studentPro.list(this.classquery);
      this.attendance.students = data.students.data.map(std => {
        return { ...std, 'attendance': 'Present' };
      });
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

      await attendPro.store(this.attendance);
      this.$message.success('Attendance added successfully.');
      this.attendance.students = [];
      this.studentclass = '';
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
</style>

