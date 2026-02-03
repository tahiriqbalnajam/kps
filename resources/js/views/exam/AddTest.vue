<template>
  <el-drawer title="Add Test" :modelValue="addedittestprop" @close="handleClose" size="95%" :rules="rules">
    <el-form style="width: 100%" :inline="true" :model="test" class="demo-form-inline" ref="addtestform">
      <el-row :gutter="10">
        <el-col :span="4">
          <el-form-item label="Test Name" prop="title">
            <el-input v-model="test.title" placeholder="Test title" clearable size="small"/>
          </el-form-item>
        </el-col>
        <el-col :span="3">
          <el-form-item label="Teacher" prop="teacher_id" style="width: 100%;">
            <el-select v-model="test.teacher_id" placeholder="Select Teacher" clearable size="small">
              <el-option v-for="item in teachers" :key="item.id" :label="item.name" :value="item.id"/>
            </el-select>
          </el-form-item>
        </el-col>
        <el-col :span="3">
          <el-form-item label="Class" prop="class_id" style="width: 100%;">
            <el-tree-select 
              check-strictly
              v-model="test.class_id" 
              :data="classes"
              placeholder="Select class/section..." 
              clearable 
              @change="getstudents()" 
              size="small"
            />
          </el-form-item>
        </el-col>
        <el-col :span="3">
          <el-form-item label="Subject" prop="subject_id" style="width: 100%;">
            <el-select v-model="test.subject_id" placeholder="Select Subject" clearable size="small">
              <el-option v-for="item in subjects" :key="item.id" :label="item.title" :value="item.id" />
            </el-select>
          </el-form-item>
        </el-col>
        <el-col :span="5">
          <el-form-item label="Date" prop="date">
            <el-date-picker v-model="test.date" type="date" placeholder="Pick a date" format="DD MMM, YYYY"
              value-format="YYYY-MM-DD" :default-value="new Date()" size="small"/>
          </el-form-item>
        </el-col>
        <el-col :span="4">
          <el-form-item label="Total Marks" prop="total_marks">
            <el-input-number v-model="test.total_marks" size="small" :min="1"/>
          </el-form-item>
        </el-col>
        <el-col :span="2">
          <el-form-item>
            <el-button 
              type="primary" 
              @click="addTest('addtestform')" 
              :loading="saving"
              :disabled="saving || !test?.students?.length || !test.title?.trim()"
            >
              {{ saving ? 'Saving...' : 'Save' }}
            </el-button>
          </el-form-item>
        </el-col>
      </el-row>
      <el-table :data="filterTableData" height="650" style="width: 100%" size="small" stripe v-loading="listloading" empty-text="select a class first">
        <el-table-column label="Absent" prop="absent">
          <template #default="scope">
            <el-switch
              v-model="scope.row.absent"
              size="small"
              active-value="yes"
              inactive-value="no"
            />
          </template>
        </el-table-column>
        <el-table-column prop="roll_no" label="Roll No" />
        <el-table-column prop="name" label="Name" />
        <el-table-column prop="parents.name" label="Father Name" />
        <el-table-column prop="stdclasses.name" label="Class" />
        <el-table-column prop="obtainedmarks" label="Obtained Marks">
          <template #default="scope">
            <el-input :tabindex="scope.row.roll_no" v-model="scope.row.score" required placeholder="Enter Marks" clearable
              @change="validateMarks(scope.row.score, test.total_marks, scope.row)" size="small" :disabled="scope.row.absent == 'yes'"/>
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
<script>
import Resource from '@/api/resource.js';
const classes = new Resource('classes');
let tests = new Resource('tests');
const students = new Resource('students');
const subjectRes = new Resource('subject_class');
const teacherRes = new Resource('teachers');
const testRes = new Resource('tests-result');

