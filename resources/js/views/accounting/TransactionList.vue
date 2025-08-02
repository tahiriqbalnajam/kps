<template>
  <div class="app-container">
    <div class="filter-container">
      <el-row :gutter="20">
        <el-col :span="6">
          <el-input
            v-model="query.keyword"
            placeholder="Search transactions..."
            @input="debounceSearch"
            clearable
          >
            <template #prefix>
              <el-icon><Search /></el-icon>
            </template>
          </el-input>
        </el-col>
        <el-col :span="4">
          <el-select v-model="query.type" placeholder="Type" @change="getList" clearable>
            <el-option label="Income" value="income" />
            <el-option label="Expense" value="expense" />
          </el-select>
        </el-col>
        <el-col :span="4">
          <el-input v-model="query.category" placeholder="Category" @input="debounceSearch" clearable />
        </el-col>
        <el-col :span="6">
          <el-date-picker
            v-model="query.date"
            type="daterange"
            range-separator="To"
            start-placeholder="Start date"
            end-placeholder="End date"
            @change="getList"
          />
        </el-col>
        <el-col :span="4">
          <el-button type="success" @click="openAddDialog('income')">
            <el-icon><Plus /></el-icon>
            Add Income
          </el-button>
        </el-col>
      </el-row>
    </div>

    <!-- Summary Row -->
    <el-row :gutter="20" class="summary-bar">
      <el-col :span="6">
        <div class="summary-item income">
          <span>Total Income: Rs. {{ formatCurrency(summary.filtered_income) }}</span>
        </div>
      </el-col>
      <el-col :span="6">
        <div class="summary-item expense">
          <span>Total Expense: Rs. {{ formatCurrency(summary.filtered_expense) }}</span>
        </div>
      </el-col>
      <el-col :span="6">
        <div class="summary-item balance">
          <span>Balance: Rs. {{ formatCurrency(summary.filtered_income - summary.filtered_expense) }}</span>
        </div>
      </el-col>
      <el-col :span="6">
        <el-button type="danger" @click="openAddDialog('expense')">
          <el-icon><Minus /></el-icon>
          Add Expense
        </el-button>
      </el-col>
    </el-row>

    <!-- Table -->
    <el-table
      v-loading="loading"
      :data="transactions"
      style="width: 100%"
      row-key="id"
    >
      <el-table-column prop="date" label="Date" width="120" sortable>
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
      <el-table-column prop="description" label="Description" show-overflow-tooltip min-width="200" />
      
      <el-table-column prop="amount" label="Amount" width="150" align="right">
        <template #default="scope">
          <span :class="scope.row.type === 'income' ? 'text-success' : 'text-danger'">
            Rs. {{ formatCurrency(scope.row.amount) }}
          </span>
        </template>
      </el-table-column>
      
      <el-table-column prop="payment_method" label="Method" width="100">
        <template #default="scope">
          <el-tag size="small">{{ scope.row.payment_method.toUpperCase() }}</el-tag>
        </template>
      </el-table-column>
      
      <el-table-column prop="reference_number" label="Reference" width="120" />
      
      <el-table-column label="Actions" width="120" align="center">
        <template #default="scope">
          <el-button size="small" @click="openEditDialog(scope.row)">
            <el-icon><Edit /></el-icon>
          </el-button>
          <el-button size="small" type="danger" @click="deleteTransaction(scope.row.id)">
            <el-icon><Delete /></el-icon>
          </el-button>
        </template>
      </el-table-column>
    </el-table>

    <!-- Pagination -->
    <pagination
      v-show="total > 0"
      :total="total"
      :page.sync="query.page"
      :limit.sync="query.limit"
      @pagination="getList"
    />

    <!-- Add/Edit Dialog -->
    <el-dialog
      :title="dialogTitle"
      v-model="dialogVisible"
      width="500px"
      @close="resetForm"
    >
      <el-form :model="form" :rules="rules" ref="formRef" label-width="120px">
        <el-form-item label="Type" prop="type">
          <el-select v-model="form.type" placeholder="Select type" style="width: 100%">
            <el-option label="Income" value="income" />
            <el-option label="Expense" value="expense" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="Category" prop="category">
          <el-input v-model="form.category" placeholder="Enter category" />
        </el-form-item>
        
        <el-form-item label="Amount" prop="amount">
          <el-input
            v-model="form.amount"
            placeholder="Enter amount"
            type="number"
            step="0.01"
            min="0"
          />
        </el-form-item>
        
        <el-form-item label="Description" prop="description">
          <el-input
            v-model="form.description"
            type="textarea"
            placeholder="Enter description"
            :rows="3"
          />
        </el-form-item>
        
        <el-form-item label="Date" prop="date">
          <el-date-picker
            v-model="form.date"
            type="date"
            placeholder="Select date"
            style="width: 100%"
          />
        </el-form-item>
        
        <el-form-item label="Payment Method" prop="payment_method">
          <el-select v-model="form.payment_method" placeholder="Select method" style="width: 100%">
            <el-option label="Cash" value="cash" />
            <el-option label="Bank Transfer" value="bank" />
            <el-option label="Cheque" value="cheque" />
            <el-option label="Online" value="online" />
          </el-select>
        </el-form-item>
        
        <el-form-item label="Reference" prop="reference_number">
          <el-input v-model="form.reference_number" placeholder="Enter reference number (optional)" />
        </el-form-item>
      </el-form>
      
      <template #footer>
        <span class="dialog-footer">
          <el-button @click="dialogVisible = false">Cancel</el-button>
          <el-button type="primary" @click="submitForm" :loading="submitting">
            {{ isEdit ? 'Update' : 'Create' }}
          </el-button>
        </span>
      </template>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import { ElMessage, ElMessageBox } from 'element-plus';
