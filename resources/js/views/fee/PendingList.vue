<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button style="margin-right: 10px;" type="success" icon="el-icon-plus" @click="openpayfee = true">
        Pay Fee
      </el-button>
      <el-button type="danger" icon="el-icon-message" :loading="smsloading" @click="addSmsQueue()">
        Add to SMS Queue
      </el-button>
    </div>
    <el-table
      ref="tbl_pendingfee"
      :data="pendingfee"
      style="width: 100%"
      :loading="loading"
      @selection-change="handleSelectionChange"
    >
      <el-table-column
        type="selection"
        width="55"
      />
      <el-table-column label="ID" prop="roll_no" />
      <el-table-column label="Student" prop="name" />
      <el-table-column label="Parent" prop="parent" />
      <el-table-column label="Phone" prop="phone" />
      <el-table-column label="Class" prop="classname" />
      <el-table-column label="Paid Till">
        <template slot-scope="scope">
          <span style="font-weight:bold; color: #000">{{ scope.row.payment_to_date | dateformat}}</span>
        </template>
      </el-table-column>
      <el-table-column label="Paid at">
        <template slot-scope="scope">
          {{ scope.row.paidat | dateformat}}
        </template>
      </el-table-column>
      <el-table-column align="right">
        <template slot="header" slot-scope="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
        </template>
        <template slot-scope="scope">
          <el-button
            size="mini"
            type="danger"
            @click="payFee(scope.row.id, scope.row.name)"
          >Pay Fee</el-button>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
    <pay-fee v-if="openpayfee" :openpayfee="openpayfee" :stdid="stdid" @donePayFee="donePayFee" />
    <fee-print v-if="openfeeprint" :feeid="feeid" :openfeeprint="openfeeprint" @doneFeePrint="doneFeePrint" />
  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import PayFee from './component/PayFee';
import FeePrint from './component/FeePrint';
import Resource from '@/api/resource';
import moment from 'moment';
const pendingfeePro = new Resource('pendingfee');
const feePro = new Resource('fee');
const smsPro = new Resource('smsqueue');
export default {
  name: '',
  components: { Pagination, PayFee, FeePrint },
  directives: { },
  filters: {
    dateformat: (date) => {
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    },
  },
  data() {
    return {
      feeid: null,
      stdid: null,
      pendingfee: null,
      openpayfee: false,
      openfeeprint: false,
      search: '',
      total: 0,
      students: null,
      findstudent: false,
      loading: false,
      smsloading: false,
      downloading: false,
      editnow: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
        pending: true,
      },
      multipleSelection: [],
    };
  },
  computed: {
  },
  created() {
    this.getList();
  },
  methods: {
    debounceInput: _.debounce(function (e) {
      this.getList();
    }, 500),
    donePayFee(data) {
      this.getList();
      this.openpayfee = false;
      console.log(data);
      if (data.print) {
        this.openfeeprint = true;
        this.feeid = data.feeid;
      }
    },
    async getList() {
      this.loading = true;
      const { data } = await pendingfeePro.list(this.query);
      this.pendingfee = data.fee.data;
      this.total = data.fee.total;
      this.loading = false;
    },
    async search_data() {
      await this.getList();
    },
    async handleEdit(id, name) {
      const { data } = await feePro.get(id);
      this.fee = data.fee;
      this.editnow = true;
    },
    async handleDelete(id, name) {
      this.confirm('Do you really want to delete?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }).then(async  () => {
        await feePro.destroy(id);
        this.getList();
        this.message({
          type: 'success',
          message: name + ' Delete successfully',
        });
      });
    },
    payFee(id, name) {
      this.stdid = id;
      this.openpayfee = true;
    },
    doneFeePrint() {
      this.openfeeprint = null;
    },
    handleSelectionChange(val) {
      console.log(val);
      this.multipleSelection = val;
    },
    async addSmsQueue() {
      this.smsloading = true;
      const sms = { sms: this.multipleSelection };
      if(this.multipleSelection.length > 0){
          await smsPro.store(sms).then(result => {
          this.smsloading = false;
          this.$message({
            message: 'SMS added to queue successfully.',
            type: 'success',
          });
          this.$refs.tbl_pendingfee.clearSelection();
          this.multipleSelection = [];
        }).catch(() => {
          this.$message({
            message: 'Something wrong while add record.',
            type: 'error',
          });
          this.smsloading = false;
          return false;
        });
      }else{
        this.smsloading = false;
        this.$message({
            message: 'Please Select Data Carefully.',
            type: 'error',
          });
      }
      
    },
  },
};
</script>
<style  scoped>
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