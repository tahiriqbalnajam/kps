<script>
  import HeadControls from '@/components/HeadControls.vue';
  import AddTest from '@/views/exam/AddTest.vue';
  import Resource from '@/api/resource.js';
  import Pagination from '@/components/Pagination/index.vue';
  import { ElNotification, ElMessage, ElMessageBox } from 'element-plus'
  import moment from 'moment';
  const tests = new Resource('tests');
  const test_result = new Resource('tests-result');
  export default {
    name: 'TestList',
    components: {
      HeadControls,
      AddTest,
      Pagination,
    },
    data() {
      return {
        formInline: {
          exam: '',
          resource: [],
        },
        listloading: false,
        showTestStudentList: false,
        dialogEditFormVisible: false,
        studentexam: false,
        organizedResultsstd: {},
        testslist: [],
        total: 0,
        rdata: {
          result_classname: '',
          result_examname: '',
          result_students: [],
          total: 0,
          addedittestprop: false,
        },
        edit: false,
        results: [],
        query: {
          page: 1,
          limit: 10,
          keyword: '',
          role: '',
          filter: {},
          include: '',
        },
        resultquery: {
          filter: {},
          include: '',
        },
      };
  },
  created() {
    this.get_Exams();
  },
  methods: {
    async get_Exams() {
      this.listloading = true;
      this.query.include = 'class,subject';
      const { data } = await tests.list(this.query);
      this.testslist = data.tests.data;
      this.total = data.tests.total;
      this.listloading = false;
    },
    async handleSizeChange (val) {
      this.query.limit = val
      await this.get_Exams()
    },
    async handleCurrentChange (val) {
      this.query.page = val
      await this.get_Exams()
    },
    async getResultClaswise(class_id, class_name) {
      this.listloading = true;
      this.resultquery.filter['id'] = class_id;
      this.resultquery.include = 'class,subject,testResults,testResults.student,testResults.student.parents';
      const { data } = await tests.list(this.resultquery);
      this.results = data.tests.data[0];
      console.log(this.results);
    },
    openPopup(id = null) {
      this.rdata.editid = id;
      this.rdata.addedittestprop = true;
    },
    dateformat: (date) => {
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    },
    async saveResult() {
      this.listloading = true;
      const { data } = await test_result.update(this.results.id, this.results.test_results);
      this.listloading = false;
    },
    getSummaries(param) {
      const { columns, data } = param
      const sums = []
      columns.forEach((column, index) => {
        if (index === 1) {
          sums[index] = h('div', { style: { fontSize: '18px' } }, [
            'Average:',
          ])
          return
        }
        if (index === 2) {
            const values = data.reduce((total, item) => total + Number(item[column.property]), 0);
          sums[index] = h('div', { style: { fontSize: '18px' } }, [
            values/ data.length,
          ])
          return;
        } else {
          sums[index] = ''
        }
      })

      return sums
    },
    handlePopupClosed() {
      this.rdata.addedittestprop = false;
      this.rdata.editid = null;
      this.get_Exams();
    },
    deleteTest(testid) {
      ElMessageBox.confirm(
      'Do you want permanently delete the test. Continue?',
      'Warning',
      {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }
    ).then(async () => {
      tests.destroy(testid);
      await this.get_Exams();
      ElMessage({
        type: 'success',
        message: 'Delete completed',
      })
    })
    .catch(() => {
    })
    }
  }
 }


</script>
<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-form-item>
          <el-col :span="4">
            <el-select v-model="formInline.exam" placeholder="Select Test" class="filter-item" clearable>
              <el-option
                  v-for="item in formInline.resource"
                  :key="item.id"
                  :label="item.examname "
                  :value="item.id"
                />
            </el-select>
          </el-col>
          <el-col :span="2">
            <el-tooltip content="Add Test" placement="top">
              <el-button class="filter-item" style="margin-left: 10px;" type="success" @click="openPopup(null)">
                <el-icon><Plus /></el-icon>
              </el-button>
            </el-tooltip>
          </el-col>
        </el-form-item>
      </head-controls>
    </div>
    <el-card class="box-card">
      <testing />
      <el-table :data="testslist" height="400" style="width: 100%" :loading="loading">
        <el-table-column prop="title" label="Title"  />
        <el-table-column prop="class.name" label="Class"  />
        <el-table-column prop="subject.title" label="Subject"  />
        <el-table-column prop="total_marks" label="Total Marks"  />
        <el-table-column prop="date" label="Date">
          <template #default="scope">
            {{dateformat(scope.row.date)}}
          </template>
        </el-table-column>
        <el-table-column>
          <template #default="scope">
            <el-button-group>
              <el-tooltip content="Class Wise" placement="top">
                <el-button color="#626aef" :dark="isDark" @click="[getResultClaswise(scope.row.id, scope.row.class.name),showTestStudentList = true]">
                  <el-icon><ScaleToOriginal /></el-icon>
                </el-button>
              </el-tooltip>

              <el-tooltip content="Student Wise" placement="top">
                <el-button color="#626aef" :dark="isDark" @click="[getResultStudentwise(scope.row.id),studentexam = true]">
                  <el-icon><UserFilled /></el-icon>
                </el-button>
              </el-tooltip>

              <el-tooltip content="Edit Test" placement="top">
                <el-button type="primary" @click="openPopup(scope.row.id)">
                  <el-icon><Edit /></el-icon>
                </el-button>
              </el-tooltip>

              <el-tooltip content="Delete Test" placement="top">
                <el-button type="danger" @click="deleteTest(scope.row.id)">
                  <el-icon><Delete /></el-icon>
                </el-button>
              </el-tooltip>

            </el-button-group>
          </template>
        </el-table-column>
      </el-table>
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
    </el-card>
    <el-drawer v-model="showTestStudentList" size="90%">
      <template #default>
        <div>
          <el-row :gutter="20" class="headerinfo">
            <el-col :span="6">
              <h2>Title: {{ results.title }}</h2>
            </el-col>
            <el-col :span="6">
              <h2>Subject: {{ results.subject.title }}</h2>
            </el-col>
            <el-col :span="6">
              <h2>Class: {{ results.class.name }}</h2>
            </el-col>
            <el-col :span="6">
              <h2>Total Marks: {{ results.total_marks }}</h2> 
            </el-col>
          </el-row>
          
          <el-row>
            <el-col :span="24">
              <el-table :data="results.test_results" stripe style="width: 100%" :summary-method="getSummaries"  show-summary>
                <el-table-column label="Student Name" prop="student.name"></el-table-column>
                <el-table-column label="Parent Name" prop="student.parents.name"></el-table-column>
                <el-table-column label="Obtained Marks" prop="score">
                  <template #default="scope">
                    <span v-show="!edit">{{scope.row.score}}</span>
                    <el-input v-model="scope.row.score" size="mini" style="width: 50px" v-show="edit"/>
                  </template>
                </el-table-column>
              </el-table>
            </el-col>
          </el-row>
        </div>
      </template>
      <template #footer>
        <span class="dialog-footer">
          <el-row gutter="20">
            <el-col :span="8">
              <el-button @click="showTestStudentList = false">Close</el-button>
            </el-col>
          </el-row>
        </span>
      </template>
    </el-drawer>
    <add-test v-if="rdata.addedittestprop" :addedittestprop="rdata.addedittestprop" :editid="rdata.editid"  @popupClosed="handlePopupClosed"/>
  </div>
</template>


<style  scoped>
  .rdata_result_examname {
      box-shadow: none;
  }
  .headerinfo {
    background: linear-gradient(to right,#0071f3,#73b4ff);
    color: white;
    border-radius: 10px;
  }
</style>

