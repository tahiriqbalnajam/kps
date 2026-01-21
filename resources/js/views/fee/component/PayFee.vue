<template>
  <el-drawer
    ref="drawer"
    title="Add Fee"
    :modelValue="closepopup"
    direction="rtl"
    custom-class="demo-drawer"
    size="50%"
    @close="donePayFee()"
  >
  <template #header>
      <el-page-header>
        <template #content>
          <span class="text-large font-600 mr-3"> Pay Fee </span>
        </template>
      </el-page-header>
    </template>
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
            value-format="YYYY-MM-DD"
            :picker-options="pickerOptions"
          />
        </el-form-item>
        <el-form-item label="Fee Type" :label-width="formLabelWidth" v-for="(fm, index) in fee.fee_meta">
          <el-row :gutter="20">
            <el-col :xs="10" :sm="10" :md="10" :lg="10" :xl ="10">
              <el-select v-model="fm.meta_key" placeholder="Select Fee Type" @change="setPrice()">
                <el-option
                  v-for="type in feetypes"
                  :key="type.id"
                  :value="type.title"
                  :label="type.title + ' (' + type.amount + ')'"
                />
              </el-select>
            </el-col>
            <el-col :xs="10" :sm="10" :md="10" :lg="10" :xl ="10">
              <el-input v-model="fm.meta_value" placeholder="Enter amount" size="normal" clearable @change=""></el-input>
            </el-col>
            <el-col :xs="4" :sm="4" :md="4" :lg="4" :xl ="4">
              <el-button type="primary" size="mini" @click="addFeeType(index, fm.button)">
                <el-icon v-if="fm.button == 'add'"><Plus /></el-icon>
                <el-icon v-if="fm.button == 'remove'"><Minus /></el-icon>
              </el-button>
            </el-col>
          </el-row>
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
import {
    Plus,
    Minus,
} from '@element-plus/icons-vue'
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
      feetypeselection: [],
      fee: {
        id: '',
        student_id: '',
        feefromto: '',
        payment_from_date: '',
        payment_to_date: '',
        fee_meta: [{
          meta_key: '',
          meta_value: '',
          button: 'add',
        }],
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
        filter: {},
      },
    };
  },
  computed: {
  },
  watch: {
    stdid: {
      handler(val, oldval) {
        if (val) {
          this.getsetStudent();
        }
      },
      immediate: true,
    },
    openpayfee: {
      handler(val) {
        if (val) {
          this.closepopup = val;
          if (this.stdid) {
            this.getsetStudent();
          }
        }
      },
      immediate: true,
    },
  },
  mounted: function() {
  },
  created() {
    this.feeTypesList();
  },
  methods: {
    addFeeType(index, button) {
      if(this.feetypes.length == this.fee.fee_meta.length) {
        this.$message({
          message: 'All fee type added.',
          type: 'warning',
        });
        return false;
      }
      if (button == 'remove') {
        this.fee.fee_meta = this.fee.fee_meta.filter((item, i) => {
          return i != index;
        });
      } else {
        this.fee.fee_meta = [...this.fee.fee_meta, { meta_key: '', meta_value: '', button: 'remove' }];
      }
      
    },
    removeFeeType(index) {
      console.log(index);
      this.feetypeselection = this.feetypeselection.filter((item) => {
          return item == index;
      });
    },
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
        if(this.query.filter.id) delete this.query.filter.id;
      }
      const { data } = await studentPro.list(this.query);
      this.students = data.students.data;
      this.findstudent = false;
    },
    async getsetStudent() {
      this.findstudent = true;
      if (this.stdid) {
        const params = {
            limit: 15,
            filter: {
                id: this.stdid
            }
        };
        const { data } = await studentPro.list(params);
        this.students = data.students.data;
        if (this.students && this.students.length > 0) {
          this.fee.student_id = this.students[0].id;
          this.setFee();
        }
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
