
<template>
    <div>
        <el-dialog v-model="rdata.dialogVisible" title="Character Certificate">
            <div>
                <h2 style="text-align: center;">PROVISIONAL & CHARACTER CERTIFICATE</h2>
                <p>Registration No ---------  Session --------</p>
                <p>It is certified that <u>{{ rdata.student.name }}</u> <br>
                S/D/O <span> {{ rdata.student.parents.name }}</span> whose date of birth (In Figures) {{ convertDate(rdata.student.dob) }} 
                (In Words) {{ convertDateToWords(new Date(rdata.student.dob)) }} <br>
                </p>
                <p>has been a regular student of this school. He/She has been declared pass/fail in the Secondary School Certificate Annual Examination ----------- held by the Board of Intermediate and Secondary Education Bahawalpur under Roll No. ---------------- securing --------------/ -------- marks. During his/her studies at this school, I have found him/her ----------------------</p>
                <p>He/She bears <u><b>good</b></u> moral character. May he/she succeed in every walk of life.</p>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import { ref, reactive, onMounted, watch, toRefs } from 'vue';
import Resource from '@/api/resource';
import moment from 'moment';

export default {
    name: 'CharacterCertificate',
    components: {},
    props: {
        showcharactercertificate: Boolean,
        stdid: Number
    },
    setup(props) {
        const { showcharactercertificate, stdid } = toRefs(props);

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
            rdata.dialogVisible = showcharactercertificate.value;
            getStudent(stdid.value);
        });

        // Define watchers
        watch(showcharactercertificate, (newValue, oldValue) => {
            if (newValue !== oldValue) {
                rdata.dialogVisible = newValue;
            }
        });

        const convertDate = (date) => {
            if (!date) 
                return null;

                return moment(date).format('DD-MM-YYYY');
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

                    const day = days[date.getDay()];
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
/* Your component styles here */
</style>
