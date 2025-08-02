<template>
  <div class="app-container">
    <div class="dashboard-header">
      <h2>Accounting Dashboard</h2>
      <p>Overview of school financial transactions</p>
    </div>

    <!-- Summary Cards -->
    <el-row :gutter="20" class="summary-cards">
      <el-col :xs="24" :sm="12" :lg="6">
        <div class="summary-card income">
          <div class="card-icon">
            <el-icon size="32"><TrendCharts /></el-icon>
          </div>
          <div class="card-content">
            <h3>Total Income</h3>
            <p class="amount">Rs. {{ formatCurrency(summary.total_income) }}</p>
          </div>
        </div>
      </el-col>
      
      <el-col :xs="24" :sm="12" :lg="6">
        <div class="summary-card expense">
          <div class="card-icon">
            <el-icon size="32"><Histogram /></el-icon>
          </div>
          <div class="card-content">
            <h3>Total Expense</h3>
            <p class="amount">Rs. {{ formatCurrency(summary.total_expense) }}</p>
          </div>
        </div>
      </el-col>
      
      <el-col :xs="24" :sm="12" :lg="6">
        <div class="summary-card balance" :class="{ negative: summary.balance < 0 }">
          <div class="card-icon">
            <el-icon size="32"><Wallet /></el-icon>
          </div>
          <div class="card-content">
            <h3>Net Balance</h3>
            <p class="amount">Rs. {{ formatCurrency(summary.balance) }}</p>
          </div>
        </div>
      </el-col>
      
      <el-col :xs="24" :sm="12" :lg="6">
        <div class="summary-card monthly">
          <div class="card-icon">
            <el-icon size="32"><Calendar /></el-icon>
          </div>
          <div class="card-content">
            <h3>Monthly Balance</h3>
            <p class="amount">Rs. {{ formatCurrency(summary.monthly_balance) }}</p>
          </div>
        </div>
      </el-col>
    </el-row>

    <!-- Action Buttons -->
    <el-row :gutter="20" class="action-buttons">
      <el-col :span="24">
        <el-button type="success" size="large" @click="openIncomeDrawer">
          <el-icon><Plus /></el-icon>
          Add Income
        </el-button>
        <el-button type="danger" size="large" @click="openExpenseDrawer">
          <el-icon><Minus /></el-icon>
          Add Expense
        </el-button>
        <el-button type="primary" size="large" @click="$router.push('/finance/accounting/transactions')">
          <el-icon><List /></el-icon>
          View All Transactions
        </el-button>
      </el-col>
    </el-row>

    <!-- Recent Transactions -->
    <div class="recent-transactions">
      <h3>Recent Transactions</h3>
      <el-table :data="recentTransactions" style="width: 100%">
        <el-table-column prop="date" label="Date" width="120">
          <template #default="scope">
            {{ formatDate(scope.row.date) }}
          </template>
        </el-table-column>
        <el-table-column prop="type" label="Type" width="100">
          <template #default="scope">
            <el-tag :type="scope.row.type === 'income' ? 'success' : 'danger'">
              {{ scope.row.type.toUpperCase() }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="category" label="Category" width="150" />
        <el-table-column prop="description" label="Description" show-overflow-tooltip />
        <el-table-column prop="amount" label="Amount" width="120" align="right">
          <template #default="scope">
            <span :class="scope.row.type === 'income' ? 'text-success' : 'text-danger'">
              Rs. {{ formatCurrency(scope.row.amount) }}
            </span>
          </template>
        </el-table-column>
        <el-table-column prop="payment_method" label="Method" width="100" />
      </el-table>
    </div>

    <!-- Income Drawer -->
    <el-drawer
      title="Add Income"
      v-model="incomeDrawerVisible"
      direction="rtl"
      size="500px"
      @close="resetIncomeForm"
    >
      <div class="drawer-content">
        <el-form
          :model="incomeForm"
          :rules="incomeRules"
          ref="incomeFormRef"
          label-width="120px"
        >
          <el-form-item label="Category" prop="category">
            <el-autocomplete
              v-model="incomeForm.category"
              :fetch-suggestions="queryIncomeCategories"
              placeholder="Enter or select category"
              style="width: 100%"
              clearable
            >
              <template #default="{ item }">
                <div>{{ item.value }}</div>
              </template>
            </el-autocomplete>
          </el-form-item>
          
          <el-form-item label="Amount (Rs.)" prop="amount">
            <el-input
              v-model="incomeForm.amount"
              placeholder="Enter amount"
              type="number"
              step="0.01"
              min="0"
            >
              <template #prefix>Rs.</template>
            </el-input>
          </el-form-item>
          
          <el-form-item label="Date" prop="date">
            <el-date-picker
              v-model="incomeForm.date"
              type="date"
              placeholder="Select date"
              style="width: 100%"
            />
          </el-form-item>
          
          <el-form-item label="Payment Method" prop="payment_method">
            <el-select v-model="incomeForm.payment_method" placeholder="Select method" style="width: 100%">
              <el-option label="Cash" value="cash" />
              <el-option label="Bank Transfer" value="bank" />
              <el-option label="Cheque" value="cheque" />
              <el-option label="Online Payment" value="online" />
            </el-select>
          </el-form-item>
          
          <el-form-item label="Description" prop="description">
            <el-input
              v-model="incomeForm.description"
              type="textarea"
              placeholder="Enter description"
              :rows="3"
            />
          </el-form-item>
          
          <el-form-item label="Reference" prop="reference_number">
            <el-input
              v-model="incomeForm.reference_number"
              placeholder="Enter reference number (optional)"
            />
          </el-form-item>
        </el-form>

        <!-- Quick Categories for Income -->
        <div class="quick-categories">
          <h4>Quick Categories</h4>
          <div class="category-buttons">
            <el-button
              v-for="category in commonIncomeCategories"
              :key="category"
              @click="incomeForm.category = category"
              size="small"
              type="success"
              plain
            >
              {{ category }}
            </el-button>
          </div>
        </div>
        
        <div class="drawer-footer">
          <el-button @click="incomeDrawerVisible = false">Cancel</el-button>
          <el-button type="success" @click="submitIncomeForm" :loading="submittingIncome">
            Add Income
          </el-button>
        </div>
      </div>
    </el-drawer>

    <!-- Expense Drawer -->
    <el-drawer
      title="Add Expense"
      v-model="expenseDrawerVisible"
      direction="rtl"
      size="500px"
      @close="resetExpenseForm"
    >
      <div class="drawer-content">
        <el-form
          :model="expenseForm"
          :rules="expenseRules"
          ref="expenseFormRef"
          label-width="120px"
        >
          <el-form-item label="Category" prop="category">
            <el-autocomplete
              v-model="expenseForm.category"
              :fetch-suggestions="queryExpenseCategories"
              placeholder="Enter or select category"
              style="width: 100%"
              clearable
            >
              <template #default="{ item }">
                <div>{{ item.value }}</div>
              </template>
            </el-autocomplete>
          </el-form-item>
          
          <el-form-item label="Amount (Rs.)" prop="amount">
            <el-input
              v-model="expenseForm.amount"
              placeholder="Enter amount"
              type="number"
              step="0.01"
              min="0"
            >
              <template #prefix>Rs.</template>
            </el-input>
          </el-form-item>
          
          <el-form-item label="Date" prop="date">
            <el-date-picker
              v-model="expenseForm.date"
              type="date"
              placeholder="Select date"
              style="width: 100%"
            />
          </el-form-item>
          
          <el-form-item label="Payment Method" prop="payment_method">
            <el-select v-model="expenseForm.payment_method" placeholder="Select method" style="width: 100%">
              <el-option label="Cash" value="cash" />
              <el-option label="Bank Transfer" value="bank" />
              <el-option label="Cheque" value="cheque" />
              <el-option label="Online Payment" value="online" />
            </el-select>
          </el-form-item>
          
          <el-form-item label="Description" prop="description">
            <el-input
              v-model="expenseForm.description"
              type="textarea"
              placeholder="Enter description"
              :rows="3"
            />
          </el-form-item>
          
          <el-form-item label="Reference" prop="reference_number">
            <el-input
              v-model="expenseForm.reference_number"
              placeholder="Enter reference number (optional)"
            />
          </el-form-item>
        </el-form>

        <!-- Quick Categories for Expense -->
        <div class="quick-categories">
          <h4>Quick Categories</h4>
          <div class="category-buttons">
            <el-button
              v-for="category in commonExpenseCategories"
              :key="category"
              @click="expenseForm.category = category"
              size="small"
              type="danger"
              plain
            >
              {{ category }}
            </el-button>
          </div>
        </div>
        
        <div class="drawer-footer">
          <el-button @click="expenseDrawerVisible = false">Cancel</el-button>
          <el-button type="danger" @click="submitExpenseForm" :loading="submittingExpense">
            Add Expense
          </el-button>
        </div>
      </div>
    </el-drawer>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { ElMessage } from 'element-plus';
import AccountingResource from '@/api/accounting';
import { 
  TrendCharts, 
  Histogram, 
  Wallet, 
  Calendar, 
  Plus, 
  Minus, 
  List 
} from '@element-plus/icons-vue';

const accountingRes = new AccountingResource();

// Dashboard data
const summary = ref({
  total_income: 0,
  total_expense: 0,
  balance: 0,
  monthly_income: 0,
  monthly_expense: 0,
  monthly_balance: 0,
  yearly_income: 0,
  yearly_expense: 0,
  yearly_balance: 0
});

const recentTransactions = ref([]);
const loading = ref(false);

// Drawer states
const incomeDrawerVisible = ref(false);
const expenseDrawerVisible = ref(false);
const submittingIncome = ref(false);
const submittingExpense = ref(false);

// Form refs
const incomeFormRef = ref(null);
const expenseFormRef = ref(null);

// Categories
const incomeCategories = ref([]);
const expenseCategories = ref([]);

// Form data
const incomeForm = ref({
  type: 'income',
  category: '',
  amount: '',
  description: '',
  date: new Date(),
  payment_method: '',
  reference_number: ''
});

const expenseForm = ref({
  type: 'expense',
  category: '',
  amount: '',
  description: '',
  date: new Date(),
  payment_method: '',
  reference_number: ''
});

// Validation rules
const formRules = {
  category: [{ required: true, message: 'Please enter category', trigger: 'blur' }],
  amount: [
    { required: true, message: 'Please enter amount', trigger: 'blur' },
    { pattern: /^\d+(\.\d{1,2})?$/, message: 'Please enter valid amount', trigger: 'blur' }
  ],
  description: [{ required: true, message: 'Please enter description', trigger: 'blur' }],
  date: [{ required: true, message: 'Please select date', trigger: 'change' }],
  payment_method: [{ required: true, message: 'Please select payment method', trigger: 'change' }]
};

const incomeRules = formRules;
const expenseRules = formRules;

// Common categories
const commonIncomeCategories = [
  'Student Fees',
  'Admission Fees',
  'Transportation Fees',
  'Library Fees',
  'Laboratory Fees',
  'Examination Fees',
  'Registration Fees',
  'Late Fee Charges',
  'Book Sales',
  'Government Grant',
  'Donation',
  'Other Income'
];

const commonExpenseCategories = [
  'Teacher Salaries',
  'Staff Salaries',
  'Utility Bills',
  'Electricity Bill',
  'Building Maintenance',
  'Office Supplies',
  'Transportation',
  'Books & Materials',
  'Equipment Purchase',
  'Internet Bill',
  'Other Expenses'
];

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-PK', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0);
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-PK');
};

