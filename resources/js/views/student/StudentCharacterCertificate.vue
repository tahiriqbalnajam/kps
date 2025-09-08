<template>
    <div>
        <el-dialog v-model="rdata.dialogVisible" title="Character Certificate" width="80%" top="5vh">
            <!-- Add Print Button -->
            <div class="print-actions">
                <el-button type="primary" @click="printCertificate">
                    <i class="el-icon-printer"></i> Print Certificate
                </el-button>
            </div>

            <div class="certificate-container" ref="certificateContent">
                <div class="certificate-content">
                    <!-- Header Section -->
                    <div class="header">
                        <img v-if="rdata.settings.school_logo" :src="rdata.settings.school_logo" alt="School Logo" class="school-logo"/>
                        <template v-if="rdata.settings.school_name">
                            <h1 class="school-name">{{ rdata.settings.school_name }}</h1>
                        </template>
                        <template v-if="rdata.settings.tagline">
                            <div class="school-motto">" {{ rdata.settings.tagline }} "</div>
                        </template>
                        <div v-if="hasContactInfo" class="contact-info">
                            <span v-if="rdata.settings.phone"><i class="el-icon-phone"></i> {{ rdata.settings.phone }}</span>
                            <template v-if="rdata.settings.website">| <span><i class="el-icon-globe"></i> {{ rdata.settings.website }}</span> |</template>
                            <span v-if="rdata.settings.address"><i class="el-icon-location"></i> {{ rdata.settings.address }}</span>
                        </div>
                        <div class="document-title">Character Certificate</div>
                    </div>

                    <!-- Certificate Content -->
                    <div class="certificate-body">
                        <div class="ref-numbers">
                            <p>Registration No: <span class="highlight">{{ rdata.student.adminssion_number || '___________' }}</span></p>
                            <p>Session: <span class="highlight">{{ getCurrentSession() }}</span></p>
                        </div>

                        <div class="main-content">
                            <p>It is certified that <span class="highlight">{{ rdata.student.name }}</span>
                            S/D/O <span class="highlight">{{ rdata.student.parents?.name }}</span> 
                            whose date of birth is <span class="highlight">{{ convertDate(rdata.student.dob) }}</span>
                            (In Words: <span class="highlight">{{ convertDateToWords(new Date(rdata.student.dob)) }}</span>)</p>

                            <p>has been a regular student of this school. {{ rdata.student.gender === 'Male' ? 'He' : 'She' }} bears 
                            <span class="highlight">good</span> moral character.</p>
                            
                            <p>May {{ rdata.student.gender === 'Male' ? 'he' : 'she' }} succeed in every walk of life.</p>
                        </div>

                        <!-- Signature Section -->
                        <div class="signature-section">
                            <div class="signature-box">
                                <div class="line"></div>
                                <p>Principal Signature</p>
                            </div>
                            <div class="signature-box">
                                <div class="line"></div>
                                <p>School Stamp</p>
                            </div>
                        </div>

                        <div class="issue-date">
                            Issue Date: {{ getCurrentDate() }}
                        </div>
                    </div>
                </div>
            </div>
        </el-dialog>
    </div>
</template>

<script>
import { ref, reactive, onMounted, watch, toRefs, computed } from 'vue';
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
        const settingsResource = new Resource('settings');
        const rdata = reactive({
            dialogVisible: false,
            student: {},
            query: {
                page: 1,
                per_page: 10,
                filter: {},
            },
            settings: {},
        });

        onMounted(() => {
            rdata.dialogVisible = showcharactercertificate.value;
            getStudent(stdid.value);
            getSettings();
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

        const hasContactInfo = computed(() => {
            return rdata.settings.phone || rdata.settings.website || rdata.settings.address;
        });

        const getCurrentDate = () => {
            return moment().format('DD MMMM, YYYY');
        };

        const getCurrentSession = () => {
            const year = new Date().getFullYear();
            return `${year}-${year + 1}`;
        };

        const getSettings = async () => {
            try {
                const { data } = await settingsResource.list();
                rdata.settings = data.settings;
            } catch (error) {
                console.error('Failed to load settings:', error);
            }
        };

        const printCertificate = () => {
            const printContent = document.querySelector('.certificate-container').innerHTML;
            const originalContent = document.body.innerHTML;
            
            document.body.innerHTML = `
                <style>
                    @media print {
                        body { margin: 0; padding: 20px; }
                        .print-actions { display: none; }
                        .certificate-container { box-shadow: none; }
                        .el-dialog { margin: 0; padding: 0; width: 100% !important; }
                    }
                </style>
                <div class="certificate-container">${printContent}</div>
            `;
            
            window.print();
            document.body.innerHTML = originalContent;
            // Reactivate Vue
            window.location.reload();
        };

        return {
            rdata,
            getStudent,
            convertDate,
            convertDateToWords,
            resource,
            hasContactInfo,
            getCurrentDate,
            getCurrentSession,
            printCertificate,
        };
    },
};
</script>

