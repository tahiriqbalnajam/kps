<template>
  <div class="app-container">
    <div class="filter-container">
      <el-card class="box-card">
        <head-controls>
          <el-row justify="space-between">
            <el-col :span="12">
              <el-row :gutter="20">
                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl ="6">
                  <el-select v-model="query.status" class="filter-item" @change="handleFilter">
                    <el-option label="Active" value="active" />
                    <el-option label="Inactive" value="inactive" />
                    <el-option label="All" value="all" />
                  </el-select>
                </el-col>
                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl ="6">
                  <el-select v-model="query.filtercol" placeholder="Filter" class="filter-item">
                    <el-option v-for="filter in filtercol" :key="filter.col" :label="filter.display" :value="filter.col" />
                  </el-select>
                </el-col>
                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl ="6">
                  <el-input v-model="query.keyword" placeholder="Teacher info" class="filter-item" v-on:input="debounceInput" />
                </el-col>
                <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl ="6">
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
      <el-table-column label="Name">
        <template #default="scope">
          <el-link :href="'#/teacher/profile/'+ scope.row.id">
            {{ scope.row.name }}
          </el-link>
        </template>
      </el-table-column>
      <el-table-column label="ID" prop="teacher_special_id" />
      <el-table-column label="Designation" prop="designation" show-overflow-tooltip />
      <el-table-column label="Test Avg" align="center" width="90">
        <template #default="scope">
          <el-tag v-if="scope.row.avg_pct != null" size="small" :type="avgTagType(scope.row.avg_pct)">
            {{ Math.round(scope.row.avg_pct) }}%
          </el-tag>
          <span v-else class="no-avg">—</span>
        </template>
      </el-table-column>
      <el-table-column label="CNIC" prop="cnic" />
      <el-table-column label="Phone" prop="phone" />
      <el-table-column label="User Account">
        <template #default="scope">
          <router-link
            v-if="scope.row.user"
            :to="`/administrator/users/edit/${scope.row.user.id}`"
            class="user-account-link"
          >
            {{ scope.row.user.email }}
          </router-link>
          <el-button
            v-else
            size="small"
            type="warning"
            @click="handleCreateAccount(scope.row)"
          >Create Account</el-button>
        </template>
      </el-table-column>
      <el-table-column label="Gender" align="center" width="70">
        <template #default="scope">
          {{ genderShort(scope.row.gender) }}
        </template>
      </el-table-column>
      <el-table-column label="Assigned Class" min-width="110">
        <template #default="scope">
          {{ scope.row.class_name || '—' }}
        </template>
      </el-table-column>
      <el-table-column label="Pay" prop="pay" />
      <el-table-column align="right">
        <template slot="header" #header="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
        </template>
        <template #default="scope">
          <el-button-group>
            <el-button
              size="small"
              type="primary"
              @click="openIDCard(scope.row)"
              title="Print ID Card"
            >
              <el-icon :size="15"><Postcard /></el-icon>
            </el-button>

            <el-button
              size="small"
              @click="handleEdit(scope.row.id, scope.row.name)"
              title="Edit"
            >
              <el-icon :size="15"><Edit /></el-icon>
            </el-button>
            <el-button
              size="small"
              type="danger"
              @click="handleDelete(scope.row.id, scope.row.name)"
              title="Delete"
            ><el-icon :size="15"><Delete /></el-icon></el-button>
          </el-button-group>
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
      title="Edit Record"
      :modelValue="editnow"
      direction="rtl"
      custom-class="demo-drawer"
      ref="drawer"
      size="90%"
      @close="updatelist()"
    >
      <div class="demo-drawer__content">
        <el-form :model="teacher" :rules="rules" ref="teacher">
          <el-divider content-position="left" style="margin-bottom: 30px;">
            <el-tag type="primary" effect="plain" round><b>1</b> Basic Info</el-tag>
          </el-divider>
          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="Name" :label-width="formLabelWidth" prop="name">
                <el-input v-model="teacher.name" autocomplete="off" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="Phone#" :label-width="formLabelWidth" prop="phone">
                <el-input v-model="teacher.phone" autocomplete="off" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="Joining Date" :label-width="formLabelWidth" prop="doj">
                <el-date-picker v-model="teacher.doj" type="date"  placeholder="Pick a joining date" format="DD/MM/YYYY"  value-format="YYYY-MM-DD"/>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="Decided Pay" :label-width="formLabelWidth">
                <el-input-number v-model="teacher.pay" autocomplete="off" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="CNIC#" :label-width="formLabelWidth" prop="cnic">
                <el-input v-model="teacher.cnic" autocomplete="off" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="Assigned Class" prop="class_id">
                <el-col :span="11">
                  <el-select v-model="teacher.class_id" placeholder="Classes">
                    <el-option
                      v-for="stdclass in classes"
                      :key="stdclass.id"
                      :label="stdclass.name"
                      :value="stdclass.id"
                      style="width: 100%">
                      <span style="float: left">{{ stdclass.name }}</span>
                      <span style="float: right">{{ stdclass.total_students }}</span>
                    </el-option>
                  </el-select>
                </el-col>
              </el-form-item>
            </el-col>

          </el-row>
          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="School Id" :label-width="formLabelWidth">
                <el-input v-model="teacher.teacher_special_id" autocomplete="off" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="Designation" :label-width="formLabelWidth" prop="designation">
                <el-input v-model="teacher.designation" autocomplete="off" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="Date of Birth" :label-width="formLabelWidth" prop="dob">
                <el-date-picker v-model="teacher.dob" type="date" placeholder="Pick a birth date" format="DD/MM/YYYY" value-format="YYYY-MM-DD"/>
              </el-form-item>
            </el-col>
          </el-row>
          <el-divider content-position="left" style="margin-bottom: 30px;">
            <el-tag type="primary" effect="plain" round><b>2</b> Other Info</el-tag>
          </el-divider>
          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="Father/Hus. Name" :label-width="formLabelWidth">
                <el-input v-model="teacher.father_name" autocomplete="off" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="F/H CNIC" :label-width="formLabelWidth">
                <el-input v-model="teacher.father_cnic" autocomplete="off" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="Gender" prop="region" :label-width="formLabelWidth">
                <el-select v-model="teacher.gender" placeholder="Gender">
                  <el-option label="Male" value="male" />
                  <el-option label="Female" value="female" />
                </el-select>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="Education" :label-width="formLabelWidth">
                <el-input v-model="teacher.education" autocomplete="off" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="Experience years" :label-width="formLabelWidth">
                <el-input v-model="teacher.experience" autocomplete="off" />
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="Address" :label-width="formLabelWidth">
                <el-input v-model="teacher.address" autocomplete="off" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-divider content-position="left" style="margin-bottom: 30px;">
            <el-tag type="primary" effect="plain" round><b>3</b> Others</el-tag>
          </el-divider>
          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="Status" prop="status">
                <el-radio-group v-model="teacher.status">
                  <el-radio-button label="active">Active</el-radio-button>
                  <el-radio-button label="inactive">Inactive</el-radio-button>
                </el-radio-group>
              </el-form-item>
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
    <el-dialog v-model="createAccountDialog" title="Create User Account" width="400px" :close-on-click-modal="false">
      <div v-if="createAccountTeacher">
        <p>Creating account for <strong>{{ createAccountTeacher.name }}</strong></p>
        <el-form ref="createAccountForm" :model="createAccountForm" :rules="createAccountRules" label-position="top">
          <el-form-item label="Email" prop="email">
            <el-input v-model="createAccountForm.email" placeholder="Email" />
          </el-form-item>
          <el-form-item label="Password" prop="password">
            <el-input v-model="createAccountForm.password" type="password" placeholder="Password" show-password />
          </el-form-item>
        </el-form>
      </div>
      <template #footer>
        <el-button @click="createAccountDialog = false">Cancel</el-button>
        <el-button type="primary" :loading="createAccountLoading" @click="submitCreateAccount">Create</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="linkExistingDialog" title="Account Already Exists" width="400px" :close-on-click-modal="false">
      <div v-if="existingUser">
        <p>
          <strong>{{ existingUser.name }}</strong>
          <span v-if="existingUser.roles && existingUser.roles.length"> — {{ formatRoles(existingUser.roles) }}</span>
        </p>
        <p>Link this teacher to that account and add the Teacher role? The account keeps its existing roles and password.</p>
      </div>
      <template #footer>
        <el-button @click="linkExistingDialog = false">Cancel</el-button>
        <el-button type="primary" :loading="createAccountLoading" @click="confirmLinkExisting">Link Account</el-button>
      </template>
    </el-dialog>

    <!-- Add the TeacherIDCard component at the end of template -->
    <teacher-idcard
      v-if="showIDCard"
      :showcardprop="showIDCard"
      @closeAddSection="closeAddSection"  
      :teacher="selectedTeacher"
    />
  </div>
