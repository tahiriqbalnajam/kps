<template>
    <div class="app-container">
      <div class="filter-container">
        <el-card class="box-card">
          <head-controls>
            <el-row justify="space-between">
              <el-col :span="12">
                <el-row :gutter="20">
                  <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl ="8">
                    <el-input v-model="query.keyword" placeholder="Type to search"/>
                  </el-col>
                  <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl ="8">
                    <el-button  class="filter-item" type="primary" :icon="Search"  @click="handleFilter">
                      {{ $t('table.search') }}
                    </el-button>
                  </el-col>
                </el-row>
              </el-col>
              <el-col :span="12">
                <el-row :gutter="20" justify="end">
                  <el-col :span="3">
                    <el-tooltip content="Add Period" placement="top">
                      <el-button class="filter-item" style="margin-left: 10px;" type="info" :icon="el-icon-plus" @click="openAddNew()">
                          <el-icon :size="15"><Plus /></el-icon>
                      </el-button>
                    </el-tooltip>
                  </el-col>
                </el-row>
              </el-col>
            </el-row>             
          </head-controls>
        </el-card>
      </div>
      <el-table
        :data="list"
        style="width: 100%;"
        size="small"
        max-height="500"
  
      >
        <el-table-column label="ID" prop="id" />
        <el-table-column label="Title" prop="title" />
        <el-table-column label="Start time" prop="start" />
        <el-table-column label="End Time" prop="end" />
        <el-table-column align="right">
          <template slot="header" #header="scope">
            <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
          </template>
          <template #default="scope">
            <el-button
              circle
              size="mini"
              @click="handleEdit(scope.row.id, scope.row.title)"
            >
              <el-icon :size="15"><Edit /></el-icon>
            </el-button>
            <el-button
              circle
              size="mini"
              type="danger"
              @click="handleDelete(scope.row.id, scope.row.title)"
            ><el-icon :size="15"><Delete /></el-icon></el-button>
          </template>
        </el-table-column>
      </el-table>
      <div class="demo-pagination-block">
        <el-pagination
          v-show="total>0"
          v-model:current-page="query.page"
          v-model:page-size="query.limit"
          :page-sizes="[10, 15, 20, 30, 50, 100]"
          :small="small"
          :disabled="disabled"
          background="white"
          layout="total, sizes, prev, pager, next, jumper"
          :total="total"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
      <el-drawer
        title="Add Periods"
        :modelValue="editnow"
        direction="rtl"
        custom-class="demo-drawer"
        ref="drawer"
        size="90%"
        @close="updatelist()"
      >
        <div class="demo-drawer__content">
          <el-form :model="period" :rules="rules" ref="period">
            <el-row :gutter="20">
              <el-col :span="8">
                <el-form-item label="Title" :label-width="formLabelWidth" prop="title">
                  <el-input v-model="period.title" autocomplete="off" />
                </el-form-item>
              </el-col>
              <el-col :span="5">
                    <el-time-picker
                      v-model="period.start"
                      placeholder="Start time"
                      :disabled-seconds="disabledSeconds"
                      clearable
                      value-format="HH:mm"
                    />
              </el-col>
              <el-col :span="8">
                <el-time-picker
                    v-model="period.end"
                    placeholder="End time"
                    clearable
                    :disabled-seconds="disabledSeconds"
                    value-format="HH:mm"
                />
              </el-col>
            </el-row>
             </el-form>
        </div>
        <template #footer>
            <div style="flex: auto">
              <el-button @click="editnow = false">Cancel</el-button>
              <el-button type="primary" @click="onSubmit('period')" :loading="loading">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
            </div>
          </template>
      </el-drawer>
      <el-drawer
        title="Edit Record"
        :visible.sync="showcard"
        direction="rtl"
        custom-class="demo-drawer"
        ref="drawer"
      >
        <div class="demo-drawer__content">
          <canvas id="canvas"></canvas>
        </div>
      </el-drawer>
    </div>
  </template>
  <script>
  import Pagination from '@/components/Pagination/index.vue';
  //import AddStudent from '@/views/students/AddStudent.vue';
  import Resource from '@/api/resource';
  var stdClass = new Resource('classes');
  import { debounce } from 'lodash';
  const periodPro = new Resource('periods');
  import {
      Check,
      Delete,
      Edit,
      Message,
      Search,
      Star,
  } from '@element-plus/icons-vue'
  export default {
    name: '',
    components: { Pagination},
    directives: { },
    filters: {
      dateformat: (date) => {
        return (!date) ? '' : moment(date).format('DD MMM, YYYY');
      },
    },
    data() {
      return {
        downloadLoading: false,
        list: null,
        search: '',
        total: 0,
        loading: false,
        downloading: false,
        editnow: false,
        showcard: false,
        addstudentpop: false,
        search: '',
        formLabelWidth: '150px',
        filtercol: [],
        rules: {
          name: [
            { required: true, message: 'Please input name', trigger: 'blur' },
          ],
          phone: [
            { required: true, message: 'Please input phone number', trigger: 'blur' },
          ],
          cnic: [
            { required: true, message: 'Please input CNIC', trigger: 'blur' },
          ],
          doj: [
            { required: true, message: 'Please input joining date', trigger: 'blur' },
          ],
          class_id: [
            { required: true, message: 'Please select class', trigger: 'blur' },
          ],
        },
        resetperiod: {
          id: '',
          title: '',
          start: '',
          end: '',
        },
        period: {
          title: '',
          start: '',
          end: '',
        },
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
      this.getClasses();
    },
    methods: {
      debounceInput: debounce(function (e) {
        this.getList();
      }, 500),
      async handleSizeChange (val) {
        this.query.limit = val
        await this.getList()
      },
      async handleCurrentChange (val) {
        this.query.page = val
        await this.getList()
      },
      openAddNew() {
        this.period = {...this.resetperiod}
        this.editnow = true;
      },
      updatelist() {
        this.getList();
        this.editnow = false;
      }, 
      async getClasses() {
        const { data } = await stdClass.list();
        this.classes = data.classes.data;
        
      },
      async getList() {
        const { data } = await periodPro.list(this.query);
        this.list = data.periods.data;
       // this.total = data.teachers.total;
        this.filtercol = data.periods.data;

      },
      async search_data() {
        await this.getList();
      },
      async handleEdit(id, name) {
        const { data } = await periodPro.get(id);
        this.period = data.period;
        this.editnow = true;
      },
      async handleDelete(id, name) {
        this.$confirm('Do you really want to delete?', 'Warning', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning'
        }).then(async () => {
          await periodPro.destroy(id);
          this.getList();
          this.$message({
            type: 'success',
            message: name+' Delete successfully',
          });
        });
      },
      async onSubmit(formName) {
        this.loading = true;
        await this.$refs[formName].validate(valid => {
          if (valid) {
            if(this.period.id != '') {
              periodPro.update(this.period.id, this.period);
              this.editnow = false;
              this.getList();
              this.loading = false;
            } else {
              periodPro.store(this.period);
              this.period = '';
              this.editnow = false;
              this.loading = false;
              this.getList();
            }
          } else {
            this.loading = false;
            return false;
          }
        });
      },
      closeAddStudent() {
        this.addstudentpop = !this.addstudentpop;
        this.stdid = null;
        this.getList();
      },
      handleFilter() {
        this.getList();
      },
      addStudentFunc() {
        this.addstudentpop = true;
      },
      disabledSeconds() {
        return Array.from({ length: 60 }, (_, i) => i); // Returns array [0, 1, 2, ..., 59]
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
  </style>