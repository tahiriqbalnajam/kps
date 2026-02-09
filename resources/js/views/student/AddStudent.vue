<template>
  <div>
    <el-drawer
      :modelValue="addeditstudentprop"
      title="Add Student"
      :direction="direction"
      :before-close="handleClose"
      size="95%"
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
            <el-col :xs="24" :sm="12" :md="8" :lg="6" :xl="6">
              <el-form-item label="Name" prop="name">
                <el-input v-model="student.name" />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8" :lg="6" :xl="6">
              <el-form-item label="Roll No." prop="roll_no">
                <el-input v-model="student.roll_no" />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="24" :md="8" :lg="12" :xl="12">
              <el-form-item label="Select Class" prop="class_id">
                <el-row :gutter="10">
                  <el-col :xs="24" :sm="14" :md="11" :lg="11" :xl="11">
                    <el-tree-select
                      v-model="student.class_id"
                      :data="classes"
                      check-strictly
                      node-key="id"
                      :props="classProps"
                      placeholder="Classes"
                      style="width: 100%"
                    />
                  </el-col>
                  <el-col :xs="24" :sm="4" :md="4" :lg="4" :xl="4" class="text-center">
                    <span style="font-size: 11px; text-align: center; width: 100%;"> If not found</span>
                  </el-col>
                  <el-col :xs="24" :sm="6" :md="9" :lg="9" :xl="9">
                    <el-button class="filter-item" style="margin-left: 10px; width: 100%;" type="warning" icon="el-icon-plus" @click="addstdclasspop = true">
                      Add Class
                    </el-button>
                  </el-col>
                </el-row>
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="8" :lg="6" :xl="6">
              <el-form-item label="DOB" prop="dob">
                <el-date-picker
                  format="DD/MM/YYYY"
                  value-format="YYYY-MM-DD"
                  v-model="student.dob"
                  type="date"
                  placeholder="Pick a date of birth"
                  style="width: 100%"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8" :lg="6" :xl="6">
              <el-form-item label="DOA" prop="doa">
                <el-date-picker
                  format="DD/MM/YYYY"
                  value-format="YYYY-MM-DD"
                  v-model="student.doa"
                  type="date"
                  placeholder="Pick a date of admission"
                  style="width: 100%"
                />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="8" :lg="6" :xl="6">
              <el-form-item label="Admission#" prop="adminssion_number">
                <el-input v-model="student.adminssion_number" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-divider content-position="left" style="margin-top:50px; margin-bottom: 30px"><el-tag type="success" effect="plain"><b>2.</b> Other Information</el-tag></el-divider>
          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item label="B Form#" prop="b_form">
                <el-input v-model="student.b_form" />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item label="Previous School" prop="previous_school">
                <el-input v-model="student.previous_school" />
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item label="Religion" prop="religion">
                <el-select v-model="student.religion" placeholder="Select Religion" style="width: 100%">
                  <el-option
                    v-for="religion in religions"
                    :key="religion.value"
                    :label="religion.label"
                    :value="religion.value"
                  />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item label="Cast" prop="cast">
                <el-input v-model="student.cast" />
              </el-form-item>
            </el-col>
          </el-row>
          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item label="Monthly Fee" prop="monthly_fee">
                <el-input-number v-model="student.monthly_fee" controls-position="right" style="width: 100%"/>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item label="Fee Discount" prop="monthly_fee_discount">
                <el-input-number v-model="student.monthly_fee_discount" controls-position="right" style="width: 100%"/>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item label="Gender" prop="gender">
                <el-select v-model="student.gender" placeholder="Gender" style="width: 100%">
                  <el-option label="Male" value="male" />
                  <el-option label="Female" value="female" />
                </el-select>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              
            </el-col>
          </el-row>
          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item label="Sibling" prop="sibling">
                <el-radio-group v-model="student.sibling" size="small">
                  <el-radio-button label="1">Yes</el-radio-button>
                  <el-radio-button label="0">No</el-radio-button>
                </el-radio-group>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item label="Orphan Student" prop="is_orphan">
                <el-radio-group v-model="student.is_orphan" size="small">
                  <el-radio-button label="Yes">Yes</el-radio-button>
                  <el-radio-button label="No">No</el-radio-button>
                </el-radio-group>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item label="Registration with PEF" prop="pef_admission">
                <el-radio-group v-model="student.pef_admission" size="small">
                  <el-radio-button label="Yes">Yes</el-radio-button>
                  <el-radio-button label="No">No</el-radio-button>
                </el-radio-group>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item label="Nadra Pending" prop="nadra_pending">
                <el-radio-group v-model="student.nadra_pending" size="small">
                  <el-radio-button label="Yes" value="Yes">Yes</el-radio-button>
                  <el-radio-button label="No" value="No">No</el-radio-button>
                </el-radio-group>
              </el-form-item>
            </el-col>
          </el-row>

          <el-divider content-position="left" style="margin-top:50px; margin-bottom: 30px;"><el-tag type="success" effect="plain"><b>3.</b> Father/Guardien Information</el-tag></el-divider>
          <el-form-item label="Parent" prop="parent_id">
            <el-row :gutter="10">
              <el-col :xs="24" :sm="14" :md="10" :lg="10" :xl="10">
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
              <el-col :xs="24" :sm="4" :md="4" :lg="4" :xl="4" class="text-center">
                <span style="font-size: 11px; text-align: center; width: 100%;"> If not found</span>
              </el-col>
              <el-col :xs="24" :sm="6" :md="10" :lg="10" :xl="10">
                <el-button class="filter-item" style="margin-left: 10px; width: 100%;" type="success" icon="el-icon-plus" @click="addparentpop = true">
                  Add Parent
                </el-button>
              </el-col>
            </el-row>
          </el-form-item>
          <el-divider content-position="left" style="margin-top:50px; margin-bottom: 30px"><el-tag type="success" effect="plain"><b>4.</b> Others</el-tag></el-divider>
          <el-row :gutter="20">
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item label="Any Action" prop="status">
                <el-radio-group v-model="student.action_required" size="small">
                  <el-radio-button label="Yes">Yes</el-radio-button>
                  <el-radio-button label="No">No</el-radio-button>
                </el-radio-group>
                <el-input v-model="student.action_details" v-if="student.action_required == 'Yes'" :rows="2" type="textarea" style="margin-top: 20px; width: 100%;"/>
              </el-form-item>
            </el-col>
            <el-col :xs="24" :sm="12" :md="6" :lg="6" :xl="6">
              <el-form-item label="Status" prop="status">
                <el-radio-group v-model="student.status" size="small">
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
    var doa = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Select  Date of Admission'));
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
    var adminssion_number = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Enter admission#'));
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
        adminssion_number: [{ validator: adminssion_number, trigger: 'blur' }],
        doa: [{ validator: doa, trigger: 'blur' }],
      },
      classes: [],
      classProps: {
        label: 'name',
        children: 'sections',
        value: 'id_value' // Change this to use our custom formatted value
      },
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
        monthly_fee_discount: '',
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
        monthly_fee_discount: '',
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
      try {
        // Parse the class_id format before submitting
        if (this.student.class_id && typeof this.student.class_id === 'string' && this.student.class_id.startsWith('class_')) {
          const parts = this.student.class_id.split('_');
          
          if (parts.length === 3) {
            const classId = parseInt(parts[1]);
            const sectionId = parseInt(parts[2]);
            
            // Update the student object for submission
            // Create a copy to avoid modifying the bound model directly during submission if using parts for display
            const studentForSubmit = { ...this.student };
            studentForSubmit.class_id = classId;
            studentForSubmit.section_id = sectionId > 0 ? sectionId : null;
            
            // Validate form using Promise approach for cleaner async/await usage
            try {
              await this.$refs[formName].validate();
            } catch (err) {
              console.log('Validation failed');
              this.loading = false;
              return false;
            }

            // Proceed with API calls
            if (studentForSubmit.id !== undefined && studentForSubmit.id !== null && studentForSubmit.id !== '') {
              // Update existing student
              await stdRes.update(studentForSubmit.id, studentForSubmit);
              
              this.$message({
                type: 'success',
                message: 'Student info has been updated successfully',
                duration: 5 * 1000,
              });
              
              this.handleClose();
            } else {
              // Create new student
              const response = await stdRes.store(studentForSubmit);
              
              this.$message({
                message: 'New Student ' + studentForSubmit.name + ' has been created successfully.',
                type: 'success',
                duration: 5 * 1000,
              });
              
              // Reset specific fields if needed, or rely on handleClose/parent reload
              this.account = {
                name: '',
                phone: '',
                address: '',
                type: '',
              };
              
              this.$emit('newcustomer', response);
              this.customerForm = false;
              // Ideally close the drawer or reset form here too
              this.handleClose(); 
            }

          } else {
            throw new Error('Invalid class format');
          }
        } else {
          throw new Error('Please select a valid class');
        }
      } catch (error) {
        console.error('Submit Error:', error);
        let errorMessage = 'An error occurred while saving.';
        
        if (error.response && error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message;
        } else if (error.message) {
          errorMessage = error.message;
        }

        this.$message({
          type: 'error',
          message: errorMessage,
          duration: 5 * 1000,
        });
      } finally {
        this.loading = false;
      }
    },
    async getStudent() {
      if(!this.stdid)
        return;
      
      let { data } = await stdRes.get(this.stdid);
      this.student = data.student;
      
      // Format the class_id to follow our new convention if section info is available
      if (this.student.class_id && this.student.section_id) {
        this.student.class_id = `class_${this.student.class_id}_${this.student.section_id}`;
      } else if (this.student.class_id) {
        this.student.class_id = `class_${this.student.class_id}_0`;
      }
      
      data = await stdParent.get(this.student.parent_id);
      this.parents = [data.data.parent];
    },
    async getClasses() {
      const { data } = await stdClass.list({ include: 'sections' });
      // Transform the data to include formatted id_value
      this.classes = data.classes.data.map(classItem => {
        return {
          ...classItem,
          id_value: `class_${classItem.id}_0`, // For main class nodes
          sections: classItem.sections?.map(section => {
            return {
              ...section,
              id_value: `class_${classItem.id}_${section.id}` // For section nodes
            };
          }) || []
        };
      });
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
        monthly_fee_discount: '',
        sibling: '0',
        religion: 'Islam',
        status: 'enable',
        action_required: 'No',
        action_details: '',
      };
    },
  },
};
</script>
<style scoped>
.text-center {
  text-align: center;
}

@media (max-width: 768px) {
  .form-container {
    padding: 10px;
  }
  
  .el-form-item {
    margin-bottom: 15px;
  }
  
  .el-divider {
    margin: 20px 0 !important;
  }
}

@media (max-width: 576px) {
  .filter-item {
    margin-left: 0 !important;
    margin-top: 10px;
  }
}
</style>