const loadDashboardData = async () => {
  loading.value = true;
  try {
    const response = await accountingRes.getDashboard();
    summary.value = response.data.summary;
    recentTransactions.value = response.data.recent_transactions;
  } catch (error) {
    ElMessage.error('Failed to load dashboard data');
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const loadCategories = async () => {
  try {
    const [incomeResponse, expenseResponse] = await Promise.all([
      accountingRes.getCategories('income'),
      accountingRes.getCategories('expense')
    ]);
    
    incomeCategories.value = incomeResponse.data.map(cat => ({ value: cat }));
    expenseCategories.value = expenseResponse.data.map(cat => ({ value: cat }));
  } catch (error) {
    console.error('Failed to load categories:', error);
  }
};

const queryIncomeCategories = (queryString, cb) => {
  const results = queryString
    ? incomeCategories.value.filter(item => 
        item.value.toLowerCase().includes(queryString.toLowerCase())
      )
    : incomeCategories.value;
  cb(results);
};

const queryExpenseCategories = (queryString, cb) => {
  const results = queryString
    ? expenseCategories.value.filter(item => 
        item.value.toLowerCase().includes(queryString.toLowerCase())
      )
    : expenseCategories.value;
  cb(results);
};

// Drawer methods
const openIncomeDrawer = () => {
  resetIncomeForm();
  incomeDrawerVisible.value = true;
};

const openExpenseDrawer = () => {
  resetExpenseForm();
  expenseDrawerVisible.value = true;
};

const resetIncomeForm = () => {
  incomeForm.value = {
    type: 'income',
    category: '',
    amount: '',
    description: '',
    date: new Date(),
    payment_method: '',
    reference_number: ''
  };
  if (incomeFormRef.value) {
    incomeFormRef.value.resetFields();
  }
};

const resetExpenseForm = () => {
  expenseForm.value = {
    type: 'expense',
    category: '',
    amount: '',
    description: '',
    date: new Date(),
    payment_method: '',
    reference_number: ''
  };
  if (expenseFormRef.value) {
    expenseFormRef.value.resetFields();
  }
};

const submitIncomeForm = async () => {
  if (!incomeFormRef.value) return;
  
  const valid = await incomeFormRef.value.validate().catch(() => false);
  if (!valid) return;
  
  submittingIncome.value = true;
  try {
    const formData = { ...incomeForm.value };
    formData.date = formData.date.toISOString().split('T')[0];
    
    await accountingRes.store(formData);
    ElMessage.success('Income added successfully');
    
    incomeDrawerVisible.value = false;
    loadDashboardData(); // Refresh dashboard data
  } catch (error) {
    ElMessage.error('Failed to add income');
    console.error(error);
  } finally {
    submittingIncome.value = false;
  }
};

const submitExpenseForm = async () => {
  if (!expenseFormRef.value) return;
  
  const valid = await expenseFormRef.value.validate().catch(() => false);
  if (!valid) return;
  
  submittingExpense.value = true;
  try {
    const formData = { ...expenseForm.value };
    formData.date = formData.date.toISOString().split('T')[0];
    
    await accountingRes.store(formData);
    ElMessage.success('Expense added successfully');
    
    expenseDrawerVisible.value = false;
    loadDashboardData(); // Refresh dashboard data
  } catch (error) {
    ElMessage.error('Failed to add expense');
    console.error(error);
  } finally {
    submittingExpense.value = false;
  }
};

onMounted(() => {
  loadDashboardData();
  loadCategories();
});
</script>

<style lang="scss" scoped>
.app-container {
  padding: 20px;
}

.dashboard-header {
  margin-bottom: 30px;
  text-align: center;
  
  h2 {
    color: #303133;
    margin-bottom: 8px;
  }
  
  p {
    color: #606266;
    margin: 0;
  }
}

.summary-cards {
  margin-bottom: 30px;
}

.summary-card {
  background: white;
  border-radius: 8px;
  padding: 20px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
  display: flex;
  align-items: center;
  margin-bottom: 20px;
  transition: transform 0.2s;
  
  &:hover {
    transform: translateY(-2px);
  }
  
  .card-icon {
    margin-right: 15px;
    padding: 12px;
    border-radius: 50%;
    
    .el-icon {
      color: white;
    }
  }
  
  .card-content {
    flex: 1;
    
    h3 {
      margin: 0 0 8px 0;
      font-size: 14px;
      color: #606266;
      font-weight: 500;
    }
    
    .amount {
      margin: 0;
      font-size: 24px;
      font-weight: bold;
      color: #303133;
    }
  }
  
  &.income .card-icon {
    background: #67c23a;
  }
  
  &.expense .card-icon {
    background: #f56c6c;
  }
  
  &.balance .card-icon {
    background: #409eff;
  }
  
  &.balance.negative .card-icon {
    background: #f56c6c;
  }
  
  &.monthly .card-icon {
    background: #e6a23c;
  }
}

.action-buttons {
  margin-bottom: 30px;
  text-align: center;
  
  .el-button {
    margin: 0 10px 10px 0;
  }
}

.recent-transactions {
  background: white;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
  
  h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #303133;
  }
}

.text-success {
  color: #67c23a !important;
  font-weight: bold;
}

.text-danger {
  color: #f56c6c !important;
  font-weight: bold;
}

.drawer-content {
  padding: 20px;
}

.drawer-footer {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #ebeef5;
  text-align: right;
  
  .el-button {
    margin-left: 10px;
  }
}

.quick-categories {
  margin-top: 20px;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 6px;
  
  h4 {
    margin: 0 0 10px 0;
    color: #303133;
    font-size: 14px;
  }
  
  .category-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    
    .el-button {
      margin: 0;
      font-size: 12px;
    }
  }
}

:deep(.el-drawer__header) {
  border-bottom: 1px solid #ebeef5;
  padding-bottom: 20px;
  margin-bottom: 0;
}

:deep(.el-drawer__body) {
  padding: 0;
}

:deep(.el-form-item__label) {
  font-weight: 600;
  color: #303133;
}

:deep(.el-input__prefix) {
  font-weight: bold;
}

@media (max-width: 768px) {
  .summary-card {
    flex-direction: column;
    text-align: center;
    
    .card-icon {
      margin-right: 0;
      margin-bottom: 10px;
    }
  }
  
  .drawer-content {
    padding: 15px;
  }
  
  .quick-categories .category-buttons {
    justify-content: center;
  }
}
</style>
