<template>
  <div>
    <el-drawer
      :modelValue="addeditstudentprop"
      title="Add Student"
      :direction="direction"
      :before-close="handleClose"
      size="80%"
    >
    <template #header>
      <el-page-header>
        <template #content>
          <span class="text-large font-600 mr-3"> Add/Edit Student </span>
        </template>
      </el-page-header>
    </template>
    <!-- <el-dialog title="Add Student" :modelValue="addeditstudentprop" :before-close="handleClose"> -->
      <div class="form-container">
        <el-form
          ref="student"
          :model="student"
          :rules="rules"
          label-position="right"
          label-width="auto"
        >
          <el-divider content-position="left" style="margin-bottom: 30px;"><el-tag type="success" effect="plain">1. Student Information</el-tag></el-divider>
          <el-row :gutter="20">
            <el-col :span="6">
              <el-form-item label="Name" prop="name">
                <el-input v-model="student.name" />
              </el-form-item>
            </el-col>
            <el-col :span="6">
              <el-form-item label="Roll No." prop="roll_no">
                <el-input v-model="student.roll_no" />
              </el-form-item>
            </el-col>
            <el-col :span="12">
              <el-form-item label="Select Class" prop="class_id">
                <el-col :span="11">
                  <el-select v-model="student.class_id" placeholder="Classes">
                    <el-option
                      v-for="stdclass in classes"
                      :key="stdclass.id"
                      :label="stdclass.name"
                      :value="stdclass.id"
                      style="width: 100%">
                      <span style="float: left">{{ stdclass.name }}</span>
                      <span style="float: right">{{ stdclass.total_students }}</span>
                    </el-option>
                  </el-select>
                </el-col>
                <el-col :span="4">
                  <span style="font-size: 11px; text-align: center; width: 100%;"> If not found</span>
                </el-col>
                <el-col :span="9">
                  <el-button class="filter-item" style="margin-left: 10px;" type="warning" icon="el-icon-plus" @click="addstdclasspop = true">
                    Add Class
                  </el-button>
                </el-col>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="20">
            <el-col :span="6">
              <el-form-item label="DOB" prop="dob">
                <el-date-picker
                  format="DD/MM/YYYY"
                  value-format="YYYY-MM-DD"
                  v-model="student.dob"
                  type="date"
                  placeholder="Pick a date of birth"
                />
              </el-form-item>
            </el-col>
            <el-col :span="6">
              <el-form-item label="DOA" prop="doa">
                <el-date-picker
                  format="DD/MM/YYYY"
                  value-format="YYYY-MM-DD"
                  v-model="student.doa"
                  type="date"
                  placeholder="Pick a date of admission"
                />
              </el-form-item>
            </el-col>
            <el-col :span="6">
              <el-form-item label="Admission#" prop="adminssion_number">
                <el-input v-model="student.adminssion_number" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-divider content-position="left" style="margin-top:50px; margin-bottom: 30px"><el-tag type="success" effect="plain"><b>2.</b> Other Information</el-tag></el-divider>
          <el-row :gutter="20">
            <el-col :span="6">
              <el-form-item label="B Form#" prop="b_form">
                <el-input v-model="student.b_form" />
              </el-form-item>
            </el-col>
            <el-col :span="6">
              <el-form-item label="Previous School" prop="previous_school">
                <el-input v-model="student.previous_school" />
              </el-form-item>
            </el-col>
            <el-col :span="6">
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
            </el-col>
            <el-col :span="6">
              <el-form-item label="Cast" prop="cast">
                <el-input v-model="student.cast" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="20">
            <el-col :span="6">
              <el-form-item label="Monthly Fee" prop="monthly_fee">
                <el-input v-model="student.monthly_fee" />
              </el-form-item>
            </el-col>
            <el-col :span="6">
              <el-form-item label="Gender" prop="gender">
                <el-select v-model="student.gender" placeholder="Gender">
                  <el-option label="Male" value="male" />
                  <el-option label="Female" value="female" />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :span="6">
              
            </el-col>
            <el-col :span="6">
              
            </el-col>
          </el-row>
          <el-row :gutter="20">
            <el-col :span="6">
              <el-form-item label="Sibling" prop="sibling">
                <el-radio-group v-model="student.sibling">
                  <el-radio-button label="1">Yes</el-radio-button>
                  <el-radio-button label="0">No</el-radio-button>
                </el-radio-group>
              </el-form-item>
            </el-col>
            <el-col :span="6">
              <el-form-item label="Orphan Student" prop="is_orphan">
                <el-radio-group v-model="student.is_orphan">
                  <el-radio-button label="Yes">Yes</el-radio-button>
                  <el-radio-button label="No">No</el-radio-button>
                </el-radio-group>
              </el-form-item>
            </el-col>
            <el-col :span="6">
              <el-form-item label="Registration with PEF" prop="pef_admission">
                <el-radio-group v-model="student.pef_admission">
                  <el-radio-button label="Yes">Yes</el-radio-button>
                  <el-radio-button label="No">No</el-radio-button>
                </el-radio-group>
              </el-form-item>
            </el-col>
            <el-col :span="6">
              <el-form-item label="Nadra Pending" prop="nadra_pending">
                <el-radio-group v-model="student.nadra_pending">
                  <el-radio-button label="Yes" value="Yes">Yes</el-radio-button>
                  <el-radio-button label="No" value="No">No</el-radio-button>
                </el-radio-group>
              </el-form-item>
            </el-col>
          </el-row>

          <el-divider content-position="left" style="margin-top:50px; margin-bottom: 30px;"><el-tag type="success" effect="plain"><b>3.</b> Father/Guardien Information</el-tag></el-divider>
          <el-form-item label="Parent" prop="parent_id">
                <el-col :span="10">
                  <el-select
                    v-model="student.parent_id"
                    filterable
                    remote
                    reserve-keyword
                    placeholder="Start typing to search parent"
                    :remote-method="searchParent"
                    :loading="parentloading"
                    style="width: 100%"
                    >
                    <el-option
                      v-for="parent in parents"
                      :key="parent.id"
                      :label="parent.name + ' (' + parent.cnic + ')'"
                      :value="parent.id"
                    />
                  </el-select>
                </el-col>
                <el-col :span="4">
                  <span style="font-size: 11px; text-align: center; width: 100%;"> If not found</span>
                </el-col>
                <el-col :span="10">
                  <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus" @click="addparentpop = true">
                    Add Parent
                  </el-button>
                </el-col>
              </el-form-item>
              <el-divider content-position="left" style="margin-top:50px; margin-bottom: 30px"><el-tag type="success" effect="plain"><b>4.</b> Others</el-tag></el-divider>
              <el-row :gutter="20">
                <el-col :span="6">
                  <el-form-item label="Any Action" prop="status">
                    <el-radio-group v-model="student.action_required">
                      <el-radio-button label="Yes">Yes</el-radio-button>
                      <el-radio-button label="No">No</el-radio-button>
                    </el-radio-group>
                    <el-input v-model="student.action_details" v-if="student.action_required == 'Yes'" :rows="2" type="textarea" style="margin-top: 20px; width: 225px;"/>
                  </el-form-item>
                </el-col>
                <el-col :span="6">
                  <el-form-item label="Status" prop="status">
                    <el-radio-group v-model="student.status">
                      <el-radio-button label="enable">Enable</el-radio-button>
                      <el-radio-button label="disable">Disable</el-radio-button>
                    </el-radio-group>
                  </el-form-item>
                </el-col>
              </el-row>
        </el-form>
        <div slot="footer" class="dialog-footer">
         
          
        </div>
      </div>
      <template #footer>
        <div style="flex: auto">
            <el-button @click="handleClose">
              Cancel
            </el-button>
            <el-button type="primary" :loading="loading" @click="handleSubmit('student')">
              {{ (student.id !== '') ? 'Edit Student' : 'Add Student' }}
            </el-button>
        </div>
      </template>
    </el-drawer>
    <!-- </el-dialog> -->
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
    addeditstudentprop: {
      type: Boolean,
      required: true,
    },
    stdid: {
      type: Number,
      default: null,
    },
  },
  emits: ['closeAddStudent'],
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
    var b_form = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Enter B form #.'));
      } else {
        callback();
      }
    };
    return {
      student_id: null,
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
        b_form: [{ validator: b_form, trigger: 'blur' }],
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
        sibling: '0',
        is_orphan: 'No',
        pef_admission: 'Yes',
        nadra_pending: 'No',
        religion: 'Islam',
        status: 'enable',
        action_required: 'No',
        action_details: '',
      },
      resetStudent: {
        id: '',
        name: '',
        parent_id: '',
        class_id: '',
        dob: '',
        b_form: '',
        gender: 'Male',
        monthly_fee: '',
        sibling: '0',
        is_orphan: 'No',
        pef_admission: 'Yes',
        nadra_pending: 'No',
        religion: 'Islam',
        status: 'enable',
        action_required: 'No',
        action_details: '',
      },
      parentquery: {
        keyword: '',
      },
    };
  },
  watch: {
    customerForm: {
      handler: function(val, oldval) {
        this.handleClose();
      },
    },
    stdid: function(val, oldval) {
      this.student_id = val;
      this.getStudent();
    },
  },
  created() {
    this.getClasses();
    //if (this.stdid !== null) {
      this.getStudent();
    //}
  },
  methods: {
    handleClose() {
      this.student = {...this.resetStudent};
      this.$emit('closeAddStudent', this.addeditstudentprop);
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
                this.handleClose();
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
      let { data } = await stdRes.get(this.student_id);
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