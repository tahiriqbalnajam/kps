<template>
  <div class="app-container">
    <div class="form-header">
      <h2>Add Income</h2>
      <p>Record a new income transaction</p>
    </div>

    <el-card class="form-container">
      <el-form
        :model="form"
        :rules="rules"
        ref="formRef"
        label-width="150px"
        size="large"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Category" prop="category">
              <el-input
                v-model="form.category"
                placeholder="e.g., School Fees, Donations, Grants"
              >
                <template #prepend>
                  <el-icon><Folder /></el-icon>
                </template>
              </el-input>
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="Amount (Rs.)" prop="amount">
              <el-input
                v-model="form.amount"
                placeholder="0.00"
                type="number"
                step="0.01"
                min="0"
              >
                <template #prepend>
                  <span>Rs.</span>
                </template>
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="Description" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            placeholder="Enter detailed description of the income source"
            :rows="4"
            show-word-limit
            maxlength="1000"
          />
        </el-form-item>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Date" prop="date">
              <el-date-picker
                v-model="form.date"
                type="date"
                placeholder="Select date"
                style="width: 100%"
                :disabled-date="disabledDate"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="Payment Method" prop="payment_method">
              <el-select v-model="form.payment_method" placeholder="Select payment method" style="width: 100%">
                <el-option label="Cash" value="cash">
                  <el-icon><Money /></el-icon>
                  <span style="margin-left: 8px">Cash</span>
                </el-option>
                <el-option label="Bank Transfer" value="bank">
                  <el-icon><CreditCard /></el-icon>
                  <span style="margin-left: 8px">Bank Transfer</span>
                </el-option>
                <el-option label="Cheque" value="cheque">
                  <el-icon><Document /></el-icon>
                  <span style="margin-left: 8px">Cheque</span>
                </el-option>
                <el-option label="Online Payment" value="online">
                  <el-icon><Monitor /></el-icon>
                  <span style="margin-left: 8px">Online Payment</span>
                </el-option>
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="Reference Number" prop="reference_number">
          <el-input
            v-model="form.reference_number"
            placeholder="Receipt number, transaction ID, etc. (optional)"
          >
            <template #prepend>
              <el-icon><DocumentCopy /></el-icon>
            </template>
          </el-input>
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="submitForm" :loading="submitting" size="large">
            <el-icon><Check /></el-icon>
            Record Income
          </el-button>
          <el-button @click="resetForm" size="large">
            <el-icon><RefreshLeft /></el-icon>
            Reset
          </el-button>
          <el-button @click="$router.push('/finance/accounting/transactions')" size="large">
            <el-icon><List /></el-icon>
            View All Transactions
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- Quick Category Suggestions -->
    <el-card class="suggestions-container" header="Common Income Categories">
      <div class="category-suggestions">
        <el-tag
          v-for="category in incomeCategories"
          :key="category"
          @click="selectCategory(category)"
          class="category-tag"
          type="success"
        >
          {{ category }}
        </el-tag>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import { useRouter } from 'vue-router';
import AccountingResource from '@/api/accounting';
import {
  Folder,
  Money,
  CreditCard,
  Document,
  Monitor,
  DocumentCopy,
  Check,
  RefreshLeft,
  List
} from '@element-plus/icons-vue';

const router = useRouter();
const accountingRes = new AccountingResource();

const formRef = ref(null);
const submitting = ref(false);

const form = ref({
  type: 'income',
  category: '',
  amount: '',
  description: '',
  date: new Date(),
  payment_method: '',
  reference_number: ''
});

const rules = {
  category: [
    { required: true, message: 'Please enter income category', trigger: 'blur' },
    { min: 2, max: 255, message: 'Category should be 2-255 characters', trigger: 'blur' }
  ],
  amount: [
    { required: true, message: 'Please enter amount', trigger: 'blur' },
    { pattern: /^\d+(\.\d{1,2})?$/, message: 'Please enter valid amount (e.g., 1000.50)', trigger: 'blur' }
  ],
  description: [
    { required: true, message: 'Please enter description', trigger: 'blur' },
    { min: 5, max: 1000, message: 'Description should be 5-1000 characters', trigger: 'blur' }
  ],
  date: [
    { required: true, message: 'Please select date', trigger: 'change' }
  ],
  payment_method: [
    { required: true, message: 'Please select payment method', trigger: 'change' }
  ]
};

const incomeCategories = ref([
  'School Fees',
  'Admission Fees',
  'Exam Fees',
  'Library Fees',
  'Transportation Fees',
  'Activity Fees',
  'Donations',
  'Grants',
  'Government Funding',
  'Sponsorship',
  'Book Sales',
  'Uniform Sales',
  'Event Revenue',
  'Interest Income',
  'Other Income'
]);

const disabledDate = (date) => {
  // Disable future dates
  return date > new Date();
};

const selectCategory = (category) => {
  form.value.category = category;
};

const submitForm = async () => {
  if (!formRef.value) return;
  
  const valid = await formRef.value.validate().catch(() => false);
  if (!valid) return;
  
  submitting.value = true;
  try {
    const formData = { ...form.value };
    formData.date = formData.date.toISOString().split('T')[0];
    formData.amount = parseFloat(formData.amount);
    
    await accountingRes.store(formData);
    
    ElMessage.success('Income recorded successfully!');
    
    // Ask if user wants to add another income or view transactions
    const action = await ElMessageBox.confirm(
      'Income has been recorded successfully. What would you like to do next?',
      'Success',
      {
        confirmButtonText: 'Add Another Income',
        cancelButtonText: 'View Transactions',
        distinguishCancelAndClose: true,
        type: 'success'
      }
    ).catch((action) => action);
    
    if (action === 'confirm') {
      resetForm();
    } else {
      router.push('/finance/accounting/transactions');
    }
  } catch (error) {
    ElMessage.error('Failed to record income. Please try again.');
    console.error(error);
  } finally {
    submitting.value = false;
  }
};

const resetForm = () => {
  if (formRef.value) {
    formRef.value.resetFields();
  }
  form.value = {
    type: 'income',
    category: '',
    amount: '',
    description: '',
    date: new Date(),
    payment_method: '',
    reference_number: ''
  };
};

onMounted(() => {
  // Load recent categories for suggestions
  accountingRes.getCategories('income').then(response => {
    const recentCategories = response.data.slice(0, 10);
    incomeCategories.value = [...new Set([...incomeCategories.value, ...recentCategories])];
  }).catch(() => {
    // Use default categories if API fails
  });
});
</script>

<style lang="scss" scoped>
.app-container {
  padding: 20px;
  max-width: 800px;
  margin: 0 auto;
}

.form-header {
  text-align: center;
  margin-bottom: 30px;
  
  h2 {
    color: #67c23a;
    margin-bottom: 8px;
    font-size: 28px;
  }
  
  p {
    color: #606266;
    margin: 0;
    font-size: 16px;
  }
}

.form-container {
  margin-bottom: 30px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
  
  .el-form-item {
    margin-bottom: 25px;
  }
  
  .el-input, .el-select, .el-date-picker {
    border-radius: 6px;
  }
  
  .el-textarea {
    border-radius: 6px;
  }
}

.suggestions-container {
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
  
  .category-suggestions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    
    .category-tag {
      cursor: pointer;
      transition: all 0.2s;
      
      &:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(103, 194, 58, 0.3);
      }
    }
  }
}

.el-button {
  border-radius: 6px;
  padding: 12px 20px;
  font-weight: 500;
}

@media (max-width: 768px) {
  .app-container {
    padding: 10px;
  }
  
  .el-col {
    margin-bottom: 10px;
  }
}
</style>
