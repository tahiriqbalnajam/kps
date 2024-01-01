<script setup>
  import { onMounted } from "vue";
  import { reactive } from 'vue';
  import Resource from '@/api/resource.js';
  const classes = new Resource('classes');
  let resource = new Resource('examtest_result');
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
    filter: {},
  })

  const exam = reactive({
    examname: '',
    class_id: '',
    subjects: '',
    students: '',
  })

  const props = defineProps({
    addedittestprop: {
      type: Boolean,
      default: true
    }
  })

  const onSubmit = async() => {
    exam.examname = formInline.examname;
    exam.class_id = formInline.stdclass;
    exam.subjects = formInline.subjects;
    exam.total_marks = formInline.total_marks;
    console.log(formInline.subjects);
    exam.students = formInline.students;
    try {
      const response = await resource.store(exam);
      resetFormInline();
      handleClose();
    } catch (error) {
      // Handle error response
      console.error(error);
    }
  }

  const getClasses = async() => {
    const{ data } = await classes.list();
    formInline.classes = data.classes.data;
  }

  const getstudents = async() => {
    query.filter.stdclass = formInline.stdclass
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
    <el-dialog title="Add Exam Marks" :modelValue="addedittestprop" @close="handleClose">
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
            <el-form-item>
              <el-button type="primary" @click="onSubmit" :disabled="!formInline.students.length">Save</el-button>
            </el-form-item>
          </el-col>
        </el-row>
        <el-table :data="formInline.students" height="400" style="width: 100%">
          <el-table-column prop="id" label="ID" width="180" />
          <el-table-column prop="name" label="Name" width="180" />
          <el-table-column label="Subjects">
            <el-table-column
              v-for="(subject, index) in formInline.subjects"
              :key="index"
              :prop="`subject_${index}`"
              :label="subject.title"
              width="180"
            >
            <template #header="innerScope">
              <el-input v-model="subject.total_marks" size="small" :placeholder="`Total ${subject.title}`" :clearable="true" />
            </template>
              <template #default="innerScope">
                <el-input
                  v-model="innerScope.row[`subject_${subject.id}`]"
                  :required="true"
                  :placeholder="`Enter Marks for ${subject.title} (${subject.id})`"
                  clearable
                />
              </template>
            </el-table-column>
          </el-table-column>
        </el-table>

      </el-form>
  </el-dialog>
  </div>
 
</template>