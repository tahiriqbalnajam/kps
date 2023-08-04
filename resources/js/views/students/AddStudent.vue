<template>
  <div>
    <el-dialog title="Add Student" :visible.sync="customerForm">
      <div class="form-container">
        <el-form
          ref="student"
          :model="student"
          :rules="rules"
          label-position="left"
          label-width="120px"
          style="max-width: 600px;"
        >
          <el-form-item label="Roll No." prop="roll_no">
            <el-input v-model="student.roll_no" />
          </el-form-item>
          <el-form-item label="Name" prop="name">
            <el-input v-model="student.name" />
          </el-form-item>
          <el-form-item label="Parent" prop="parent_id">
            <el-select
              v-model="student.parent_id"
              filterable
              remote
              reserve-keyword
              placeholder="Search Parent"
              :remote-method="searchParent"
              :loading="parentloading">
              <el-option
                v-for="parent in parents"
                :key="parent.id"
                :label="parent.name"
                :value="parent.id"
              />
            </el-select>
            <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus" @click="addparentpop = true">
              Add Parent
            </el-button>
          </el-form-item>
          <el-form-item label="Select Class" prop="class_id">
            <el-select v-model="student.class_id" placeholder="Classes">
              <el-option
                v-for="stdclass in classes"
                :key="stdclass.id"
                :label="stdclass.name"
                :value="stdclass.id">
                <span style="float: left">{{ stdclass.name }}</span>
                <span style="float: right">{{ stdclass.total_students }}</span>
              </el-option>
            </el-select>
            <el-button class="filter-item" style="margin-left: 10px;" type="warning" icon="el-icon-plus" @click="addstdclasspop = true">
              Add Class
            </el-button>
          </el-form-item>
          <el-form-item label="DOB" prop="dob">
            <el-date-picker
              format="dd/MM/yyyy"
              value-format="yyyy-MM-dd"
              v-model="student.dob"
              type="date"
              placeholder="Pick a date of birth"
            />
          </el-form-item>
          <el-form-item label="Admission#" prop="adminssion_number">
            <el-input v-model="student.adminssion_number" />
          </el-form-item>
          <el-form-item label="B Form#" prop="b_form">
            <el-input v-model="student.b_form" />
          </el-form-item>
          <el-form-item label="Gender" prop="gender">
            <el-select v-model="student.gender" placeholder="Select">
              <el-option
                v-for="gender in genders"
                :key="gender.value"
                :label="gender.label"
                :value="gender.value"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="Monthly Fee" prop="monthly_fee">
            <el-input v-model="student.monthly_fee" />
          </el-form-item>
          <el-form-item label="Subling" prop="subling">
            <el-radio-group v-model="student.subling">
              <el-radio-button label="1">Yes</el-radio-button>
              <el-radio-button label="0">No</el-radio-button>
            </el-radio-group>
          </el-form-item>
          <el-form-item label="Religion" prop="religion">
            <el-select v-model="student.religion" placeholder="Select Religion">
              <el-option
                v-for="religion in religions"
                :key="religion.value"
                :label="religion.label"
                :value="religion.value"
              />
            </el-select>
          </el-form-item>
          <el-form-item label="Subling" prop="subling">
            <el-radio-group v-model="student.status">
              <el-radio-button label="enable">Enable</el-radio-button>
              <el-radio-button label="disable">Disable</el-radio-button>
            </el-radio-group>
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="customerForm = false;">
            Cancel
          </el-button>
          <el-button type="primary" :loading="loading" @click="handleSubmit('student')">
            {{ (student.id !== '') ? 'Edit Student' : 'Add Student' }}
          </el-button>
        </div>
      </div>
    </el-dialog>
    <add-parent v-if="addparentpop" :editnowprop="addparentpop" @closePopUp="addparentpop = !addparentpop"></add-parent>
    <add-class v-if="addstdclasspop" :addeditclassprop="addstdclasspop" @closeAddClass="closeAddClassPopup()"></add-class>
  </div>
