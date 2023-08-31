<script setup>
  import { reactive, onMounted, computed, ref } from 'vue';
  import moment from 'moment';
  import Resource from '@/api/resource.js';
  import HeadControls from '@/components/HeadControls.vue';
  const  attendence = new Resource('teacher_attendance');
  import { checkSalaryGenerated } from '@/api/teacher';
  import { generatePay } from '@/api/teacher';
  import { PaySalary } from '@/api/teacher';
  
  const formInline = reactive({
    resource: '',
    teacher_select: '',
    teacherr: '',
    estimated_pay: '',
    type: '',
    has_generated:'',
    alertRec: false,
    showtitle: false,
    paysalaryalert:false,
    paysalary:false,
    loading: false,
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
  const handleDateChange = async() => {
    get_list();  
    const content = tooltipContent.value;
    };
  const query2 = reactive({
    month: moment().format("YYYY-MM-DD"),
    type: '',
    resource: '',
  })

  const get_list  = async() => {
    query.type = 'teachers_salarygenerated';
    const { data } = await attendence.list(query);
    formInline.resource = data.teacherwithsalary;
      for (let i = 0; i < formInline.resource.length; i++) {
        const row = formInline.resource[i];
        row.allowed_holidays = data.setting.teacher_leaves_allowed;
      }
    formInline.has_generated = data.has_generated
  }
  const generate_pay = async() => {
      const { data } = await generatePay(query);
      get_list();
      formInline.showtitle = (data.has_generated === "Yes")?true:false;
      const title = alertTitle.value;
      formInline.alertRec = true;
      formInline.paysalaryalert = false;
      hideAlertAfter(3000);
    };
    const hideAlertAfter = (milliseconds) => {
      setTimeout(() => {
        formInline.alertRec = false;
      }, milliseconds);
    };
    const model = reactive({
      user_id: '',
      amount: 0,
    });
    const payFee = (id) => {
      model.user_id = id;
      formInline.paysalary = true;
      console.log(model.user_id);
    };
    const onSubmit = async() => {
      const { data } = await PaySalary(model);
      formInline.showtitle = (data.has_generated === "Yes")?true:false;
      const title = alertTitle.value;
      formInline.alertRec = true;
      formInline.paysalaryalert = true;
      formInline.paysalary = false;
    }

  onMounted(() => {
    get_list();
  });
  const tooltipContent = computed(() => {
    return formInline.has_generated === 'Yes' ? 'Regenerate Salaries' : 'Generate Salaries';
  });
  const alertTitle = computed(() => {
    if (formInline.paysalaryalert) {
      return formInline.showtitle ? 'Salary Paid' : 'Salary Not Paid';
    } else {
      return formInline.showtitle ? 'Salary Generated' : 'Salary Not Generated';
    }
  });


</script>
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
                         @change="handleDateChange"
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


<style  scoped>
.rdata_result_examname {
    /* border: none !important; */
    box-shadow: none;
}
</style>