import AccountingResource from '@/api/accounting';
import Pagination from '@/components/Pagination/index.vue';
import { 
  Search, 
  Plus, 
  Minus, 
  Edit, 
  Delete 
} from '@element-plus/icons-vue';
import { debounce } from 'lodash';

const accountingRes = new AccountingResource();

// Data
const transactions = ref([]);
const summary = ref({
  filtered_income: 0,
  filtered_expense: 0
});
const loading = ref(false);
const submitting = ref(false);
const dialogVisible = ref(false);
const isEdit = ref(false);
const formRef = ref(null);

const query = ref({
  page: 1,
  limit: 15,
  keyword: '',
  type: '',
  category: '',
  date: []
});

const total = ref(0);

const form = ref({
  type: '',
  category: '',
  amount: '',
  description: '',
  date: '',
  payment_method: '',
  reference_number: ''
});

const rules = {
  type: [{ required: true, message: 'Please select type', trigger: 'change' }],
  category: [{ required: true, message: 'Please enter category', trigger: 'blur' }],
  amount: [
    { required: true, message: 'Please enter amount', trigger: 'blur' },
    { pattern: /^\d+(\.\d{1,2})?$/, message: 'Please enter valid amount', trigger: 'blur' }
  ],
  description: [{ required: true, message: 'Please enter description', trigger: 'blur' }],
  date: [{ required: true, message: 'Please select date', trigger: 'change' }],
  payment_method: [{ required: true, message: 'Please select payment method', trigger: 'change' }]
};

// Computed
const dialogTitle = computed(() => {
  return isEdit.value ? 'Edit Transaction' : 'Add Transaction';
});

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

const getList = async () => {
  loading.value = true;
  try {
    const params = { ...query.value };
    if (params.date && params.date.length === 2) {
      params.date = [
        params.date[0].toISOString().split('T')[0],
        params.date[1].toISOString().split('T')[0]
      ];
    }
    
    const response = await accountingRes.list(params);
    transactions.value = response.data.transactions.data;
    summary.value = response.data.summary;
    total.value = response.data.transactions.total;
  } catch (error) {
    ElMessage.error('Failed to load transactions');
    console.error(error);
  } finally {
    loading.value = false;
  }
};

const debounceSearch = debounce(() => {
  query.value.page = 1;
  getList();
}, 500);

const openAddDialog = (type = '') => {
  isEdit.value = false;
  form.value = {
    type: type,
    category: '',
    amount: '',
    description: '',
    date: new Date(),
    payment_method: '',
    reference_number: ''
  };
  dialogVisible.value = true;
};

const openEditDialog = (transaction) => {
  isEdit.value = true;
  form.value = {
    id: transaction.id,
    type: transaction.type,
    category: transaction.category,
    amount: transaction.amount,
    description: transaction.description,
    date: new Date(transaction.date),
    payment_method: transaction.payment_method,
    reference_number: transaction.reference_number || ''
  };
  dialogVisible.value = true;
};

const submitForm = async () => {
  if (!formRef.value) return;
  
  const valid = await formRef.value.validate().catch(() => false);
  if (!valid) return;
  
  submitting.value = true;
  try {
    const formData = { ...form.value };
    formData.date = formData.date.toISOString().split('T')[0];
    
    if (isEdit.value) {
      await accountingRes.update(formData.id, formData);
      ElMessage.success('Transaction updated successfully');
    } else {
      await accountingRes.store(formData);
      ElMessage.success('Transaction created successfully');
    }
    
    dialogVisible.value = false;
    getList();
  } catch (error) {
    ElMessage.error('Failed to save transaction');
    console.error(error);
  } finally {
    submitting.value = false;
  }
};

const deleteTransaction = async (id) => {
  try {
    await ElMessageBox.confirm('Are you sure you want to delete this transaction?', 'Warning', {
      confirmButtonText: 'OK',
      cancelButtonText: 'Cancel',
      type: 'warning'
    });
    
    await accountingRes.destroy(id);
    ElMessage.success('Transaction deleted successfully');
    getList();
  } catch (error) {
    if (error !== 'cancel') {
      ElMessage.error('Failed to delete transaction');
      console.error(error);
    }
  }
};

const resetForm = () => {
  if (formRef.value) {
    formRef.value.resetFields();
  }
};

// Lifecycle
onMounted(() => {
  getList();
});
</script>

<style lang="scss" scoped>
.filter-container {
  margin-bottom: 20px;
}

.summary-bar {
  margin-bottom: 20px;
  
  .summary-item {
    background: white;
    padding: 15px;
    border-radius: 4px;
    box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
    
    &.income span {
      color: #67c23a;
      font-weight: bold;
    }
    
    &.expense span {
      color: #f56c6c;
      font-weight: bold;
    }
    
    &.balance span {
      color: #409eff;
      font-weight: bold;
    }
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

.el-table {
  background: white;
  border-radius: 4px;
  box-shadow: 0 2px 12px 0 rgba(0, 0, 0, 0.1);
}
</style>
