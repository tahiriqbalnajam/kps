<script setup>
    import { reactive } from 'vue';
    import moment from 'moment';
    import Resource from '@/api/resource.js';
    import HeadControls from '@/components/HeadControls.vue';
    let attRes = new Resource('teacher_attendance');
    const $this =  reactive({
                query: {
                    teacher: '',
                    month: moment().format("YYYY-MM-DD"),
                },
                attendance: {
                    teachers: [],
                    date: moment(),
                },
            })
    const getList = async() => {
        const {data} = await attRes.list($this.query);
        $this.attendance.teachers = await data.attendace.map((teacher) => {
        let attend = [];
        for (let i = 0; i < 31; i++) {
          let found = teacher.attendance.find( att => {  
            const date = moment(att.attendance_date); // Thursday Feb 2015
            const dow = date.date();
            console.log(dow);
            return (dow-1) == i
          });
          if (!found){
            attend[i] = ({'id': i, 'teacher_id': teacher.id, 'status': '--'});
          } else {
            found.status = (found.status == 'absent') ? 'A' : (found.status == 'present') ? 'P' : 'L';
            attend[i] = found;
          }
        }
        return {
          ...teacher,
          attendance: attend,
        };
      });
    }
    getList();
</script>


<template>
     <div class="app-container">
        <div class="filter-container">
            <head-controls>
                <el-form-item label="Select Month">
                    <el-col :span="4">
                        <el-date-picker
                            v-model="$this.query.month"
                            type="month"
                            format="MMM"
                            value-format="YYYY-MM-DD"
                            placeholder="Pick a month" 
                        />
                    </el-col>
                </el-form-item>
            </head-controls>
        </div>
        <table class="tblwdborder">
            <tr>
                <th>Teacher Name</th>
                <th v-for="index in 31" :key="index">{{index}}</th>
            </tr>
            <tr v-for="teacher in  $this.attendance.teachers" :key="teacher.id">
                <td>{{ teacher.name }}</td>
                <td v-for="att in teacher.attendance" :key="att.id" :class="{'absent': (att.status == 'A')}">{{att.status}}</td>
            </tr>
        </table>
    </div>
</template>


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