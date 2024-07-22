<script setup>
  import { reactive, getCurrentInstance } from 'vue';
  import Pagination from '@/components/Pagination/index.vue';
  import HeadControls from '@/components/HeadControls.vue';
  import Resource from '@/api/resource.js';
  let balanceRes = new Resource('balances');
  const $this =  reactive({
                query: {
                    keyword: '',
                    page: 1,
                    limit: 15,
                    total: 0
                },
                accounts: [],
                attendance: {
                    student_id: '',
                },
                loading: false,
                chart: null,
                chartdata: {
                    months:[],
                    absent:[]
                }
            })
  
  const search = () => {
    getAccounts();
  }

  const getAccounts = async() => {
    const { data } = await balanceRes.list($this.query);
    $this.accounts = data.balances.data;
    $this.query.total = data.balances.total;
  }
  const handleSizeChange =  async(val) => {
      $this.query.limit = val
      await getAccounts()
    }
  const handleCurrentChange = async (val) =>{
      $this.query.page = val
      await getAccounts()
  }
  getAccounts();
</script>
<template>
  <div class="app-container">
      <div class="filter-container">
          <head-controls>
            <el-form-item label="Account Name">
              <el-row :gutter="10">
                 <el-col :span="16">
                  <el-input
                    v-model="$this.query.keyword"
                    label="Search account"
                    placeholder="Enter name"
                  />
                </el-col>
                <el-col :span="6">
                  <el-button type="primary" @click="search()" :disabled="!$this.query.keyword">Search</el-button>
                </el-col>
              </el-row>
            </el-form-item>
          </head-controls>
      </div>
      <el-table :data="$this.accounts" style="width: 100%" max-height="600px">
        <el-table-column prop="accounts.name" label="Name" />
        <el-table-column prop="accounts.phone" label="Phone#" />
        <el-table-column prop="jama" label="Jama(Cr)" />
        <el-table-column prop="naam" label="Naam(Dr)" />
        <el-table-column prop="balance" label="Balance" />
      </el-table>
      <div class="demo-pagination-block">
        <el-pagination
          v-show="$this.query.total > 0"
          v-model:current-page="$this.query.page"
          v-model:page-size="$this.query.limit"
          :page-sizes="[10, 15, 20, 30, 50, 100]"
          :small="small"
          :disabled="disabled"
          background="white"
          layout="total, sizes, prev, pager, next, jumper"
          :total="$this.query.total"
          @size-change="handleSizeChange"
          @current-change="handleCurrentChange"
        />
      </div>
  </div>
</template>