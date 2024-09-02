<template>
    <div>
        <el-card shadow="hover">
            <el-descriptions
                :column="1"
                :size="50"
                border
                :style="{boxShadow: 'Light Shadow', borderRadius: 'Large Radius'}"
            >
                <el-descriptions-item label="Name">{{  teacher.name }}</el-descriptions-item>
                <el-descriptions-item label="Father Name">{{ teacher.father_name }}</el-descriptions-item>
                <el-descriptions-item label="DOJ">{{ dateformat(teacher.doj) }}</el-descriptions-item>
                <el-descriptions-item label="Education">{{ teacher.education }}</el-descriptions-item>
                <el-descriptions-item label="Experience">{{ teacher.experience }}</el-descriptions-item>
                <el-descriptions-item label="Gender">{{ teacher.gender }}</el-descriptions-item>
                <el-descriptions-item label="CNIC">{{ teacher.cnic }}</el-descriptions-item>
                <el-descriptions-item label="Address">{{ teacher.address }}</el-descriptions-item>
                <el-descriptions-item label="Phone">{{ teacher.phone }}</el-descriptions-item>
            </el-descriptions>
        </el-card>
    </div>
</template>

<script>
import moment from 'moment';
import Resource from '@/api/resource';
const teachRes = new Resource('teachers');

export default {
    name: 'TeacherInfo',
    data() {
        return {
            teacher:[]
        };
    },
    methods: {
        dateformat: (date) => {
            return (!date) ? '' : moment(date).format('DD MMM, YYYY');
        },
        async getTeacher(id) {
            const {data} = await teachRes.get(id);
            this.teacher = data.teacher[0];
        }
    },
    mounted() {
        const teacherId = this.$route.params.id;
        this.getTeacher(teacherId);
    },
};
</script>

<style scoped>
/* Your component's CSS styles go here */
</style>