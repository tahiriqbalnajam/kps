<template>
    <el-tabs tab-position="top">
        <el-tab-pane v-for="(classItem, index) in testData" :label="classItem.class">
            <el-row :gutter="20">
                <el-col :xs="8" :sm="8" :md="8" :lg="8" :xl ="8" v-for="(subjects, subjectName) in classItem.subjects" :key="subjectName">
                    <el-card shadow="hover">
                        <h3>{{ subjectName }}</h3>
                        <el-table :data="subjects" border>
                        <el-table-column prop="test_title" label="Test Title"></el-table-column>
                        <el-table-column prop="average_marks" label="Average Marks"></el-table-column>
                        </el-table>
                    </el-card>
                </el-col>
            </el-row>
        </el-tab-pane>
    </el-tabs>
</template>

<script>
import Resource from '@/api/resource';
import { getTests } from '@/api/teacher';

const teachRes = new Resource('teacher');
export default {
    name: 'TeacherTests',
    data() {
        return {
            activeClass: '', // To control which collapse item is open
            testData: [],
            // Your component's data goes here
        };
    },
    mounted() {
        const teacherId = this.$route.params.id;
        this.getTestsAvg(teacherId);
    },
    methods: {
        async getTestsAvg(teacherId) {
            const {data} = await getTests(teacherId);
            this.testData = data.tests;

        }

    },
};
</script>

<style scoped>
/* Your component's CSS styles go here */
</style>