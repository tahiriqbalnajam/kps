<template>
  <el-select v-model="selectedClass" placeholder="Select class" @change="onClassChange">
    <el-option
      v-for="stdclass in classes"
      :key="stdclass.id"
      :label="stdclass.name"
      :value="stdclass.id"
    />
  </el-select>
</template>

<script>
import Resource from '@/api/resource';
const classPro = new Resource('classes');
export default {
  props: {
  },
  data() {
    return {
      selectedClass: null,
      classes: [],

    };
  },
  methods: {
    async getClasses() {
      const { data } = await classPro.list(this.query);
      this.classes = data.classes.data;
      
    },
    onClassChange(value) {
      this.$emit('class-changed', value);
    },
  },
};
</script>