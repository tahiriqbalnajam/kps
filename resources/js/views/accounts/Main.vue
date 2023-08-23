<template>
  <div class="app-container">
    <el-row style="margin-bottom:20px;">
      <el-input v-model="listQuery.keyword" class="demo-input-label" placeholder="Enter account name" clearable @keyup.native="debounceInput" />
      <el-select
        v-model="listQuery.account_type"
        clearable
        filterable
        remote
        reserve-keyword
        default-first-option
        placeholder="Find Account"
        :remote-method="getAccounts"
        :loading="loading"
      >
        <el-option
          v-for="acount in accounts"
          :key="acount.id"
          :label="acount.title"
          :value="acount.id"
        />
      </el-select>
      <el-button type="primary" @click="getList()"><svg-icon icon-class="search" /></el-button>
      <el-button type="success" icon="el-icon-user-solid" @click="addCustomer">Add Customer</el-button>
      <el-button
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="danger"
        icon="el-icon-download"
        @click="handleDownload"
      >
        {{ $t('table.export') }}
      </el-button>
    </el-row>
    <el-table
      :data="list"
      style="width: 100%"
    >
      <el-table-column label="ID" prop="id" />
      <el-table-column label="Name" prop="name" />
      <el-table-column label="Type" prop="account_type.title" />
      <el-table-column label="Phone" prop="phone" />
      <el-table-column label="Address" prop="address" />
      <el-table-column align="right">
        <template slot="header" slot-scope="scope">
          <el-input ref="search" v-model="listQuery.keyword" size="mini" placeholder="Type to search" @keyup.native="debounceInput" />
        </template>
        <template slot-scope="scope">
          <el-button
            type="success"
            icon="el-icon-s-order"
            size="mini"
            circle
            @click="customer_khata(scope.row.id, scope.row.name)"
          />
          <el-button
            type="primary"
            icon="el-icon-edit"
            size="mini"
            circle
            @click="handleEdit(scope.row.id, scope.row.name)"
          />
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" ref="search " :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
    <!-- new account form -->
    <add-account v-if="customerForm" :accountid="accountid" @addcustomer="addCustomerpopup" />
    <el-drawer
      :title="title"
      :visible.sync="drawer"
      direction="rtl"
      size="70%"
    >
      <div class="filter-container">
        <el-date-picker
          v-model="listRecord.daterange"
          type="daterange"
          align="right"
          unlink-panels
          range-separator="To"
          start-placeholder="Start date"
          end-placeholder="End date"
          :picker-options="pickerOptions"
          format="dd/MM/yyyy"
          value-format="yyyy-MM-dd"
          style="width:415px"
          :default-value="defaultDate"
        />
        <el-button class="" type="primary" icon="el-icon-search" @click="customer_khata(selected_account)">
          {{ $t('table.search') }}
        </el-button>
        <el-button type="primary" plain @click="print">Print</el-button>
        <el-button type="primary" plain @click="addtransactionshow = true">Add Transaction</el-button>
      </div>
      <el-card class="box-card">
        <div class="total-records-detail">
          <span class="total-records-title">Current Detail: </span>
          <el-tag
            effect="dark"
            class="tagheader"
          >
            {{ finalTotal }}
          </el-tag>
        </div>
        <el-table id="printMe" stripe border :data="gridData" style="width: 100%" height="400">
          <el-table-column label="Date" class="table-data">
            <template slot-scope="scope">
              <i class="el-icon-time" />
              {{ scope.row.created_at | dateformat }}
            </template>
          </el-table-column>
          <el-table-column label="Jama">
            <template slot-scope="scope">
              {{ (scope.row.jama_account==selected_account) ? scope.row.amount : '--' }}
            </template>
          </el-table-column>last_date_total
          <el-table-column label="Naam">
            <template slot-scope="scope">
              {{ (scope.row.naam_account==selected_account) ? scope.row.amount : '--' }}
            </template>
          </el-table-column>
          <el-table-column label="Balance">
            <template slot-scope="scope">
              {{ scope.row.balance }}
            </template>
          </el-table-column>
          <el-table-column property="comments" label="Detail" />
        </el-table>
        <pagination v-show="totalRecord>0" :total="totalRecord" :page.sync="listRecord.page" :limit.sync="listRecord.limit" @pagination="customer_khata(selected_account)" />
      </el-card>
    </el-drawer>
    <el-alert
      v-show="showAlert=false"
      title="error alert"
      type="error"
      effect="dark"
    />
    <add-transaction v-if="addtransactionshow" :showdrawer="addtransactionshow" :accountid="selected_account" :account-title="title" @toggledrawer="closeTranaction" />
  </div>
