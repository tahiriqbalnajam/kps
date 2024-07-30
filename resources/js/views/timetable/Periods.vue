<template>
    <div class="app-container">
      <div class="filter-container">
        <el-card class="box-card">
          <head-controls>
            <el-row justify="space-between">
              <el-col :span="12">
                <el-row :gutter="20">
                  <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl ="8">
                    <el-select v-model="query.filtercol" placeholder="Filter" class="filter-item">
                      <el-option v-for="filter in filtercol" :key="filter.id" :label="filter.display | uppercaseFirst" :value="filter.title" />
                    </el-select>
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
                    <el-tooltip content="Add Teacher" placement="top">
                      <el-button class="filter-item" style="margin-left: 10px;" type="info" :icon="el-icon-plus" @click="openAddNew()">
                          <el-icon :size="15"><Plus /></el-icon>
                      </el-button>
                    </el-tooltip>
                  </el-col>
                  <el-col :span="2">
                    <el-tooltip content="Teacher Excel" placement="top">
                      <el-button class="filter-item" :loading="downloadLoading"  type="danger" :icon="Search"  @click="handleDownload">
                        <el-icon><Download /></el-icon>
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
          <el-form :model="period" :rules="rules" ref="teacher">
            <el-row :gutter="20">
              <el-col :span="8">
                <el-form-item label="Title" :label-width="formLabelWidth" prop="title">
                  <el-input v-model="period.title" autocomplete="off" />
                </el-form-item>
              </el-col>
              <el-col :span="8">
                <el-time-select
                    v-model="period.period_start"
                    style="width: 240px"
                    :max-time="endTime"
                    class="mr-4"
                    placeholder="Start time"
                    start="08:30"
                    step="00:15"
                    end="18:30"
                    />
                <el-time-select
                    v-model="period.period_end"
                    style="width: 240px"
                    :min-time="startTime"
                    placeholder="End time"
                    start="08:30"
                    step="00:15"
                    end="18:30"
                />
              </el-col>
            </el-row>
             </el-form>
        </div>
        <template #footer>
            <div style="flex: auto">
              <el-button @click="editnow = false">Cancel</el-button>
              <el-button type="primary" @click="onSubmit('teacher')" :loading="loading">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
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
        teacher: {
          id: '',
          name: '',
          cnic: '',
          father_name: '',
          father_cnic: '',
          pay: '',
          education: '',
          phone: '',
          address: '',
          gender:'',
          status:'active',
          class_id: '',
        },
        resetteacher: {
          id: '',
          name: '',
          cnic: '',
          father_name: '',
          father_cnic: '',
          pay: '',
          education: '',
          phone: '',
          address: '',
          gender:'',
          status:'active',
          class_id: '',
        },
        period: {
         title: '',
         period_start: '',
         period_end: '',
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
        this.teacher = {...this.resetteacher}
        this.editnow = true;
      },
      updatelist() {
        this.getList();
        this.update = false;
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
        const { data } = await resourcePro.get(id);
        this.teacher = data.teacher[0];
        this.editnow = true;
      },
      async handleDelete(id, name) {
        this.$confirm('Do you really want to delete?', 'Warning', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning'
        }).then(async () => {
          await resourcePro.destroy(id);
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
            if(this.teacher.id != '') {
              resourcePro.update(this.teacher.id, this.teacher);
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
      async generateCard() {
        let text = "Tahir iqbal";
  
        await QRCode.toCanvas(document.getElementById('canvas'),
          'sample text', { toSJISFunc: QRCode.toSJIS }, function (error) {
            if (error) {
              console.error(error);
            } else {
              console.log('success!');
            }
          });
        this.showcard = true;
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
      handleDownload() {
        this.downloadLoading = true;
        import('@/vendor/Export2Excel').then(excel => {
          const tHeader = ['Name', 'Gender', 'DOB','CNIC','Pay','Phone','address'];
          const filterVal = ['name', 'gender', 'dob','cnic','pay','phone','address'];
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