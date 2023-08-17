<script setup>
    import { reactive } from 'vue';
    import moment from 'moment';
    import Resource from '@/api/resource.js';
    let attRes = new Resource('teacher_attendance');
    const $this =  reactive({
                query: {
                    teacher: '',
                    month: moment().format("YYYY-MM-DD"),
                },
                attendance: {
                    students: [],
                    date: moment(),
                },
            })
    const getList = async() => {
        const {data} = await attRes.list($this.query);
    }
    getList();
</script>


<template>
     <div class="app-container">
        <div class="filter-container">
            <el-select v-model="$this.query.teacher" placeholder="Select class">
                <el-option
                v-for="stdclass in classes"
                :key="stdclass.id"
                :label="stdclass.name"
                :value="stdclass.id"
                />
            </el-select>
            <el-date-picker
                v-model="$this.query.month"
                type="month"
                format="MMM"
                value-format="YYYY-MM-DD"
                placeholder="Pick a month" 
            />
        </div>
        <table class="tblwdborder">
            <tr>
                <th>Teacher Name</th>
                <th v-for="index in 31" :key="index">{{index}}</th>
            </tr>
            <tr v-for="student in attendance.teachers" :key="student.id">
                <td>{{ student.name }}</td>
                <td v-for="att in student.attendance" :key="att.id" :class="{'absent': (att.status == 'absent')}">{{att.status}}</td>
            </tr>
        </table>
    </div>
</template>