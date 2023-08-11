<script setup>
import HeadControls from '@/components/HeadControls.vue';
  import AddTest from '@/views/exam/AddTest.vue';
  import { onMounted, ref } from "vue";
  import { reactive } from 'vue';
  import Resource from '@/api/resource.js';
  const classes = new Resource('classes');
  const resource = new Resource('exam_result');
  const students = new Resource('students');

  const formInline = reactive({
    examname: '',
    classes: '',
    stdclass: '',
    resource: '',
  })

  const addedittestprop = ref(false);

  const exam_result_students = async() => {
    const { data } = await resource.list();
    formInline.resource = data.resource.data;
    console.log(formInline);
  }


  const  closeAddTest= () => {
    alert('asdfs')
    addedittestprop= ref(false);
  }


  onMounted(() => {
    
    exam_result_students();
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
            <el-button class="filter-item" style="margin-left: 10px;" type="success" @click="addedittestprop = true">
              Add Test
            </el-button>
          </el-col>
        </el-form-item>
      </head-controls>
    </div>
    <el-card class="box-card">
      <el-table :data="formInline.resource" style="width: 100%">
        <el-table-column prop="id" label="ID" width="180" />
        <el-table-column prop="examname" label="Exam Name" width="180" />
        <el-table-column prop="total_marks" label="Total Marks" width="180" >
        </el-table-column>
      </el-table>
    </el-card>
    <add-test :addedittestprop="addedittestprop"  @closeAddTest="closeAddTest"/>
  </div>
</template>
<style  scoped>
</style>

