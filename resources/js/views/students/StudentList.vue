<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
            <el-row :gutter="20" justify="space-between">
              <el-col :span="14">
                <el-row>
                  <el-col :span="6"  :xs="14" :sm="12" :md="10" :lg="6" :xl="6">
                    <el-select v-model="query.filtercol" placeholder="Class" class="filter-item">
                      <el-option v-for="filter in filtercol" :key="filter.col" :label="upperFirst(filter.display)" :value="filter.col" />
                    </el-select>
                  </el-col>
                  <el-col :span="6"  :xs="14" :sm="12" :md="10" :lg="6" :xl="6">
                    <el-input v-model="query.keyword" placeholder="Student info" style="width: 200px;" class="filter-item" v-on:input="debounceInput" clearable />
                  </el-col>
                  <el-col :span="4"  :xs="14" :sm="12" :md="10" :lg="6" :xl="4">
                    <el-tree-select 
                      check-strictly
                      v-model="query.stdclass" 
                      :data="classes"
                      placeholder="Class" clearable 
                      style="width: 130px" class="filter-item" 
                      @change="handleFilter"
                    />
                  </el-col>
                  <el-col :span="2"  :xs="14" :sm="12" :md="10" :lg="6" :xl="3">
                    <el-select
                      v-model="query.morefilters"
                      multiple
                      placeholder="Select more filter"
                      style="width: 240px"
                      @change="handleFilter"
                    >
                      <el-option label="Male" value="gender_male"/>
                      <el-option label="Female" value="gender_female"/>
                      <el-option label="Is Orphan" value="is_orphan"/>
                      <el-option label="PEF Adm Pedning" value="pef_admission_pending"/>
                      <el-option label="PEF Adm Done" value="pef_admission_done"/>
                      <el-option label="Nadra Pending" value="nadra_pending"/>
                      <el-option label="Age < 5year" value="under_five"/>
                      <el-option label="View Disabled" value="view_inactive"/>
                    </el-select>
                    <!-- <el-button  class="filter-item" type="primary" :icon="Search"  @click="handleFilter">
                      {{ $t('table.search') }}
                    </el-button> -->
                  </el-col>
                </el-row>
              </el-col>
              <el-col :span="4"  :xs="6" :sm="6" :md="6" :lg="6" :xl="6">
                <el-row justify="end">
                  <el-col :span="6" :xs="6" :sm="6" :md="6" :lg="6" :xl="4">
                    <el-tooltip content="Add Student" placement="top">
                      <el-button class="filter-item" type="success" :icon="User" @click="addStudentFunc()">
                        <el-icon :size="15"><UserFilled /></el-icon>
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
                    <el-tooltip content="Change Class" placement="top">
                      <el-button :disabled="multiStudentOption.multiStudent.length <= 0" class="filter-item"  type="warning" :icon="Edit"  @click="dialogVisible = true">
                        <el-icon><Sort /></el-icon>  
                      </el-button>
                    </el-tooltip>
                  </el-col>
                </el-row>
              </el-col>
            </el-row>
        </head-controls>
      <el-alert title="Record Update" type="success" v-if="alertRec"> </el-alert>
    </div>
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
        <el-table-column label="Gender" prop="gender" />
        <el-table-column label="Fee" prop="monthly_fee" />
        <el-table-column label="DOB">
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
  Document,
  Edit,
  Message,
  Search,
  Star,
  ArrowDown,
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
.demo-pagination-block {
  margin-top: 10px;
}
.demo-pagination-block .demonstration {
  margin-bottom: 16px;
}
</style>