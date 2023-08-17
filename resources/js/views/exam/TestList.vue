<script setup>
import HeadControls from '@/components/HeadControls.vue';
  import AddTest from '@/views/exam/AddTest.vue';
  import Pagination from '@/components/Pagination/index.vue';
  import { onMounted, ref } from "vue";
  import { reactive } from 'vue';
  import Resource from '@/api/resource.js';
  const classes = new Resource('classes');
  const resource = new Resource('exams');
  const students = new Resource('students');

const dialogFormVisible = ref(false)
const dialogEditFormVisible = ref(false)

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
    result_classname: '',
  })

  const query = reactive({
    page: 1,
    limit: 15,
    keyword: '',
    filtercol: 'exams',
    stdclass: '',
  })

  const elementsave = reactive({
    ids: '',
    exam_ids: '',
    student_ids: '',
    class_ids: '',
    total_markss: '',
    obtained_markss: '',
  })

  const get_Exams = async() => {
    const { data } = await resource.list(query);
    formInline.resource = data.exams.data;
  }
 
  const updateExamResult= async() =>{
    console.log(rdata.result_students)
    rdata.result_students.forEach(element => {
      elementsave.ids = element.id;
  //    elementsave.exam_ids = element.exam_id;
  //    elementsave.student_ids = element.student_id;
  //    elementsave.class_ids = element.class_id;
   //   elementsave.total_markss = element.total_marks;
    //  elementsave.obtained_markss = element.obtained_marks;

    //console.log(elementsave);
    //resource.destroy(elementsave); 
    resource.update(element.id, 'obtained_marks='+element.obtained_marks);

    get_Exams();
   });

//resource.destroy(rdata.result_students);

    //console.log(rdata.result_students);
   // rdata.result_students.forEach(element => {
   //   const exam_id = element.exam_id
   // });
    //const { data } = await resource.update(element.exam_id, 'obtained_marks='+element.obtained_marks);
    //formInline.updateexamresult = data.resource.data;
    //const { data } = await resource.update('41', 'obtained_marks=900');
    //formInline.updateexamresult = data.resource.data;
  }

  const getResultClaswise = async(examsid, testname) => {
    const result = formInline.resource.filter(item => item.id == examsid);
    //console.log(result);
    rdata.result_students = result[0].results;
    
    rdata.result_examname = result[0].examname;
    rdata.result_classname = result[0].classes.name;
    rdata.result_examname = "Exam Name:      "+rdata.result_examname+" Class: "+rdata.result_classname;

  }

  const openPopup = () => {
    console.log('pop called');
    rdata.addedittestprop = true
  }

  const popupClosed = () => {
    console.log('pop closed');
    rdata.addedittestprop = false
    get_Exams();
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
              <el-button type="primary" :icon="Edit" @click="[getResultClaswise(scope.row.id, scope.row.examname),dialogFormVisible = true]">Class Wise</el-button>
              <el-button type="primary" :icon="Share">Student Wise</el-button>
              <el-button type="primary" :icon="Share" @click="[getResultClaswise(scope.row.id, scope.row.examname),dialogEditFormVisible = true]">Edit</el-button>
            </el-button-group>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

  <el-dialog v-model="dialogFormVisible" v-model:title="rdata.result_examname">
    <el-form :model="form">
      <el-table :data="rdata.result_students" style="width: 100%">
        <el-table-column prop="student.name" label="Student"/>
        <el-table-column prop="total_marks" label="Total Marks"  />
        <el-table-column prop="obtained_marks" label="Obtain Marks" />
      </el-table>
    </el-form>
    <template #footer>
      <span class="dialog-footer">
        <el-button @click="dialogFormVisible = false">Cancel</el-button>
        <el-button type="primary" @click="dialogFormVisible = false">
          Update
        </el-button>
      </span>
    </template>
  </el-dialog>

  <el-dialog v-model="dialogEditFormVisible" v-model:title="rdata.result_examname">
    <el-form :model="form">
      <el-table :data="rdata.result_students" style="width: 100%">
        <el-table-column prop="student.name" label="Student"/>
        <el-table-column prop="total_marks" label="Total Marks"  />
        <el-table-column prop="obtained_marks" label="Obtain Marks" >
          <template #default="scope">
              <el-input v-model="scope.row.obtained_marks" required placeholder="Enter Marks" clearable />
            </template>
        </el-table-column>
      </el-table>
    </el-form>
    <template #footer>
      <span class="dialog-footer">
        <el-button @click="dialogEditFormVisible = false">Cancel</el-button>
        <el-button type="primary" @click="updateExamResult()">
          Update
        </el-button>
      </span>
    </template>
  </el-dialog>


  <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="get_Exams" />
    <add-test :addedittestprop="rdata.addedittestprop"  @popupclosed="popupClosed"/>
  </div>
</template>


<style  scoped>
.rdata_result_examname {
    /* border: none !important; */
    box-shadow: none;
}
</style>

