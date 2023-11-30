
<template>
    <div>
        <el-dialog v-model="rdata.dialogVisible" title="Character Certificate">
            <div>
                <h1 style="text-align: center;">SCHOOL LEAVING CERTIFICATE</h1>
                <p><strong>Name of Student:</strong> {{ rdata.student.name }}</p>
                <p><strong>Father's Name:</strong> {{ rdata.student.parents.name }}</p>
                <p><strong>Date of Birth (in Figures):</strong> {{ convertDate(rdata.student.dob) }}</p>
                <p><strong>Date of Birth (in Words):</strong> {{ convertDate(rdata.student.dob) }}</p>
                <p><strong>Admission No.:</strong> {{ rdata.student.adminssion_number }}   <strong>Date of Admission:</strong> {{ convertDate(rdata.student.created_at) }} </p>
                <p><strong>Class in which admitted:</strong> ---------------------------------------------</p>
                <p><strong>Date of leaving the school:</strong> --------------------------------------</p>
                <p><strong>Class in which studying / Passed / Failed:</strong> --------------------------------------</p>
                <p><strong>Examination:</strong> ------------------------------------------------</p>
                <p><strong>Roll No.:</strong> ------------------------- <strong>Marks Obtained:</strong> ------------------------</p>
                <p><strong>Total Marks:</strong> --------------------------------</p>
                <p>Certified that above mentioned particulars are in accordance with the school record.</p>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import { ref, reactive, onMounted, watch, toRefs } from 'vue';
import Resource from '@/api/resource';
import moment from 'moment';

export default {
    name: 'SchoolLeavingCertificate',
    components: {},
    props: {
        showschoolleavingcertificate: Boolean,
        stdid: Number
    },
    setup(props) {
        const { showschoolleavingcertificate, stdid } = toRefs(props);

        const resource = new Resource('students');
        const rdata = reactive({
            dialogVisible: false,
            student: {},
            query: {
                page: 1,
                per_page: 10,
                filter: {},
            }
        });

        onMounted(() => {
            rdata.dialogVisible = showschoolleavingcertificate.value;
            getStudent(stdid.value);
        });

        // Define watchers
        watch(showschoolleavingcertificate, (newValue, oldValue) => {
            if (newValue !== oldValue) {
                rdata.dialogVisible = newValue;
            }
        });

        const convertDate = (date) => {
            if (!date) 
                return null;

                return moment(date).format('DD/MM/YYYY');
        }

        const convertDateToWords = (date) => {
                if (!date) 
                    return null;

                    const months = [
                        "January", "February", "March", "April", "May", "June", "July",
                        "August", "September", "October", "November", "December"
                    ];

                    const days = [
                        "zero", "one", "two", "three", "four", "five", "six", "seven", "eight", "nine",
                        "ten", "eleven", "twelve", "thirteen", "fourteen", "fifteen", "sixteen", "seventeen", "eighteen", "nineteen",
                        "twenty", "twenty-one", "twenty-two", "twenty-three", "twenty-four", "twenty-five", "twenty-six", "twenty-seven", "twenty-eight", "twenty-nine",
                        "thirty", "thirty-one"
                    ];

                    const day = days[date.getDate()];
                    const month = months[date.getMonth()];
                    const year = date.getFullYear();

                    return `${day} ${month} ${year}`;
                };

        const getStudent = async (id) => {
            rdata.query.filter.id = id;
            const { data } = await resource.list(rdata.query);
            rdata.student = data.students.data[0];
            // Perform the search logic here
            // You can use the searchText value to filter the items or make an API call
        };

        return {
            rdata,
            getStudent,
            convertDate,
            convertDateToWords,
            resource,
        };
    },
};
</script>

<style scoped>

</style>
