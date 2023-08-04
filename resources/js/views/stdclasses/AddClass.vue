<template>
  <el-drawer
    title="Add/Edit Class"
    :visible.sync="closepopup"
    direction="rtl"
    custom-class="demo-drawer"
    ref="drawer"
    @close="cancelAddClass()"
    size="60%">
    <div class="demo-drawer__content">
      <el-form :model="stdclass">
        <el-form-item label="Name" :label-width="formLabelWidth">
          <el-input v-model="stdclass.name" autocomplete="off"></el-input>
        </el-form-item>
      </el-form>
      <div class="demo-drawer__footer">
        <el-button @click="cancelAddClass()">Cancel</el-button>
        <el-button type="primary" @click="onSubmit" :loading="loading">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
      </div>
    </div>
  </el-drawer>
</template>
<script>
import Resource from '@/api/resource';
const classesPro = new Resource('classes');
export default {
  name: 'AddClass',
  props: {
    addeditclassprop: {
      type: Boolean,
      required: true,
    },
    classid: {
      type: Number,
      default: null,
    },
  },
  data() {
    return {
      closepopup: false,
      formLabelWidth: '250',
      loading: false,
      stdclass: {
        id: '',
        name: '',
      },
    };
  },
  mounted: function () {
    this.closepopup = this.addeditclassprop;
  },
  created() {
    if(this.classid !== null)
      this.handleEdit();
  },
  methods: {
    cancelAddClass() {
      this.closepopup = false;
      this.$emit("closeAddClass", "yes") //callback function
    },
    async handleEdit() {
      const { data } = await classesPro.get(this.classid);
      this.stdclass = data.class;
    },
    async onSubmit(formName) {
      this.loading = true;
      if(this.stdclass.id != '') {
        await classesPro.update(this.stdclass.id, this.stdclass);
        this.editnow = false;
        this.cancelAddClass();
      } else {
        const { data } = await classesPro.store(this.stdclass);
        this.loading = false;
        this.closepopup = false;
        this.cancelAddClass();
      }
      this.loading = false;
    }
  }
}
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

