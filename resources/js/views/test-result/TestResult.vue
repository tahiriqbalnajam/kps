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
  class_id: '',
  students: '',
})

const query = reactive({
  page: 1,
  limit: 15,
  keyword: '',
  filtercol: 'name',
  stdclass: '',
})
const onSubmit = () => {
    resource.store(formInline);
    getTestResultName();
}

const getTestResultName = async() => {
    const{ data } = await classes.list();
    formInline.classes = data.classes.data;
  }

const getClasses = async() => {
    const{ data } = await classes.list();
    formInline.classes = data.classes.data;
  }
const getstudents = async() => {
  const { data } = await students.list(formInline);
  formInline.students = data.students.data;
  console.log(formInline.students);
}
onMounted(() => {
  getClasses();

});



</script>
<template>
  <el-form :inline="true" :model="formInline" class="demo-form-inline">
    <el-form-item label="Exam Name">
      <el-input v-model="formInline.examname" placeholder="Exam Name" clearable />
    </el-form-item>
    <el-form-item label="Class Name">
      <el-select v-model="formInline.class_id" placeholder="Select Class" clearable @change="getstudents()" >
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
          <el-input v-model="scope.row.obtained_marks" placeholder="Exam Name" clearable />
        </template>
      </el-table-column>
    </el-table>

  </el-form>

 
</template>