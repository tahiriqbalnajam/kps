<template>
  <div>
    <el-drawer
      title="Add Transaction"
      :modelValue="isshowdrawer"
      direction="rtl"
      size="75%"
      custom-class="custom_drawer"
      @close="drawerClose"
    >
      <div class="demo-drawer__content">
        <el-form ref="ruleForm" :model="transaction" :rules="rules" label-position="left">
          <el-row :gutter="20">
            <el-col :span="8">
              <el-form-item label="Account Jama" prop="accjama">
                <el-select
                  v-model="transaction.jama_account"
                  clearable
                  filterable
                  remote
                  reserve-keyword
                  default-first-option
                  placeholder="Start typing or scaning for product"
                  :remote-method="getAccounts"
                  :loading="accounts_loading"
                  class="selectproduct"
                  label="Select Product"
                  :input="debounceInput"
                >
                  <el-option
                    v-for="account in accounts"
                    :key="account.id"
                    :label="account.name"
                    :value="account.id"
                  />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="Account Naam" prop="accnaam">
                <el-select
                  v-model="transaction.naam_account"
                  clearable
                  filterable
                  remote
                  reserve-keyword
                  default-first-option
                  placeholder="Start typing or scaning for product"
                  :remote-method="getAccounts"
                  :loading="accounts_loading"
                  class="selectproduct"
                  label="Select Product"
                >
                  <el-option
                    v-for="account in accounts"
                    :key="account.id"
                    :label="account.name"
                    :value="account.id"
                  />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="8">
              <el-form-item label="Amount" prop="amount">
                <el-input-number v-model="transaction.amount" controls-position="right" :min="1" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-form-item label="Details">
            <el-input v-model="transaction.comments" type="textarea" placeholder="Enter deatils here" />
          </el-form-item>
        </el-form>
        <div class="demo-drawer__footer">
          <el-button @click="cancelForm">Cancel</el-button>
          <el-button type="primary" :loading="loading" @click="addTransaction('ruleForm')">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
        </div>
      </div>
    </el-drawer>
  </div>
</template>
<script>
import Resource from '@/api/resource';
import { debounce } from 'lodash';
const Trans = new Resource('transaction');
const userRes = new Resource('getusers');
export default {
  name: 'AddTransaction',
  components: { },
  directives: { },
  props: {
    showdrawer: { type: Boolean },
    accountid: {
      type: Number,
      default: null,
    },
    accountTitle: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      loading: false,
      accounts_loading: false,
      search: '',
      total: 0,
      downloading: false,
      accounts_jama: [],
      accounts: [],
      transaction: {
        jama_account: '',
        naam_account: '',
        amount: 0,
        comments: '',
      },
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        select: '',
      },
      rules: {

      },
    };
  },
  computed: {
    isshowdrawer: {
      get: function(){
        return this.showdrawer;
      },
      set: function(newValue) {
        this.accounts = { id: this.accountid, name: this.accountTitle };
        return newValue;
      },
    },
  },
  created() {
    if (this.accountid) {
      this.accounts = [{ id: this.accountid, name: this.accountTitle }];
    }
  },
  mounted() {
    this.drawer = this.showdrawer;
  },
  methods: {
    debounceInput: debounce(function (e) {
      this.getAccounts();
    }, 500),
    async getAccounts(query) {
      this.accounts_loading = true;
      this.query.keyword = query;
      const { data } = await userRes.list(this.query);
      this.accounts = data.users.data;
      this.accounts_loading = false;
    },
    cancelForm() {
      this.isshowdrawer = false;
    },
    async addTransaction(formName) {
      this.loading = true;
      await Trans.store(this.transaction).then(result => {
        this.loading = false;
        this.transaction.jama_account = '';
        this.transaction.naam_account = '';
        this.transaction.amount = 1;
        this.transaction.comments = '';
        this.$refs[formName].resetFields();
        this.$emit('toggledrawer', 'addtransaction');
        this.$message({
          message: 'Added Successfully.',
          type: 'success',
        });
      }).catch(error => {
        this.loading = false;
      });
    },
    drawerClose() {
      this.transaction.jama_account = '';
      this.transaction.naam_account = '';
      this.transaction.amount = 1;
      this.transaction.comments = '';
      this.loading = false;
      this.isshowdrawer = false;
      this.$emit('toggledrawer', 'justclose');
    },
  },
};
</script>
<style lang="css" scoped>
  .drawer .el-drawer__header {
    padding: 0 !important;
    font-size: 21px;
    font-weight: bold;
  }
  .demo-drawer__content {
    padding: 20px;
  }
</style>
