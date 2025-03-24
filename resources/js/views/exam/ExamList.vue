<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-row :gutter="20" justify="space-between">
          <el-col :span="14">
            <el-form :inline="true" :model="query">
              <el-form-item>
                <el-input
                  v-model="query.filter.title"
                  placeholder="Search by exam title"
                  clearable
                  @input="handleSearch"
                />
              </el-form-item>
              <el-form-item>
                <el-select 
                  v-model="query.filter.class_id" 
                  placeholder="Select Class"
                  clearable
                  @change="handleSearch"
                  style="width: 200px"
                >
                  <el-option
                    v-for="item in classes"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id"
                  />
                </el-select>
              </el-form-item>
              <el-form-item>
                <el-date-picker
                  v-model="query.filter.created_at"
                  type="date"
                  placeholder="Select date"
                  format="YYYY-MM-DD"
                  value-format="YYYY-MM-DD"
                  clearable
                  @change="handleSearch"
                />
              </el-form-item>
            </el-form>
          </el-col>
          <el-col :span="4"  :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
            <el-row justify="end">
              <el-col :span="6" :xs="6" :sm="6" :md="6" :lg="6" :xl="4">
                <el-tooltip content="Add Exam" placement="top">
                  <el-button class="filter-item" type="success" @click="addExamFunc()">
                    <el-icon :size="15"><Plus /></el-icon>
                  </el-button>
                </el-tooltip>
              </el-col>
              <el-col :span="6"  :xs="6" :sm="6" :md="6" :lg="6" :xl="4">
                <el-tooltip content="Students Excel" placement="top">
                  <el-button class="filter-item" :loading="downloadLoading"  type="danger" :icon="Search"  @click="handleDownload">
                    <el-icon><Download /></el-icon>
                  </el-button>
                </el-tooltip>
              </el-col>
              <el-col :span="6"  :xs="6" :sm="6" :md="6" :lg="6" :xl="4">
              </el-col>
            </el-row>
          </el-col>
        </el-row>
      </head-controls>
      <el-alert title="Record Update" type="success" v-if="alertRec"> </el-alert>
    </div>
    <el-card class="box-card">
      <testing />
      <el-table :data="examdata" height="600" style="width: 100%">
        <el-table-column prop="title" label="Exam"  />
        <el-table-column prop="classes.name" label="Class"  />
        <el-table-column prop="created_at" label="Date">
          <template #default="scope">
            {{changeDate(scope.row.created_at)}}
          </template>
        </el-table-column>
        <el-table-column label="Actions">
          <template #default="scope">
            <el-button type="warning" size="small" @click="editExam(scope.row)">
              <el-icon><Edit /></el-icon> Edit
            </el-button>
            <el-button type="primary" @click="openAddMarks(scope.row)">Add Marks</el-button>
            <el-button type="success" @click="openPrintReports(scope.row)">Print Reports</el-button>
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
    <add-exam 
      v-if="addExamVisible" 
      :addExamVisible="addExamVisible" 
      :examToEdit="examToEdit"
      @examAdded="handleExamAdded" 
      @examUpdated="handleExamUpdated"
      @update:visible="addExamVisible = $event" 
    />
    <add-marks v-if="addMarksVisible" :addMarksVisible="addMarksVisible" :exam="selectedExam" :class_id="selectedExam.class_id" @close="addMarksVisible = false" />
    <print-reports v-if="printReportsVisible" :printReportsVisible="printReportsVisible" :exam="selectedExam" @close="printReportsVisible = false" />
  </div>
</template>
<script>
import { Edit, Plus, Download } from '@element-plus/icons-vue';
import { debounce } from 'lodash';  // Add this import
import Pagination from '@/components/Pagination/index.vue';
import HeadControls from '@/components/HeadControls.vue';
import Resource from '@/api/resource';
import AddExam from './components/AddExam.vue';
import AddMarks from './components/AddMarks.vue';
import PrintReports from './components/PrintReports.vue';
import moment from 'moment';
const examRes = new Resource('exams');
const classRes = new Resource('classes');

export default {
    name: 'ExamList',
    components: {
        Pagination,
        HeadControls,
        AddExam,
        AddMarks,
        PrintReports,
    },
    data() {
        return {
          Loading: false,
          total: 0,
          examdata: [],
          addExamVisible: false,
          addMarksVisible: false,
          printReportsVisible: false,
          selectedExam: null,
          examToEdit: null,
          classes: [],
          query: {
            page: 1,
            limit: 10,
            filter: {
              title: '',
              class_id: '',
              created_at: '',
            },
          },
        };
    },
    created() {
      this.get_Exams();
      this.getClasses();
    },
    methods: {
      changeDate(date) {
        return moment(date).format('DD/MM/YYYY');
      },
      async handleSizeChange (val) {
        this.query.limit = val
        await this.get_Exams()
      },
      async handleCurrentChange (val) {
        this.query.page = val
        await this.get_Exams()
      },
      async get_Exams() {
        this.Loading = true;
        const { data } = await examRes.list(this.query);
        this.Loading = false;
        this.examdata = data.exams.data;
        this.total = data.exams.total;

      },
      async getClasses() {
        const { data } = await classRes.list();
        this.classes = data.classes.data;
      },
      handleSearch: debounce(function() {
        this.query.page = 1; // Reset to first page when searching
        this.get_Exams();
      }, 300),
      addExamFunc() {
        this.examToEdit = null;
        this.addExamVisible = true;
      },
      editExam(exam) {
        this.examToEdit = exam;
        this.addExamVisible = true;
      },
      handleExamAdded() {
        this.get_Exams();
        this.addExamVisible = false;
      },
      handleExamUpdated() {
        this.get_Exams();
        this.addExamVisible = false;
        this.examToEdit = null;
      },
      openAddMarks(exam) {
        this.selectedExam = exam;
        this.addMarksVisible = true;
      },
      openPrintReports(exam) {
        this.selectedExam = exam;
        this.printReportsVisible = true;
      },
    },
    mounted() {
        // Code to run when the component is mounted goes here
    },
};
</script>

<style  scoped>
.rdata_result_examname {
    /* border: none !important; */
    box-shadow: none;
}
.el-form-item {
  margin-bottom: 10px;
  margin-right: 10px;
}
</style>

