<template>
  <div class="app-container">
    <div class="filter-container">
      <el-select v-model="query.class" placeholder="Select class">
        <el-option
          v-for="stdclass in classes"
          :key="stdclass.id"
          :label="stdclass.name"
          :value="stdclass.id"
        />
      </el-select>
      <el-date-picker
        v-model="query.month"
        type="month"
        format="MMM"
        value-format="YYYY-MM-DD"
        placeholder="Pick a month" 
      />
      <el-button type="primary" :loading="loading"  @click="getReport()">
        {{ loading ? 'Submitting ...' : 'get report' }}
      </el-button>
    </div>
    <el-scrollbar height="700px">
      <table class="tblwdborder">
        <tr>
          <th>Student Name</th>
          <th v-for="index in 31" :key="index">{{index}}</th>
        </tr>
        <tr v-for="student in attendance.students" :key="student.id">
          <td>{{ student.name }}</td>
          <td v-for="att in student.attendances" :key="att.id" :class="{'absent': (att == 'A')}">{{att}}</td>
        </tr>
      </table>
    </el-scrollbar>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import Resource from '@/api/resource';
import moment from 'moment';
import { debounce } from 'lodash';
import {studentAttMonthlyReport} from '@/api/attendance';
const classPro = new Resource('classes');
const attendPro = new Resource('attendance');
export default {
  name: '',
  components: { Pagination },
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
      },
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

