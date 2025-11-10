<template>
  <el-drawer
    title="Add/Edit Class"
    :modelValue="addeditclassprop"
    direction="rtl"
    custom-class="demo-drawer"
    ref="drawer"
    @close="cancelAddClass()"
    size="60%"
  >
    <div class="demo-drawer__content">
      <el-form :model="stdclass">
        <el-form-item label="Name" :label-width="formLabelWidth">
          <el-input v-model="stdclass.name" autocomplete="off"></el-input>
        </el-form-item>
        <el-form-item label="Priority" :label-width="formLabelWidth">
          <el-input-number v-model="stdclass.priority" :min="0" :max="9999" placeholder="0"></el-input-number>
          <span style="margin-left: 10px; color: #909399; font-size: 12px;">Lower numbers appear first</span>
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
  emits: ['closeAddClass'],
  data() {
    return {
      closepopup: false,
      formLabelWidth: '250',
      loading: false,
      stdclass: {
        id: '',
        name: '',
        priority: 0,
      },
    };
  },
  mounted: function () {
    this.closepopup = this.addeditclassprop;
  },
  created() {
    //console.log('Created hook - classid:', this.classid, 'Type:', typeof this.classid);
    // Using explicit conversion to number for stronger type checking
    if (this.classid !== null && this.classid !== undefined && Number(this.classid) > 0) {
      //console.log('Calling handleEdit from created hook');
      this.handleEdit();
    }
  },
  watch: {
    // Add watcher for classid to handle when it changes after component creation
    classid(newVal) {
      //console.log('Watcher - classid changed to:', newVal);
      if (newVal !== null && newVal !== undefined && Number(newVal) > 0) {
       // console.log('Calling handleEdit from watcher');
        this.handleEdit();
      } else {
        // Reset form when classid is cleared
        this.stdclass = {
          id: '',
          name: '',
          priority: 0,
        };
      }
    }
  },
  methods: {
    cancelAddClass() {
      this.closepopup = false;
      this.$emit("closeAddClass", "yes") //callback function
    },
    async handleEdit() {
      //console.log('handleEdit called with classid:', this.classid);
      try {
        const { data } = await classesPro.get(this.classid);
        //console.log('API response:', data);
        if (data && data.class) {
          this.stdclass = data.class;
        } else {
          console.error('Class data not found in API response');
        }
      } catch (error) {
        console.error('Error fetching class data:', error);
      }
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
    },
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
