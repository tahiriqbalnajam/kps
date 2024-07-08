<script>
import Resource from '@/api/resource.js';
const classes = new Resource('classes');
let tests = new Resource('tests');
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
  computed: {
    filterTableData() {
      return this.attendance.students.filter(
        (data) =>
          !this.search ||
          data.name.toLowerCase().includes(this.search.toLowerCase())
      )
    }

  },
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
        date: new Date(),
        students: {},
      },
      search:'',
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
  computed: {
    filterTableData() {
      if(this.test.students.length)
        return this.test.students.filter(
          (data) =>
            !this.search ||
            data.name.toLowerCase().includes(this.search.toLowerCase())
        )
      else
        return '';
    }

  },
  created() {
    this.getClasses();
  },
  methods: {
    cancelAddParent() {
      this.closepopup = false;
      this.$emit('closePopUp', 'yes')
    },
    async getstudents() {
      this.query.filter['stdclass'] = this.test.class_id;
      const { data } = await students.list(this.query);
      this.test.students = data.students.data;

      const subjectdata = await subjectRes.list(this.query);
      this.subjects = subjectdata.data.classubj.data[0].subjects;
    },
    async getClasses() {
      const { data } = await classes.list();
      this.classes = data.classes.data;
    },
    async addTest(formName) {
      alert('asdfasdf');
      this.$refs[formName].validate(
        async (valid) => {
          if (valid) {
            this.listloading = true;
            const { data } = await tests.store(this.test);
            this.listloading = false;
            this.$message({
              message: 'Test added successfully',
              type: 'success',
            });
            this.handleClose();
          }
        }
      );

    },
    handleClose() {
      this.addedittestprop = false;
      this.$emit('popupClosed', 'yes')
    },
    validateMarks(obtain, total, student) {
      if (obtain > total || obtain < 0) {
        this.$message.error('Enter correct marks.');
        student.score = 0
      }

    }
  }
};
</script>

<template>
  <el-drawer title="Add Test" :modelValue="addedittestprop" @close="handleClose" size="90%" :rules="rules">
    <el-form style="width: 100%" :inline="true" :model="test" class="demo-form-inline" ref="addtestform">
      <el-row :gutter="10">
        <el-col :span="5">
          <el-form-item label="Test Name">
            <el-input v-model="test.title" placeholder="Test title" clearable prop="title" />
          </el-form-item>
        </el-col>
        <el-col :span="4">
          <el-form-item label="Class">
            <el-select v-model="test.class_id" placeholder="Select Class" clearable @change="getstudents()"
              prop="class_id">
              <el-option v-for="item in classes" :key="item.id" :label="item.name" :value="item.id"
                @click="onchange" />
            </el-select>
          </el-form-item>
        </el-col>
        <el-col :span="4">
          <el-form-item label="Subject">
            <el-select v-model="test.subject_id" placeholder="Select Subject" clearable>
              <el-option v-for="item in subjects" :key="item.id" :label="item.title" :value="item.id" />
            </el-select>
          </el-form-item>
        </el-col>
        <el-col :span="4">
          <el-form-item label="Date">
            <el-date-picker v-model="test.date" type="test.date" placeholder="Pick a date" format="DD MMM, YYYY"
              value-format="YYYY-MM-DD" :default-value="new Date()" />
          </el-form-item>
        </el-col>
        <el-col :span="5">
          <el-form-item label="Total Marks">
            <el-input-number v-model="test.total_marks" />
          </el-form-item>
        </el-col>
        <el-col :span="2">
          <el-form-item>
            <el-button type="primary" @click="addTest('addtestform')" :disabled="!test.students.length">Save</el-button>
          </el-form-item>
        </el-col>
      </el-row>
      <el-table :data="filterTableData" height="650" style="width: 100%">
        <el-table-column prop="name" label="Name" />
        <el-table-column prop="parents.name" label="Father Name" />
        <el-table-column prop="stdclasses.name" label="Class" />
        <el-table-column prop="obtainedmarks" label="Obtained Marks">
          <template #default="scope">
            <el-input v-model="scope.row.score" required placeholder="Enter Marks" clearable
              @change="validateMarks(scope.row.score, test.total_marks, scope.row)" />
          </template>
        </el-table-column>
        <el-table-column label="Search">
          <template #header>
            <el-input v-model="search" size="small" placeholder="Type to search" />
          </template>
          <template #default="scope">
            {{ (scope.row.score > 0 && test.total_marks > 0) ? ((scope.row.score / test.total_marks) * 100)+"%" : "0%" }}
          </template>
        </el-table-column>
      </el-table>
    </el-form>
  </el-drawer>
</template>
