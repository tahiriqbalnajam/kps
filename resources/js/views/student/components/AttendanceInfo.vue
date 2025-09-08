<template>
    <div>
        <el-row :gutter="20">
            <el-col :span="12">
                <div ref="chart" :class="className" :style="{ height: height, width: width }" />
            </el-col>
            <el-col :span="6">
                <el-progress type="dashboard" :percentage=" this.attendance.percent_present" >
                    <template #default="{ percentage }">
                        <span class="percentage-value">{{ percentage }}%</span>
                        <span class="percentage-label">Overall</span>
                    </template>
                </el-progress>
                <el-tag :type="(this.attendance.today_status == 'present') ? 'success' : 'danger'" effect="light">
                    Today: {{ this.attendance.today_status }}
                </el-tag>
            </el-col>
            <el-col :span="6">
                <el-progress type="dashboard" :percentage=" this.attendance.average_present" >
                    <template #default="{ percentage }">
                        <span class="percentage-value">{{ percentage }}%</span>
                        <span class="percentage-label">Overall</span>
                    </template>
                </el-progress>
                <el-tag type="success" effect="light">
                    Yesterday: {{ this.attendance.yesterday_status }}
                </el-tag>
            </el-col>
        </el-row>
        <el-row :gutter="20">
            <el-col :span="8">
                <el-card class="blue">
                    <table class="table" width="100%">
                        <tr>
                            <th colspan="2" class="header">PRESENTS</th>
                        </tr>
                        <tr>
                            <td class="total">Total</td>
                            <td class="total">{{ this.attendance.total_present }}</td>
                        </tr>
                        <tr>
                            <td class="month">This month</td>
                            <td class="month">{{ this.attendance.this_month_present }}</td>
                        </tr>
                    </table>
                </el-card>
            </el-col>
            <el-col :span="8">
                <el-card  class="red">
                    <table class="table" width="100%">
                        <tr>
                            <th colspan="2"  class="header">ABSENTS</th>
                        </tr>
                        <tr>
                            <td class="total">Total</td>
                            <td class="total">{{ this.attendance.total_absent }}</td>
                        </tr>
                        <tr>
                            <td class="month">This month</td>
                            <td class="month">{{ this.attendance.this_month_absent }}</td>
                        </tr>
                    </table>
                </el-card>
            </el-col>
            <el-col :span="8">
                <el-card class="info">
                    <table class="table" width="100%">
                        <tr>
                            <th colspan="2"  class="header">LEAVES</th>
                        </tr>
                        <tr>
                            <td class="total">Total</td>
                            <td class="total">{{ this.attendance.total_leave }}</td>
                        </tr>
                        <tr>
                            <td class="month">This month</td>
                            <td class="month">{{ this.attendance.this_month_leave }}</td>
                        </tr>
                    </table>
                </el-card>
            </el-col>
        </el-row>
    </div>
</template>
<script>
    import * as echarts from 'echarts';
    import Resource from '@/api/resource';
    import { useRoute } from 'vue-router';
    const route = useRoute()
    import {getStudentAttTotals} from '@/api/attendance';
    import {userStore} from "@/store/user";
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
        const useUserStore = userStore()
        const studentId = useUserStore.student.student_id; // Accessing the URL parameter named 'id'
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
  font-size: 18px;
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
.table .header {
    text-align: left;
}
.table .total {
    font-size: 16px;
    font-weight: bold;
}
.table .month {
    font-size: 11px;
}
.blue {
    background: linear-gradient(45deg, #5e81f4, #7191f7)!important;
    color: white;
}
.red {
    background: linear-gradient(45deg, #ff808b, #f79099)!important;
    color: white;
}
.info {
    background: linear-gradient(45deg, #9698d6, #a9abdb) !important;
    color: white;
}
</style>
