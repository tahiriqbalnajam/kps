<template>
  <div class="app-container">
    <div class="filter-container">
      <el-date-picker
        v-model="query.daterange"
        type="daterange"
        align="right"
        unlink-panels
        range-separator="To"
        start-placeholder="Start date"
        end-placeholder="End date"
        :picker-options="pickerOptions"
        format="dd/MM/yyyy"
        value-format="yyyy-MM-dd"
        style="width:415px"
        :default-value="defaultDate"
      />
      <el-button class="" type="primary" icon="el-icon-search" @click="handleFilter">
        {{ $t('table.search') }}
      </el-button>
      <el-button type="danger" icon="el-icon-plus" @click="drawer = true">
        Add Transaction
      </el-button>
    </div>
    <el-row :gutter="12">
      <el-col :span="24">
        <el-card shadow="always">
          <el-table
            :data="transactions"
            style="width: 100%"
            :row-class-name="tableRowClassName"
          >
            <el-table-column label="Date">
              <template slot-scope="scope">
                {{ scope.row.created_at | dateformat }}
              </template>
            </el-table-column>
            <el-table-column label="Jama" prop="jama_account.name" />
            <el-table-column label="Naam" prop="naam_account.name" />
            <el-table-column label="Amount" prop="amount" />
            <el-table-column label="Details" prop="comments" />
            <el-table-column align="right">
              <template slot-scope="scope">
                <el-button
                  v-if="scope.row.status == 'enable'"
                  size="mini"
                  type="danger"
                  class="el-icon-delete"
                  @click="markDel(scope.row.id)"
                />
                <el-button
                  v-if="scope.row.status == 'disable'"
                  size="mini"
                  type="primary"
                  class="el-icon-check"
                  @click="markDel(scope.row.id)"
                />
              </template>
            </el-table-column>
          </el-table>
        </el-card>
      </el-col>
    </el-row>
    <pagination v-show="totalrecords>0" :total="totalrecords" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
    <add-transaction v-if="drawer" :showdrawer="drawer" @toggledrawer="handelDrawer" />
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import AddTransaction from '@/views/accounts/AddTransaction.vue';
import Resource from '@/api/resource';
import moment from 'moment';
const transResor = new Resource('transaction');
export default {
  name: '',
  components: { Pagination, AddTransaction },
  directives: { },
  filters: {
    dateformat: (date) => {
      return (!date) ? '' : moment(date).format('DD MMM, YYYY');
    },
  },
  data() {
    return {
      drawer: false,
      transactions: null,
      search: '',
      totalrecords: 0,
      loading: true,
      defaultDate: new Date().toJSON().slice(0, 10).replace(/-/g, '/'),
      downloading: false,
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
        daterange: [this.todayDate(), this.todayDate()],
      },
      pickerOptions: {
        shortcuts: [{
          text: 'Last week',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
          },
        }, {
          text: 'Last month',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit('pick', [start, end]);
          },
        }, {
          text: 'Last 3 months',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
            picker.$emit('pick', [start, end]);
          },
        }],
      },
    };
  },
  computed: {
  },
  created() {
    this.getList();
  },
  methods: {
    todayDate() {
      var today = new Date();
      var dd = String(today.getDate()).padStart(2, '0');
      var mm = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
      var yyyy = today.getFullYear();
      today = yyyy + '-' + mm + '-' + dd;
      return today;
    },
    tableRowClassName({ row, rowIndex }) {
      console.log(row);
      if (row.status == 'disable') {
        return 'deleted-row';
      }
    },
    async getList() {
      const { data } = await transResor.list(this.query);
      this.transactions = data.transactions.data;
      this.totalrecords = data.transactions.total;
    },
    async markDel(id) {
      await transResor.destroy(id);
      this.getList();
    },
    handleFilter() {
      this.getList();
    },
    handelDrawer(value) {
      this.drawer = false;
      if (value !== 'justclose') {
        this.getList();
      }
    },
  },
};
</script>
<style>
  .el-table .deleted-row {
    color: #ff4949;
  }

</style>
