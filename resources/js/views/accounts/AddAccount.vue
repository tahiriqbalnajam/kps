<template>
  <div>
    <el-dialog title="Add Account" :visible.sync="customerForm">
      <!-- new area form popup -->
      <el-dialog title="Add Area" :visible.sync="addareapop" append-to-body @opened="focusfeild('newareatitle')">
        <el-form ref="addareaform" :rules="rules" :model="newarea" label-width="170px" size="mini" :loading="addarealoading">
          <el-form-item label="Enter Title">
            <el-input ref="newareatitle" v-model="newarea.area_title" clearable />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="addareapop = false">
            Cancel
          </el-button>
          <el-button type="primary" @click="addNewArea()">
            Confirm
          </el-button>
        </div>
      </el-dialog>
      <!-- end of new area popup -->
      <div class="form-container">
        <el-form
          ref="account"
          :model="account"
          :rules="rules"
          label-position="left"
          label-width="120px"
          style="max-width: 600px;"
        >
          <el-form-item label="Name" prop="name">
            <el-input v-model="account.name" />
          </el-form-item>
          <el-form-item label="Phone" prop="phone">
            <el-input v-model="account.phone" />
          </el-form-item>
          <el-form-item label="Account Type">
            <el-radio-group v-model="account.account_type">
              <el-radio :label="1">Customer</el-radio>
              <el-radio :label="2">Supplier</el-radio>
              <el-radio :label="3">Staff/Saleman</el-radio>
              <el-radio :label="4">General</el-radio>
            </el-radio-group>
          </el-form-item>
          <el-form-item v-show="account.account_type == 3" label="Saleman Profit %" prop="Saleman Profit %">
            <el-input v-model="account.saleman_profit" />
          </el-form-item>
          <el-form-item label="Select Area">
            <el-select v-model="account.area_id" filterable placeholder="Select">
              <el-option
                v-for="area in areas"
                :key="area.id"
                :label="area.area_title"
                :value="area.id"
              />
            </el-select>
            <el-button @click="addareapop = true"><svg-icon icon-class="add" /> New Area</el-button>
          </el-form-item>
          <el-form-item label="Address" prop="address">
            <el-input v-model="account.address" />
          </el-form-item>
        </el-form>
        <div slot="footer" class="dialog-footer">
          <el-button @click="customerForm = false; focusfeild('newareatitle')">
            Cancel
          </el-button>
          <el-button type="primary" @click="handleSubmit('account')">
            Confirm
          </el-button>
        </div>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import Resource from '@/api/resource';
import { getAreas } from '@/api/helper';
const customerResource = new Resource('customer');
var areaRes = new Resource('areas');
export default {
  name: 'AddAccount',
  components: { },
  directives: { },
  props: {
    defaultType: {
      type: Number,
      default() {
        return 1;
      },
    },
    accountid: {
      default: null,
      type: Number,
    },
  },
  data() {
    var name = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please input the Name'));
      } else {
        callback();
      }
    };
    var phone = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Phone Number Cannot Blank'));
      } else {
        callback();
      }
    };
    var address = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Address Cannot Blank'));
      } else {
        callback();
      }
    };
    return {
      customerForm: true,
      addareapop: false,
      addarealoading: false,
      accounts: {
        name: '',
        account_type: 1,
        phone: '',
        address: '',
        status: '',
        title: '',
        area_id: '',
      },
      account: {
        name: '',
        phone: '',
        address: '',
        account_type: '',
        saleman_profit: '',
      },
      areas: [],
      newarea: {
        area_title: '',
      },
      rules: {
        name: [{ validator: name, trigger: 'blur' }],
        phone: [{ validator: phone, trigger: 'blur' }],
        address: [{ validator: address, trigger: 'blur' }],
      },
    };
  },
  computed: {
  },
  watch: {
    customerForm: {
      handler: function(val, oldval) {
        this.tellToParent();
      },
    },
  },
  created() {
    if (this.accountid !== null){
      this.getAccount();
    }
    this.getAreas();
  },
  mounted() {
    this.account.account_type = this.defaultType;
  },
  methods: {
    async getAccount() {
      const { data } = await customerResource.get(this.accountid);
      this.account = data.account;
      this.account.account_type = this.account.account_type.id;
    },
    tellToParent() {
      this.$emit('addcustomer', this.customerForm);
    },
    handleSubmit(formName) {
      this.$refs[formName].validate(valid => {
        if (valid) {
          if (this.account.id !== undefined && this.account.id !== null) {
            console.log('goging to put');
            customerResource
              .update(this.account.id, this.account)
              .then(response => {
                this.$message({
                  type: 'success',
                  message: 'Customer info has been updated successfully',
                  duration: 5 * 1000,
                });
                this.getList();
              })
              .catch(error => {
                console.log(error);
              })
              .finally(() => {
                this.customerForm = false;
              });
          } else {
            customerResource
              .store(this.account)
              .then(response => {
                this.$message({
                  message:
                    'New Customer  ' +
                    this.account.name +
                    ' has been created successfully.',
                  type: 'success',
                  duration: 5 * 1000,
                });
                this.account = {
                  name: '',
                  phone: '',
                  address: '',
                  type: '',
                };
                this.$emit('newcustomer', response);
                this.customerForm = false;
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
      this.tellToParent();
    },
    async getAreas() {
      const areas = await areaRes.list();
      this.areas = areas.data.areas;
    },
    async addNewArea() {
      const { data } = await areaRes.store(this.newarea);
      this.addareapop = false;
      this.getAreas();
      this.account.area_id = data.area.id;
    },
  },
};
</script>
<style  scoped>
</style>
