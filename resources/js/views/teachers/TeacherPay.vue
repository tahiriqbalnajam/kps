<script setup>
  import { reactive } from 'vue';
  import { onMounted, ref } from "vue";
  import moment from 'moment';
  import Resource from '@/api/resource.js';
  import HeadControls from '@/components/HeadControls.vue';
  const  attendence = new Resource('teacher_attendance');
  const dialogEditFormVisible = ref(false);
 
  const formInline = reactive({
    resource: '',
    teacher_select: '',
    teacherr: '',
    estimated_pay: '',
    type: '',
  })

  const teacherInline = reactive({
    resource: '',
    type: '',
  })

  const query = reactive({
    month: moment().format("YYYY-MM-DD"),
    type: '',
    resource: '',
  })

  const query2 = reactive({
    month: moment().format("YYYY-MM-DD"),
    type: '',
    resource: '',
  })

  const get_list  = async() => {
    query.type = 'teachers_salarygenerated';
    const { data } = await attendence.list(query);
    formInline.resource = data.teacherwithsalary;
    formInline.resource = formInline.resource.filter(item => item.type == 'App\\Models\\Teacher');
    //const { data } = await attendence.list(query);
    //formInline.resource = data.attendace;
    //getteacher();
  }

  const generate_pay  = () => {
    
    query2.type = 'generatepay';
    console.log(teacherInline.resource);
    query2.resource = teacherInline.resource;
    console.log(query2);
    attendence.store(query2);
    get_list();
  }

  const getteacher  = async() => {
    query.type = 'getteachers';
    const { data } = await attendence.list(query);
    teacherInline.resource = data.teachers;
    //const teacherid = formInline.teacher_select;
    teacherInline.resource = teacherInline.resource.filter(item => item.type == 'App\\Models\\Teacher');
    
  }
  onMounted(() => {
    getteacher();
    get_list();
  });


</script>
<template>
  <div class="app-container">
     <div class="filter-container">
         <head-controls>
             <el-form-item label="Select Month">
                 <el-col :span="4">
                     <el-date-picker
                         v-model="query.month"
                         type="month"
                         format="MMM"
                         value-format="YYYY-MM-DD"
                         placeholder="Pick a month" 
                     />
                 </el-col>
                 <el-col :span="4">
                    <el-tooltip content="Generate Salary" placement="top">
                      <el-button type="primary" @click="generate_pay()">
                        <el-icon><Edit /></el-icon>
                      </el-button>
                  </el-tooltip>
                </el-col>
             </el-form-item>
         </head-controls>
     </div>
     <el-card class="box-card">
      <el-table :data="formInline.resource" height="600" style="width: 100%">
        <el-table-column prop="name" label="Teacher"  />
        <el-table-column prop="pay" label="Total Pay"  />
        <el-table-column prop="estimated_pay" label="Estimated Pay">
          <template #default="scope">
            {{ scope.row.estimated_pay ? (Math.round( scope.row.estimated_pay)) : '' }}
          </template>

        </el-table-column>

      </el-table>
    </el-card>

 </div>
</template>


<style  scoped>
.rdata_result_examname {
    /* border: none !important; */
    box-shadow: none;
}
</style>

