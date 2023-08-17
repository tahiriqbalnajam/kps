<script setup>
  import { onMounted } from "vue";
  import { reactive } from 'vue';
  import Resource from '@/api/resource.js';
  const classes = new Resource('classes');
  let resource = new Resource('exam_result');
  const students = new Resource('students');
  
  const formInline = reactive({
    examname: '',
    classes: '',
    stdclass: '',
    students: '',
  })

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
    exam.total_marks = formInline.total_marks;
    console.log(formInline.students);
    exam.students = formInline.students;
    resource.store(exam);
    handleClose();
  }

  const getClasses = async() => {
    const{ data } = await classes.list();
    formInline.classes = data.classes.data;
  }

  const getstudents = async() => {
    query.stdclass=formInline.stdclass
    const { data } = await students.list(query);
    formInline.students = data.students.data;
  }

  const emit = defineEmits(['popupclosed'])
  onMounted(() => {
    getClasses();
  });

  const handleClose = () => {
    console.log('popup going to close')
    emit('popupclosed')
  }

</script>
<template>

  <div >
    <el-dialog title="Add Test" :modelValue="addedittestprop" @close="handleClose">
      <el-form style="width: 100%" :inline="true" :model="formInline" class="demo-form-inline">
        <el-form-item label="Exam Name">
          <el-input v-model="formInline.examname" placeholder="Exam Name" clearable />
        </el-form-item>
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
        <el-form-item label="Totla Marks">
          <el-input-number v-model="formInline.total_marks" />
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="onSubmit" :disabled="!formInline.students.length">Save</el-button>
        </el-form-item>
        <el-table :data="formInline.students" style="width: 100%">
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