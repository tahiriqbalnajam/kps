<template>
  <div class="app-container">
     <div class="filter-container">
         <head-controls>
          <el-alert :title="alertTitle" type="success" v-if="formInline.alertRec"></el-alert>
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
      <el-table :data="formInline.resource" height="600" style="width: 100%" empty-text="Not Generated">
        <el-table-column prop="name" label="Teacher"  />
        <el-table-column prop="pay" label="Total Pay"  />
        <el-table-column prop="month" label="Pay/ Day">
          <template #default="scope">
            {{ scope.row.pay ? ( Math.round(scope.row.pay/30)) : '' }}
          </template>
        </el-table-column>
        <el-table-column prop="allowed_holidays" label="Allowed Holidays"  />
        <el-table-column prop="absent" label="Absent Days"  />
        <el-table-column prop="leaves" label="Leave Days"  />
        <el-table-column prop="working" label="Working Days" />
        <el-table-column prop="estimated_pay" label="Estimated Pay">
          <template #default="scope">
            {{ scope.row.estimated_pay ? (Math.round( scope.row.estimated_pay)) : '' }}
          </template>
        </el-table-column>
        <el-table-column align="right" fixed="right">
          <template #default="scope">
            <el-tooltip content="Pay Salary" placement="top">
              <el-button type="success" @click="payFee(scope.row.id)">
                  <el-icon><Coin /></el-icon>
                </el-button>
            </el-tooltip>
          </template>
        </el-table-column>

      </el-table>
    </el-card>
    <el-drawer
      title="Pay Salary"
      :modelValue="formInline.paysalary"
      direction="rtl"
      custom-class="demo-drawer"
      ref="drawer"
    >
      <div class="demo-drawer__content">
        <el-form :model="model">
          <el-form-item label="Amount" :label-width="formLabelWidth">
            <el-input-number v-model="model.amount" :min="1" :max="10000" />
          </el-form-item>
        </el-form>
        <div class="demo-drawer__footer">
          <el-button @click="paysalary = false">Cancel</el-button>
          <el-button type="primary" @click="onSubmit" :loading="loading">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
        </div>
      </div>
    </el-drawer>
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

      }
    }
  },
  created(){
    this.getPay();
  },
  methods: {
      async getPay(){
        const {data} = allTeachersPay();
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

