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
                <el-row v-for="student in $this.data" justify="end" class="student-data">
                    <el-col :span="12" class="classname">{{ student.class_name }}</el-col>
                    <el-col :span="12" class="classname">Total: {{ student.absent_students.length }}</el-col>
                    <el-col :span="24" class="studen-list">
                        <el-row v-for="std in student.absent_students" class="list-item">
                            <el-col :span="4">{{ std.name }}</el-col>
                            <el-col :span="4">{{ std.parent_name }}</el-col>
                            <el-col :span="4">{{ std.phone }}</el-col>
                            <el-col :span="4"><el-button type="primary" size="small" @click="openAddCommentDialog(std.attendace_id)">Add Comment</el-button></el-col>
                            <el-col :span="8">{{ std.comment }}</el-col>
                        </el-row>
                    </el-col>
                </el-row>
            </el-scrollbar>
        </el-card>
        <el-dialog
            v-model="$this.openAddComment"
            title="Notice"
            width="500"
            destroy-on-close
            center
        >
            <div>
                <el-input type="textarea" :rows="2" v-model="$this.comment.comment" placeholder="Enter comment" 
                :maxlength="200" show-word-limit :autosize="{ minRows: 2, maxRows: 4 }">
                </el-input>
            </div>
            <template #footer>
                <div class="dialog-footer">
                    <el-button @click="centerDialogVisible = false">Cancel</el-button>
                    <el-button type="primary" @click="addComment()">
                        Add Comment
                    </el-button>
                </div>
            </template>
        </el-dialog>
    </div>
</template>

<script setup>
    import { reactive } from 'vue';
    import HeadControls from '@/components/HeadControls.vue';
    import { absentForeachClass } from '@/api/attendance.js';
    import { addAbsentComment } from '@/api/attendance.js';
    const $this =  reactive({
                data: null,
                openAddComment: false,
                query: {
                    date: new Date().toISOString().split('T')[0]
                },
                comment: {
                    attendance_id: null,
                    student_id: null,
                    comment: null
                }
            })
    const getList = async() => {
        const { data } = await absentForeachClass($this.query);
        $this.data = data.class_student;
    }
    const openAddCommentDialog = (attendace_id) => {
        $this.openAddComment = true;
        $this.comment.attendance_id = attendace_id;

    }
    const addComment = async() => {
        const { data } = await addAbsentComment($this.comment);
        $this.openAddComment = false;
        getList();
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
.list-item {
    padding: 5px;
    border-bottom: 1px solid #0064ff52;
}
.list-item:nth-child(even) {
    background-color: #f2f2f2; /* Alternate background color for even list items */
}

.list-item:nth-child(odd) {
    background-color: #ffffff; /* Default background color for odd list items */
}



</style>