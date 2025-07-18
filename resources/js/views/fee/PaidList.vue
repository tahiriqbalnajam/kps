<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-row :gutter="16" type="flex" align="middle">
          <el-col :xs="24" :sm="24" :md="18" :lg="18" :xl="18">
            <el-row :gutter="16" type="flex" align="middle">
              <!-- Class Filter -->
              <el-col :xs="6" :sm="4" :md="3" :lg="3" :xl="3">
                <el-form-item label="" class="mb-0">
                  <el-select 
                    v-model="query.stdclass" 
                    placeholder="Class" 
                    clearable 
                    class="filter-item full-width"
                    @change="handleFilter">
                    <el-option 
                      v-for="item in classes" 
                      :key="item.id" 
                      :label="upperFirst(item.name)" 
                      :value="item.id" />
                  </el-select>
                </el-form-item>
              </el-col>
              
              <!-- Fee Type Filter -->
              <el-col :xs="6" :sm="4" :md="3" :lg="3" :xl="3">
                <el-form-item label="" class="mb-0">
                  <el-select 
                    v-model="query.feetype_id" 
                    placeholder="Fee Type" 
                    clearable 
                    class="filter-item full-width"
                    @change="handleFilter">
                    <el-option 
                      v-for="item in feeTypes" 
                      :key="item.id" 
                      :label="upperFirst(item.title)" 
                      :value="item.id" />
                  </el-select>
                </el-form-item>
              </el-col>

              <!-- Date Range Picker -->
              <el-col :xs="24" :sm="12" :md="10" :lg="6" :xl="6">
                <el-form-item label="" class="mb-0">
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
                    class="full-width"
                  />
                </el-form-item>
              </el-col>
              
              <!-- Action Buttons -->
              <el-col :xs="24" :sm="24" :md="8" :lg="6" :xl="6">
                <div class="action-buttons">
                  <el-button 
                    class="filter-item" 
                    type="primary"
                    @click="getList()">
                    <el-icon class="el-icon--left"><Search /></el-icon> {{ $t('table.search') }}
                  </el-button>
                  <el-button 
                    class="filter-item" 
                    type="success" 
                    :loading="downloading"
                    @click="downloadPDF()">
                    <el-icon class="el-icon--left"><Download /></el-icon> Download Report
                  </el-button>
                </div>
              </el-col>
            </el-row>
          </el-col>
          
          <!-- Total Display -->
          <el-col :xs="24" :sm="24" :md="6" :lg="6" :xl="6" class="total-container">
            <div class="total-display">
              <span class="total-label">Total: </span>
              <el-tag
                type="danger"
                effect="dark"
                class="total-value"
              >
                {{ totalfee }}
              </el-tag>
            </div>
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
          <el-input ref="search" v-model="query.keyword" size="small" placeholder="Type to search" v-on:input="debounceInput" />
        </template>
        <template slot="header" #default="scope">
            <el-button
              type="primary"
              size="small"
              @click="printIt(scope.row.id)"
            ><el-icon><Printer /></el-icon></el-button>
            <el-button
              size="small"
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
  Download,
  Search,
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
const feetypeRes = new Resource('feetypes');
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
      feeTypes: null,
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
        feetype_id: '',
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
    this.getFeeTypes();
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
      
      // Get all records without pagination
      this.getAllFeeRecords().then(allRecords => {
        const doc = new jsPDF();

        doc.text("Fee Paid Details", 10, 10);

        autoTable(doc, {
          head: [['Student', 'Class', 'Amount', 'Fee Type', 'For Month', 'Paid at']],

          body: allRecords.map(fee => [
            fee.student.name, 
            fee.student.stdclasses.name, 
            fee.amount, 
            this.formatFeeTypes(fee.fee_meta),
            this.showduration(fee.payment_from_date, fee.payment_to_date), 
            this.dateformat(fee.created_at)
          ]),
        });

        // Add total at the bottom
        const totalRow = [['', 'Total:', this.totalfeeAll, '', '', '']];
        autoTable(doc, {
          startY: doc.lastAutoTable.finalY + 10,
          body: totalRow,
          theme: 'plain',
          styles: { fontStyle: 'bold' }
        });

        doc.save('fee-payment-report.pdf');
        this.downloading = false;
      }).catch(error => {
        console.error('Error fetching data for PDF:', error);
        this.$message.error('Failed to generate PDF');
        this.downloading = false;
      });
    },
    
    // Helper method to format fee types from fee_meta array
    formatFeeTypes(feeMetaArray) {
      if (!feeMetaArray || !feeMetaArray.length) return '';
      
      // For PDF, we need to use '\n' for new lines
      // jsPDF-AutoTable will handle the line breaks properly
      return feeMetaArray
        .map(meta => `${meta.meta_key}: ${meta.meta_value}`)
        .join('\n');
    },
    
    // New method to get all fee records without pagination
    async getAllFeeRecords() {
      // Create a copy of the current query without pagination parameters
      const pdfQuery = { ...this.query };
      delete pdfQuery.page;
      delete pdfQuery.limit;
      
      try {
        const { data } = await feePro.list(pdfQuery);
        this.totalfeeAll = data.totalFeeCollected;
        return data.fee.data;
      } catch (error) {
        console.error('Error fetching all records:', error);
        throw error;
      }
    },
    
    todayDate() {
      var today = new Date();
      var dd = String(today.getDate()).padStart(2, '0');
      var mm = String(today.getMonth() + 1).padStart(2, '0');
      var yyyy = today.getFullYear();
      today = yyyy + '-' + mm + '-' + dd;
      return today;
    },
    async handleFilter() {
      await this.getList();
    },
    async getList() {
      this.tblloading = true;
      const { data } = await feePro.list(this.query);
      this.fee = data.fee.data;
      this.total = data.fee.total;
      this.tblloading = false;
      this.totalfee = data.totalFeeCollected;
    },
    totalFee() {
      return  this.fee.reduce((sum, fee) => sum + parseFloat(fee.amount), 0);
    },
    async getClasses() {
      const{ data } = await clasRes.list();
      this.classes = data.classes.data;
    },
    async getFeeTypes() {
      const { data } = await feetypeRes.list();
      this.feeTypes = data.feetypes.data;
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
  
  /* New styles for improved layout */
  .full-width {
    width: 100% !important;
  }
  
  .mb-0 {
    margin-bottom: 0;
  }
  
  .action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
  }
  
  .total-container {
    display: flex;
    justify-content: flex-end;
  }
  
  .total-display {
    display: flex;
    align-items: center;
  }
  
  .total-label {
    font-weight: bold;
    font-size: 17px;
    margin-right: 8px;
  }
  
  .total-value {
    font-weight: bold;
    font-size: 17px;
  }
  
  /* Responsive adjustments */
  @media (max-width: 768px) {
    .action-buttons {
      margin-top: 16px;
      justify-content: space-between;
    }
    
    .total-container {
      justify-content: flex-start;
      margin-top: 16px;
    }
  }
  
  @media (max-width: 576px) {
    .action-buttons {
      flex-direction: column;
      width: 100%;
    }
    
    .action-buttons .el-button {
      width: 100%;
      margin-left: 0 !important;
      margin-bottom: 8px;
    }
  }
</style>