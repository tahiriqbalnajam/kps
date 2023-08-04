<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button class="filter-item" style="margin-left: 10px;" type="success" icon="el-icon-plus" @click="editnow = true">
        Add Teacher
      </el-button>
    </div>
    <el-table
      :data="list"
      style="width: 100%"
    >
      <el-table-column label="ID" prop="id" />
      <el-table-column label="Name" prop="name" />
      <el-table-column label="NIC" prop="nic" />
      <el-table-column label="Pay" prop="pay" />
      <el-table-column label="Phone" prop="phone" />
      <el-table-column label="Address" prop="address" />
      <el-table-column align="right">
        <template slot="header" slot-scope="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
        </template>
        <template slot-scope="scope">
          <el-button type="primary" @click="generateCard()" icon="el-icon-bank-card" />
          <el-button
            size="mini"
            @click="handleEdit(scope.row.id, scope.row.name)"
            icon="el-icon-edit"
          />
          <el-button
            icon="el-icon-delete"
            size="mini"
            type="danger"
            @click="handleDelete(scope.row.id, scope.row.name)"
          />
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="query.page" :limit.sync="query.limit" @pagination="getList" />
    <el-drawer
      title="Edit Record"
      :visible.sync="editnow"
      direction="rtl"
      custom-class="demo-drawer"
      ref="drawer"
    >
      <div class="demo-drawer__content">
        <el-form :model="teacher">
          <el-form-item label="Name" :label-width="formLabelWidth">
            <el-input v-model="teacher.name" autocomplete="off" />
          </el-form-item>
          <el-form-item label="NIC#" :label-width="formLabelWidth">
            <el-input v-model="teacher.nic" autocomplete="off" />
          </el-form-item>
          <el-form-item label="Education" :label-width="formLabelWidth">
            <el-input v-model="teacher.education" autocomplete="off" />
          </el-form-item>
          <el-form-item label="Phone#" :label-width="formLabelWidth">
            <el-input v-model="teacher.phone" autocomplete="off" />
          </el-form-item>
          <el-form-item label="Address" :label-width="formLabelWidth">
            <el-input v-model="teacher.address" type="textarea" autocomplete="off" />
          </el-form-item>
        </el-form>
        <div class="demo-drawer__footer">
          <el-button @click="editnow = false">Cancel</el-button>
          <el-button type="primary" @click="onSubmit" :loading="loading">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
        </div>
      </div>
    </el-drawer>
    <el-drawer
      title="Edit Record"
      :visible.sync="showcard"
      direction="rtl"
      custom-class="demo-drawer"
      ref="drawer"
    >
      <div class="demo-drawer__content">
        <canvas id="canvas"></canvas>
      </div>
    </el-drawer>
  </div>
</template>
<script>
import QRCode from 'qrcode';
import Pagination from '@/components/Pagination';
import Resource from '@/api/resource';
const resourcePro = new Resource('teachers');
export default {
  name: '',
  components: { Pagination, QRCode },
  directives: { },
  data() {
    return {
      list: null,
      search: '',
      total: 0,
      loading: false,
      downloading: false,
      editnow: false,
      showcard: false,
      formLabelWidth: 250,
      teacher: {
        id: '',
        name: '',
        nic: '',
        pay: '',
        education: '',
        phone: '',
        address: '',
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
    async getList() {
      const { data } = await resourcePro.list(this.query);
      this.list = data.teachers.data;
      this.total = data.teachers.total;
    },
    async search_data() {
      await this.getList();
    },
    async handleEdit(id, name) {
      const { data } = await resourcePro.get(id);
      this.teacher = data.teacher;
      this.editnow = true;
    },
    async handleDelete(id, name) {
      this.$confirm('Do you really want to delete?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(async () => {
        await resourcePro.destroy(id);
        this.getList();
        this.$message({
          type: 'success',
          message: name+' Delete successfully',
        });
      });
    },
    async onSubmit() {
      if(this.teacher.id != '') {
        await resourcePro.update(this.teacher.id, this.teacher);
        this.editnow = false;
        this.getList();
      } else {
        await resourcePro.store(this.teacher);
        this.editnow = false;
        this.getList();
      }
    },
    async generateCard() {
      let text = "Tahir iqbal";

      await QRCode.toCanvas(document.getElementById('canvas'),
        'sample text', { toSJISFunc: QRCode.toSJIS }, function (error) {
          if (error) {
            console.error(error);
          } else {
            console.log('success!');
          }
        });
      this.showcard = true;
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