</template>
<script>
//import { fetchListc } from '@/api/customer.js';
//import { fetchAcc } from '@/api/customer.js';
//import { search } from '@/api/customer.js';
//import { getKhata } from '@/api/customer.js';
import Resource from '@/api/resource';
const customerResource = new Resource('customer');
const areaRes = new Resource('areas');
//import { getKhataDate } from '@/api/customer.js';
import waves from '@/directive/waves';
import { parseTime } from '@/utils';
import moment from 'moment';
import Pagination from '@/components/Pagination/index.vue';
import AddTransaction from '@/views/accounts/AddTransaction.vue';
export default {
  name: 'Customer',
  directives: { waves },
  components: { Pagination, AddAccount, AddTransaction },
  filters: {
    dateformat: (date) => {
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    },
  },
  staticData: () => ({
    balance_session: 0,
  }),
  data() {
    return {
      addtransactionshow: false,
      accountid: null,
      balancetodate: 0,
      defaultDate: new Date().toJSON().slice(0, 10).replace(/-/g, '/'),
      list: [],
      accounts: [],
      total: 0,
      loading: true,
      customerForm: false,
      finalTotal: '',
      totalRecord: '',
      drawer: false,
      gridData: [],
      showAlert: false,
      newLength: '',
      title: null,
      selected_account: null,
      option: [],
      pickerOptions: {
        shortcuts: [{
          text: 'Last week',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
          },
        }, {
          text: 'Last month',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit('pick', [start, end]);
          },
        }, {
          text: 'Last 3 months',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
            picker.$emit('pick', [start, end]);
          },
        }],
      },
      listQuery: {
        page: 1,
        limit: 20,
        keyword: '',
        importance: undefined,
        title: undefined,
        type: undefined,
        account_type: '',
      },
      listRecord: {
        page: 1,
        limit: 15,
        id: '',
        daterange: '',
      },
      khataDetail: '',
      value: '',
      downloadLoading: false,
      search: '',
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        daterange: [this.todayDate(), this.todayDate()],
        role: '',
      },
      account: {
        name: '',
        account_type: 1,
        phone: '',
        address: '',
        status: '',
        title: '',
        area_id: '',
      },
    };
  },
  created() {
    this.getList();
    this.getAccounts();
  },
  methods: {
    addCustomerpopup(data) {
      this.accountid = null;
      this.customerForm = false;
    },
    focusfeild(field) {
      this.$refs[field].focus();
    },
    debounceInput: _.debounce(function(e) {
      this.getList();
    }, 500),
    async getList() {
      this.loading = true;
      const { data } = await fetchListc(this.listQuery);
      this.list = data.accounts.data;
      this.total = data.accounts.total;
      this.loading = false;
    },
    async getAreas() {
      const areas = await areaRes.list();
      this.areas = areas.data.areas;
    },
    async getAccounts(){
      const { data } = await fetchAcc();
      this.accounts = data.accounts;
    },
    addCustomer() {
      this.customerForm = true;
      this.title = 'Create a New Customer';
      this.account = {
        id: null,
        name: '',
        phone: '',
        address: '',
        account_type: '',
      };
    },
    handleDelete(id, name) {
      this.$confirm(
        'This will permanently delete Customer ' + name + '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          customerResource
            .destroy(id)
            .then(response => {
              this.$message({
                type: 'success',
                message: 'Delete completed',
              });
              this.getList();
            })
            .catch(error => {
              console.log(error);
            });
        });
    },
    handleEdit(id, name) {
      this.accountid = id;
      this.customerForm = true;
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['id', 'name', 'phone', 'address'];
        const filterVal = ['id', 'name', 'phone', 'address'];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: 'table-list',
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === 'timestamp') {
            return parseTime(v[j]);
          } else {
            return v[j];
          }
        })
      );
    },
    async search_data(event){
      this.list = [];
      this.datum = await search(this.search);
      this.list = this.datum.data.data;
    },
    async customer_khata(id, name = ''){
      if (name) {
        this.title = 'Mr.' + name;
      }

      this.selected_account = id;
      this.listRecord.id = id;
      const { data } = await getKhata(this.listRecord);
      this.gridData = data.data.data;
      this.finalTotal = data.acc_total[0].acc_total;
      this.balancetodate = parseFloat(data.last_date_total[0].acc_total);
      this.totalRecord = data.data.total;
      this.drawer = true;
    },
    async get_khata_by_date(){
      console.log(this.listRecord.daterange);
      const { data } = await getKhataDate(this.listRecord.daterange);
      this.gridData = [];
      this.gridData = data.data.data;
    },
    print() {
      var contents = document.getElementById('printMe').innerHTML;
      var frame1 = document.createElement('iframe');
      const headcontent = document.getElementsByTagName('head')[0].innerHTML;
      frame1.name = 'frame1';
      frame1.style.position = 'absolute';
      frame1.style.top = '-1000000px';
      document.getElementById('printMe').appendChild(frame1);
      var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ? frame1.contentDocument.document : frame1.contentDocument;
      frameDoc.document.open();
      frameDoc.document.write('<html><head><title>DIV Contents</title>');
      frameDoc.document.write(headcontent);
      frameDoc.document.write('</head><body>');
      frameDoc.document.write(contents);
      frameDoc.document.write('</body></html>');
      frameDoc.document.close();
      setTimeout(function() {
        window.frames['frame1'].focus();
        window.frames['frame1'].print();
        document.getElementById('printMe').removeChild(frame1);
      }, 500);
      return false;
    },
    todayDate() {
      var today = new Date();
      var dd = String(today.getDate()).padStart(2, '0');
      var mm = String(today.getMonth() + 1).padStart(2, '0');
      var yyyy = today.getFullYear();
      today = yyyy + '-' + mm + '-' + dd;
      return today;
    },
    closeTranaction() {
      this.addtransactionshow = false;
    },
  },
};
</script>
<style scoped>
  .filter-container{
    margin-left: 20px !important;
  }
  .el-card.box-card {
      width: 90%;
      margin-top: 15px;
      margin-left: 20px;
  }
  span.tagheader.el-tag.el-tag--medium.el-tag--dark {
    float: right;
    font-size: 20px;
    font-weight: bold;
}
.total-records-detail{
    float: right;
    font-size: 20px;
    font-weight: bold;
}
span.total-records-title {
    padding-right: 10px;
}
.filter-container{
  margin-left:10px;
}
.table-data{
    border:solid #000 !important;
    border-width:1px 0 0 1px !important;
}
.demo-input-label {
    display: inline-block;
    width: 230px;
  }
</style>
