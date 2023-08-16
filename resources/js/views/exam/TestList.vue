<script setup>
import HeadControls from '@/components/HeadControls.vue';
  import AddTest from '@/views/exam/AddTest.vue';
  import { onMounted, ref } from "vue";
  import { reactive } from 'vue';
  import Resource from '@/api/resource.js';
  const classes = new Resource('classes');
  const resource = new Resource('exams');
  const students = new Resource('students');

const dialogFormVisible = ref(false)


const form = reactive({
  name: '',
  region: '',
  date1: '',
  date2: '',
  delivery: false,
  type: [],
  resource: '',
  desc: '',
})



  const formInline = reactive({
    examname: '',
    classes: '',
    stdclass: '',
    resource: '',
    getexamsstudents: '',
    getstudentss:'',
    updateexamresult: '',

  })

  const rdata = reactive({
    addedittestprop: false,
    result_students: '',
    result_examname: '',
  })


  const query = reactive({
    page: 1,
    limit: 15,
    keyword: '',
    filtercol: 'exams',
    stdclass: '',
  })

  const query2 = reactive({
    page: 1,
    limit: 15,
    keyword: '',
    filtercol: 'student',
    stdclass: '',
  })


  const get_Exams = async() => {
    const { data } = await resource.list(query);
    formInline.resource = data.exams.data;
  }
 
  const updateExamResult= async() =>{
    console.log(rdata.result_students);
    const { data } = await resource.update(rdata.result_students.id, rdata.result_students.obtained_marks);
    formInline.updateexamresult = data.resource.data;
  }

  const getResultClaswise = async(examsid, testname) => {
    const result = formInline.resource.filter(item => item.id == examsid);
    //console.log(result);
    rdata.result_students = result[0].results;
    console.log(rdata.result_students);
    rdata.result_examname = testname;
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
    get_Exams();
  });


</script>
<template>
  <div class="app-container">
    <div class="filter-container">
      <head-controls>
        <el-form-item>
          <el-col :span="4">
            <el-select v-model="formInline.exam" placeholder="Select Class" class="filter-item" clearable>
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
        <el-table-column prop="examname" label="Exam"  />
        <el-table-column prop="classes.name" label="Class"  />
        <el-table-column prop="total_marks" label="Total Marks"  />
        <el-table-column prop="created_at" label="Date"  />
        <el-table-column>
          <template #default="scope">
            <el-button-group>
              <el-button type="primary"  :icon="Edit" @click="[getResultClaswise(scope.row.id, scope.row.examname),dialogFormVisible = true]">Class Wise</el-button>
              <el-button type="primary" :icon="Share">Student Wise</el-button>
            </el-button-group>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

  <el-dialog v-model="dialogFormVisible" title="">
    <el-form :model="form">
      <el-table :data="rdata.result_students" style="width: 100%">
        <el-table-column prop="student.name" label="Student"/>
        <el-table-column prop="total_marks" label="Total Marks"  />
        <el-table-column prop="obtained_marks" label="Obtain Marks">
          <template #default="scope">
              <el-input v-model="scope.row.obtained_marks" required placeholder="Obtained Marks" clearable />
            </template>
          </el-table-column>
      </el-table>
    </el-form>
    <template #footer>
      <span class="dialog-footer">
        <el-button @click="dialogFormVisible = false">Cancel</el-button>
        <el-button type="primary" @click="[updateExamResult(),dialogFormVisible = false]">
          Update
        </el-button>
      </span>
    </template>
  </el-dialog>
    <add-test :addedittestprop="rdata.addedittestprop"  @popupclosed="popupClosed"/>
  </div>
</template>
<style  scoped>
</style>

