<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-row>
          <el-col :span="5">
            <el-form-item>
              <el-date-picker
              v-model="query.date"
              type="date"
              format="DD MMM, YYYY"
              value-format="YYYY-MM-DD"
              placeholder="Pick a day"
              @change="getAttendanceByDate()"/>
            </el-form-item>
          </el-col>
          <el-col :span="5">
            <el-button type="primary" :loading="loading" :disabled="attendance.teachers.length <= 0" @click="submitAttendance">
              {{ loading ? 'Submitting ...' : 'Save Attendance' }}
            </el-button>
          </el-col>
        </el-row>
      </head-controls>
      </div>
    <el-table
      :data="attendance.teachers"
      style="width: 100%"
      :stripe="true"
      :border="true"
      empty-text="Select a class first!"
    >
      <el-table-column label="Teacher Name" prop="name" />
      <el-table-column>
        <template #default="scope">
          <el-radio-group v-model="scope.row.attendance" size="small" text-color="" :fill="(scope.row.attendance == 'present') ? '#67c23a' : (scope.row.attendance == 'absent') ? '#f56c6c' : '#909399'">
            <el-radio-button label="present" />
            <el-radio-button label="absent" />
            <el-radio-button label="leave" />
            <el-radio-button label="holiday" />
          </el-radio-group>
        </template>
      </el-table-column>
    </el-table>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import HeadControls from '@/components/HeadControls.vue';
import Resource from '@/api/resource';
const teachersPro = new Resource('teachers');
const attendPro = new Resource('teacher_attendance');
import { debounce } from 'lodash';
const resourcePro = new Resource('resource');
export default {
  name: 'TeacherAttendance',
  props: [],
  components: { Pagination, HeadControls },
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
      already_attendance: [],
      attendance: {
        teachers: [],
        date: this.todayDate(),
      },
      query: {
        date: this.todayDate()
      },
      classquery: {
        stdclass: '',
      },
    };
  },
  computed: {
  },
  created() {
    this.getAttendance();
    this.getTeachers();
  },
  methods: {
    debounceInput: debounce(function (e) {
      this.getTeachers();
    }, 500),
    async getAttendanceByDate() {
      this.attendance.date = this.query.date;
      this.getAttendance();
      this.getTeachers();
    },
    async getTeachers() {
      const { data } = await teachersPro.list(this.classquery);
      this.attendance.teachers = data.teachers.data.map(teacher => {
        let already = this.already_attendance.find((tech) => tech.teacher_id == teacher.id);
        if(already) {
          return { ...teacher, 'attendance': already.status };
        } else {  
          return { ...teacher, 'attendance': 'present' };
        }
      });
    },
    async getAttendance() {
      const { data } = await attendPro.list(this.query);
      if(data.attendace.length <= 0 ) {
        this.$message.info('Attendance is pending for this day.');
      }
      this.already_attendance = data.attendace;

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
