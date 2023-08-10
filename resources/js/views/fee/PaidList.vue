<template>
  <div class="app-container">
    <div class="filter-container">
      <el-row :gutter="10">
        <el-col :xs="8" :sm="6" :md="4" :lg="18" :xl="18">
          <el-input v-model="query.keyword" placeholder="Enter Student Name" style="width: 200px;" class="filter-item"/>
          <el-date-picker
            v-model="query.date"
            type="daterange"
            align="right"
            unlink-panels
            range-separator="To"
            start-placeholder="Start month"
            end-placeholder="End month"
            :picker-options="pickerOptions" 
            value-format="yyyy-MM-dd"
          />
          <el-button class="filter-item" type="primary" icon="el-icon-search" @click="getList()">
            {{ $t('table.search') }}
          </el-button>
          <el-button :loading="downloadLoading" style="margin:0 0 20px 20px;" type="primary" icon="document" @click="handleDownload">
            {{ $t('excel.export') }} Report
          </el-button>
          <el-divider direction="vertical" />
          <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus" @click="openpayfee = true">
            Pay Fee
          </el-button>
        </el-col>
        <el-col :xs="4" :sm="6" :md="8" :lg="6" :xl="18" style="text-align:right">
          <span style="font-weight:bold; font-size:17px;">Total: </span>
          <el-tag
            type="danger"
            effect="dark"
            style="font-weight:bold; font-size:17px"
          >
            {{ totalfee }}
          </el-tag>
        </el-col>
      </el-row>
    </div>
    <el-table
      :data="fee"
      style="width: 100%"
      :loading="tblloading"
    >
      <el-table-column label="ID" prop="student.roll_no" />
      <el-table-column label="Student" prop="student.name" />
      <el-table-column label="Class" prop="student.stdclasses.name" />
      <el-table-column label="Amount" prop="amount" />
      <el-table-column label="Fee Type" prop="feetype.title" />
      <el-table-column label="For Month(s)" prop="payment_from_date">
        <template slot-scope="scope">
          {{ showduration(scope.row.payment_from_date,  scope.row.payment_to_date) }}
        </template>
      </el-table-column>dateformat
      <el-table-column label="Paid at" prop="created_at">
        <template slot-scope="scope">
          {{ scope.row.created_at | dateformat }}
        </template>
      </el-table-column>
      <el-table-column align="right">
        <template slot="header" slot-scope="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
        </template>
        <template slot-scope="scope">
          <el-tooltip content="Print" placement="top">
            <el-button
              type="primary"
              icon="el-icon-printer"
              size="mini"
              @click="printIt(scope.row.id)"
            />
          </el-tooltip>
          <el-tooltip content="Delete" placement="top">
            <el-button
              size="mini"
              type="danger"
              icon="el-icon-delete"
              @click="handleDelete(scope.row.id, scope.row.name)"
            />
          </el-tooltip>

        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
    <fee-print v-if="openfeeprint" :feeid="feeid" :openfeeprint="openfeeprint" @doneFeePrint="doneFeePrint"/>
    <el-drawer
      title="Edit Record"
      :visible.sync="editnow"
      direction="rtl"
      custom-class="demo-drawer"
      ref="drawer"
    >
      <div class="demo-drawer__content">
        <el-form :model="fee">
          <el-form-item label="Name" :label-width="formLabelWidth">
            <el-input v-model="fee.name" autocomplete="off" />
          </el-form-item>
        </el-form>
        <div class="demo-drawer__footer">
          <el-button @click="editnow = false">Cancel</el-button>
          <el-button type="primary" @click="onSubmit" :loading="loading">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
        </div>
      </div>
    </el-drawer>
    <pay-fee v-if="openpayfee" :openpayfee="openpayfee" @donePayFee="donePayFee"/>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import PayFee from './component/PayFee.vue';
