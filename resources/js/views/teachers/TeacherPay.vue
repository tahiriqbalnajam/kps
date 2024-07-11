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
                    @change="getPay()"
                />
              </el-col>
              <el-col :span="4">
                <el-tooltip :content="tooltipContent" placement="top">
                  <el-button type="primary" @click="generate_pay()">
                    <el-icon><Money /></el-icon>
                  </el-button>
                </el-tooltip>
              </el-col>
          </el-form-item>
         </head-controls>
     </div>
     <el-card class="box-card">
      <el-table size="small" :data="teachers" height="600" style="width: 100%;" max-height="550" empty-text="Not Generated">
        <el-table-column prop="name" label="Teacher"  />
        <el-table-column prop="total_days_month" label="Total Days"  />
        <el-table-column prop="working_days" label="Working Days" />
        <el-table-column prop="attende_days" label="Present Days"  />
        <el-table-column prop="absent_days" label="Absent Days"  />
        <el-table-column prop="payable_days" label="Payable Days"  />
        <el-table-column prop="daily_salary" label="Daily Salary" />
        <el-table-column prop="total_pay" label="Estimated Pay">
          <template #default="scope">
            <el-tag type="success">{{ scope.row.total_pay }}</el-tag>
          </template>
        </el-table-column>
      </el-table>
    </el-card>
 </div>
</template>
<script>
import HeadControls from '@/components/HeadControls.vue';
import { allTeachersPay } from '@/api/teacher';

export default {
  name:'TeachersPay',
  components:{HeadControls},
  data() {
    return {
      teachers:{},
      query:{
        month: new Date().toISOString().substr(0, 7),

      }
    }
  },
  created(){
    this.getPay();
  },
  methods: {
      async getPay(){
        const { data } = await allTeachersPay();
        this.teachers = data.pay
      }
  }
}
</script>


<style  scoped>
.rdata_result_examname {
    /* border: none !important; */
    box-shadow: none;
}
</style>

