<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button style="margin-right: 10px;" type="success" @click="payFee(null)">
        <el-icon class="el-icon--left"><Money /></el-icon>Pay Fee
      </el-button>
      <el-button type="danger" :loading="smsloading" @click="addSmsQueue()">
       <el-icon class="el-icon--left"><Message /></el-icon> Add to SMS Queue
      </el-button>
    </div>
    <el-table
      ref="tbl_pendingfee"
      :data="pendingfee"
      style="width: 100%"
      :loading="loading"
      @selection-change="handleSelectionChange"
      size="small"
    >
      <el-table-column
        type="selection"
        width="55"
      />
      <el-table-column label="Student" prop="name" />
      <el-table-column label="Parent" prop="parent" />
      <el-table-column label="Phone" prop="phone" />
      <el-table-column label="Class" prop="classname" />
      <el-table-column label="Paid Till">
        <template #default="scope">
          <span style="font-weight:bold; color: #000">{{ dateformat(scope.row.payment_to_date)}}</span>
        </template>
      </el-table-column>
      <el-table-column label="Paid at">
        <template #default="scope">
          {{ dateformat(scope.row.paidat)}}
        </template>
      </el-table-column>
      <el-table-column align="right">
        <template slot="header" #header="scope">
          <el-input ref="search" v-model="query.keyword" size="small" placeholder="Type to search" v-on:input="debounceInput" />
        </template>
        <template #default="scope">
          <el-button
            size="small"
            type="danger"
            @click="payFee(scope.row.id, scope.row.name)"
          >Pay Fee</el-button>
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
    <pay-fee v-if="openpayfee" :openpayfee="openpayfee" :stdid="stdid" @donePayFee="donePayFee" />
    <fee-print v-if="openfeeprint" :feeid="feeid" :openfeeprint="openfeeprint" @doneFeePrint="doneFeePrint" />
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import PayFee from './component/PayFee.vue';
import FeePrint from './component/FeePrint.vue';
import Resource from '@/api/resource';
import { Plus,Message,Money, Edit } from '@element-plus/icons-vue'
import moment from 'moment';
import { debounce } from 'lodash';
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
    dateformat(date){
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    },
    debounceInput: debounce(function (e) {
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
    async handleSizeChange (val) {
      this.query.limit = val
      await this.getList()
    },
    async handleCurrentChange (val) {
      this.query.page = val
      await this.getList()
    },
    async getList() {
      this.loading = true;
      const { data } = await pendingfeePro.list(this.query);
      this.pendingfee = data.students.data;
      this.total = data.students.total;
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