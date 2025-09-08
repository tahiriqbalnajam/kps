<template>
    <el-card>
        <div class="user-profile">
            <div class="box-center">
                <div class="user-name text-center">
                    {{ student.name }}
                </div>
            </div>
            <div class="box-social">
                <el-descriptions
                    :column="1"
                    :size="50"
                    :style="{boxShadow: 'Light Shadow', borderRadius: 'Large Radius'}"
                    border
                >
                    <el-descriptions-item label="Father Name">{{ student.parents.name }}</el-descriptions-item>
                    <el-descriptions-item label="Address">{{ student.parents.address }}</el-descriptions-item>
                    <el-descriptions-item label="Registration No">{{ student.adminssion_number }}</el-descriptions-item>
                    <el-descriptions-item label="Date of Admission">{{ student.doa }}</el-descriptions-item>
                    <el-descriptions-item label="Class">{{ student.stdclasses.name }}</el-descriptions-item>
                    <el-descriptions-item label="Date Of Birth">{{ student.dob }}</el-descriptions-item>
                    <el-descriptions-item label="Gender">{{ student.gender }}</el-descriptions-item>
                    <el-descriptions-item label="Student Birth Form ID / NIC">{{
                            student.b_form
                        }}
                    </el-descriptions-item>
                    <el-descriptions-item label="Cast">{{ student.cast }}</el-descriptions-item>
                    <el-descriptions-item label="Previous School">{{ student.previous_school }}</el-descriptions-item>
                    <el-descriptions-item label="Orphan">{{ student.is_orphan }}</el-descriptions-item>
                    <el-descriptions-item label="Religion">{{ student.religion }}</el-descriptions-item>
                </el-descriptions>
            </div>
        </div>
    </el-card>
</template>
<script>
import Resource from '@/api/resource';
import {useRoute} from 'vue-router';
import {userStore} from "@/store/user";

const route = useRoute()
const student = new Resource('students');
const parent = new Resource('parents');
export default {
    name: 'StudentInfo',
    components: {},
    data() {
        return {
            student: {
                parents: {},
                stdclasses: {}
            },
            parent: {},
            query: {
                studentid: '',
            }
        };
    },
    mounted() {
        const useUserStore = userStore()
        const studentId = useUserStore.student.student_id; // Accessing the URL parameter named 'id'
        this.getProfile(studentId);
    },
    methods: {
        async getProfile(stdid) {
            let {data} = await student.get(stdid);
            this.student = data.student;
        }
    }
};
</script>

<style lang="scss" scoped>
.user-profile {
    .user-name {
        font-weight: bold;
    }

    .box-center {
        padding-top: 10px;
    }

    .user-role {
        padding-top: 10px;
        font-weight: 400;
        font-size: 14px;
    }

    .box-social {
        padding-top: 30px;

        .el-table {
            border-top: 1px solid #dfe6ec;
        }
    }

    .user-follow {
        padding-top: 20px;
    }
}
</style>