import FeePrint from './component/FeePrint.vue';
import Resource from '@/api/resource';
import moment from 'moment';
import { debounce } from 'lodash';
const feePro = new Resource('fee');
const classes = new Resource('classes');
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
      downloadLoading: false,
      feeid: null,
      openfeeprint: false,
      fee: [],
      totalfee: 0,
      classes: null,
      openpayfee: false,
      search: '',
      total: 0,
      loading: false,
      tblloading: false,
      downloading: false,
      editnow: false,
      formLabelWidth: 250,
      feee: {
        id: '',
        name: '',
      },
      query: {
        page: 1,
        limit: 15,
        date: [this.todayDate(), this.todayDate()],
        class: '',
      },
      pickerOptions: {
        shortcuts: [{
          text: 'Last week',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: 'Last month',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: 'Last 3 months',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
            picker.$emit('pick', [start, end]);
          }
        }]
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
    debounceInput: debounce(function (e) {
      this.getList();
    }, 500),
    showpayment(from, to) {
      return from + ' - ' + to;
    },
    donePayFee() {
      this.getList();
    },
    doneFeePrint() {
      this.openfeeprint = false;
    },
    todayDate() {
      var today = new Date();
      var dd = String(today.getDate()).padStart(2, '0');
      var mm = String(today.getMonth() + 1).padStart(2, '0');
      var yyyy = today.getFullYear();
      today = yyyy + '-' + mm + '-' + dd;
      return today;
    },
    async getList() {
      this.tblloading = true;
      const { data } = await feePro.list(this.query);
      this.fee = data.fee.data;
      this.total = data.fee.total;
      this.tblloading = false;
      this.totalfee = data.totalfee.fee;
    },
    async getClasses() {
      const{ data } = await classes.list();
      this.classes = data.classes.data;
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
        type: 'warning'
      }).then(async () => {
        await feePro.destroy(id);
        this.getList();
        this.message({
          type: 'success',
          message: name+' Delete successfully'
        });
      });
    },
    async onSubmit() {
      if (this.fee.id !== '') {
        await feePro.update(this.fee.id, this.fee);
        this.editnow = false;
        this.getList();
      } else {
        await feePro.store(this.fee);
        this.editnow = false;
        this.getList();
      }
    },
    printIt(feeid) {
      this.feeid = feeid;
      this.openfeeprint = true;
    },
    showduration(from, to) {
      const fmonth = moment(from).format('MMM');
      const fyear = moment(from).format('YY');

      const tmonth = moment(to).format('MMM');
      const tyear = moment(to).format('YY');
      if (fmonth === tmonth)
        return fmonth + ',' + fyear;
      else
        return fmonth + ', ' + fyear + ' - ' + tmonth + ', ' + fyear;
    },
    handleDownload() {
      this.downloadLoading = true;
      import('@/vendor/Export2Excel').then(excel => {
        const tHeader = ['Id', 'Name', 'Class', 'Amount','Fee Type','Paid From','Paid To','Paid at'];
        const filterVal = ['id', 'name', 'class', 'amount','fee_type','payment_from_date','payment_to_date','created_at'];
        const list = this.formateData(this.fee);
        console.log(list);
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
          id: record.student.id,
          name: record.student.name,
          class: record.student.stdclasses.name,
          amount: record.amount,
          fee_type: record.feetype.title,
          payment_from_date: moment(record.payment_from_date).format('MMM, YY'),
          payment_to_date: moment(record.payment_to_date).format('MMM, YY'),
          created_at: moment(record.created_at).format('DD/MM/YY')
        }
      )
      );
      const total = { id: '', name: '', class: 'Total', amount: this.totalfee, fee_type: '', payment_from_date: '', payment_to_date: '', created_at: '' };
      formatedData.push(total);
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
  .el-date-editor >>> .el-range-separator {
    width:10% !important;
  }
  .el-range-editor--medium.el-input__inner {
    vertical-align: top;
  }
</style>