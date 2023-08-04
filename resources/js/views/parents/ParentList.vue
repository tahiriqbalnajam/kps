<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus" @click="addparentpop = true">
        Add Parent
      </el-button>
    </div>
    <el-table
      :data="parents"
      style="width: 100%"
    >
      <el-table-column label="ID" prop="id" />
      <el-table-column label="Name" prop="name" />
      <el-table-column label="Phone" prop="phone" />
      <el-table-column label="Address" prop="address" />
      <el-table-column label="Profession" prop="profession" />
      <el-table-column label="CNIC" prop="cnic" />
      <el-table-column align="right">
        <template slot="header" slot-scope="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search"  v-on:input="debounceInput" />
        </template>
        <template slot-scope="scope">
          <el-button
            size="mini"
            @click="handleEdit(scope.row.id, scope.row.name)"
          >Edit</el-button>
          <el-button
            size="mini"
            type="danger"
            @click="handleDelete(scope.row.id, scope.row.name)"
          >Delete</el-button>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
    <add-parent v-if="addparentpop" :editnowprop="addparentpop" :parentid="parentid" @closePopUp="closePopUp()" />
  </div>
</template>
<script>
import Pagination from '@/components/Pagination';
import Resource from '@/api/resource';
import AddParent from './AddParent';
const parentsPro = new Resource('parents');
export default {
  name: 'ParentList',
  components: { Pagination, AddParent },
  directives: { },
  data() {
    return {
      parentid: null,
      parents: null,
      search: '',
      total: 0,
      loading: false,
      downloading: false,
      addparentpop: false,
      formLabelWidth: '250',
      parent: {
        id: '',
        name: '',
      },
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
    };
  },
  computed: {
  },
  created() {
    this.getList();
  },
  methods: {
    debounceInput: _.debounce(function (e) {
      this.getList();
    }, 500),
    closeAddParent(parm) {
      this.addparentpop = false;
    },
    async getList() {
      const { data } = await parentsPro.list(this.query);
      this.parents = data.parents.data;
      this.total = data.parents.total;
    },
    async search_data() {
      await this.getList();
    },
    handleEdit(id, name) {
      this.parentid = id;
      this.addparentpop = true;
    },
    closePopUp() {
      this.parentid = null;
      this.addparentpop = false;
      this.getList();
    },
  },
};
</script>
<style >
  .el-drawer__body {
    flex: 1;
    padding: 20px;
  }
  .demo-drawer__content {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 20px;
  }
</style>