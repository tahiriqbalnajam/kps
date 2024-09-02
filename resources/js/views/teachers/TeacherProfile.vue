<template>
    <el-row :gutter="20">
        <el-col :xs="6" :sm="6" :md="6" :lg="6" :xl ="6">
            <teacher-info />
        </el-col>
        <el-col :xs="18" :sm="18" :md="18" :lg="18" :xl ="18">
            <teacher-tests />
        </el-col>
    </el-row>
    
    
</template>

<script>
import TeacherTests from './components/TeacherTests.vue';
import TeacherInfo from './components/TeacherInfo.vue';
export default {
    name: 'TeacherProfile',
    components: { TeacherTests, TeacherInfo },
    data() {
        return {
            // Your component's data goes here
        };
    },
    methods: {
        // Your component's methods go here
    },
    mounted() {
        //const studentId = this.$route.params.id;
    },
};
</script>

<style scoped>
/* Your component's CSS styles go here */
</style>