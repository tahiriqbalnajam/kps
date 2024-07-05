<script>
import Resource from '@/api/resource.js';
const classes = new Resource('classes');
let resource = new Resource('exam_result');
const students = new Resource('students');
const subjectRes = new Resource('subject_class');

export default {
  name: "AddTest",
  props: {
    addedittestprop: {
      type: Boolean,
      required: true,
    },
    parentid: {
      type: Number,
      default: null,
    },
  },
  emits: ['popupclosed'],
  data() {
    var title = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please enter the Title of test.'));
      } else {
        callback();
      }
    };
    var class_id = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please select a class.'));
      } else {
        callback();
      }
    };
    var subject_id = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please select a subject.'));
      } else {
        callback();
      }
    };
    var total_marks = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please enter the Total Marks.'));
      } else {
        callback();
      }
    };
    return {
      listloading: false,
      classes: null,
      subjects: null,
      test: {
        subject_id: '',
        class_id: '',
        total_marks: 0,
        students: {},
      },
      query: {
        filter: {},
      },
      rules: {
        title: [{ validator: title, trigger: 'blur' }],
        class_id: [{ validator: class_id, trigger: 'blur' }],
        subject_id: [{ validator: subject_id, trigger: 'blur' }],
        total_marks: [{ validator: total_marks, trigger: 'blur' }],
      },
    };
  },
  created() {
    this.getClasses();
  },
  methods: {
    cancelAddParent() {
      this.closepopup = false;
      this.$emit('closePopUp', 'yes')
    },
    async getstudents(){
      this.query.filter['stdclass'] = this.test.class_id;
      const { data } = await students.list(this.query);
      this.test.students = data.students.data;

      const subjectdata  = await subjectRes.list(this.query);
      this.subjects = subjectdata.data.classubj.data[0].subjects;
    },
    async getClasses() {
      const{ data } = await classes.list();
      this.classes = data.classes.data;
    },
    async addTest(formName) {
      this.$refs[formName].validate(
        async (valid) => {
          if (valid) {

          }
        }
      );

    },
    handleClose() {
      this.addedittestprop = false;
      this.$emit('popupClosed', 'yes')
    },
    validateMarks(obtain, total, student) {
      if(obtain > total || obtain < 0) {
        this.$message.error('Enter correct marks.');
        student.obtained_marks = 0
      }

    }
  }
};
</script>

<template>

<el-drawer title="Add Test" :modelValue="addedittestprop" @close="handleClose" size="90%" :rules="rules">
      <el-form style="width: 100%" :inline="true" :model="test" class="demo-form-inline" ref="addtest">
        <el-row :gutter="10">
          <el-col :span="6">
            <el-form-item label="Test Name">
              <el-input v-model="test.title" placeholder="Test title" clearable prop="title"/>
            </el-form-item>
          </el-col>
          <el-col :span="4">
            <el-form-item label="Class">
              <el-select v-model="test.class_id" placeholder="Select Class" clearable @change="getstudents()"  prop="class_id">
                <el-option
                  v-for="item in classes"
                  :key="item.id"
                  :label="item.name "
                  :value="item.id"
                  @click="onchange"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="4">
            <el-form-item label="Subject">
              <el-select v-model="test.subject_id" placeholder="Select Subject" clearable>
                <el-option
                  v-for="item in subjects"
                  :key="item.id"
                  :label="item.title"
                  :value="item.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="6">
            <el-form-item label="Total Marks">
              <el-input-number v-model="test.total_marks" />
            </el-form-item>
          </el-col>
          <el-col :span="2">
            <el-form-item>
              <el-button type="primary" @click="addTest('addtest')" :disabled="!test.students.length">Save</el-button>
            </el-form-item>
          </el-col>
        </el-row>
       
        <el-table :data="test.students" height="400" style="width: 100%">
          <el-table-column prop="name" label="Name" width="180" />
          <el-table-column prop="parents.name" label="Father Name" width="180" />
          <el-table-column prop="stdclasses.name" label="Class" width="180" />
          <el-table-column prop="obtainedmarks" label="Obtained Marks" width="180" >
            <template #default="scope">
              <el-input v-model="scope.row.obtained_marks" required placeholder="Enter Marks" clearable  
                        @change="validateMarks(scope.row.obtained_marks, test.total_marks, scope.row)"
              />
            </template>
          </el-table-column>
        </el-table>
      </el-form>
</el-drawer>
 
</template>
