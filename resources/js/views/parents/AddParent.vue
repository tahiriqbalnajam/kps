<template>
  <el-drawer
    ref="drawer"
    title="Edit Record"
    :modelValue="closepopup"
    :rules="rules"
    direction="ltr"
    custom-class="demo-drawer"
    size="60%"
    @closed="cancelAddParent"
  >
    <div class="demo-drawer__content">
      <el-form :model="parent" label-width="120px" ref="addparentform">
        <el-form-item label="Name"  prop="name">
          <el-input v-model="parent.name" autocomplete="off" />
        </el-form-item>
        <el-form-item label="Phone"  prop="phone">
          <el-input v-model="parent.phone" autocomplete="off" />
        </el-form-item>
        <el-form-item label="Password"  prop="password">
          <el-input v-model="parent.password" autocomplete="off" />
        </el-form-item>
        <el-form-item label="Address"  prop="address">
          <el-input type="textarea" :rows="2" v-model="parent.address" autocomplete="off" />
        </el-form-item>
        <el-form-item label="Profession"  prop="profession">
          <el-input v-model="parent.profession" autocomplete="off" />
        </el-form-item>
        <el-form-item label="CNIC"  prop="cnic">
          <el-input v-model="parent.cnic" autocomplete="off" />
        </el-form-item>
        <el-form-item>
          <el-button @click="cancelAddParent()">Cancel</el-button>
          <el-button type="primary" :loading="loading" @click="onSubmit('addparentform')">
            {{ loading ? 'Submitting ...' : 'Submit Now' }}
          </el-button>
        </el-form-item>
      </el-form>
    </div>
  </el-drawer>
</template>

<script>
import Resource from '@/api/resource';
const parentsPro = new Resource('parents');
export default {
  name: 'AddParent',
  props: {
    editnowprop: {
      type: Boolean,
      required: true,
    },
    parentid: {
      type: Number,
      default: null,
    },
  },
  emits: ['cancelAddParent'],
  data() {
    var name = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please enter the Name'));
      } else {
        callback();
      }
    };
    var phone = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please enter phone like: 03457050405'));
      } else {
        callback();
      }
    };
    var password = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please enter password for parent'));
      } else {
        callback();
      }
    };
    var address = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please enter address'));
      } else {
        callback();
      }
    };
    var profession = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please enter profession.'));
      } else {
        callback();
      }
    };
    var cnic = (rule, value, callback) => {
      if (!value) {
        return callback(new Error('Please enter CNIC.'));
      } else {
        callback();
      }
    };
    return {
      closepopup: false,
      formLabelWidth: '250',
      rules: {
        name: [{ validator: name, trigger: 'blur' }],
        phone: [{ validator: phone, trigger: 'blur' }],
        password: [{ validator: password, trigger: 'blur' }],
        address: [{ validator: address, trigger: 'blur' }],
        profession: [{ validator: profession, trigger: 'blur' }],
        cnic: [{ validator: cnic, trigger: 'blur' }],
      },
      loading: false,
      parent: {
        id: '',
        name: '',
        phone: '',
        password: '',
        address: '',
        profession: '',
        cnic: '',
      },
    };
  },
  mounted: function () {
    this.closepopup = this.editnowprop;
  },
  created() {
    if(this.parentid !== null)
      this.handleEdit();
  },
  methods: {
    cancelAddParent() {
      this.closepopup = false;
      this.$emit('closePopUp', 'yes')
    },
    async handleEdit() {
      const { data } = await parentsPro.get(this.parentid);
      this.parent = data.parent;
    },
    async onSubmit(formName) {
      this.loading = true;
      this.$refs[formName].validate(
        async (valid) => {
          if (valid) {
            if(this.parent.id != '') {
              await parentsPro.update(this.parent.id, this.parent);
              this.cancelAddParent();
            } else {
              this.loading = true;
              await parentsPro.store(this.parent)
                .then(response => {
                  this.$message({
                    type: 'success',
                    message: 'Parent '+ this.parent.name + ' added successfully'
                  });
                  this.loading = false;
                  this.cancelAddParent();
                })
                .catch(error => {
                  this.$message({
                    type: 'error',
                    message: 'Something wrong while adding parent' + error,
                  });
                  this.loading = false;
                })
                .finally(() => {
                  this.loading = false;
                });
            }
          }
        }
      );
    },
  },
};
</script>
<style scoped>
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
