<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-row :gutter="20">
          <el-col :span="5">
            <el-form-item>
              <el-select v-model="attendance.stdclass" placeholder="Select class" @change="getStudent">
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
            <el-date-picker
              v-model="attendance.date"
              type="date"
              format="DD MMM, YYYY"
              value-format="YYYY-MM-DD"
              placeholder="Pick a day" 
              @change="getStudent" />
          </el-col>
          <el-col :span="3">
            <el-button type="primary" :loading="loading" :disabled="attendance.students.length <= 0" @click="submitAttendance">
              {{ loading ? 'Submitting ...' : 'Save Attendance' }}
            </el-button>
          </el-col>
        </el-row>
      </head-controls>
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
      <el-table-column label="Student Name" prop="name" />
      <el-table-column label="Father name" prop="parents.name" />
      <el-table-column>
        <template #header>
        <el-input v-model="search" size="small" placeholder="Type to search" />
      </template>
        <template  #default="scope">
          <el-radio-group v-model="scope.row.attendance" size="small" text-color="" :fill="(scope.row.attendance == 'present') ? '#67c23a' : (scope.row.attendance == 'absent') ? '#f56c6c' : '#909399'">
            <el-radio-button label="Present" value="present" />
            <el-radio-button label="Absent" value="absent"/>
            <el-radio-button label="Leave" value="leave"/>
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
    todayDate() {
      var today = new Date();
      var dd = String(today.getDate()).padStart(2, '0');
      var mm = String(today.getMonth() + 1).padStart(2, '0');
      var yyyy = today.getFullYear();
      today = yyyy + '-' + mm + '-' + dd;
      return today;
    },
    async getStudent() {
      this.student_loading = true;
      this.query.filter.stdclass = this.attenquery.stdclass = this.attendance.stdclass;
      this.query.filter.status = 'enable';
      this.query.fields = 'id,name,roll_no,class_id,parent_id';
      const { data } = await studentPro.list(this.query);
      this.attenquery.month = this.attendance.date;
      const attenDD = await studentAttMarked(this.attenquery);
      const hasrec = Object.keys(attenDD.data.attendance).length
      if(hasrec > 0) {
        this.$alert('Attendance has already been submitted for this day.', 'Warning', {
          confirmButtonText: 'OK',
          type: 'warning'
        });
      }
      this.attendance.students = data.students.data.map(std => {
        const atten = attenDD.data.attendance.find(att => att.student_id == std.id);
        if(atten) {
          return { ...std, 'attendance': atten.status[0] + atten.status.slice(1) };
        }
        return { ...std, 'attendance': 'present' };
      });
      this.student_loading = false;
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
      this.attendance.stdclass = '';
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

