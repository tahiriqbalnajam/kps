<script setup>
  import HeadControls from '@/components/HeadControls.vue';
  import AddExam from '@/views/exam/AddExam.vue';
  import Pagination from '@/components/Pagination/index.vue';
  import { ElNotification, ElMessage, ElMessageBox } from 'element-plus'
  import { onMounted, ref } from "vue";
  import { reactive } from 'vue';
  import moment from 'moment';
  import Resource from '@/api/resource.js';
  const resource = new Resource('exams');
  const dialogFormVisible = ref(false);
  const dialogEditFormVisible = ref(false);

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
    updateexamresult: '',
  })

  const updateexams= reactive({
    filtercol: '',
    examname: '',
  })

  const rdata = reactive({
    addedittestprop: false,
    result_students: '',
    result_examname: '',
    result_classname: '',
    total: 0,
    listloading: false,
    result_id: '',
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
    rdata.listloading = true;
    const { data } = await resource.list(query);
    rdata.listloading = false;
    formInline.resource = data.exams.data;
    rdata.total = data.exams.total;
   //console.log(formInline.resource);
  }
 
  const updateExamResult= async() =>{
    rdata.result_examname
   // console.log(rdata.result_students)
   updateexams.filtercol = 'update_result';
    rdata.result_students.forEach(element => {
      elementsave.ids = element.id;
  //    elementsave.exam_ids = element.exam_id;
  //    elementsave.student_ids = element.student_id;
  //    elementsave.class_ids = element.class_id;
   //   elementsave.total_markss = element.total_marks;
    //  elementsave.obtained_markss = element.obtained_marks;

    //console.log(elementsave);
    //resource.destroy(elementsave); 
    updateexams.id = element.id;
    updateexams.obtained_marks = element.obtained_marks;
    resource.update(element.id, updateexams);
   });

    updateexams.filtercol = 'update_exams';
    updateexams.id = rdata.result_id;
    updateexams.examname = rdata.result_examname;
    resource.update(updateexams.id, updateexams);

//resource.destroy(rdata.result_students);

    //console.log(rdata.result_students);
   // rdata.result_students.forEach(element => {
   //   const exam_id = element.exam_id
   // });
    //const { data } = await resource.update(element.exam_id, 'obtained_marks='+element.obtained_marks);
    //formInline.updateexamresult = data.resource.data;
    //const { data } = await resource.update('41', 'obtained_marks=900');
    //formInline.updateexamresult = data.resource.data;
    get_Exams();

    ElNotification({
      title: 'Success',
      message: 'Record Has Been Updated',
      type: 'success',
    })
  }

  const deleteExam = async(examsid) => {

    ElMessageBox.confirm(
      'Do you want permanently delete the exam. Continue?',
      'Warning',
      {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning',
      }
    ).then(() => {
      get_Exams();
      const examid = examsid;
      resource.destroy(examid);
      get_Exams();
      ElMessage({
        type: 'success',
        message: 'Delete completed',
      })
    })
    .catch(() => {
    })
  }
  

  const getResultClaswise = async(examsid, testname) => {
    const result = formInline.resource.filter(item => item.id == examsid);
    //console.log(result);
    rdata.result_students = result[0].results;
    rdata.result_examname = result[0].examname;
    rdata.result_id = result[0].id;
    rdata.result_classname = "class: " + result[0].classes.name;
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
        <el-form-item v-loading="listloading">
          <el-col :span="4">
            <el-select v-model="formInline.exam" placeholder="Select Exam" class="filter-item" clearable>
              <el-option
                  v-for="item in formInline.resource"
                  :key="item.id"
                  :label="item.examname "
                  :value="item.id"
                />
            </el-select>
          </el-col>
          <el-col :span="2">
            <el-tooltip content="Add New Exam" placement="top">
              <el-button class="filter-item" style="margin-left: 10px;" type="success" @click="openPopup">
                <el-icon><Plus /></el-icon>
              </el-button>
            </el-tooltip>
          </el-col>
        </el-form-item>
      </head-controls>
    </div>
    <el-card class="box-card">
      <testing />
      <el-table :data="formInline.resource" height="600" style="width: 100%">
        <el-table-column prop="examname" label="Exam"  />
        <el-table-column prop="classes.name" label="Class"  />
        <el-table-column prop="total_marks" label="Total Marks"  />
        <el-table-column prop="created_at" label="Date">
          <template #default="scope">
            {{moment(scope.row.created_at).format('DD/MM/YYYY')}}
          </template>
        </el-table-column>
        <el-table-column>
          <template #default="scope">
            <el-button-group>
              <el-tooltip content="Class Wise" placement="top">
                <el-button color="#626aef" :dark="isDark" @click="[getResultClaswise(scope.row.id, scope.row.examname),dialogFormVisible = true]">
                  <el-icon><ScaleToOriginal /></el-icon>
                </el-button>
              </el-tooltip>

              <el-tooltip content="Student Wise" placement="top">
                <el-button color="#626aef" :dark="isDark" >
                  <el-icon><UserFilled /></el-icon>
                </el-button>
              </el-tooltip>

              <el-tooltip content="Edit Exam" placement="top">
                <el-button type="primary" @click="[getResultClaswise(scope.row.id, scope.row.examname),dialogEditFormVisible = true]">
                  <el-icon><Edit /></el-icon>
                </el-button>
              </el-tooltip>

              <el-tooltip content="Delete Exam" placement="top">
                <el-button type="danger" @click="[deleteExam(scope.row.id)]">
                  <el-icon><Delete /></el-icon>
                </el-button>
              </el-tooltip>

            </el-button-group>
          </template>
        </el-table-column>
      </el-table>
    </el-card>

  <el-dialog v-model="dialogFormVisible" v-model:title="rdata.result_classname">
    <el-form :model="form">
      <el-form label-position="left"  style="max-width: 300px;">
          <el-form-item label="Exam Name:">
            <el-input readonly v-model="rdata.result_examname" />
          </el-form-item>
        </el-form>
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
          Print
        </el-button>
      </span>
    </template>
  </el-dialog>

  <el-dialog v-model="dialogEditFormVisible" v-model:title="rdata.result_classname">
    <el-form :model="form">
      <el-form label-position="left"  style="max-width: 300px;">
          <el-form-item label="Exam Name" prop="result_examname">
            <el-input v-model="rdata.result_examname" />
          </el-form-item>
        </el-form>
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
        <el-button type="primary" @click="[updateExamResult(),dialogEditFormVisible = false]">
          Update
        </el-button>
      </span>
    </template>
  </el-dialog>


  <pagination v-show="rdata.total>0" :total="rdata.total" :page.sync="query.page" :limit.sync="query.limit" @pagination="get_Exams" />
    <add-exam :addedittestprop="rdata.addedittestprop"  @popupclosed="popupClosed"/>
  </div>
</template>


<style  scoped>
.rdata_result_examname {
    /* border: none !important; */
    box-shadow: none;
}
</style>

