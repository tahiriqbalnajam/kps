<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-row :gutter="10">
          <el-col :xs="18" :sm="18" :md="18" :lg="18" :xl="18">
            <el-row :gutter="20">
              <el-col :xs="4" :sm="4" :md="4" :lg="4" :xl ="4">
                <el-form-item label="">
                  <el-select v-model="query.stdclass" placeholder="Class" clearable style="width: 130px" class="filter-item" @change="handleFilter">
                      <el-option v-for="item in classes" :key="item.id" :label="upperFirst(item.name)" :value="item.id" />
                  </el-select>
                </el-form-item>
              </el-col>
              <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl ="8">
                <el-date-picker
                  v-model="query.date"
                  type="daterange"
                  align="right"
                  unlink-panels
                  range-separator="To"
                  start-placeholder="Start month"
                  end-placeholder="End month"
                  :picker-options="pickerOptions" 
                  value-format="YYYY-MM-DD"
                />
              </el-col>
              <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl ="2">
                <el-button class="filter-item" type="primary" icon="el-icon-search" @click="getList()">
                  {{ $t('table.search') }}
                </el-button>
              </el-col>
              <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl ="2">
                <el-button class="filter-item" type="success" icon="el-icon-plus" @click="openpayfee = true">
                  Pay Fee
                </el-button>
              </el-col>
              <el-col :xs="2" :sm="2" :md="2" :lg="2" :xl ="2">
                <el-button class="filter-item"type="success" icon="el-icon-plus" @click="downloadPDF()">
                  Download Report
                </el-button>
              </el-col>
            </el-row>
          </el-col>
          <el-col :xs="6" :sm="6" :md="8" :lg="6" :xl="6" style="text-align:right">
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
      </head-controls>
    </div>
    <el-table
      :data="fee"
      style="width: 100%"
      :loading="tblloading"
      max-height="600"
      size="small"
    >
      <el-table-column label="ID" prop="student.roll_no" />
      <el-table-column label="Student" prop="student.name" />
      <el-table-column label="Class" prop="student.stdclasses.name" />
      <el-table-column label="Amount" prop="amount" />
      <el-table-column label="Fee Detail" prop="feetype.title" >
        <template #default="scope">
          <ul class="fee-details">
            <li v-for="meta in scope.row.fee_meta">{{ meta.meta_key+' = '+meta.meta_value }}</li>
          </ul>
        </template>
      </el-table-column>
      <el-table-column label="For Month(s)" prop="payment_from_date">
        <template #default="scope">
          {{ showduration(scope.row.payment_from_date,  scope.row.payment_to_date) }}
        </template>
      </el-table-column>
      <el-table-column label="Paid at" prop="created_at">
        <template #default="scope">
          {{ dateformat(scope.row.created_at) }}
        </template>
      </el-table-column>
      <el-table-column align="right">
        <template #header>
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
        </template>
        <template slot="header" #default="scope">
            <el-button
              type="primary"
              size="mini"
              @click="printIt(scope.row.id)"
            ><el-icon><Printer /></el-icon></el-button>
            <el-button
              size="mini"
              type="danger"
              @click="handleDelete(scope.row.id, scope.row.name)"
            ><el-icon><Delete /></el-icon></el-button>
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
import {
  Printer,
  Delete,
} from '@element-plus/icons-vue'
import jsPDF from 'jspdf';
import autoTable from 'jspdf-autotable';
import HeadControls from '@/components/HeadControls.vue';
import Pagination from '@/components/Pagination/index.vue';
import PayFee from './component/PayFee.vue';
import FeePrint from './component/FeePrint.vue';
import Resource from '@/api/resource';
import moment from 'moment';
import { debounce } from 'lodash';
const feePro = new Resource('fee');
const clasRes = new Resource('classes');
export default {
  name: '',
  components: { Pagination,HeadControls, PayFee, FeePrint },
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
        stdclass: '',
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
    upperFirst(txt) {
      if (txt) {
        return txt.charAt(0).toUpperCase() + txt.slice(1)
      }
    },
    dateformat(date) {
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    },
    debounceInput: debounce(function (e) {
      this.getList();
    }, 500),
    showpayment(from, to) {
      return from + ' - ' + to;
    },
    async handleSizeChange (val) {
      this.query.limit = val
      await this.getList()
    },
    async handleCurrentChange (val) {
      this.query.page = val
      await this.getList()
    },
    donePayFee() {
      this.getList();
    },
    doneFeePrint() {
      this.openfeeprint = false;
    },
    downloadPDF() {
      this.downloading = true;
      const doc = new jsPDF();

      doc.text("Fee Paid Details", 10, 10);

      autoTable(doc, {
        head: [['Student', 'Class', 'Amount', 'For Month', 'Paid at']],

        body: this.fee.map(fee => [fee.student.name, fee.student.stdclasses.name, 
                                    fee.amount,
                                    this.dateformat(fee.payment_from_date), 
                                    this.dateformat(fee.created_at)]),
      });

      doc.save('filtered-data.pdf');

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
      this.totalfee = this.totalFee();
    },
    totalFee() {
      return  this.fee.reduce((sum, fee) => sum + parseFloat(fee.amount), 0);
    },
    async getClasses() {
      const{ data } = await clasRes.list();
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
      this.$confirm('Do you really want to delete?', 'Warning', {
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
  .fee-details {
    list-style: none;
    padding: 0;
  }
</style>