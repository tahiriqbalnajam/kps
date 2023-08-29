<script setup>
  import { onMounted } from "vue";
  import { reactive } from 'vue';
  import Resource from '@/api/resource.js';
  const classes = new Resource('classes');
  let resource = new Resource('exam_result');
  const students = new Resource('students');
  const subjectRes = new Resource('subject_class');
  
  const formInline = reactive({
    examname: '',
    classes: '',
    stdclass: '',
    students: '',
    subjects: ''
  })

  const resetFormInline = () => {
    formInline.examname = '';
    formInline.stdclass = '';
    formInline.students = '';
    formInline.subjects = '';
  }

  const localdata = reactive({
    loading: false
  })
  
  const query = reactive({
    page: 1,
    limit: 15,
    keyword: '',
    filtercol: 'name',
    stdclass: '',
  })

  const exam = reactive({
    examname: '',
    class_id: '',
    total_marks: '',
    students: '',
  })

  const props = defineProps({
    addedittestprop: {
      type: Boolean,
      default: true
    }
  })

  const onSubmit = () => {
    exam.examname = formInline.examname;
    exam.class_id = formInline.stdclass;
    exam.subject_id = formInline.subject;
    exam.total_marks = formInline.total_marks;
    console.log(formInline.students);
    exam.students = formInline.students;
    resource.store(exam);
    resetFormInline();
    handleClose();
  }

  const getClasses = async() => {
    const{ data } = await classes.list();
    formInline.classes = data.classes.data;
  }

  const getstudents = async() => {
    query.stdclass = formInline.stdclass
    const { data } = await students.list(query);
    formInline.students = data.students.data;

    const subjectdata  = await subjectRes.list(query);
    console.log(subjectdata.data.classubj.data[0].subjects);
    formInline.subjects = subjectdata.data.classubj.data[0].subjects;
  }

  const emit = defineEmits(['popupclosed'])
  onMounted(() => {
    getClasses();
  });

  const handleClose = () => {
    //addedittestprop = false;
    emit('popupclosed')
  }

</script>
<template>

  <div >
    <el-dialog title="Add Test" :modelValue="addedittestprop" @close="handleClose">
      <el-form style="width: 100%" :inline="true" :model="formInline" class="demo-form-inline">
        <el-row :gutter="10">
          <el-col :span="8">
            <el-form-item label="Exam Name">
              <el-input v-model="formInline.examname" placeholder="Exam Name" clearable />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Class Name">
              <el-select v-model="formInline.stdclass" placeholder="Select Class" clearable @change="getstudents()" >
                <el-option
                  v-for="item in formInline.classes"
                  :key="item.id"
                  :label="item.name "
                  :value="item.id"
                  @click="onchange"
                />
              </el-select>
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item label="Class Name">
              <el-select v-model="formInline.subject" placeholder="Select Subject" clearable>
                <el-option
                  v-for="item in formInline.subjects"
                  :key="item.id"
                  :label="item.title"
                  :value="item.id"
                />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>
        <el-row :gutter="10">
          <el-col :span="8">
            <el-form-item label="Totla Marks">
              <el-input-number v-model="formInline.total_marks" />
            </el-form-item>
          </el-col>
          <el-col :span="8">
            <el-form-item>
              <el-button type="primary" @click="onSubmit" :disabled="!formInline.students.length">Save</el-button>
            </el-form-item>
          </el-col>
        </el-row>
       
        <el-table :data="formInline.students" height="400" style="width: 100%">
          <el-table-column prop="id" label="ID" width="180" />
          <el-table-column prop="name" label="Name" width="180" />
          <el-table-column prop="obtainedmarks" label="Obtained Marks" width="180" >
            <template #default="scope">
              <el-input v-model="scope.row.obtained_marks" required placeholder="Enter Marks" clearable />
            </template>
          </el-table-column>
        </el-table>
      </el-form>
  </el-dialog>
  </div>
 
</template>