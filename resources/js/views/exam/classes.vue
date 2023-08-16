<script>
import Resource from '@/api/resource.js';
const classes = new Resource('classes');

export default {
  data() {
    return {
      stdid: null,
      list: null,
      listloading: false,
      classes: null,
      formInline: {
        name: '',
        class: '',
      },
    };
  },
  created() {
    this.getClasses();
  },
  methods: {
    async getClasses() {
      const{ data } = await classes.list();
      this.classes = data.classes.data;
    },
  }
};


</script>
<template>
  <el-form :inline="true" :model="formInline" class="demo-form-inline">
    <div class="app-container">
      <div class="filter-container">
        <el-select v-model="formInline.class" placeholder="Class" clearable style="width: 130px">
          <el-option v-for="item in classes" :key="item.id" :label="item.name | uppercaseFirst" :value="item.id" />
        </el-select>
      </div>
    </div>
  </el-form>
</template>