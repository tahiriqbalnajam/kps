<template>
  <div class="app-container">
    <div class="filter-container">
        <head-controls>
            <div class="header-flex">
              <div class="header-left">
                <el-select v-model="query.filtercol" placeholder="Class" class="filter-item">
                  <el-option v-for="filter in filtercol" :key="filter.col" :label="filter.display" :value="filter.col" />
                </el-select>
                <el-input v-model="query.keyword" placeholder="Parent info" class="filter-item" v-on:input="debounceInput" />
                <el-button class="filter-item" type="primary" :icon="Search" @click="handleFilter">
                  {{ $t('table.search') }}
                </el-button>
                <el-checkbox v-model="query.has_app" @change="handleFilter" border class="filter-item">
                  App Installed
                </el-checkbox>
              </div>
              <div class="header-right">
                <el-button-group>
                  <el-tooltip content="Add Parent" placement="top">
                    <el-button type="info" @click="addparentpop = true">
                      <el-icon :size="15"><Plus /></el-icon>
                    </el-button>
                  </el-tooltip>
                  <el-tooltip content="Bulk Generate User Accounts" placement="top">
                    <el-button type="success" @click="handleBulkCreateAccounts">
                      <el-icon><User /></el-icon>
                    </el-button>
                  </el-tooltip>
                  <el-tooltip content="Download Login Credentials" placement="top">
                    <el-button :loading="credentialsLoading" type="warning" @click="handleDownloadCredentials">
                      <el-icon><Key /></el-icon>
                    </el-button>
                  </el-tooltip>
                  <el-tooltip content="Export to Excel" placement="top">
                    <el-button :loading="downloadLoading" type="danger" @click="handleDownload">
                      <el-icon><Download /></el-icon>
                    </el-button>
                  </el-tooltip>
                </el-button-group>
              </div>
            </div>
        </head-controls>
    </div>
    <el-table
      :data="parents"
      style="width: 100%"
       max-height="450"
    >
      <el-table-column label="ID" prop="id" />
      <el-table-column label="Name" prop="name" />
      <el-table-column label="Phone" prop="phone" />
      <el-table-column label="Address" prop="address" />
      <el-table-column label="CNIC" prop="cnic" />
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
            size="mini"
            type="warning"
            @click="handleCreateAccount(scope.row)"
          >Create Account</el-button>
        </template>
      </el-table-column>
      <el-table-column label="Children" prop="children">
        <template #default="scope">
          <el-table :data=" scope.row.students" size="small">
            <el-table-column type="index"/>
            <el-table-column property="name" label="Name">
              <template #default="studentScope">
                <router-link 
                  :to="`/students/report/${studentScope.row.id}`"
                  class="student-link"
                >
                  {{ studentScope.row.name }}
                </router-link>
              </template>
            </el-table-column>
            <el-table-column property="stdclasses.name" label="Class" />
          </el-table>
        </template>
      </el-table-column>
      <el-table-column align="right">
        <template #header="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search"  v-on:input="debounceInput" />
        </template>
        <template #default="scope">
          <el-tooltip content="Edit Parent" placement="top">
            <el-button
              size="mini"
              @click="handleEdit(scope.row.id, scope.row.name)"
            >Edit</el-button>
          </el-tooltip>
          <el-tooltip
            v-if="scope.row.students && scope.row.students.length > 0"
            :content="`Cannot delete: this parent has ${scope.row.students.length} child(ren)`"
            placement="top"
          >
            <el-button size="mini" type="danger" disabled>Delete</el-button>
          </el-tooltip>
          <el-tooltip
            v-else
            content="Delete Parent"
            placement="top"
          >
            <el-button
              size="mini"
              type="danger"
              @click="handleDelete(scope.row.id, scope.row.name)"
            >Delete</el-button>
          </el-tooltip>
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
    <add-parent v-if="addparentpop" :editnowprop="addparentpop" :parentid="parentid" @closePopUp="closePopUp()" />

    <el-dialog v-model="createAccountDialog" title="Create User Account" width="400px" :close-on-click-modal="false">
      <div v-if="createAccountParent">
        <p>Creating account for <strong>{{ createAccountParent.name }}</strong></p>
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

    <el-dialog v-model="bulkCreateAccountsDialog" title="Bulk Generate User Accounts" width="400px" :close-on-click-modal="false">
      <p>This will create user accounts for <strong>all parents</strong> who don't have one yet. Each account will use <code>phone@idlschool.pk</code> as the email.</p>
      <el-form ref="bulkCreateAccountsForm" :model="bulkCreateAccountsForm" :rules="bulkCreateAccountsRules" label-position="top">
        <el-form-item label="Default Password" prop="password">
          <el-input v-model="bulkCreateAccountsForm.password" type="password" placeholder="Enter default password for all new accounts" show-password />
        </el-form-item>
      </el-form>
      <template #footer>
        <el-button @click="bulkCreateAccountsDialog = false">Cancel</el-button>
        <el-button type="primary" :loading="bulkCreateAccountsLoading" @click="submitBulkCreateAccounts">Generate Accounts</el-button>
      </template>
    </el-dialog>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import Resource from '@/api/resource';
