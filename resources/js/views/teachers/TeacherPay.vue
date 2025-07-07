<template>
  <div class="app-container">
     <div class="filter-container">
         <head-controls>
          <el-form-item label="Select Month">
              <el-col :span="4" :sm="6">
                <el-date-picker
                    v-model="query.month"
                    type="month"
                    format="MMM"
                    value-format="YYYY-MM-DD"
                    placeholder="Pick a month"
                    :disabled-date="disabledDate" 
                    @change="getPay()"
                />
              </el-col>
              <el-col :span="4">
                <el-tooltip content="Click to save Salaries" placement="top">
                  <el-button type="primary" @click="savePay()">
                    <el-icon><Money /></el-icon> Save Salaries
                  </el-button>
                </el-tooltip>
              </el-col>
          </el-form-item>
         </head-controls>
     </div>
     <el-card class="box-card">
      <el-table size="small" :data="teachers" style="width: 100%;" max-height="550" empty-text="Not Generated" show-summary :summary-method="getSummaries">
        <el-table-column prop="name" label="Teacher"  />
        <el-table-column prop="salary" label="Salary"  />
        <el-table-column prop="working_days" label="Working Days">
           <template #default="scope">
            <el-input v-model="scope.row.working_days" placeholder="" size="normal" clearable @change="updateDaysFromWorking(scope.row)" />
          </template>
        </el-table-column>
        <el-table-column prop="present_days" label="Present Days">
           <template #default="scope">
            <el-input v-model="scope.row.present_days" placeholder="" size="normal" clearable @change="updateDaysFromPresent(scope.row)" />
          </template>
        </el-table-column>
        <el-table-column prop="absent_days" label="Absent Days">
           <template #default="scope">
            <el-input v-model="scope.row.absent_days" placeholder="" size="normal" clearable @change="updateDaysFromAbsent(scope.row)" />
          </template>
        </el-table-column>
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
          <el-input v-model="scope.row.fine" placeholder="" size="normal" clearable @change="calc_salary()" />
        </template>
      </el-table-column>
      <el-table-column label="Bonus">
        <template #default="scope">
          <el-input v-model="scope.row.bonus" placeholder="" size="normal" clearable @change="calc_salary()"></el-input>
        </template>
      </el-table-column>
      <el-table-column label="Paid">
        <template #default="scope">
          <el-input v-model="scope.row.paid" placeholder="" size="normal" clearable @change="calc_salary()"></el-input>
        </template>
      </el-table-column>
      <el-table-column label="Previous Balance">
        <template #default="scope">
         {{ scope.row.previous_balance }}
        </template>
      </el-table-column>
      <el-table-column label="Balance">
        <template #default="scope">
          {{ scope.row.balance = total_pay(scope.row) }}
        </template>
      </el-table-column>

      </el-table>
    </el-card>
 </div>
</template>
<script>
import HeadControls from '@/components/HeadControls.vue';
import { allTeachersPay, saveSalary, findSavedPay } from '@/api/teacher';

export default {
  name:'TeachersPay',
  components:{HeadControls},
  data() {
    return {
      teachers:{},
      query:{
        month: new Date().toISOString().substr(0, 7) + '-01',

      }
    }
  },
  created(){
    this.getPay();
  },
  methods: {
    disabledDate(time) {
      return time.getTime() > Date.now()
    },
    updateDaysFromWorking(row) {
      // When working days changes, update absent days based on present days
      const workingDays = parseInt(row.working_days) || 0;
      const presentDays = parseInt(row.present_days) || 0;
      
      // Ensure present days doesn't exceed working days
      row.present_days = Math.min(presentDays, workingDays);
      
      // Calculate absent days
      row.absent_days = workingDays - parseInt(row.present_days);
      
      this.calc_salary();
    },
    
    updateDaysFromPresent(row) {
      // When present days changes, update absent days
      const workingDays = parseInt(row.working_days) || 0;
      const presentDays = parseInt(row.present_days) || 0;
      
      // Ensure present days doesn't exceed working days
      row.present_days = Math.min(presentDays, workingDays);
      
      // Calculate absent days
      row.absent_days = workingDays - parseInt(row.present_days);
      
      this.calc_salary();
    },
    
    updateDaysFromAbsent(row) {
      // When absent days changes, update present days
      const workingDays = parseInt(row.working_days) || 0;
      const absentDays = parseInt(row.absent_days) || 0;
      
      // Ensure absent days doesn't exceed working days
      row.absent_days = Math.min(absentDays, workingDays);
      
      // Calculate present days
      row.present_days = workingDays - parseInt(row.absent_days);
      
      this.calc_salary();
    },
    calc_salary() {
      this.teachers.forEach(teacher => {
        // Convert to integers to ensure proper calculation
        const workingDays = parseInt(teacher.working_days) || 0;
        const absentDays = parseInt(teacher.absent_days) || 0;
        const allowLeaves = parseInt(teacher.allow_leaves) || 0;
        const dailySalary = parseFloat(teacher.daily_salary) || 0;
        
        // Calculate payable days
        const payableDays = workingDays - Math.max(0, absentDays - allowLeaves);
        teacher.payable_days = payableDays;
        
        // Calculate total pay
        teacher.total_pay = Math.round(dailySalary * payableDays);
      });
    },
    total_pay(row){
      const total_pay = parseInt(row.total_pay) || 0;
      const fine = parseInt(row.fine) || 0;
      const bonus = parseInt(row.bonus) || 0;
      const paid = parseInt(row.paid) || 0;
      const previous_balance = parseInt(row.previous_balance) || 0;
      
      // Calculate balance according to the formula
      return total_pay - fine + bonus - paid + previous_balance;
    },
    async savePay() {
      const { data } = await findSavedPay(this.query);
      if(data.salaries) {
        this.$message({
          type: 'error',
          message: 'Salaries are already saved for this month'
        });
        return;
      }

      this.$confirm('Do you really want to save? This action cannot be undone', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
    }).then(async () => {
        await saveSalary({'salaries' : this.teachers});
        this.$message({
          type: 'success',
          message: 'Salaries saved successfully'
        });
      })
    },  
    async getPay(){
      const { data } = await allTeachersPay(this.query);
      this.teachers = data.pay.map((item) => ({
            ...item,
            fine: item.fine || '0', // Set default value if none exists
            bonus: item.bonus || '0', // Set default value if none exists
            paid: item.paid || '0', // Set default value if none exists
          }));
    },
    getSummaries(param) {
      const { columns, data } = param
      const sums = []
      columns.forEach((column, index) => {
        if (index === 1) {
          const values = data.reduce((total, item) => total + Number(item[column.property]), 0);
          sums[index] = h('div', { style: { fontWeight: 'bold' } }, [
            '',
            values
          ])
          return
        }
        else if (index === 8 ||  index === 13) {
          const values = data.reduce((total, item) => total + Number(item[column.property]), 0);
          sums[index] = h('div', { style: { fontWeight: 'bold' } }, [
            values,
          ])
          return;
        } else if ( index === 13) {
          console.log(item);
          const values = data.reduce((total, item) => total + Number(item[column.property]), 0);
          sums[index] = h('div', { style: { fontWeight: 'bold' } }, [
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

