<template>
  <div class="app-container">
    <h2>Available Manufacturers</h2>
    <el-table
      :data="list"
      style="width: 100%"
      :loading="loading"
    >
      <el-table-column label="ID" prop="id" />
      <el-table-column label="Name" prop="name" />
      <el-table-column align="right">
        <template slot="header" slot-scope="scope">
          <el-input ref="search" v-model="search" size="mini" placeholder="Type to search" :onkeypress="search_data()" />
        </template>
        <template slot-scope="scope">
          <el-button
            size="mini"
            @click="handledit(scope.row.id, scope.row.name)"
          >Edit</el-button>
          <el-button
            size="mini"
            type="danger"
            @click="handleDelete(scope.row.id, scope.row.name)"
          >Delete</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-dialog :title="title" :visible.sync="outletForm">
      <div class="form-container">
        <el-form
          ref="outlet"
          :model="manufactuer"
          :rules="rules"
          label-position="left"
          label-width="150px"
          style="max-width: 500px;"
        >
          <el-form-item label="Name" prop="name">
            <el-input v-model="manufactuer.name" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="outletForm = false">
            Cancel
          </el-button>
          <el-button type="primary" @click="handleSubmit('outlet')">
            Confirm
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import Resource from '@/api/resource';
import { search_manu } from '@/api/article';
const manuResource = new Resource('manufacturer');
export default {
  name: 'Outlet',
  data() {
    var name = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please input the Outlet Name'));
      } else {
        callback();
      }
    };
    return {
      list: [],
      loading: false,
      outletForm: false,
      search: '',
      title: '',
      value: '',
      datum: '',
      active: 'true',
      inactive: 'false',
      manufactuer: {
        id: '',
        name: '',
      },
      total: 0,
      listQuery: {
        page: 1,
        limit: 4,
        importance: undefined,
        title: undefined,
        type: undefined,
      },
      rules: {
        name: [{ validator: name, trigger: 'blur' }],
      },

    };
  },
  created() {
    this.getList();
  },
  methods: {
    async getList() {
      this.loading = true;
      const { data } = await manuResource.list({});
      this.list = data.manfuacturers;
      this.loading = false;
    },
    handleSubmit(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          if (this.manufactuer.id !== undefined) {
            manuResource
              .update(this.manufactuer.id, this.manufactuer)
              .then(response => {
                this.$message({
                  type: 'success',
                  message: 'Manufactuer info has been updated successfully',
                  duration: 5 * 1000,
                });
                this.getList();
              })
              .catch(error => {
                console.log(error);
              })
              .finally(() => {
                this.outletForm = false;
              });
          } else {
            manuResource
              .store(this.outlet)
              .then(response => {
                this.$message({
                  message:
                    'New Outlet' +
                    this.outlet.name +
                    ' has been created successfully.',
                  type: 'success',
                  duration: 5 * 1000,
                });
                this.outlet = {
                  name: '',
                  status: '',
                };
                this.outletForm = false;
                this.getList();
              })
              .catch(error => {
                console.log(error);
              });
          }
        } else {
          console.log('error submit!!');
          return false;
        }
      });
    },
    handleDelete(id, name) {
      this.$confirm(
        'This will permanently delete Manufacture ' + name + '. Continue?',
        'Warning',
        {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
          type: 'warning',
        }
      )
        .then(() => {
          manuResource
            .destroy(id)
            .then(response => {
              this.$message({
                type: 'success',
                message: 'Delete completed',
              });
              this.getList();
            })
            .catch(error => {
              console.log(error);
            });
        });
    },
    handledit(id, name){
      this.title = 'Edit  ' + name + '  Manufactuer';
      this.manufactuer = this.list.find(manufactuer => manufactuer.id === id);
      console.log(this.manufactuer);
      this.outletForm = true;
    },
    async search_data(){
      this.list = [];
      this.datum = await search_manu(this.$refs.search.value);
      console.log(this.datum);
      this.list = this.datum.data.result;
    },
  },
};
</script>