export default {
  name: "AddTest",
  props: {
    addedittestprop: {
      type: Boolean,
      required: true,
    },
    editid: {
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
      saving: false,
      classes: [],
      subjects: [],
      teachers: [],
      test: {
        title: '',
        teacher_id: '',
        subject_id: '',
        class_id: '',
        section_id: null,
        total_marks: 0,
        date: new Date(),
        students: [],
      },
      search:'',
      query: {
        filter: {},
        fields: {},
      },
      testquery: {
        filter: {},
      },
      testresult: {
        filter: {},
      },
      class_subject: {
        filter: {},
      },
      rules: {
        title: [
          { required: true, message: 'Please enter the test title', trigger: 'blur' },
          { validator: title, trigger: 'blur' }
        ],
        class_id: [
          { required: true, message: 'Please select a class', trigger: 'change' },
          { validator: class_id, trigger: 'change' }
        ],
        subject_id: [
          { required: true, message: 'Please select a subject', trigger: 'change' },
          { validator: subject_id, trigger: 'change' }
        ],
        total_marks: [
          { required: true, message: 'Please enter total marks', trigger: 'blur' },
          { type: 'number', min: 1, message: 'Total marks must be greater than 0', trigger: 'blur' },
          { validator: total_marks, trigger: 'blur' }
        ],
        date: [
          { required: true, message: 'Please select a date', trigger: 'change' }
        ],
      },
    };
  },
  async mounted() {
    if (this.editid !== null) {
      console.log('in mounted condition');
      this.testquery.filter['id'] = this.editid;
      const { data } = await tests.list(this.testquery);
      this.test = data.tests.data[0];
      
      // Convert class_id to tree-select format
      if (this.test.section_id) {
        // If there's a section_id, use section format
        this.test.class_id = `section_${this.test.section_id}`;
      } else if (this.test.class_id) {
        // If only class_id, use class format
        this.test.class_id = `class_${this.test.class_id}`;
      }
      
      this.testresult.filter['test_id'] = this.editid;
      let results = await testRes.list(this.testresult);
      results = results.data.results.data;
      await this.getstudents();
      this.test.students.forEach(student => {
        let result = results.find(result => result.student_id === student.id);
        if (result) {
          student.score = result.score;
          student.absent = result.absent;
          student.test_result_id = result.id;
        }
      });
      
    }
  },
  computed: {
    filterTableData() {
      if(this.test?.students?.length)
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
    this.getTeachers();
  },
  methods: {
    cancelAddParent() {
      this.closepopup = false;
      this.$emit('closePopUp', 'yes')
    },
    async getstudents() {
      if (!this.test.class_id) return;
      
      this.listloading = true;
      
      // Parse the selected value to determine if it's a class or section
      const selectedValue = this.test.class_id.toString();
      let classId = null;
      let sectionId = null;
      
      if (selectedValue.startsWith('class_')) {
        classId = selectedValue.split('_')[1];
        this.test.section_id = null;
      } else if (selectedValue.startsWith('section_')) {
        sectionId = selectedValue.split('_')[1];
        this.test.section_id = sectionId;
        
        // Find the class ID for this section
        const selectedSection = this.findSectionById(sectionId);
        if (selectedSection) {
          classId = selectedSection.class_id;
        }
      }
      
      // Build query for students
      this.query.filter = {
        stdclass: classId,
        status: 'enable'
      };
      
      if (sectionId) {
        this.query.filter['section_id'] = sectionId;
      }
      
      this.query.fields = {
        'students': 'id,name,roll_no,parent_id,class_id,parent.id, parent.name, class.id, class.name',
        'parents': 'id,name'
      };
      
      const { data } = await students.list(this.query);
      this.test.students = data.students.data;
      
      // Get subjects for the class
      this.class_subject.filter['id'] = classId;
      const subjectdata = await subjectRes.list(this.class_subject);
      this.subjects = subjectdata.data.classubj.data[0].subjects;
      
      this.listloading = false;
    },
    
    findSectionById(sectionId) {
      for (const classItem of this.classes) {
        if (classItem.children) {
          const section = classItem.children.find(section => section.id == sectionId);
          if (section) {
            return section;
          }
        }
      }
      return null;
    },
    
    async getClasses() {
      const { data } = await classes.list({ include: 'sections' });
      // Transform the classes data to include class and section hierarchy for tree select
      this.classes = data.classes.data.map(classItem => {
        // Create the parent class node
        const classNode = {
          value: `class_${classItem.id}`,
          label: `${classItem.name}`,
          id: classItem.id,
          type: 'class',
          name: classItem.name,
          students_count: classItem.students_count,
          males_count: classItem.males_count,
          females_count: classItem.females_count
        };
        
        // Add children if there are sections
        if (classItem.sections && classItem.sections.length > 0) {
          classNode.children = classItem.sections.map(section => ({
            value: `section_${section.id}`,
            label: `${section.name}`,
            id: section.id,
            type: 'section',
            class_id: classItem.id,
            name: section.name,
            students_count: section.students_count,
            males_count: section.males_count,
            females_count: section.females_count
          }));
        }
        
        return classNode;
      });
    },
    async getTeachers() {
      const { data } = await teacherRes.list();
      this.teachers = data.teachers.data;
    },
    async addTest(formName) {
      // Start validation first, before setting loading state
      this.$refs[formName].validate(
        async (valid) => {
          if (valid) {
            // Only set loading state after validation passes
            this.saving = true;
            
            try {
              // Prepare test data with proper class_id and section_id
              const testData = { ...this.test };
              
              // Parse the selected value to extract actual class_id and section_id
              const selectedValue = testData.class_id.toString();
              
              if (selectedValue.startsWith('class_')) {
                testData.class_id = parseInt(selectedValue.split('_')[1]);
                testData.section_id = null;
              } else if (selectedValue.startsWith('section_')) {
                const sectionId = parseInt(selectedValue.split('_')[1]);
                testData.section_id = sectionId;
                
                // Find the class ID for this section
                const selectedSection = this.findSectionById(sectionId);
                if (selectedSection) {
                  testData.class_id = selectedSection.class_id;
                }
              }
              
              if (this.editid) {
                const { data } = await tests.update(this.editid, testData);
                this.$message({
                  message: 'Test updated successfully',
                  type: 'success',
                });
                this.handleClose();
              } else {
                const { data } = await tests.store(testData);
                this.$message({
                  message: 'Test added successfully',
                  type: 'success',
                });
                // Reset form after successful save
                this.resetForm();
                this.handleClose();
              }
            } catch (error) {
              console.error('Error saving test:', error);
              this.$message({
                message: error.response?.data?.message || 'Error saving test. Please try again.',
                type: 'error',
              });
            } finally {
              // Always reset saving state
              this.saving = false;
            }
          } else {
            // Validation failed - show error message
            this.$message({
              message: 'Please fill in all required fields correctly',
              type: 'error',
            });
            return false;
          }
        }
      );
    },
    
    resetForm() {
      this.test = {
        title: '',
        teacher_id: '',
        subject_id: '',
        class_id: '',
        total_marks: 0,
        date: new Date(),
        students: [],
      };
      this.subjects = [];
      // Reset form validation
      if (this.$refs.addtestform) {
        this.$refs.addtestform.resetFields();
      }
    },
    handleClose() {
      // Reset loading states
      this.saving = false;
      this.listloading = false;
      
      // Reset form if not editing
      if (!this.editid) {
        this.resetForm();
      }
      
      console.log('close child called');
      this.$emit('popupClosed', 'yes');
    },
    validateMarks(obtain, total, student) {
      if (obtain > total || obtain < 0) {
        this.$message.error('Obtain marks should not be greater than Total marks.');
        student.score = 0
      }

    }
  }
};
</script>
