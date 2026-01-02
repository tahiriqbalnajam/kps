<template>
  <div class="app-container">
    <!-- Compact Filter Header -->
    <div class="compact-filter-header">
      <div class="filter-section">
        <!-- Search Controls -->
        <div class="search-controls">
          <el-select 
            v-model="query.filtercol" 
            placeholder="Search by..." 
            class="search-type-select"
            size="default"
          >
            <el-option v-for="filter in filtercol" :key="filter.col" :label="upperFirst(filter.display)" :value="filter.col" />
          </el-select>
          
          <el-input 
            v-model="query.keyword" 
            placeholder="Enter search term..." 
            class="search-input" 
            v-on:input="debounceInput" 
            clearable
            size="default"
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
        </div>

        <!-- Filter Controls -->
        <div class="filter-controls">
          <el-tree-select 
            check-strictly
            v-model="query.stdclass" 
            :data="classes"
            placeholder="Select class..." 
            clearable 
            class="class-select" 
            @change="handleFilter"
            size="default"
          />
          
          <el-select
            v-model="query.morefilters"
            multiple
            placeholder="Filters..."
            class="more-filters-select"
            @change="handleFilter"
            collapse-tags
            collapse-tags-tooltip
            size="default"
          >
            <el-option label="👨 Male" value="gender_male"/>
            <el-option label="👩 Female" value="gender_female"/>
            <el-option label="💝 Orphan" value="is_orphan"/>
            <el-option label="📋 PEF Pending" value="pef_admission_pending"/>
            <el-option label="✅ PEF Done" value="pef_admission_done"/>
            <el-option label="🆔 Nadra Pending" value="nadra_pending"/>
            <el-option label="👶 Age < 5yr" value="under_five"/>
            <el-option label="🚫 Disabled" value="view_inactive"/>
          </el-select>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="action-section">
        <el-button-group class="action-button-group">
          <el-tooltip content="Add New Student" placement="top">
            <el-button 
              type="success" 
              @click="addStudentFunc()"
              size="default"
              class="action-btn"
            >
              <el-icon><UserFilled /></el-icon>
            </el-button>
          </el-tooltip>
          
          <el-tooltip content="Export to Excel" placement="top">
            <el-button 
              :loading="downloadLoading"  
              type="primary" 
              @click="handleDownload"
              size="default"
              class="action-btn"
            >
              <el-icon><Download /></el-icon>
            </el-button>
          </el-tooltip>
          
          <el-tooltip content="Change Selected Students Class" placement="top">
            <el-button 
              :disabled="multiStudentOption.multiStudent.length <= 0" 
              type="warning" 
              @click="dialogVisible = true"
              size="default"
              class="action-btn"
            >
              <el-icon><Sort /></el-icon>
            </el-button>
          </el-tooltip>
        </el-button-group>
      </div>
    </div>

    <!-- Success Alert -->
    <el-alert 
      title="Records updated successfully!" 
      type="success" 
      v-if="alertRec"
      show-icon
      :closable="true"
      @close="alertRec = false"
      class="success-alert"
    />

    <!-- Student Table Card -->
    <el-card class="box-card">
  <el-table
          :data="list"
          height="600"
          stripe 
          style="width: 100%"
          v-loading="listloading"
          @selection-change="handleSelectionChange"
          :table-layout="auto"
          size="small"
        >
        <el-table-column type="selection" width="55" />
        <el-table-column label="Roll #" prop="roll_no" width="80" />
        <el-table-column label="Adm #" prop="adminssion_number">
          <template #default="scope">
            <el-badge is-dot class="item" v-if="scope.row.action_required == 'Yes'">
              <el-popover trigger="hover" placement="top">
                <p>Action: {{ scope.row.action_details }}</p>
                <template #reference>
                  {{ scope.row.adminssion_number }}
                </template>
              </el-popover>
            </el-badge>
            <span v-else>{{  scope.row.adminssion_number }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Name" prop="">
            <template #default="scope">
              <el-link :href="'#/students/report/'+ scope.row.id">
                <el-popover trigger="hover" placement="top">
                  <p>B Form# {{ scope.row.b_form }}</p>
                  <template #reference>
                    {{ scope.row.name }}
                  </template>
                </el-popover>
              </el-link>
            </template>
        </el-table-column>
        <el-table-column label="Parent" prop="parents.name" />
        <el-table-column label="Phone" prop="parents.phone" />
        <el-table-column label="Class" prop="stdclasses.name" />
        <el-table-column label="Fee" prop="monthly_fee" />
        <el-table-column 
          label="DOB" 
          prop="dob"
        >
          <template #header>
            <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
              <span>DOB</span>
              <div style="display: flex; gap: 4px; align-items: center;">
                <el-date-picker
                  v-model="dobDateRange"
                  type="daterange"
                  size="small"
                  placeholder="Filter by date range"
                  start-placeholder="From"
                  end-placeholder="To"
                  format="DD/MM/YYYY"
                  value-format="YYYY-MM-DD"
                  style="width: 160px; font-size: 11px;"
                  @change="handleDOBFilter"
                  clearable
                />
                <el-button
                  v-if="dobDateRange && dobDateRange.length > 0"
                  size="small"
                  type="danger"
                  icon="Close"
                  circle
                  @click="clearDOBFilter"
                  title="Clear DOB filter"
                  style="width: 20px; height: 20px; font-size: 10px;"
                />
              </div>
            </div>
          </template>
          <template #default="scope">
            {{ dateformat(scope.row.dob)}}
          </template>
        </el-table-column>
        <el-table-column align="right" fixed="right">
          <template #default="scope">
            <el-dropdown split-button type="primary" @click="payFee(scope.row.id, scope.row.name)" size="small" @command="handleCommand">
              Pay Fee
              <template #dropdown>
                <el-dropdown-menu>
                  <el-dropdown-item icon="el-icon-money" :command="'feedetail~'+scope.row.id">Fee Detail</el-dropdown-item>
                  <el-dropdown-item icon="el-icon-edit" :command="'edit~'+scope.row.id">Edit Student</el-dropdown-item>
                  <el-dropdown-item icon="el-icon-user" :command="'admission~'+scope.row.id">Admi. Certificate</el-dropdown-item>
                  <el-dropdown-item icon="el-icon-delete" :command="'certificat~'+scope.row.id">Character Certificat</el-dropdown-item>
                  <el-dropdown-item icon="el-icon-delete" :command="'schoolleaving~'+scope.row.id">School Leaving Certificat</el-dropdown-item>
                  <el-dropdown-item icon="el-icon-delete" :command="'delete~'+scope.row.id">Delete Student</el-dropdown-item>
                  <el-dropdown-item icon="el-icon-delete" :command="'qrcode~'+scope.row.id">QRCode</el-dropdown-item>
                </el-dropdown-menu>
              </template>
            </el-dropdown>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
    
    <el-dialog
      title="Change Class"
      :modelValue="dialogVisible"
      width="30%">
      <span>Enter Class Name</span>
      <el-select v-model="multiStudentOption.changeClass" placeholder="Select">
        <el-option
          v-for="item in classes"
          :key="item.id"
          :label="item.name"
          :value="item.id">
        </el-option>
      </el-select>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogVisible = false">Cancel</el-button>
        <el-button :disabled="multiStudentOption.changeClass == ''" type="primary" @click="changeClass()" >Change Class</el-button>
      </span>
    </el-dialog>
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
    <add-student  v-if="addstudentpop" :addeditstudentprop="addstudentpop" :stdid="stdid" @closeAddStudent="closeAddStudent()"/>
    <admission-certificate  v-if="openadmitcert" :openadmitcert="openadmitcert" :stdid="stdid" @closeAdmitCert="closeAdmitCert()"/>
    <pay-fee v-if="openpayfee" :openpayfee="openpayfee" :stdid="stdid" @donePayFee="donePayFee" />
    <fee-detail v-if="openfeedetail" :openfeedetail="openfeedetail" :stdid="studentid" @doneFeeDetail="doneFeeDetail" />
    <character-certificate  v-if="showcharactercertificate" :showcharactercertificate="showcharactercertificate" :stdid="studentid" @doneFeeDetail="doneFeeDetail" />
    <school-leaving-certificate  v-if="showschoolleavingcertificate" :showschoolleavingcertificate="showschoolleavingcertificate" :stdid="studentid" @doneFeeDetail="doneFeeDetail" />
    <fee-print v-if="openfeeprint" :feeid="feeid" :openfeeprint="openfeeprint" @doneFeePrint="doneFeePrint" />
    <student-idcard v-if="showIDCard"
      :showcardprop="showIDCard"
      @closeIdcard="closeIdcard"  
      :stdid="studentid" />
  </div>
</template>
<script>
import {
  User,
  UserFilled,
  Document,
  Edit,
  Message,
  Search,
  Star,
  ArrowDown,
  Download,
  Sort,
} from '@element-plus/icons-vue'
import moment from 'moment';
import { debounce } from 'lodash';
import Pagination from '@/components/Pagination/index.vue';
import Resource from '@/api/resource';
import PayFee from '@/views/fee/component/PayFee.vue';
import FeePrint from '@/views/fee/component/FeePrint.vue';
import FeeDetail from '@/views/fee/component/FeeDetail.vue';
import CharacterCertificate from '@/views/students/StudentCharacterCertificate.vue';
import SchoolLeavingCertificate from '@/views/students/SchoolLeavingCertificate.vue';
import AddStudent from '@/views/students/AddStudent.vue';
import StudentIdcard from '@/views/students/components/StudentIdcard.vue';
import AdmissionCertificate from '@/views/students/components/AdmissionCertificate.vue';
import { editClass, exportStudent } from '@/api/student.js'; // Ensure exportStudent is imported
import HeadControls from '@/components/HeadControls.vue';
const student = new Resource('students');
const classes = new Resource('classes');
export default {
  name: 'StudentList',
  components: { Pagination, AddStudent,  PayFee, FeePrint, FeeDetail, HeadControls, CharacterCertificate, 
                SchoolLeavingCertificate,AdmissionCertificate, StudentIdcard },
  directives: { },
  filters: {
    
  },
  data() {
    return {
      showIDCard: false,
      downloadLoading: false,
      stdid: null,
      studentid: null,
      list: null,
      addparentpop: false,
      addstdclasspop: false,
      openpayfee: false,
      openfeeprint: false,
      openfeedetail: false,
      listloading: false,
      classes: null,
      addstudentpop: false,
      search: '',
      total: 0,
      loading: true,
      downloading: false,
      dialogVisible: false,
      alertRec: false,
      showcharactercertificate: false,
      showschoolleavingcertificate: false,
      openadmitcert: false,
      multiStudentOption:{
        multiStudent: [],
        changeClass: "",
      },
      filtercol: [
        { col:  'adminssion_number', display: 'adminssion_number'},
        { col: 'roll_no', display: 'Roll No.' },
        { col: 'name', display: 'Student Name' },
        { col: 'parent_name', display: 'Parent Name' },
        { col: 'parent_phone', display: 'Phone#' },
        { col: 'all', display: 'All' },
      ],
      query: {
        page: 1,
        limit: 15,
        filter: {},
        filtercol: 'name',
        stdclass: '',
        morefilters: [],
      },
      dobDateRange: null,
    };
  },
  computed: {
  },
  created() {
    this.getList();
    this.getClasses();
  },
  methods: {
    async handleSizeChange (val) {
      console.log(`每页 ${val} 条`);
      this.query.limit = val
      await this.getList()
    },
    async handleCurrentChange (val) {
      this.query.page = val
      await this.getList()
    },
    upperFirst(txt) {
      if (txt) {
        return txt.charAt(0).toUpperCase() + txt.slice(1)
      }
    },
    dateformat: (date) => {
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    },
    handleDOBFilter() {
      // Apply DOB date range filter and refresh the list
      this.getList();
    },
    clearDOBFilter() {
      // Clear the DOB date range filter
      this.dobDateRange = null;
      // Remove the DOB filters from query
      if (this.query.filter['dob_from']) {
        delete this.query.filter['dob_from'];
      }
      if (this.query.filter['dob_to']) {
        delete this.query.filter['dob_to'];
      }
      // Refresh the list
      this.getList();
    },
    handleCommand(command) {
      console.log(command);
      let info = command.split('~');
      const method = info[0];
      const id = info[1];

      if (method == 'feedetail') {
        this.showFeeDetails(id);
      }
      if (method == 'edit') {
        this.handleEdit(id);
      }
      if (method == 'admission') {
        //this.openAdmissionCert(id);
        this.openadmitcert = true;
        this.stdid = id;
      }

      if (method == 'delete') {
        this.handleDelete(id);
      }
      if (method == 'certificat') {
        this.showCharacterCertificate(id);
      }
      if (method == 'schoolleaving') {
        this.SchoolLeavingCertificate(id);
      }
      if (method == 'qrcode') {
        this.generateStudentQRCode(id);
      }
    },
    debounceInput: debounce(function (e) {
      this.getList();
    }, 500),
    async getList() {
      this.listloading = true;
      const filterKey = this.query.filtercol;
      const filterValue = this.query.keyword;
      
      // Initialize filter object
      this.query.filter = {
        [filterKey]: filterValue,
      };
      
      // Process stdclass value to extract class_id or section_id
      if (this.query.stdclass) {
        const selectedValue = this.query.stdclass.toString();
        
        if (selectedValue.startsWith('class_')) {
          // Extract class ID from class_X format
          const classId = selectedValue.split('_')[1];
          this.query.filter['stdclass'] = classId;
        } else if (selectedValue.startsWith('section_')) {
          // Extract section ID from section_X format
          const sectionId = selectedValue.split('_')[1];
          this.query.filter['section_id'] = sectionId;
        }
      }
      
      // Process additional filters
      if(this.query.morefilters.length > 0) {
        this.query.morefilters.forEach(filter => {
          if(filter == 'gender_male')
            this.query.filter['gender'] = 'Male';
          else if(filter == 'gender_female')
            this.query.filter['gender'] = 'Female';
          else if(filter == 'pef_admission_done')
            this.query.filter['pef_admission'] = 'Yes';
          else if(filter == 'pef_admission_pending')
            this.query.filter['pef_admission'] = 'No';
          else if(filter == 'under_five')
            this.query.filter['age_less_than'] = '5';
          else if(filter == 'view_inactive')
            this.query.filter['status'] = 'disable';
          else 
            this.query.filter[filter] = 'Yes';
        });
      }
      
      // Process DOB date range filter
      if (this.dobDateRange && this.dobDateRange.length === 2) {
        this.query.filter['dob_from'] = this.dobDateRange[0];
        this.query.filter['dob_to'] = this.dobDateRange[1];
      }
      
      const { data } = await student.list(this.query);
      this.listloading = false;
      this.list = data.students.data;
      this.total = data.students.total;
    },

    async getClasses() {
      let query = {
        include: 'sections'
      };
      const{ data } = await classes.list(query);
      // Transform the classes data to include class and section hierarchy for tree select
      this.classes = data.classes.data.map(classItem => {
        // Create the parent class node
        const classNode = {
          value: `class_${classItem.id}`,
          label: `${classItem.name}`,
          id: classItem.id,
          type: 'class',
          name: classItem.name,
          students_count: classItem.students_count,
          males_count: classItem.males_count,
          females_count: classItem.females_count
        };
        
        // Add children if there are sections
        if (classItem.sections && classItem.sections.length > 0) {
          classNode.children = classItem.sections.map(section => ({
            value: `section_${section.id}`,
            label: `${section.name}`,
            id: section.id,
            type: 'section',
            class_id: classItem.id,
            name: section.name,
            students_count: section.students_count,
            males_count: section.males_count,
            females_count: section.females_count
          }));
        }
        
        return classNode;
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
    handleEdit(id) {
      this.stdid = id;
      this.addstudentpop = true;
    },
    addStudentFunc() {
      this.addstudentpop = true;
    },
    openAdmissionCert() {
      this.openadmitcert = true;
    },
    closeAdmitCert() {
      this.openadmitcert = false;
    },
    payFee(id, name) {
      this.stdid = id;
      this.openpayfee = true;
    },
    doneFeePrint() {
      this.openfeeprint = null;
    },
    donePayFee(data) {
      this.getList();
      this.openpayfee = false;
      if (data.print) {
        this.openfeeprint = true;
        this.feeid = data.feeid;
      }
    },
    doneFeeDetail() {
      this.openfeedetail = false;
    },
    handleSelectionChange(val) {
      this.multiStudentOption.multiStudent = val.map(prod => prod.id);
    },
    changeClass(){
      this.loadingprice = true;
      editClass(this.multiStudentOption);
      this.loadingprice = false;
      this.dialogVisible = false;
      this.alertRec = true;
      this.getList();
    },
    showCharacterCertificate(id) {
      this.showcharactercertificate = true;
      this.studentid = id;
    },
    SchoolLeavingCertificate(id) {
      this.showschoolleavingcertificate = true;
      this.studentid = id;
    },
    async handleDownload() {
      if (this.total === 0) {
        this.$message({
          message: 'No data to export.',
          type: 'info'
        });
        return;
      }
      this.downloadLoading = true;
      try {
        const exportParams = {};

        // Main filter: filtercol and keyword
        if (this.query.filtercol && this.query.keyword !== undefined && this.query.keyword !== null && this.query.keyword !== '') {
          if (this.query.filtercol === 'all') {
             exportParams[`filter[all]`] = this.query.keyword;
          } else {
             exportParams[`filter[${this.query.filtercol}]`] = this.query.keyword;
          }
        }

        // Class/Section filter
        if (this.query.stdclass) {
          const selectedValue = this.query.stdclass.toString();
          if (selectedValue.startsWith('class_')) {
            const classId = selectedValue.split('_')[1];
            exportParams['filter[stdclass]'] = classId; 
          } else if (selectedValue.startsWith('section_')) {
            const sectionId = selectedValue.split('_')[1];
            exportParams['filter[section_id]'] = sectionId; 
          }
        }

        // More filters
        if (this.query.morefilters && this.query.morefilters.length > 0) {
          this.query.morefilters.forEach(filter => {
            if (filter === 'gender_male') exportParams['filter[gender]'] = 'Male';
            else if (filter === 'gender_female') exportParams['filter[gender]'] = 'Female';
            else if (filter === 'is_orphan') exportParams['filter[is_orphan]'] = 'Yes'; 
            else if (filter === 'pef_admission_done') exportParams['filter[pef_admission]'] = 'Yes';
            else if (filter === 'pef_admission_pending') exportParams['filter[pef_admission]'] = 'No';
            else if (filter === 'nadra_pending') exportParams['filter[nadra_b_form_verified]'] = 'No'; 
            else if (filter === 'under_five') exportParams['filter[age_less_than]'] = '5'; 
            else if (filter === 'view_inactive') exportParams['filter[status]'] = 'disable'; 
          });
        }

        // DOB date range filter
        if (this.dobDateRange && this.dobDateRange.length === 2) {
          exportParams['filter[dob_from]'] = this.dobDateRange[0];
          exportParams['filter[dob_to]'] = this.dobDateRange[1];
        }
        
        console.log('Export Parameters being sent:', exportParams); // Debug log
        
        exportStudent(exportParams) // Pass the constructed exportParams
          .then(response => {
            const blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `students_${moment().format('YYYYMMDD_HHmmss')}.csv`);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url); // Clean up
          })
          .catch(error => {
            console.error('Error downloading file:', error);
            this.$message({
              message: 'Download failed. Please try again.',
              type: 'error'
            });
          });

      } catch (error) {
        console.error('Error during export:', error);
        this.$message({
          message: 'Export failed. Please try again.',
          type: 'error'
        });
      } finally {
        // It's hard to know when download finishes with window.location.href
        // Consider using a timeout or a more sophisticated download handling if needed
        setTimeout(() => {
            this.downloadLoading = false;
        }, 1500); // Stop loading indicator after a delay
      }
    },
    formateData(dataToFormat) {
      const formatedData = dataToFormat.map(record => (
        {
          adminssion_number: record.adminssion_number,
          roll_no: record.roll_no,
          name: record.name,
          parent_name: record.parents ? record.parents.name : '',
          phone: record.parents ? record.parents.phone : '',
          class: record.stdclasses ? record.stdclasses.name : '',
          gender: record.gender,
          fee: record.monthly_fee,
          dob: this.dateformat(record.dob), // Ensure dob is formatted
          b_form: record.b_form,
          status: record.status,
        }
      )
      );
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
    showFeeDetails(id) {
      this.studentid = id;
      this.openfeedetail = true;
    },
    generateStudentQRCode(id) {
      console.log(id);
      if (id) {
        this.studentid = id;
        this.showIDCard = true;
      } else {
        this.$message.error('Could not open ID card: Missing student data');
      }
    },
    closeIdcard() {
      this.showIDCard = false;
      this.studentid = null;
    },
    handleDelete(id) {
      this.$confirm('Are you sure you want to delete this student?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        // Add delete API call here
        student.destroy(id).then(() => {
          this.$message({
            type: 'success',
            message: 'Student deleted successfully!'
          });
          this.getList();
        }).catch(error => {
          this.$message.error('Failed to delete student');
        });
      }).catch(() => {
        this.$message({
          type: 'info',
          message: 'Delete cancelled'
        });
      });
    },
    addClass() {

    },
    addSession() {

    },
    search_data() {

    }
  },
};
</script>
<style scoped>
/* Main Container */
.app-container {
  padding: 16px;
  background: #f5f7fa;
  min-height: 100vh;
}

/* Compact Filter Header */
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

/* Filter Section */
.filter-section {
  display: flex;
  gap: 16px;
  align-items: center;
  flex: 1;
  flex-wrap: wrap;
}

/* Search Controls */
.search-controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

.search-type-select {
  width: 140px;
  min-width: 140px;
}

.search-input {
  width: 280px;
  min-width: 200px;
}

/* Filter Controls */
.filter-controls {
  display: flex;
  gap: 12px;
  align-items: center;
}

.class-select {
  width: 160px;
  min-width: 160px;
}

.more-filters-select {
  width: 200px;
  min-width: 200px;
}

/* Action Section */
.action-section {
  display: flex;
  align-items: center;
  margin-left: 16px;
}

.action-button-group {
  display: flex;
  gap: 0;
  border-radius: 6px;
  overflow: hidden;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.action-btn {
  border-radius: 0;
  border-right: 1px solid rgba(255, 255, 255, 0.3);
  font-weight: 500;
  transition: all 0.3s ease;
  min-width: 44px;
  height: 36px;
}

.action-btn:last-child {
  border-right: none;
}

.action-btn:hover {
  transform: translateY(-1px);
  z-index: 1;
  position: relative;
}

/* Success Alert */
.success-alert {
  margin-bottom: 16px;
  border-radius: 6px;
}

/* Pagination */
.demo-pagination-block {
  margin-top: 16px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.demo-pagination-block .demonstration {
  margin-bottom: 16px;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .search-input {
    width: 240px;
    min-width: 180px;
  }
  
  .class-select {
    width: 140px;
    min-width: 140px;
  }
  
  .more-filters-select {
    width: 180px;
    min-width: 180px;
  }
}

@media (max-width: 992px) {
  .compact-filter-header {
    flex-direction: column;
    gap: 12px;
    align-items: stretch;
  }
  
  .filter-section {
    justify-content: center;
  }
  
  .action-section {
    margin-left: 0;
    justify-content: center;
  }
  
  .search-controls {
    justify-content: center;
    flex-wrap: wrap;
  }
  
  .filter-controls {
    justify-content: center;
    flex-wrap: wrap;
  }
}

@media (max-width: 768px) {
  .app-container {
    padding: 12px;
  }
  
  .compact-filter-header {
    padding: 16px;
  }
  
  .filter-section {
    flex-direction: column;
    gap: 12px;
  }
  
  .search-controls {
    width: 100%;
    flex-direction: column;
    gap: 8px;
  }
  
  .filter-controls {
    width: 100%;
    flex-direction: column;
    gap: 8px;
  }
  
  .search-type-select,
  .search-input,
  .class-select,
  .more-filters-select {
    width: 100%;
    min-width: unset;
  }
  
  .action-button-group {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .compact-filter-header {
    padding: 12px;
  }
  
  .action-btn {
    min-width: 40px;
    height: 32px;
  }
  
  .action-button-group {
    width: 100%;
    justify-content: space-around;
  }
}

/* Enhanced Input Styles */
.search-input :deep(.el-input__wrapper) {
  border-radius: 6px;
  transition: all 0.3s ease;
}

.search-input :deep(.el-input__wrapper):hover {
  border-color: #409eff;
}

.search-input :deep(.el-input__wrapper):focus-within {
  border-color: #409eff;
  box-shadow: 0 0 0 2px rgba(64, 158, 255, 0.2);
}

/* Select Styles */
.search-type-select :deep(.el-select__wrapper),
.class-select :deep(.el-select__wrapper),
.more-filters-select :deep(.el-select__wrapper) {
  border-radius: 6px;
  transition: all 0.3s ease;
}

.search-type-select :deep(.el-select__wrapper):hover,
.class-select :deep(.el-select__wrapper):hover,
.more-filters-select :deep(.el-select__wrapper):hover {
  border-color: #409eff;
}

/* Custom scrollbar for filter dropdowns */
:deep(.el-select-dropdown__list) {
  max-height: 200px;
}

:deep(.el-select-dropdown__list)::-webkit-scrollbar {
  width: 6px;
}

:deep(.el-select-dropdown__list)::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

:deep(.el-select-dropdown__list)::-webkit-scrollbar-thumb {
  background: #c0c4cc;
  border-radius: 3px;
}

:deep(.el-select-dropdown__list)::-webkit-scrollbar-thumb:hover {
  background: #a8abb2;
}
</style>