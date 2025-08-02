<template>
  <div class="app-container">
    <div class="form-header">
      <h2>Add Expense</h2>
      <p>Record a new expense transaction for the school</p>
    </div>

    <el-card class="form-card">
      <el-form
        :model="form"
        :rules="rules"
        ref="formRef"
        label-width="150px"
        class="expense-form"
      >
        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Category" prop="category">
              <el-autocomplete
                v-model="form.category"
                :fetch-suggestions="queryCategories"
                placeholder="Enter or select category"
                style="width: 100%"
                clearable
              >
                <template #default="{ item }">
                  <div>{{ item.value }}</div>
                </template>
              </el-autocomplete>
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="Amount (Rs.)" prop="amount">
              <el-input
                v-model="form.amount"
                placeholder="Enter amount"
                type="number"
                step="0.01"
                min="0"
              >
                <template #prefix>Rs.</template>
              </el-input>
            </el-form-item>
          </el-col>
        </el-row>

        <el-row :gutter="20">
          <el-col :span="12">
            <el-form-item label="Date" prop="date">
              <el-date-picker
                v-model="form.date"
                type="date"
                placeholder="Select date"
                style="width: 100%"
              />
            </el-form-item>
          </el-col>
          
          <el-col :span="12">
            <el-form-item label="Payment Method" prop="payment_method">
              <el-select v-model="form.payment_method" placeholder="Select method" style="width: 100%">
                <el-option label="Cash" value="cash" />
                <el-option label="Bank Transfer" value="bank" />
                <el-option label="Cheque" value="cheque" />
                <el-option label="Online Payment" value="online" />
              </el-select>
            </el-form-item>
          </el-col>
        </el-row>

        <el-form-item label="Description" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            placeholder="Enter detailed description of the expense"
            :rows="4"
            show-word-limit
            maxlength="500"
          />
        </el-form-item>

        <el-form-item label="Reference Number" prop="reference_number">
          <el-input
            v-model="form.reference_number"
            placeholder="Enter reference number (optional)"
          />
        </el-form-item>

        <el-form-item>
          <el-button type="primary" @click="submitForm" :loading="submitting" size="large">
            <el-icon><Minus /></el-icon>
            Add Expense
          </el-button>
          <el-button @click="resetForm" size="large">
            <el-icon><Refresh /></el-icon>
            Reset
          </el-button>
          <el-button @click="$router.push('/finance/accounting/transactions')" size="large">
            <el-icon><List /></el-icon>
            View Transactions
          </el-button>
        </el-form-item>
      </el-form>
    </el-card>

    <!-- Quick Categories -->
    <el-card class="quick-categories">
      <template #header>
        <span>Common Expense Categories</span>
      </template>
      <div class="category-buttons">
        <el-button
          v-for="category in commonExpenseCategories"
          :key="category"
          @click="form.category = category"
          size="small"
          type="warning"
          plain
        >
          {{ category }}
        </el-button>
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
  Minus, 
  Refresh, 
  List 
} from '@element-plus/icons-vue';

const router = useRouter();
const accountingRes = new AccountingResource();

const formRef = ref(null);
const submitting = ref(false);
const categories = ref([]);

const form = ref({
  type: 'expense',
  category: '',
  amount: '',
  description: '',
  date: new Date(),
  payment_method: '',
  reference_number: ''
});

const rules = {
  category: [{ required: true, message: 'Please enter category', trigger: 'blur' }],
  amount: [
    { required: true, message: 'Please enter amount', trigger: 'blur' },
    { pattern: /^\d+(\.\d{1,2})?$/, message: 'Please enter valid amount', trigger: 'blur' }
  ],
  description: [{ required: true, message: 'Please enter description', trigger: 'blur' }],
  date: [{ required: true, message: 'Please select date', trigger: 'change' }],
  payment_method: [{ required: true, message: 'Please select payment method', trigger: 'change' }]
};

const commonExpenseCategories = [
  'Teacher Salaries',
  'Staff Salaries',
  'Utility Bills',
  'Electricity Bill',
  'Water Bill',
  'Gas Bill',
  'Internet Bill',
  'Phone Bill',
  'Building Maintenance',
  'Equipment Purchase',
  'Office Supplies',
  'Cleaning Supplies',
  'Transportation',
  'Insurance',
  'Legal Fees',
  'Marketing',
  'Books & Materials',
  'Laboratory Equipment',
  'Computer Equipment',
  'Furniture',
  'Security Services',
  'Other Expenses'
];

const queryCategories = (queryString, cb) => {
  const results = queryString
    ? categories.value.filter(item => 
        item.value.toLowerCase().includes(queryString.toLowerCase())
      )
    : categories.value;
  cb(results);
};

const loadCategories = async () => {
  try {
    const response = await accountingRes.getCategories('expense');
    categories.value = response.data.map(cat => ({ value: cat }));
  } catch (error) {
    console.error('Failed to load categories:', error);
  }
};

const submitForm = async () => {
  if (!formRef.value) return;
  
  const valid = await formRef.value.validate().catch(() => false);
  if (!valid) return;
  
  submitting.value = true;
  try {
    const formData = { ...form.value };
    formData.date = formData.date.toISOString().split('T')[0];
    
    await accountingRes.store(formData);
    ElMessage.success('Expense added successfully');
    
    router.push('/finance/accounting/transactions');
  } catch (error) {
    ElMessage.error('Failed to add expense');
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
    type: 'expense',
    category: '',
    amount: '',
    description: '',
    date: new Date(),
    payment_method: '',
    reference_number: ''
  };
};

onMounted(() => {
  loadCategories();
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
    color: #f56c6c;
    margin-bottom: 8px;
  }
  
  p {
    color: #606266;
    margin: 0;
  }
}

.form-card {
  margin-bottom: 20px;
  
  .expense-form {
    padding: 20px;
  }
}

.quick-categories {
  .category-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    
    .el-button {
      margin: 0;
    }
  }
}

:deep(.el-form-item__label) {
  font-weight: 600;
  color: #303133;
}

:deep(.el-input__prefix) {
  color: #f56c6c;
  font-weight: bold;
}

@media (max-width: 768px) {
  .app-container {
    padding: 10px;
  }
  
  .form-card .expense-form {
    padding: 10px;
  }
  
  :deep(.el-form-item) {
    margin-bottom: 15px;
  }
}
</style>
