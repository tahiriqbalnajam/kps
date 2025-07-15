<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button class="filter-item" style="margin-left: 10px;" type="success" @click="editnow = true">
        <el-icon class="el-icon--left">
            <Plus />
          </el-icon> Add Fee Type
      </el-button>
    </div>
    <el-table
        :data="feetypes"
        style="width: 100%"
      >
      <el-table-column label="ID" prop="id" />
      <el-table-column label="Title" prop="title" />
      <el-table-column label="Amount" prop="amount" />
      <el-table-column align="right">
      <template slot="header" #header="scope">
        <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
      </template>
      <template #default="scope">
        <el-button
          size="mini"
          @click="handleEdit(scope.row.id, scope.row.title)"
          >Edit</el-button>
        <el-button
          v-if="scope.row.title !== 'Monthly Fee'"
          size="mini"
          type="danger"
          @click="handleDelete(scope.row.id, scope.row.title)"
        >Delete</el-button>
      </template>
      </el-table-column>
    </el-table>
   <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
    <el-drawer
      title="Edit Record"
      :modelValue="editnow"
      direction="rtl"
      custom-class="demo-drawer"
      ref="drawer"
      :before-close="handleClose"
    >
      <div class="demo-drawer__content">
        <el-form :model="feetype">
          <el-form-item label="Title" :label-width="formLabelWidth">
            <el-input v-model="feetype.title" autocomplete="off"></el-input>
          </el-form-item>
          <el-form-item label="Amount" :label-width="formLabelWidth">
            <el-input v-model="feetype.amount" autocomplete="off"></el-input>
          </el-form-item>
        </el-form>
        <div class="demo-drawer__footer">
          <el-button @click="editnow = false">Cancel</el-button>
          <el-button type="primary" @click="onSubmit" :loading="loading">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
        </div>
      </div>
    </el-drawer>
  </div>
</template>
<script>
import Pagination from '@/components/Pagination/index.vue';
import Resource from '@/api/resource';
import { debounce } from 'lodash';
import { Plus } from '@element-plus/icons-vue'
const feetypesPro = new Resource('feetypes');
export default {
    name: 'FeeTypes',
    components: { Pagination },
    directives: { },
    data() {
      return {
        feetypes: null,
        search: '',
        total: 0,
        loading: false,
        downloading: false,
        editnow: false,
        formLabelWidth: 250,
        feetype: {
          id: '',
          title: '',
          amount: 0,
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
      debounceInput: debounce(function (e) {
        this.getList();
      }, 500),
      async getList() {
        const { data } = await feetypesPro.list(this.query);
        this.feetypes = data.feetypes.data;
        this.total = data.feetypes.total;
      },
      async search_data() {
        await this.getList();
      },
      async handleEdit(id, name) {
        const { data } = await feetypesPro.get(id);
        this.feetype = data.feetype;
        this.editnow = true;
      },
      async handleDelete(id, name) {
        this.$confirm('Do you really want to delete?', 'Warning', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning'
        }).then(async () => {
          await feetypesPro.destroy(id);
          this.getList();
          this.$message({
            type: 'success',
            message: name+' Delete successfully'
          });
        })
      },
      async onSubmit() {
        if(this.feetype.id != '') {
          await feetypesPro.update(this.feetype.id, this.feetype);
          this.editnow = false;
          this.getList();
          this.resetFeeType();
        } else {
          await feetypesPro.store(this.feetype);
          this.editnow = false;
          this.getList();
          this.resetFeeType();
        }
      },
      handleClose() {
        this.editnow = false;
        this.resetFeeType();
      },
      resetFeeType() {
        this.feetype = {
          id: '',
          title: '',
          amount: 0,
        }
      },
    },
};
</script>
<style  scoped>
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