</template>
<script>
import Resource from '@/api/resource';
import AddClass from '@/views/stdclasses/AddClass.vue';
import AddParent from '@/views/parents/AddParent.vue';
const stdRes = new Resource('students');
var stdClass = new Resource('classes');
var stdParent = new Resource('parents');
export default {
  name: 'AddStudent',
  components: {AddClass, AddParent },
  directives: { },
  props: {
    addstudentpop: {
      type: Boolean,
      required: true,
    },
    stdid: {
      type: Number,
      default: null,
    },
  },
  data() {
    var roll_no = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please enter the Roll No.'));
      } else {
        callback();
      }
    };
    var name = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please enter the Name'));
      } else {
        callback();
      }
    };
    var parent = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Search and select parent'));
      } else {
        callback();
      }
    };
    var class_id = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Select a class'));
      } else {
        callback();
      }
    };
    var dob = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Select  Date of birth'));
      } else {
        callback();
      }
    };
    var monthly_fee = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Enter monthly fee.'));
      } else {
        callback();
      }
    };
    return {
      loading: false,
      parentloading: false,
      customerForm: true,
      addstdclasspop: false,
      addparentpop: false,
      rules: {
        roll_no: [{ validator: roll_no, trigger: 'blur' }],
        name: [{ validator: name, trigger: 'blur' }],
        parent_id: [{ validator: parent, trigger: 'blur' }],
        class_id: [{ validator: class_id, trigger: 'blur' }],
        dob: [{ validator: dob, trigger: 'blur' }],
        monthly_fee: [{ validator: monthly_fee, trigger: 'blur' }],
      },
      classes: [],
      parents: [],
      genders: [
        {
          label: 'Male',
          value: 'Male',
        },
        {
          label: 'Female',
          value: 'Female',
        },
      ],
      religions: [
        {
          label: 'Islam',
          value: 'Islam',
        },
        {
          label: 'Christian',
          value: 'Christian',
        },
      ],
      student: {
        id: '',
        name: '',
        parent_id: '',
        class_id: '',
        dob: '',
        b_form: '',
        gender: 'Male',
        monthly_fee: '',
        subling: '0',
        religion: 'Islam',
        status: 'enable',
      },
      parentquery: {
        keyword: '',
      },
    };
  },
  watch: {
    customerForm: {
      handler: function(val, oldval) {
        this.tellToParent();
      },
    },
  },
  created() {
    this.getClasses();
    if (this.stdid !== null) {
      this.getStudent();
    }
  },
  methods: {
    tellToParent() {
      this.$emit('closeAddStudent', this.customerForm);
    },
    async handleSubmit(formName) {
      this.loading = true;
      await this.$refs[formName].validate(valid => {
        if (valid) {
          if (this.student.id !== undefined && this.student.id !== null && this.student.id !== '') {
            stdRes
              .update(this.student.id, this.student)
              .then(response => {
                this.$message({
                  type: 'success',
                  message: 'Student info has been updated successfully',
                  duration: 5 * 1000,
                });
                this.tellToParent();
                this.loading = false;
              })
              .catch(error => {
                this.$message({
                  type: 'error',
                  message: 'Somthing Wrong while updating' + error,
                  duration: 5 * 1000,
                });
                this.loading = false;
              })
              .finally(() => {
                this.customerForm = false;
                this.loading = false;
              });
          } else {
            stdRes
              .store(this.student)
              .then(response => {
                this.$message({
                  message:
                    'New Student  ' +
                    this.student.name +
                    ' has been created successfully.',
                  type: 'success',
                  duration: 5 * 1000,
                });
                this.account = {
                  name: '',
                  phone: '',
                  address: '',
                  type: '',
                };
                this.$emit('newcustomer', response);
                this.customerForm = false;
              })
              .catch(error => {
                console.log(error);
              });
          }
        } else {
          console.log('error submit!!');
          this.loading = false;
          return false;
        }
      });
    },
    async getStudent() {
      let { data } = await stdRes.get(this.stdid);
      this.student = data.student;
      data = await stdParent.get(this.student.parent_id);
      this.parents = [data.data.parent];
    },
    async getClasses() {
      const { data } = await stdClass.list();
      this.classes = data.classes.data;
      
    },
    async searchParent(query) {
      this.parentloading = true;
      this.parentquery.keyword = query;
      const { data } = await stdParent.list(this.parentquery);
      this.parents = data.parents.data;
      this.parentloading = false;
    },
    closeAddClassPopup() {
      this.getClasses();
      this.addstdclasspop = false;
      this.student = {
        id: '',
        name: '',
        parent_id: '',
        class_id: '',
        dob: '',
        gender: 'Male',
        monthly_fee: '',
        subling: '0',
        religion: 'Islam',
      };
    },
  },
};
</script>
<style  scoped>
</style>