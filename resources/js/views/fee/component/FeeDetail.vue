<template>
  <el-drawer
    ref="drawer"
    title="Fee Details"
    :modelValue="closepopup"
    direction="rtl"
    custom-class="demo-drawer"
    size="80%"
    @close="donePayFee()"
  >
    <div class="demo-drawer__content">
      <h2>{{ Object.entries(fees)[0][1]['student']['name'] }} ( {{ Object.entries(fees)[0][1]['student']['stdclasses']['name'] }} )</h2>
      <table class="tblwdborder">
        <tr>
          <th>Amount</th>
          <th>Fee Period</th>
          <th>Paid At</th>
        </tr>
        <tr v-for="fee in fees" :key="fee.id">
          <td>{{ fee.amount }}</td>
          <td>{{ formateDate(fee.payment_from_date)}} to {{ formateDate(fee.payment_to_date)}}</td>
          <td>{{formateDate(fee.created_at)}}</td>
        </tr>
      </table>
      <div class="demo-drawer__footer">
        <el-button @click="Print()">Print</el-button>
      </div>
    </div>
  </el-drawer>
</template>
<script>
import Resource from '@/api/resource';
import moment from 'moment';
const feePro = new Resource('fee');
export default {
  name: 'FeeDetail',
  filters: {
    monthformat: (date) => {
      return (!date) ? '' : moment(date).format('MMM, YYYY');
    },
    dataformat(date) {
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    }
  },
  components: { },
  directives: { },
  props: {
    openfeedetail: {
      type: Boolean,
      required: true,
    },
    stdid: {
      type: Number,
      default: null,
    },
  },
  data() {
    return {
      closepopup: false,
      formLabelWidth: '120',
      loading: false,
      fees: [],
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        filter: {},
        id: '',
        role: '',
        pending: true,
      },
    };
  },
  computed: {
  },
  watch: {
    stdid: function(val, oldval) {
      this.query.id = val;
      this.getFeeDetail(this.query);
    },
  },
  mounted: function() {
    this.closepopup = this.openfeedetail;
    this.query.id = this.stdid;
  },
  created() {
    this.query.filter.id = this.stdid;
    this.getFeeDetail();
  },
  methods: {
    formateDate(date) {
      const formated = moment(date).format('DD-MM-YYYY');
      return formated
      this.query.filter.to_date = moment(this.query.filter.to_date).format('YYYY-MM-DD');
    },
    donePayFee(printnow = false, feeid = null) {
      this.closepopup = false;
      this.$emit('doneFeeDetail');
    },
    async getFeeDetail() {
      this.query.id = this.stdid;
      const { data } = await feePro.list(this.query);
      this.fees = data.fee.data;
    }
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
  .tblwdborder {
    border-collapse: collapse;
    width: 100%;
    font-size: 14px;
  }
  .tblwdborder th {
    text-align: left;	
    border: 1px solid #0000001a;
    padding: 3px;
  }
  .tblwdborder tr td, .tblwdborder tr  th {
    border: 1px solid #0000001a;
    padding: 3px;
  }
  .tblwdborder tr:nth-child(odd) {
    background-color: #e1e0e061;
  }
  .absent {
    background: red;
    color:#fff;
  }
</style>
