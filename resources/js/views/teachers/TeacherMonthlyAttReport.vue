<template>
  <div class="app-container">
     <div class="filter-container">
         <head-controls>
          <el-row>
            <el-col :span="12">
              <el-row :gutter="20">
                <el-col :span="12">
                  <el-form-item label="Select Month">
                    <el-date-picker
                        v-model="query.month"
                        type="month"
                        format="MMM"
                        value-format="YYYY-MM-DD"
                        placeholder="Pick a month" 
                    />
                  </el-form-item>
                </el-col>
                <el-col :span="12">
                  <el-button type="primary" :loading="loading"  @click="getList()">{{ loading ? 'Submitting ...' : 'Get report' }}</el-button>
                </el-col>
              </el-row>
            </el-col>
          </el-row>
         </head-controls>
     </div>
     <el-scrollbar height="300px">
       <table class="tblwdborder">
           <tr>
               <th>Teacher Name</th>
               <th v-for="index in 31" :key="index">{{index}}</th>
           </tr>
           <tr v-for="teacher in  attendance.teachers" :key="teacher.id">
               <td>{{ teacher.name }}</td>
               <td v-for="att in teacher.attendances" :key="att.id" :class="{'absent': (att == 'absent')}">
                {{(att == 'absent') ? 'A' : (att == leave) ? 'L' : 'P'}}

              </td>
           </tr>
       </table>
   </el-scrollbar>
 </div>
</template>

<script>
    import moment from 'moment';
    import Resource from '@/api/resource.js';
    import HeadControls from '@/components/HeadControls.vue';
    import { teacherMonthlyAttReport } from '@/api/attendance.js';
    let attRes = new Resource('teacher_attendance');
    export default {
      name: 'TeacherMonthlyReport',
      components: {  HeadControls  },
      directives: {  },
      data() {
        return {
          query: {
              teacher: '',
              month: moment().format("YYYY-MM-DD"),
          },
          attendance: {
              teachers: [],
              date: moment(),
          },
        };
      },
      created() {
        this.getList();
      },
      methods: {
        async getList(){
            const {data} = await teacherMonthlyAttReport(this.query);
            this.attendance.teachers = data.attendance;
          }
      },
    };
</script>
<style  scoped>
  .el-drawer__body {
    flex: 1;
    padding: 20px;
  }
  .demo-drawer__content {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 20px;
  }
  .tblwdborder {
  border-collapse: collapse;
  width: 100%;
}
.tblwdborder th {
  text-align: left;	
  border: 1px solid #0000001a;
  padding: 3px;
}
.tblwdborder tr td, .tblwdborder tr  th {
  border: 1px solid #0000001a;
  padding: 3px;
}
.tblwdborder tr:nth-child(odd) {
   background-color: #e1e0e061;
}
.absent {
  background: red;
  color:#fff;
}
</style>