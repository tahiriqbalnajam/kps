<template>
    <div class="app-container">
        <div class="filter-container">
            <head-controls>
                <el-row :gutter="20" justify="space-between">
                    <el-col :span="12">
                        <el-row :gutter="20">
                            <el-col :span="12">
                                <el-form-item>
                                <el-date-picker
                                    v-model="$this.query.date"
                                    type="date"
                                    format="DD MMM, YYYY"
                                    value-format="YYYY-MM-DD"
                                    placeholder="Pick a day" 
                                />
                                </el-form-item>
                            </el-col>
                            <el-col :span="12">
                                <el-button type="primary" :loading="loading"  @click="getList">
                                    {{ loading ? 'Submitting ...' : 'Show Report' }}
                                </el-button>
                            </el-col>
                        </el-row> 
                    </el-col>
                </el-row>
            </head-controls>           
        </div>
        <el-card shadow="always">
            <el-scrollbar height="700px">
                <el-row v-for="student in $this.data" justify="end">
                    <el-col :span="12" class="classname">{{ student.class_name }}</el-col>
                    <el-col :span="12" class="classname">Total: {{ student.absent_students.length }}</el-col>
                    <el-col :span="24" class="studen-list">
                        <el-row v-for="std in student.absent_students">
                            <el-col :span="8">{{ std.name }}</el-col>
                            <el-col :span="8">{{ std.parent_name }}</el-col>
                            <el-col :span="8">{{ std.phone }}</el-col>
                        </el-row>
                    </el-col>
                </el-row>
            </el-scrollbar>
        </el-card>
    </div>
</template>

<script setup>
    import { reactive } from 'vue';
    import HeadControls from '@/components/HeadControls.vue';
    import { absentForeachClass } from '@/api/attendance.js';
    const $this =  reactive({
                data: null,
                query: {
                    date: new Date().toISOString().split('T')[0]
                }
            })
    const getList = async() => {
        const { data } = await absentForeachClass($this.query);
        console.log(data.class_student)
        $this.data = data.class_student;
    }
    getList();
</script>

<style scoped>
.classname {
    background: #0064ffbf;
    padding: 5px;
    font-size: 15px;
    color: white;
    border-radius: 5px 5px 0 0;
    font-weight: bold;
}

.studen-list {
    border: 1px solid #0064ff52;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 0 0 5px 5px;
}

</style>