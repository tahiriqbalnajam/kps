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
      <el-table size="small" :data="teachers" style="width: 100%;" max-height="550" empty-text="Not Generated" show-summary :summary-method="getSummaries">
        <el-table-column prop="name" label="Teacher"  />
        <el-table-column prop="pay" label="Salary"  />
        <el-table-column prop="working_days" label="Working Days" />
        <el-table-column prop="attende_days" label="Present Days"  />
        <el-table-column prop="absent_days" label="Absent Days"  />
        <el-table-column prop="allow_leaves" label="Allow Leaves"  />
        <el-table-column prop="payable_days" label="Payable Days"  />
        <el-table-column prop="daily_salary" label="Daily Salary" />
        <el-table-column prop="total_pay" label="Estimated Salary">
          <template #default="scope">
            <el-tag type="success">{{ scope.row.total_pay }}</el-tag>
          </template>
        </el-table-column>
      <el-table-column label="Fine">
        <template #default="scope">
          <el-input v-model="scope.row.fine" placeholder="" size="normal" clearable @change="" />
        </template>
      </el-table-column>
      <el-table-column label="Bonus">
        <template #default="scope">
          <el-input v-model="scope.row.bonus" placeholder="" size="normal" clearable @change=""></el-input>
        </template>
      </el-table-column>
      <el-table-column label="Paid">
        <template #default="scope">
          <el-input v-model="scope.row.paid" placeholder="" size="normal" clearable @change=""></el-input>
        </template>
      </el-table-column>
      <el-table-column label="Previous Balance">
        <template #default="scope">
         {{ scope.row.previous_balance }}
        </template>
      </el-table-column>
      <el-table-column label="Balance">
        <template #default="scope">
          {{ total_pay(scope.row) }}
        </template>
      </el-table-column>
      >
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
    total_pay(row){
      const total_pay = (row.total_pay) ?? 0;
      const fine = (row.fine) ?? 0;
      const bonus = (row.bonus) ?? 0;
      const paid = (row.paid) ?? 0;
      const previous_balance = (row.previous_balance) ?? 0;
      return parseInt(total_pay) - parseInt(fine) + parseInt(bonus) - parseInt(paid) + parseInt(previous_balance);
    },
    async getPay(){
      const { data } = await allTeachersPay();
      this.teachers = data.pay
    },
    getSummaries(param) {
      const { columns, data } = param
      const sums = []
      columns.forEach((column, index) => {
        if (index === 1) {
          const values = data.reduce((total, item) => total + Number(item[column.property]), 0);
          sums[index] = h('div', { style: { fontSize: '18px' } }, [
            'Total Pay:',
            values
          ])
          return
        }
        else if (index === 2) {
          const values = data.reduce((total, item) => total + Number(item[column.property]), 0);
          sums[index] = h('div', { style: { fontSize: '18px' } }, [
            values,
          ])
          return;
        } else {
          sums[index] = ''
        }
      })

      return sums
    },

  }
}
</script>


<style  scoped>
.rdata_result_examname {
    /* border: none !important; */
    box-shadow: none;
}
</style>

