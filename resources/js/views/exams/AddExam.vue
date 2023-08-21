<template>
  <el-drawer
    title="Add/Edit Exam"
    :visible.sync="closepopup"
    direction="rtl"
    custom-class="demo-drawer"
    ref="drawer"
    @close="cancelAddExam()"
    size="60%"
  >
    <div class="demo-drawer__content">
      <el-form :model="exam">
        <el-form-item label="Name" :label-width="formLabelWidth">
          <el-input v-model="exam.title" autocomplete="off"></el-input>
        </el-form-item>
      </el-form>
      <div class="demo-drawer__footer">
        <el-button @click="cancelAddExam()">Cancel</el-button>
        <el-button type="primary" @click="onSubmit" :loading="loading">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
      </div>
    </div>
  </el-drawer>
</template>

<script>
import Resource from '@/api/resource';
const examsPro = new Resource('exams');
export default {
  name: 'AddExam',
  props: {
    addexamprop: {
      type: Boolean,
      required: true,
    },
    examid: {
      type: Number,
      default: null,
    },
  },
  data() {
    return {
      closepopup: false,
      formLabelWidth: '250',
      loading: false,
      exam: {
        id: '',
        title: '',
      },
      resetexam: {
        id: '',
        title: '',
      },
    };
  },
  mounted: function () {
    this.closepopup = this.addexamprop;
  },
  created() {
    if(this.examid !== null)
      this.handleEdit();
  },
  methods: {
    cancelAddExam() {
      this.examid = null;
      this.closepopup = false;
      this.exam = this.resetexam;
      this.$emit("closeAddExam", "yes") //callback function
    },
    async handleEdit() {
      const { data } = await examsPro.get(this.examid);
      this.exam = data.exam;
    },
    async onSubmit(formName) {
      this.loading = true;
      if(this.exam.id !== '') {
        await examsPro.update(this.exam.id, this.exam);
        this.editnow = false;
        this.cancelAddClass();
      } else {
        const { data } = await examsPro.store(this.exam);
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
