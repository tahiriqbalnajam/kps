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
      <el-table :data="examdata" height="600" style="width: 100%" size="small" stripe>
        <el-table-column prop="title" label="Exam"  />
        <el-table-column prop="classes.name" label="Class"  />
        <el-table-column prop="created_at" label="Date">
          <template #default="scope">
            {{changeDate(scope.row.created_at)}}
          </template>
        </el-table-column>
        <el-table-column label="Actions" width="100" align="center">
          <template #default="scope">
            <el-dropdown trigger="click" @command="handleCommand($event, scope.row)">
              <el-button type="primary" size="small">
                Actions <el-icon class="el-icon--right"><arrow-down /></el-icon>
              </el-button>
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item command="edit">
                    <el-icon><Edit /></el-icon> Edit Subjects
                  </el-dropdown-item>
                  <el-dropdown-item command="add-marks">
                    <el-icon><DocumentAdd /></el-icon> Add Marks
                  </el-dropdown-item>
                  <el-dropdown-item command="view-marks">
                    <el-icon><List /></el-icon> View Marks
                  </el-dropdown-item>
                  <el-dropdown-item command="print-reports">
                    <el-icon><GoldMedal /></el-icon> Print Reports
                  </el-dropdown-item>
                  <el-dropdown-item command="award-list" divided>
                    <el-icon><Document /></el-icon> Award List
                  </el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
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
    <view-marks-list v-if="viewMarksListVisible" :viewMarksListVisible="viewMarksListVisible" :exam="selectedExam" @close="viewMarksListVisible = false" />
    <print-reports v-if="printReportsVisible" :printReportsVisible="printReportsVisible" :exam="selectedExam" @close="printReportsVisible = false" />
    <award-list-print 
      v-if="awardListVisible" 
      v-model="awardListVisible"
      :examData="awardListData.exam" 
      :students="awardListData.students"
      :totalPossibleMarks="awardListData.totalPossibleMarks"
      @close="awardListVisible = false" 
    />
  </div>
</template>
<script>
import { Edit, Plus, Download, DocumentAdd, List, GoldMedal, ArrowDown, Document } from '@element-plus/icons-vue';
import { debounce } from 'lodash';  // Add this import
import Pagination from '@/components/Pagination/index.vue';
import HeadControls from '@/components/HeadControls.vue';
import Resource from '@/api/resource';
import AddExam from './components/AddExam.vue';
import AddMarks from './components/AddMarks.vue';
import ViewMarksList from './components/ViewMarksList.vue';
import PrintReports from './components/PrintReports.vue';
import AwardListPrint from './components/AwardListPrint.vue';
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
        ViewMarksList,
        PrintReports,
        AwardListPrint,
    },
    data() {
        return {
          Loading: false,
          total: 0,
          examdata: [],
          addExamVisible: false,
          addMarksVisible: false,
          viewMarksListVisible: false,
          printReportsVisible: false,
          awardListVisible: false,
          awardListData: {
            exam: {},
            students: [],
            totalPossibleMarks: 0,
          },
          selectedExam: null,
          examToEdit: null,
          classes: [],
          query: {
            page: 1,
            limit: 15,
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
      openViewMarksList(exam) {
        this.selectedExam = exam;
        this.viewMarksListVisible = true;
      },
      openPrintReports(exam) {
        this.selectedExam = exam;
        this.printReportsVisible = true;
      },
      handleCommand(command, exam) {
        switch(command) {
          case 'edit':
            this.editExam(exam);
            break;
          case 'add-marks':
            this.openAddMarks(exam);
            break;
          case 'view-marks':
            this.openViewMarksList(exam);
            break;
          case 'print-reports':
            this.openPrintReports(exam);
            break;
          case 'award-list':
            this.downloadAwardList(exam);
            break;
        }
      },
      async downloadAwardList(exam) {
        try {
          this.$message.info('Loading award list...');
          
          const response = await fetch(`/api/exams/${exam.id}/award-list`, {
            method: 'GET',
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`,
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
          });
          
          if (!response.ok) {
            throw new Error('Failed to load award list');
          }
          
          const result = await response.json();
          
          this.awardListData = {
            exam: result.data.exam,
            students: result.data.students,
            totalPossibleMarks: result.data.totalPossibleMarks,
          };
          
          this.awardListVisible = true;
          
        } catch (error) {
          console.error('Error loading award list:', error);
          this.$message.error('Failed to load award list');
        }
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

