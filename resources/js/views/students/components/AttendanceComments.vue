<template>
    <el-card style="margin-top: 10px;">
        <el-table :data="comments" border stripe size="small" empty-text="Sorry, no comment">
            <el-table-column label="Date">
                <template #default="scope">
                    {{ dateformat(scope.row.attendance_date)}}
                </template>
            </el-table-column>
            <el-table-column label="Comments" prop="comment" />
        </el-table>        
    </el-card>
</template>
<script>
    import moment from 'moment';
    import Resource from '@/api/resource';
    import { useRoute } from 'vue-router';
    const route = useRoute();
    import {getAttComments} from '@/api/attendance';
    const attendance = new Resource('attendances');
    export default {
        name: 'AttendanceComments',
        components: {
        },
        data() {
        return {
            comments: [],
            query: {
                id: '',
            }
        };
    },
    mounted() {
        const { proxy } = getCurrentInstance();
        const studentId = this.$route.params.id; // Accessing the URL parameter named 'id'
        this.getComments(studentId);

    },
    methods: {
        async getComments(stdid) {
            let { data } = await getAttComments(stdid);
            this.comments = data.comments;
        },
        dateformat: (date) => {
            return (!date) ? '' : moment(date).format('DD MMM, YYYY');
        },
    }
  };
</script>

<style scoped>
</style>