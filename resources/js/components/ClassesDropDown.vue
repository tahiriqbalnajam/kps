<script setup>
    import { reactive } from 'vue';
    import Resource from '@/api/resource';

    const classes = new Resource('classes');
    const props = defineProps(['selectmodel'])
    const $this = reactive({
        classes: null,
    })
    const getClasses = async() => {
        const{ data } = await classes.list();
        $this.classes = data.classes.data;
    }
    getClasses();
</script>
<template>
    <el-select :model="props.selectmodel" placeholder="Select" @input="$emit('update:selectmodel', $event)">
        <el-option
          v-for="item in $this.classes"
          :key="item.id"
          :label="item.name"
          :value="item.id">
        </el-option>
      </el-select>
</template>