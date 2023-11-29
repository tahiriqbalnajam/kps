<template>
  <el-dialog title="Fee Print" :modelValue="opendialog" width="80%" top="5vh" :before-close="handleClose">
    <el-button type="primary" plain @click="print" @keydown.native.enter="print()">Print</el-button>
    <div class="app-container" id="printMe">
      <el-row :gutter="10">
        <el-col :span="8">
          <el-image
            style="height:220px"
            :src="`uploads/${settings.logo}`"
            fit="fit"
          />
          <el-divider />
          <table width="90%">
            <tr style="padding:5px"><td>Student Copy</td><td>Print Date: {{ todayDateTime() }}</td></tr>
          </table>
          <table id="info" align="left" style="font-size: 14px;font-weight: normal;text-align: left; width:90%">
            <tr>
              <td>Name </td><td><strong>{{ fee.student.name }}</strong></td>
            </tr>
            <tr>
              <td>Father Name  </td><td><strong>{{ fee.student.parents.name }}</strong></td>
            </tr>
            <tr>
              <td>Class </td><td><strong>{{ fee.student.stdclasses.name }}</strong></td>
            </tr>
            <tr>
              <td>Fee Period </td><td><strong>{{ fee.payment_from_date | monthformat }} To {{ fee.payment_to_date | monthformat }}</strong></td>
            </tr>
          </table>
          <table align="left">
            <tr>
              <td></td>
            </tr>
            <tr>
              <td>Amount Paid: <strong class="paid_amount">{{ fee.amount }}</strong> Rs.</td>                            
            </tr>
            <tr>
              <td>Arrears :&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td>Paper Fund : &mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td>Paid Fee : &mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td>Total Fee : &mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td>Signature : &mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td v-html=" settings.invoice_footer" />
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
              <td style="text-align:left; font-size:12px; font-weight:normal; border-top:1px solid"><br>Developed by IDLBridge 03457050405                           
              </td>
            </tr>
          </table>
        </el-col>
        <el-col :span="8">
          <el-image
            style="height:220px"
            :src="`uploads/${settings.logo}`"
            fit="fit"
          />
          <el-divider />
          <table width="90%">
            <tr style="padding:5px"><td>Admin Copy</td><td>Print Date: {{ todayDateTime() }}</td></tr>
          </table>
          <table id="info" align="left" style="font-size: 14px;font-weight: normal;text-align: left; width:90%">
            <tr>
              <td>Name </td><td><strong>{{ fee.student.name }}</strong></td>
            </tr>
            <tr>
              <td>Father Name  </td><td>{{ fee.student.parents.name }}</td>
            </tr>
            <tr>
              <td>Class </td><td><strong>{{ fee.student.stdclasses.name }}</strong></td>
            </tr>
            <tr>
              <td>Fee Period </td><td><strong>{{ fee.payment_from_date | monthformat }} To {{ fee.payment_to_date | monthformat }}</strong></td>
            </tr>
          </table>
          <table align="left">
            <tr>
              <td></td>
            </tr>
            <tr>
              <td>Amount Paid: <strong class="paid_amount">{{ fee.amount }}</strong> Rs.</td>                            
            </tr>
            <tr>
              <td>Arrears :&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td>Paper Fund : &mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td>Paid Fee : &mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td>Total Fee : &mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td>Signature : &mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td v-html=" settings.invoice_footer" />
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
              <td style="text-align:left; font-size:12px; font-weight:normal; border-top:1px solid"><br>Developed by IDLBridge 03457050405                           
              </td>
            </tr>
          </table>
        </el-col>
        <el-col :span="8">
          <el-image
            style="height:220px"
            :src="`uploads/${settings.logo}`"
            fit="fit"
          />
          <el-divider />
          <table width="90%">
            <tr style="padding:5px"><td>Office Copy</td><td>Print Date: {{ todayDateTime() }}</td></tr>
          </table>
          <table id="info" align="left" style="font-size: 14px;font-weight: normal;text-align: left; width:90%">
            <tr>
              <td>Name </td><td><strong>{{ fee.student.name }}</strong></td>
            </tr>
            <tr>
              <td>Father Name  </td><td>{{ fee.student.parents.name }}</td>
            </tr>
            <tr>
              <td>Class </td><td><strong>{{ fee.student.stdclasses.name }}</strong></td>
            </tr>
            <tr>
              <td>Fee Period </td><td><strong>{{ fee.payment_from_date | monthformat }} To {{ fee.payment_to_date | monthformat }}</strong></td>
            </tr>
          </table>
          <table id="other-details" align="left">
            <tr>
              <td></td>
            </tr>
            <tr>
              <td>Amount Paid: <strong class="paid_amount">{{ fee.amount }}</strong> Rs.</td>                            
            </tr>
            <tr>
              <td>Arrears :&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td>Paper Fund : &mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td>Paid Fee : &mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td>Total Fee : &mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td>Signature : &mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;&mdash;</td>
            </tr>
            <tr>
              <td v-html=" settings.invoice_footer" />
            </tr>
            <tr><td>&nbsp;</td></tr>
            <tr>
              <td style="text-align:left; font-size:12px; font-weight:normal; border-top:1px solid"><br>Developed by IDLBridge 03457050405                           
              </td>
            </tr>
          </table>
        </el-col>
      </el-row>
    </div>
  </el-dialog>
