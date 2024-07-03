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
                  <el-col :span="6"  :xs="14" :sm="12" :md="10" :lg="8" :xl="6">
                    <el-input v-model="query.keyword" placeholder="Student info" style="width: 200px;" class="filter-item" v-on:input="debounceInput" clearable />
                  </el-col>
                  <el-col :span="4"  :xs="14" :sm="12" :md="10" :lg="6" :xl="4">
                    <el-select v-model="query.stdclass" placeholder="Class" clearable style="width: 130px" class="filter-item" @change="handleFilter">
                      <el-option v-for="item in classes" :key="item.id" :label="upperFirst(item.name)" :value="item.id" />
                    </el-select>
                  </el-col>
                  <el-col :span="2"  :xs="14" :sm="12" :md="10" :lg="3" :xl="3">
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
          height="500"
          stripe 
          style="width: 100%"
          v-loading="listloading"
          @selection-change="handleSelectionChange"
          :table-layout="auto"
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
                  <el-dropdown-item icon="el-icon-delete" :command="'certificat~'+scope.row.id">Character Certificat</el-dropdown-item>
                  <el-dropdown-item icon="el-icon-delete" :command="'schoolleaving~'+scope.row.id">School Leaving Certificat</el-dropdown-item>
                  <el-dropdown-item icon="el-icon-delete" :command="'delete~'+scope.row.id">Delete Student</el-dropdown-item>
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
    <add-student  :if="addstudentpop" :addeditstudentprop="addstudentpop" :stdid="stdid" @closeAddStudent="closeAddStudent()"/>
    <pay-fee v-if="openpayfee" :openpayfee="openpayfee" :stdid="stdid" @donePayFee="donePayFee" />
    <fee-detail v-if="openfeedetail" :openfeedetail="openfeedetail" :stdid="studentid" @doneFeeDetail="doneFeeDetail" />
    <character-certificate  v-if="showcharactercertificate" :showcharactercertificate="showcharactercertificate" :stdid="studentid" @doneFeeDetail="doneFeeDetail" />
    <school-leaving-certificate  v-if="showschoolleavingcertificate" :showschoolleavingcertificate="showschoolleavingcertificate" :stdid="studentid" @doneFeeDetail="doneFeeDetail" />
    <fee-print v-if="openfeeprint" :feeid="feeid" :openfeeprint="openfeeprint" @doneFeePrint="doneFeePrint" />
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
import { editClass } from '@/api/student.js';
import HeadControls from '@/components/HeadControls.vue';
const student = new Resource('students');
const classes = new Resource('classes');
export default {
  name: 'StudentList',
  components: { Pagination, AddStudent,  PayFee, FeePrint, FeeDetail, HeadControls, CharacterCertificate, SchoolLeavingCertificate },
  directives: { },
  filters: {
    
  },
  data() {
    return {
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

      if (method == 'delete') {
        this.handleDelete(id);
      }
      if (method == 'certificat') {
        this.showCharacterCertificate(id);
      }
      if (method == 'schoolleaving') {
        this.SchoolLeavingCertificate(id);
      }
    },
    debounceInput: debounce(function (e) {
      this.getList();
    }, 500),
    async getList() {
      this.listloading = true;
      const filterKey = this.query.filtercol;
      const filterValue = this.query.keyword;
      this.query.filter = {
        [filterKey]: filterValue,
        ['stdclass']: this.query.stdclass,
      };
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
          else 
            this.query.filter[filter] = 'Yes';
        });
      }
      const { data } = await student.list(this.query);
      this.listloading = false;
      this.list = data.students.data;
      this.total = data.students.total;
      // alert(this.total);
    },

    async getClasses() {
      const{ data } = await classes.list();
      this.classes = data.classes.data;
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
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['Roll#', 'Adm #', 'Name', 'Parent Name', 'Phone','Class','Gender','Fee','DOB'];
        const filterVal = ['roll_no', 'adminssion_number', 'name', 'parent_name', 'phone','class','gender','fee','dob'];
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
          adminssion_number: record.adminssion_number,
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
    showFeeDetails(id) {
      this.studentid = id;
      this.openfeedetail = true;
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