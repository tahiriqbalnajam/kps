<template>
  <el-drawer
    ref="drawer"
    title="Add Fee"
    :visible.sync="closepopup"
    direction="rtl"
    custom-class="demo-drawer"
    size="50%"
    @close="donePayFee()"
  >
    <div class="demo-drawer__content">
      <el-form :model="fee">
        <el-form-item label="Student" :label-width="formLabelWidth">
          <el-select
            v-model="fee.student_id"
            filterable
            remote
            reserve-keyword
            placeholder="Please enter a keyword"
            :remote-method="getStudent"
            :loading="findstudent"
            style="width:80%"
            @change="setFee()"
          >
            <el-option
              v-for="student in students"
              :key="student.id"
              :label="student.name + ' - '+ student.stdclasses.name + ' (Fee:'+student.monthly_fee+')'"
              :value="student.id"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="From - To" :label-width="formLabelWidth">
          <el-date-picker
            v-model="fee.feefromto"
            type="monthrange"
            align="right"
            unlink-panels
            range-separator="To"
            start-placeholder="Start month"
            end-placeholder="End month"
            value-format="yyyy-MM-dd"
            :picker-options="pickerOptions"
          />
        </el-form-item>
        <el-form-item label="Fee Type" :label-width="formLabelWidth">
          <el-select v-model="fee.fee_type_id" placeholder="Select Fee Type" @change="setPrice()">
            <el-option
              v-for="type in feetypes"
              :key="type.id"
              :value="type.id"
              :label="type.title + ' (' + type.amount + ')'"
            />
          </el-select>
        </el-form-item>
        <el-form-item label="Amount" :label-width="formLabelWidth">
          <el-input v-model="fee.amount" autocomplete="off" />
        </el-form-item>
      </el-form>
      <div class="demo-drawer__footer">
        <el-button @click="donePayFee()">Cancel</el-button>
        <el-button type="primary" :loading="loading" @click="onSubmit">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
      </div>
    </div>
  </el-drawer>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import Resource from '@/api/resource';
const feePro = new Resource('fee');
const feeTypePro = new Resource('feetypes');
const studentPro = new Resource('students');
export default {
  name: 'PayFee',
  components: { Pagination },
  directives: { },
  props: {
    openpayfee: {
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
      findstudent: false,
      students: null,
      fee: {
        id: '',
        student_id: '',
        feefromto: '',
        payment_from_date: '',
        payment_to_date: '',
        amount: '',
        fee_type_id: 1,
      },
      feetypes: [],
      pickerOptions: {
        shortcuts: [{
          text: 'This month',
          onClick(picker) {
            const my_date = new Date();
            const first_date = new Date(my_date.getFullYear(), my_date.getMonth(), 1);
            const last_date = new Date(my_date.getFullYear(), my_date.getMonth() + 1, 0);
            picker.$emit('pick', [first_date, last_date]);
          },
        }, {
          text: 'This year',
          onClick(picker) {
            const end = new Date();
            const start = new Date(new Date().getFullYear(), 0);
            picker.$emit('pick', [start, end]);
          },
        }, {
          text: 'Last 6 months',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setMonth(start.getMonth() - 6);
            picker.$emit('pick', [start, end]);
          },
        }],
      },
      query: {
        page: 1,
        limit: 15,
        keyword: '',
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
      this.getStudent(this.query);
    },
  },
  mounted: function() {
    this.closepopup = this.openpayfee;
  },
  created() {
    this.feeTypesList();
    this.query.id = this.stdid;
    this.getsetStudent();
  },
  methods: {
    donePayFee(printnow = false, feeid = null) {
      this.resetFee();
      this.closepopup = false;
      const callbackdata = {
        print: printnow,
        feeid: feeid,
      }
      this.$emit('donePayFee', callbackdata);
    },
    async feeTypesList() {
      const { data } = await feeTypePro.list();
      this.feetypes = data.feetypes.data;
    },
    async getStudent(query) {
      this.findstudent = true;
      if (query) {
        this.query.keyword = query;
      }
      const { data } = await studentPro.list(this.query);
      this.students = data.students.data;
      this.findstudent = false;
    },
    async getsetStudent(query) {
      this.findstudent = true;
      if (this.query.keyword !== '' || this.query.id !== null) {
        const { data } = await studentPro.list(this.query);
        this.students = data.students.data;
        this.fee.student_id = this.students[0].id;
        this.setFee();
      }
      this.findstudent = false;
    },
    async onSubmit() {
      this.loading = true;
      if (this.fee.id != '') {
        await feePro.update(this.fee.id, this.fee);
        this.editnow = false;
        this.getList();
      } else {
        await feePro.store(this.fee).then(result => {
          this.loading = false;
          this.$message({
            message: 'Fee added successfully.',
            type: 'success',
          });
          console.log(result);
          this.donePayFee(true, result.data.fee.id);
        }).catch(() => {
          this.$message({
            message: 'Put each value kindly.',
            type: 'error',
          });
          this.loading = false;
          return false;
        });
      }
      this.loading = false;
    },
    setFee() {
      const selectedStudent = this.students.filter(std => this.fee.student_id === std.id).shift();
      this.fee.amount = selectedStudent.monthly_fee;
    },
    setPrice() {
      const selecretfee = this.feetypes.filter(getfee => getfee.id == this.fee.fee_type_id).shift();
      console.log(selecretfee);
      if (selecretfee.id != '1') {
        this.fee.amount = selecretfee.amount;
      }
    },
    resetFee() {
      this.fee = {
        id: '',
        student_id: '',
        feefromto: '',
        payment_from_date: '',
        payment_to_date: '',
        amount: '',
        fee_type_id: '',
      };
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
</style>
