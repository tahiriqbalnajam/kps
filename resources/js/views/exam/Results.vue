<script setup>
    import { reactive, onMounted, computed } from 'vue';
    import ClassesDropDown from '@/components/ClassesDropDown.vue';
    const $this = reactive({
                class_subject: {
                    class_id: ''
                },
                activeName: 'classsubject',
            });
    const handleClick = (tab , event) => {
        console.log(tab, event)
    }
</script>
<template>
    <div class="app-container">
        <el-tabs v-model="$this.activeName" class="demo-tabs" @tab-click="handleClick">
            <el-tab-pane label="Class/Subject" name="classsubject">
                <div class="filter-container">
                    <head-controls>
                        <el-form-item label="Select Class">
                            <el-col :span="4">
                               <ClassesDropDown :selectmodel.sync="$this.class_subject.class_id" />
                            </el-col>
                            <el-col :span="4">
                                <el-tooltip :content="tooltipContent" placement="top">
                                <el-button type="primary" @click="generate_pay()">
                                    <el-icon><Money /></el-icon>
                                </el-button>
                            </el-tooltip>
                            </el-col>
                        </el-form-item>
                    </head-controls>
                </div>    
            </el-tab-pane>
            <el-tab-pane label="Config" name="second">Config</el-tab-pane>
            <el-tab-pane label="Role" name="third">Role</el-tab-pane>
            <el-tab-pane label="Task" name="fourth">Task</el-tab-pane>
        </el-tabs>
    </div>
</template>