<style scoped>
.certificate-container {
    background: #fff;
    padding: 30px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.certificate-content {
    border: 2px solid #333;
    padding: 40px;
    position: relative;
    background: #fff;
}

.header {
    text-align: center;
    margin-bottom: 40px;
}

.school-logo {
    width: 100px;
    height: 100px;
    object-fit: contain;
    margin-bottom: 15px;
}

.school-name {
    font-size: 32px;
    font-weight: bold;
    color: #2c3e50;
    margin: 10px 0;
}

.school-motto {
    font-style: italic;
    color: #7f8c8d;
    font-size: 18px;
    margin: 10px 0;
}

.contact-info {
    display: flex;
    gap: 15px;
    justify-content: center;
    color: #666;
    margin: 15px 0;
}

.document-title {
    font-size: 28px;
    font-weight: bold;
    color: #2980b9;
    margin: 30px 0;
    text-transform: uppercase;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}

.certificate-body {
    font-size: 16px;
    line-height: 1.8;
    color: #333;
}

.ref-numbers {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
}

.highlight {
    color: #2980b9;
    font-weight: 600;
    border-bottom: 1px dotted #bdc3c7;
    padding: 0 5px;
}

.main-content {
    text-align: justify;
    margin: 30px 0;
}

.signature-section {
    display: flex;
    justify-content: space-between;
    margin-top: 60px;
    padding-top: 20px;
}

.signature-box {
    text-align: center;
    width: 200px;
}

.line {
    width: 100%;
    height: 1px;
    background: #000;
    margin-bottom: 10px;
}

.issue-date {
    margin-top: 40px;
    text-align: right;
    font-style: italic;
    color: #666;
}

@media print {
    .el-dialog {
        margin: 0;
        padding: 0;
        width: 100% !important;
    }

    .certificate-container {
        box-shadow: none;
    }
}

.print-actions {
    text-align: right;
    margin-bottom: 20px;
}

@media print {
    .print-actions {
        display: none;
    }
    
    .el-dialog {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        box-shadow: none !important;
    }
    
    .certificate-container {
        padding: 0;
        box-shadow: none;
    }
}

@media print {
    @page {
        size: A4;
        margin: 0;
    }

    body {
        margin: 0;
    }

    .el-dialog {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        box-shadow: none !important;
    }
    
    .certificate-container {
        padding: 15px;
        box-shadow: none;
        width: 210mm;
        min-height: 297mm;
        page-break-after: avoid;
        page-break-inside: avoid;
    }

    .certificate-content {
        padding: 20px;
        border: 1px solid #333;
    }

    .header {
        margin-bottom: 20px;
    }

    .school-logo {
        width: 80px;
        height: 80px;
        margin-bottom: 10px;
    }

    .school-name {
        font-size: 24px;
        margin: 5px 0;
    }

    .school-motto {
        font-size: 16px;
        margin: 5px 0;
    }

    .document-title {
        font-size: 22px;
        margin: 15px 0;
    }

    .certificate-body {
        font-size: 14px;
        line-height: 1.6;
    }

    .signature-section {
        margin-top: 30px;
    }

    .issue-date {
        margin-top: 20px;
    }

    .print-actions {
        display: none;
    }
}
</style>