</template>
<script>
import TeacherIdcard from '@/views/teachers/components/TeacherIdcard.vue';
import Pagination from '@/components/Pagination/index.vue';
//import AddStudent from '@/views/students/AddStudent.vue';
import Resource from '@/api/resource';
import axios from 'axios';
var stdClass = new Resource('classes');
import { debounce } from 'lodash';
const resourcePro = new Resource('teachers');
import {
    Check,
    Delete,
    Edit,
    Message,
    Search,
    Star,
    Printer,
} from '@element-plus/icons-vue'

export default {
  name: 'TeacherList',
  components: { 
    Pagination,
    TeacherIdcard,
  },
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
      filtercol: [
        { col: 'name', display: 'Name' },
        { col: 'cnic', display: 'CNIC' },
        { col: 'phone', display: 'Phone#' },
        { col: 'all', display: 'All' },
      ],
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
        designation: [
          { required: true, message: 'Please input designation', trigger: 'blur' },
        ],
        class_id: [
          { required: true, message: 'Please select class', trigger: 'blur' },
        ],
      },
      teacher: {
        id: '',
        name: '',
        teacher_special_id: '',
        designation: '',
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
        dob: '',
      },
      resetteacher: {
        id: '',
        name: '',
        teacher_special_id: '',
        designation: '',
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
        dob: '',
      },
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        filtercol: 'name',
        status: 'active',
        role: '',
      },
      showIDCard: false,
      selectedTeacher: {},
      createAccountDialog: false,
      createAccountLoading: false,
      createAccountTeacher: null,
      createAccountForm: { email: '', password: '' },
      createAccountRules: {
        email: [{ required: true, type: 'email', message: 'Valid email required', trigger: 'blur' }],
        password: [{ required: true, min: 6, message: 'Minimum 6 characters', trigger: 'blur' }],
      },
      linkExistingDialog: false,
      existingUser: null,
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
      const { data } = await resourcePro.list(this.query);
      this.list = data.teachers.data;
      this.total = data.teachers.total;
    },
    avgTagType(p) {
      if (p >= 80) return 'success';
      if (p >= 60) return 'warning';
      return 'danger';
    },
    genderShort(g) {
      const s = String(g || '').toLowerCase();
      if (s.startsWith('m')) return 'M';
      if (s.startsWith('f')) return 'F';
      return g || '—';
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
    handleCreateAccount(teacher) {
      this.createAccountTeacher = teacher;
      const phone = (teacher.phone || '').replace(/[^0-9]/g, '');
      const suggestedEmail = phone ? phone + '@idlschool.pk' : '';
      // Check if any other teacher in the loaded list already uses this email
      const emailTaken = this.list.some(
        t => t.id !== teacher.id && t.user && t.user.email === suggestedEmail
      );
      this.createAccountForm.email = (!suggestedEmail || emailTaken) ? '' : suggestedEmail;
      this.createAccountForm.password = '';
      this.createAccountDialog = true;
      if (!phone) {
        this.$nextTick(() => {
          this.$message.warning('This teacher has no phone number, so no email could be suggested. Please enter one.');
        });
      } else if (emailTaken) {
        this.$nextTick(() => {
          this.$message.warning(`Email "${suggestedEmail}" is already in use. Please enter a different email.`);
        });
      }
    },
    async submitCreateAccount(linkExisting = false) {
      if (!linkExisting) {
        try {
          await this.$refs.createAccountForm.validate();
        } catch {
          return;
        }
      }
      this.createAccountLoading = true;
      try {
        await axios.post(`/api/teachers/${this.createAccountTeacher.id}/create-account`,
          { ...this.createAccountForm, link_existing: linkExisting });
        this.$message.success(linkExisting
          ? 'Existing account linked and Teacher role added'
          : 'User account created successfully');
        this.createAccountDialog = false;
        this.linkExistingDialog = false;
        this.getList();
      } catch (error) {
        const res = error.response?.data;
        // The email already belongs to a user (often the same person's parent
        // account). Ask before touching that account.
        if (res?.code === 'email_exists') {
          this.existingUser = res.existing_user;
          this.linkExistingDialog = true;
          return;
        }
        const errors = res?.errors;
        const msg = (errors && errors.email && errors.email[0])
          || res?.message
          || 'Failed to create account';
        this.$message.error(msg);
      } finally {
        this.createAccountLoading = false;
      }
    },
    confirmLinkExisting() {
      this.linkExistingDialog = false;
      this.submitCreateAccount(true);
    },
    formatRoles(roles) {
      return roles.map(r => r.charAt(0).toUpperCase() + r.slice(1)).join(', ');
    },
    async onSubmit( formName) {
      this.loading = true;
      await this.$refs[formName].validate(valid => {
        if (valid) {
          if(this.teacher.id != '') {
            resourcePro.update(this.teacher.id, this.teacher);
            this.editnow = false;
            this.getList();
            this.loading = false;
          } else {
            resourcePro.store(this.teacher);
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
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['Name', 'Gender', 'DOB', 'CNIC', 'Pay', 'Phone', 'Assigned Class'];
        const filterVal = ['name', 'gender', 'dob', 'cnic', 'pay', 'phone', 'class_name'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'teachers_list',
        });
        this.downloadLoading = false;
      });
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
    openIDCard(teacher) {
      this.selectedTeacher = teacher;
      // Ensure we have the needed data before opening the dialog
      if (teacher && teacher.id) {
        console.log('Opening ID card for teacher:', teacher);
        this.showIDCard = true;
      } else {
        console.error('Missing teacher data:', teacher);
        this.$message.error('Could not open ID card: Missing teacher data');
      }
    },
    closeAddSection() {
      this.showIDCard = false;
    },
  },
};
</script>
<style  scoped>
  .no-avg {
    color: #a0aec0;
  }
  .user-account-link {
    color: #409eff;
    text-decoration: none;
    font-weight: 500;
  }
  .user-account-link:hover {
    text-decoration: underline;
  }
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