import axios from 'axios';
import AddParent from '@/views/parents/AddParent.vue';
import HeadControls from '@/components/HeadControls.vue';
import { debounce } from 'lodash';
const parentsPro = new Resource('parents');
export default {
  name: 'ParentList',
  components: { Pagination, AddParent, HeadControls},
  directives: { },
  filters: {
    dateformat: (date) => {
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    },
  },
  data() {
    return {
      downloadLoading: false,
      credentialsLoading: false,
      parentid: null,
      parents: null,
      search: '',
      total: 0,
      loading: false,
      downloading: false,
      addparentpop: false,
      addstudentpop: false,
      search: '',
      formLabelWidth: '250',
      parent: {
        id: '',
        name: '',
      },
      createAccountDialog: false,
      createAccountLoading: false,
      createAccountParent: null,
      createAccountForm: { email: '', password: '' },
      createAccountRules: {
        email: [{ required: true, type: 'email', message: 'Valid email required', trigger: 'blur' }],
        password: [{ required: true, min: 6, message: 'Minimum 6 characters', trigger: 'blur' }],
      },
      bulkCreateAccountsDialog: false,
      bulkCreateAccountsLoading: false,
      bulkCreateAccountsForm: { password: '' },
      bulkCreateAccountsRules: {
        password: [{ required: true, min: 6, message: 'Minimum 6 characters', trigger: 'blur' }],
      },
      filtercol: [
        { col: 'name', display: 'Name' },
        { col: 'cnic', display: 'CNIC' },
        { col: 'phone', display: 'Phone#' },
        { col: 'all', display: 'All' },
      ],
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        filtercol: 'name',
        role: '',
        has_app: false,
      },
    };
  },
  computed: {
  },
  created() {
    this.getList();
  },
  methods: {
    debounceInput: debounce(function (e) {
      this.getList();
    }, 500),
    closeAddParent(parm) {
      this.addparentpop = false;
    },
    async handleSizeChange (val) {
      console.log(`每页 ${val} 条`);
      this.query.limit = val
      await this.getList()
    },
    async handleCurrentChange (val) {
      this.query.page = val
      await this.getList()
    },
    async getList() {
      const { data } = await parentsPro.list(this.query);
      this.parents = data.parents.data;
      this.total = data.parents.total;
    },
    async search_data() {
      await this.getList();
    },
    handleEdit(id, name) {
      this.parentid = id;
      this.addparentpop = true;
    },
    closePopUp() {
      this.parentid = null;
      this.addparentpop = false;
      this.getList();
    },
    addStudentFunc() {
      this.addstudentpop = true;
    },
    closeAddStudent() {
      this.addstudentpop = !this.addstudentpop;
      this.stdid = null;
      this.getList();
    },
    handleFilter() {
      this.getList();
    },
    async handleDelete(id, name) {
      try {
        await this.$confirm(`Delete parent "${name}"? This cannot be undone.`, 'Confirm', {
          confirmButtonText: 'Delete',
          cancelButtonText: 'Cancel',
          type: 'warning',
        });
        await parentsPro.destroy(id);
        this.$message.success('Parent deleted successfully');
        this.getList();
      } catch (e) {
        if (e !== 'cancel') {
          const msg = e.response?.data?.message || 'Failed to delete parent';
          this.$message.error(msg);
        }
      }
    },
    handleCreateAccount(parent) {
      this.createAccountParent = parent;
      const phone = parent.phone.replace(/[^0-9]/g, '');
      const suggestedEmail = phone + '@idlschool.pk';
      // Check if any other parent in the loaded list already uses this email
      const emailTaken = this.parents.some(
        p => p.id !== parent.id && p.user && p.user.email === suggestedEmail
      );
      this.createAccountForm.email = emailTaken ? '' : suggestedEmail;
      this.createAccountForm.password = '';
      this.createAccountDialog = true;
      if (emailTaken) {
        this.$nextTick(() => {
          this.$message.warning(`Email "${suggestedEmail}" is already in use. Please enter a different email.`);
        });
      }
    },
    async submitCreateAccount() {
      try {
        await this.$refs.createAccountForm.validate();
      } catch {
        return;
      }
      this.createAccountLoading = true;
      try {
        await axios.post(`/api/parents/${this.createAccountParent.id}/create-account`, this.createAccountForm);
        this.$message.success('User account created successfully');
        this.createAccountDialog = false;
        this.getList();
      } catch (error) {
        const errors = error.response?.data?.errors;
        const msg = (errors && errors.email && errors.email[0])
          || error.response?.data?.message
          || 'Failed to create account';
        this.$message.error(msg);
      } finally {
        this.createAccountLoading = false;
      }
    },
    handleBulkCreateAccounts() {
      this.bulkCreateAccountsForm.password = '';
      this.bulkCreateAccountsDialog = true;
    },
    async submitBulkCreateAccounts() {
      try {
        await this.$refs.bulkCreateAccountsForm.validate();
      } catch {
        return;
      }
      this.bulkCreateAccountsLoading = true;
      try {
        const { data } = await axios.post('/api/parents/bulk-create-accounts', this.bulkCreateAccountsForm);
        this.$message.success(data.message || 'Accounts created successfully');
        this.bulkCreateAccountsDialog = false;
        this.getList();
      } catch (error) {
        const msg = error.response?.data?.message || 'Failed to generate accounts';
        this.$message.error(msg);
      } finally {
        this.bulkCreateAccountsLoading = false;
      }
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['Name', 'Phone', 'Address', 'Profession', 'CNIC', 'Children'];
        const data = this.parents.map(p => {
          const children = p.students && p.students.length
            ? p.students.map((s, i) => `${i + 1}. ${s.name}`).join(', ')
            : '-';
          return [p.name || '', p.phone || '', p.address || '', p.profession || '', p.cnic || '', children];
        });
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'parents_list',
        });
        this.downloadLoading = false;
      });
    },
    handleDownloadCredentials() {
      this.credentialsLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['Parent Name', 'Phone', 'Children'];
        const data = this.parents
          .filter(p => p.user)
          .map(p => {
            const children = p.students && p.students.length
              ? p.students.map((s, i) => `${i + 1}. ${s.name}`).join(', ')
              : '-';
            return [p.name || '', p.user.phone || p.phone || '', children];
          });
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'parent_login_credentials',
        });
        this.credentialsLoading = false;
      });
    },
  },
};
</script>
<style >
  .header-flex {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    padding: 0 15px 15px 0;
  }
  .header-left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
  }
  .header-left .el-select {
    width: 120px;
  }
  .header-left .el-input {
    width: 180px;
  }
  .header-left .el-select,
  .header-left .el-input,
  .header-left .el-button,
  .header-left .el-checkbox {
    flex-shrink: 0;
  }
  .header-left .el-checkbox.is-bordered {
    height: 32px;
    padding: 0 12px;
  }
  .header-right {
    display: flex;
    align-items: center;
    flex-shrink: 0;
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

  /* User account link styling */
  .user-account-link {
    color: #409eff;
    text-decoration: none;
    font-weight: 500;
  }
  .user-account-link:hover {
    text-decoration: underline;
  }

  /* Student name link styling */
  .student-link {
    color: #409eff;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s ease;
  }
  
  .student-link:hover {
    color: #66b1ff;
    text-decoration: underline;
  }
  
  .student-link:active {
    color: #3a8ee6;
  }
</style>
<style>
.el-popper.is-customized {
  /* Set padding to ensure the height is 32px */
  padding: 6px 12px;
  background: linear-gradient(90deg, rgb(159, 229, 151), rgb(204, 229, 129));
}

.el-popper.is-customized .el-popper__arrow::before {
  background: linear-gradient(45deg, #b2e68d, #bce689);
  right: 0;
}
</style>