</template>

<script>
import Resource from '@/api/resource';
import moment from 'moment';
const feePro = new Resource('fee');
const settingPro = new Resource('settings');
export default {
  name: 'FeePrint',
  filters: {
    monthformat: (date) => {
      return (!date) ? '' : moment(date).format('MMM, YYYY');
    },
  },
  props: {
    feeid: {
      type: Number,
      required: true,
    },
    openfeeprint: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      opendialog: true,
      local_feeid: null,
      localfeeid: null,
      closefeeprint: false,
      list: null,
      addparentpop: false,
      addstdclasspop: false,
      listloading: false,
      classes: null,
      addstudentpop: false,
      search: '',
      total: 0,
      loading: true,
      downloading: false,
      fee: {
        student: {
          parent: {},
          stdclasses: {},
        },
      },
      settings: [],
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        stdclass: '',
      },
    };
  },
  watch: {
    local_feeid: function(val, oldval) {
      console.log('watch is running');
      this.getFeeDetail(val);
    },
  },
  async created() {
    await this.getSettings();
    await this.getFeeDetail();
  },
  mounted: function() {
    this.opendialog = this.openfeeprint;
    this.local_feeid = this.feeid;
  },
  methods: {
    handleClose() {
      this.doneFeePrint();
    },
    doneFeePrint() {
      this.$emit('doneFeePrint', 'yes');
    },
    async getFeeDetail() {
      const { data } = await feePro.get(this.feeid);
      this.fee = data.fee;
    },
    async getSettings() {
      const { data } = await settingPro.list();
      this.settings = data.settings;
    },
    todayDateTime() {
      return moment().format('D, MMM YY h:mm');
    },
    print() {
      var contents = document.getElementById('printMe').innerHTML;
      var frame1 = document.createElement('iframe');
      let headcontent = document.getElementsByTagName('head')[0].innerHTML;
      frame1.name = "frame1";
      frame1.style.position = "absolute";
      frame1.style.top = "-1000000px";
      document.getElementById('printMe').appendChild(frame1);
      var frameDoc = frame1.contentWindow ? frame1.contentWindow : frame1.contentDocument.document ? frame1.contentDocument.document : frame1.contentDocument;
      frameDoc.document.open();
      frameDoc.document.write('<html><head><title>DIV Contents</title>');
      frameDoc.document.write(headcontent);
      frameDoc.document.write('</head><body>');
      frameDoc.document.write(contents);
      frameDoc.document.write('</body></html>');
      frameDoc.document.close();
      setTimeout(function () {
        window.frames["frame1"].focus();
        window.frames["frame1"].print();
        document.getElementById('printMe').removeChild(frame1);
      }, 500);
      return false;
    },
  },
};
</script>

<style scoped>
  #info {
    border-collapse: collapse;
  }
  #info >>> td {
		border:1px solid;
		padding:2px 0 2px 10px;
  }
	.paid_amount{
		font-size:20px;
  }
  #other-details >>> td {
    padding: 2px 0 2px 10px;
  }
</style>
