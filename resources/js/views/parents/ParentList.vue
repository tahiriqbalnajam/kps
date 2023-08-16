<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
          <el-form-item>
            <el-col :span="4">
              <el-select v-model="query.filtercol" placeholder="Class" class="filter-item">
                <el-option v-for="filter in filtercol" :key="filter.col" :label="filter.display | uppercaseFirst" :value="filter.col" />
              </el-select>
            </el-col>
            <el-col :span="3">
              <el-input v-model="query.keyword" placeholder="Parent info"  class="filter-item" v-on:input="debounceInput" />
            </el-col>
            <el-col :span="1">
              <el-button  class="filter-item" type="primary" :icon="Search"  @click="handleFilter">
                {{ $t('table.search') }}
              </el-button>
            </el-col>
            <el-col :span="2">
              <el-button class="filter-item" style="margin-left: 10px;" type="success" :icon="User" @click="addStudentFunc()">
                <el-icon :size="15"><UserFilled /></el-icon>Add Student
              </el-button>
            </el-col>
            <el-col :span="2">
              <el-button class="filter-item" style="margin-left: 10px;" type="info" icon="el-icon-plus" @click="addparentpop = true">
                Add Parent
              </el-button>
            </el-col>
            <el-col :span="2">
              <el-button class="filter-item" :loading="downloadLoading"  type="danger" :icon="Search"  @click="handleDownload">
                Parent Excel
              </el-button>
            </el-col>
            <el-col :span="2">
              <el-button :disabled="multiStudentOption.multiStudent.length <= 0" class="filter-item"  style="margin-left: 10px;" type="warning" :icon="Edit"  @click="dialogVisible = true">
                Change Class
              </el-button>
            </el-col>
          </el-form-item>
        </head-controls>
      
    </div>
    <el-table
      :data="parents"
      style="width: 100%"
    >
      <el-table-column label="ID" prop="id" />
      <el-table-column label="Name" prop="name" />
      <el-table-column label="Phone" prop="phone" />
      <el-table-column label="Address" prop="address" />
      <el-table-column label="Profession" prop="profession" />
      <el-table-column label="CNIC" prop="cnic" />
      <el-table-column align="right">
        <template slot="header" slot-scope="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search"  v-on:input="debounceInput" />
        </template>
        <template slot-scope="scope">
          <el-button
            size="mini"
            @click="handleEdit(scope.row.id, scope.row.name)"
          >Edit</el-button>
          <el-button
            size="mini"
            type="danger"
            @click="handleDelete(scope.row.id, scope.row.name)"
          >Delete</el-button>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
    <add-parent v-if="addparentpop" :editnowprop="addparentpop" :parentid="parentid" @closePopUp="closePopUp()" />
    <add-student  :addeditstudentprop="addstudentpop" :stdid="stdid" @closeAddStudent="closeAddStudent()"/>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import Resource from '@/api/resource';
import AddParent from '@/views/parents/AddParent.vue';
import AddStudent from '@/views/students/AddStudent.vue';
import { debounce } from 'lodash';
const parentsPro = new Resource('parents');
export default {
  name: 'ParentList',
  components: { Pagination, AddParent, AddStudent},
  directives: { },
  filters: {
    dateformat: (date) => {
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    },
  },
  data() {
    return {
      downloadLoading: false,
      parentid: null,
      parents: null,
      search: '',
      total: 0,
      loading: false,
      downloading: false,
      addparentpop: false,
      addstudentpop: false,
      search: '',
      formLabelWidth: '250',
      parent: {
        id: '',
        name: '',
      },
      filtercol: [
        { col: 'name', display: 'Name' },
        { col: 'cnic', display: 'CNIC' },
        { col: 'phone', display: 'Phone#' },
        { col: 'all', display: 'All' },
      ],
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        filtercol: 'name',
        role: '',
      },
    };
  },
  computed: {
  },
  created() {
    this.getList();
  },
  methods: {
    debounceInput: debounce(function (e) {
      this.getList();
    }, 500),
    closeAddParent(parm) {
      this.addparentpop = false;
    },
    async getList() {
      const { data } = await parentsPro.list(this.query);
      this.parents = data.parents.data;
      this.total = data.parents.total;
    },
    async search_data() {
      await this.getList();
    },
    handleEdit(id, name) {
      this.parentid = id;
      this.addparentpop = true;
    },
    closePopUp() {
      this.parentid = null;
      this.addparentpop = false;
      this.getList();
    },
    addStudentFunc() {
      this.addstudentpop = true;
    },
    closeAddStudent() {
      this.addstudentpop = !this.addstudentpop;
      this.stdid = null;
      this.getList();
    },
    handleFilter() {
      this.getList();
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['Name', 'Phone','Address','Profession','CNIC'];
        const filterVal = ['name', 'phone','address','profession','cnic'];
        const list = this.formateData(this.list);
        const data = this.formatJson(filterVal, list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'paid_fee_today',
        });
      });
    },
    formateData(data) {
      const formatedData = data.map(record => (
        {
          roll_no: record.roll_no,
          name: record.name,
          parent_name: record.parents.name,
          phone: record.parents.phone,
          class: record.stdclasses.name,
          gender: record.gender,
          fee: record.monthly_fee,
          dob: record.dob,
        }
      )
      );
      this.downloadLoading = false;
      return formatedData;
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v => filterVal.map(j => {
        if (j === 'timestamp') {
          return parseTime(v[j]);
        } else {
          return v[j];
        }
      }));
    },
  },
};
</script>
<style >
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
</style>