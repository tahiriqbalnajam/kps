<script setup>
    import { reactive, getCurrentInstance } from 'vue';
    import { studentAttReport } from '@/api/attendance.js'
    import Resource from '@/api/resource.js';
    import HeadControls from '@/components/HeadControls.vue';
    let stdRes = new Resource('students');
    import * as echarts from 'echarts'
    import { debounce } from 'lodash';
    const $this =  reactive({
                query: {
                    keyword: '',
                    filter: {
                        name: ''
                   },
                },
                students: [],
                attendance: {
                    student_id: '',
                },
                loading: false,
                chart: null,
                chartdata: {
                    months:[],
                    absent:[]
                }
            })
    const selectAttandance = async() => {
        const {data} = await studentAttReport($this.attendance);
        $this.chartdata.months = data.attendance.map(item => item.month);
        $this.chartdata.absent = data.attendance.map(item => item.absent);
        $this.chart.setOption({
            xAxis: {
            data:  $this.chartdata.months
            },
            series: [
            {
                name: 'Absent',
                data: $this.chartdata.absent
            }
            ]
        });
    }
    const debounceInput = debounce(function (e) {
      this.getList();
    }, 500);

    const selectStudent = async(query) => {
        if(query === '')
            return;
        $this.query.filter.name = query;
        const { data } = await stdRes.list($this.query);
        $this.students = data.students.data;
    }
    onMounted(() => {
        nextTick(() => {
            initChart()
        })
    })
    const animationDuration = 6000
    
    const { proxy } = getCurrentInstance()
    const initChart = () => {
    $this.chart = echarts.init(document.getElementById('barchart'))
    $this.chart.setOption({
        tooltip: {
        trigger: 'axis',
        axisPointer: {
            type: 'line' // 默认为直线，可选为：'line' | 'shadow'
        }
        },
        grid: {
        left: '2%',
        right: '2%',
        bottom: '3%',
        containLabel: true
        },
        xAxis: {
            data: $this.chartdata.months
        },
        yAxis: {
            type: 'value'
        },
        series: [
            {
                name: 'Absent',
                type: 'bar',
                data: $this.chartdata.absent
            }
        ]
    })
    }
</script>
<template>
    <div class="app-container">
        <div class="filter-container">
            <head-controls>
                <el-form-item label="Search Student">
                    <el-select
                        v-model="$this.attendance.student_id"
                        filterable
                        remote
                        reserve-keyword
                        default-first-option
                        placeholder="Please enter student name"
                        :remote-method="selectStudent"
                        :loading="$this.loading"
                        @change="selectAttandance"
                        clearable
                        v-on:input="debounceInput"
                    >
                        <el-option
                        v-for="student in $this.students"
                            :key="student.id"
                            :label="student.name + ' - ' +student.stdclasses.name"
                            :value="student.id"
                        />
                    </el-select>
                </el-form-item>
            </head-controls>
        </div>
        <div class="chart" id="barchart" :style="{ height: '300px', width: '100%' }" />
    </div>
</template>