<script setup>
import HeadControls from '@/components/HeadControls.vue';
  import AddTest from '@/views/exam/AddTest.vue';
  import { onMounted, ref } from "vue";
  import { reactive } from 'vue';
  import Resource from '@/api/resource.js';
  const classes = new Resource('classes');
  const resource = new Resource('exam_result');
  const students = new Resource('students');


  const getexamsstudents = new Resource('exam_result');

  const formInline = reactive({
    examname: '',
    classes: '',
    stdclass: '',
    resource: '',
    getexamsstudents: '',
  })

  const rdata = reactive({
    addedittestprop: false,
  })


  const query = reactive({
    page: 1,
    limit: 15,
    keyword: '',
    filtercol: 'examname',
    stdclass: '',
  })

  const query2 = reactive({
    page: 1,
    limit: 15,
    keyword: '',
    filtercol: 'student',
    stdclass: '',
  })

  const exam_result_students = async() => {
    const { data } = await resource.list(query);
    formInline.resource = data.resource.data;
    console.log(formInline);
  }

  const getexamsstudentsfun = async() => {
    const { data } = await getexamsstudents.list(query2);
    formInline.getexamsstudents = data.getexamsstudents.data;
  }
  
  const openPopup = () => {
    console.log('pop called');
    rdata.addedittestprop = true
  }

  const popupClosed = () => {
    console.log('pop closed');
    rdata.addedittestprop = false
  }

  onMounted(() => {
    getexamsstudentsfun();
    //exam_result_students();
  });


</script>
<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-form-item>
          <el-col :span="4">
            <el-select v-model="formInline.testall" placeholder="Select Class" class="filter-item" clearable>
              <el-option
                  v-for="item in formInline.resource"
                  :key="item.id"
                  :label="item.examname "
                  :value="item.id"
                />
            </el-select>
          </el-col>
          <el-col :span="2">
            <el-button class="filter-item" style="margin-left: 10px;" type="success" @click="openPopup">
              Add Test
            </el-button>
          </el-col>
        </el-form-item>
      </head-controls>
    </div>
    <el-card class="box-card">
      <el-table :data="formInline.resource" style="width: 100%">
        <el-table-column prop="id" label="ID"  />
        <el-table-column prop="examname" label="Exam Name"  />
        <el-table-column prop="total_marks" label="Total Marks"  />

        <el-table-column>
            <el-button-group>
              <el-button type="primary" :icon="Edit">Class Wise</el-button>
              <el-button type="primary" :icon="Share">Student Wise</el-button>
            </el-button-group>
        </el-table-column>
      </el-table>
    </el-card>
    <add-test :addedittestprop="rdata.addedittestprop"  @popupclosed="popupClosed"/>
  </div>
</template>
<style  scoped>
</style>

