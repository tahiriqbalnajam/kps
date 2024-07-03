<template>
    <el-row :gutter="20">
        <el-col :span="8">
            <div ref="chart" :class="className" :style="{ height: height, width: width }" />
        </el-col>
        <el-col :span="8">
            <el-card>
                <el-progress type="dashboard" :percentage=" this.attendance.percent_present" >
                    <template #default="{ percentage }">
                        <span class="percentage-value">{{ percentage }}%</span>
                        <span class="percentage-label">Overall</span>
                    </template>
                </el-progress>
            </el-card>
        </el-col>
        <el-col :span="8">
            <el-card>
            <div class="box-center">
                <div class="user-name">afa</div>
                <div class="user-role">asdfa</div>
            </div>
            </el-card>
        </el-col>   
    </el-row>
</template>
<script>
    import * as echarts from 'echarts';
    import Resource from '@/api/resource';
    import { useRoute } from 'vue-router';
    const route = useRoute()
    import {getStudentAttTotals} from '@/api/attendance';
    const attendance = new Resource('attendances');
    export default {
        name: 'StudentInfo',
        components: {
        },
        data() {
        return {
            className: 'chart',
            width: '100%',
            height: '300px',
            chart: null,
            attendance: {},
            query: {
                id: '',
            }
        };
    },
    mounted() {
        const { proxy } = getCurrentInstance();
        const studentId = this.$route.params.id; // Accessing the URL parameter named 'id'
        nextTick(() => {
            this.initChart(proxy)
        })

        this.getAttendance(studentId);

    },
    methods: {
        async getAttendance(stdid) {
            let { data } = await getStudentAttTotals(stdid);
            this.attendance = data.attendance;
            this.chart.setOption({
                tooltip: {
                trigger: 'item',
                formatter: '{a} <br/>{b} : {c} ({d}%)'
                },
                toolbox: {
                show: true,
                feature: {
                    mark: { show: true },
                    dataView: { show: true, readOnly: false },
                    restore: { show: true },
                    saveAsImage: { show: true }
                }
                },
                legend: {
                left: 'center',
                top: 'bottom',
                data: ['P', 'L', 'A']
                },
                series: [
                {
                    name: 'Attendance Details',
                    type: 'pie',
                    roseType: 'area',
                    radius: [10, 60],
                    data: [
                    { value: this.attendance.total_present, name: 'P' },
                    { value: this.attendance.total_absent, name: 'A' },
                    { value: this.attendance.total_leave, name: 'L' },
                    ],
                    animationEasing: 'cubicInOut',
                    animationDuration: 2600
                }
                ]
            })
        },
        initChart(proxy){
            this.chart = echarts.init(this.$refs.chart, 'macarons');
        }
    }
  };
</script>

<style scoped>
.percentage-value {
  display: block;
  margin-top: 10px;
  font-size: 28px;
}
.percentage-label {
  display: block;
  margin-top: 10px;
  font-size: 12px;
}
.demo-progress .el-progress--line {
  margin-bottom: 15px;
  max-width: 600px;
}
.demo-progress .el-progress--circle {
  margin-right: 15px;
}
</style>