<template>
  <div class="app-container">
    <div class="compact-filter-header">
      <div class="filter-section">
        <div class="search-controls">
          <el-input
            v-model="query.filter.title"
            placeholder="Search by exam title..."
            clearable
            @input="handleSearch"
            size="default"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
        </div>

        <div class="filter-controls">
          <el-tree-select
            v-model="query.filter.class_id"
            :data="classes"
            placeholder="Select Class/Section"
            clearable
            @change="handleSearch"
            check-strictly
            size="default"
          />

          <el-date-picker
            v-model="query.filter.created_at"
            type="date"
            placeholder="Select date"
            format="YYYY-MM-DD"
            value-format="YYYY-MM-DD"
            clearable
            @change="handleSearch"
            size="default"
          />
        </div>
      </div>

      <div class="action-section">
        <el-tooltip content="Add Exam" placement="top">
          <el-button type="success" @click="addExamFunc()" size="default" class="action-btn">
            <el-icon><Plus /></el-icon>
          </el-button>
        </el-tooltip>

        <el-tooltip content="Export Excel" placement="top">
          <el-button :loading="downloadLoading" type="primary" @click="handleDownload" size="default" class="action-btn">
            <el-icon><Download /></el-icon>
          </el-button>
        </el-tooltip>
      </div>
    </div>
    <el-card class="box-card">
      <testing />
      <el-table :data="examdata" height="600" style="width: 100%" size="small" stripe>
        <el-table-column prop="title" label="Exam"  />
        <el-table-column prop="classes.name" label="Class"  />
        <el-table-column label="Section" width="120">
          <template #default="scope">
            {{ scope.row.section?.name || 'All' }}
          </template>
        </el-table-column>
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
                  <el-dropdown-item command="datesheet">
                    <el-icon><Calendar /></el-icon> View Datesheet
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
                  <el-dropdown-item command="delete" divided style="color: #f56c6c;">
                    <el-icon><Delete /></el-icon> Delete
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
    <date-sheet-print
      v-if="datesheetVisible"
      v-model="datesheetVisible"
      :exam="datesheetData.exam"
      :school="datesheetData.school"
    />
  </div>
</template>
<script>
import { Edit, Plus, Download, DocumentAdd, List, GoldMedal, ArrowDown, Document, Delete, Calendar, Search } from '@element-plus/icons-vue';
import { debounce } from 'lodash';  // Add this import
import Pagination from '@/components/Pagination/index.vue';
import Resource from '@/api/resource';
import AddExam from './components/AddExam.vue';
import AddMarks from './components/AddMarks.vue';
import ViewMarksList from './components/ViewMarksList.vue';
import PrintReports from './components/PrintReports.vue';
import AwardListPrint from './components/AwardListPrint.vue';
import DateSheetPrint from './components/DateSheetPrint.vue';
import moment from 'moment';
import { transformClassesToTree } from '@/utils/classHelper';
import { sessionStore } from '@/store/session';
const examRes = new Resource('exams');
const classRes = new Resource('classes');

export default {
    name: 'ExamList',
    components: {
        Pagination,
        AddExam,
        AddMarks,
        ViewMarksList,
        PrintReports,
        AwardListPrint,
        DateSheetPrint,
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
          datesheetVisible: false,
          datesheetData: {
            exam: {},
            school: {},
          },
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
    computed: {
      currentSessionId() {
        return sessionStore().currentSessionId;
      },
    },
    created() {
      this.get_Exams();
      this.getClasses();
    },
    watch: {
      currentSessionId() {
        this.get_Exams();
      },
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
        const { data } = await classRes.list({ include: 'sections' });
        this.classes = transformClassesToTree(data.classes.data);
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
          case 'datesheet':
            this.openDateSheet(exam);
            break;
          case 'delete':
            this.deleteExam(exam);
            break;
        }
      },
      async openDateSheet(exam) {
        try {
          const url = new URL(`/api/exams/${exam.id}/datesheet`, window.location.origin);
          const response = await fetch(url.toString(), {
            method: 'GET',
            headers: {
              'Authorization': `Bearer ${localStorage.getItem('token')}`,
              'Accept': 'application/json',
              'Content-Type': 'application/json',
            },
          });

          if (!response.ok) {
            throw new Error('Failed to load date sheet');
          }

          const result = await response.json();
          this.datesheetData = {
            exam: result.data.exam,
            school: result.data.school,
          };
          this.datesheetVisible = true;
        } catch (error) {
          console.error('Error loading date sheet:', error);
          this.$message.error('Failed to load date sheet');
        }
      },
      async deleteExam(exam) {
        try {
          await this.$confirm(`Delete exam "${exam.title}"? This cannot be undone.`, 'Confirm Delete', {
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel',
            type: 'warning',
            confirmButtonClass: 'el-button--danger',
          });
          await examRes.destroy(exam.id);
          this.$message.success('Exam deleted successfully');
          this.get_Exams();
        } catch (e) {
          if (e !== 'cancel') {
            const msg = e.response?.data?.message || 'Failed to delete exam';
            this.$message.error(msg);
          }
        }
      },
      async downloadAwardList(exam) {
        try {
          this.$message.info('Loading award list...');

          const url = new URL(`/api/exams/${exam.id}/award-list`, window.location.origin);
          if (this.currentSessionId) url.searchParams.set('session_id', this.currentSessionId);

          const response = await fetch(url.toString(), {
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

<style scoped>
.compact-filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  background: white;
  padding: 12px 20px;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-bottom: 16px;
  border: 1px solid #e4e7ed;
}

.filter-section {
  display: flex;
  gap: 16px;
  align-items: center;
  flex: 1;
  flex-wrap: wrap;
}

.search-controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-controls .el-input {
  width: 280px;
  min-width: 200px;
}

.filter-controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

.filter-controls .el-tree-select {
  width: 220px;
}

.filter-controls .el-date-picker {
  width: 180px;
}

.action-section {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: 16px;
}

.action-btn {
  min-width: 44px;
  height: 36px;
  border-radius: 6px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
}

.action-btn:hover {
  transform: translateY(-1px);
}
</style>

