<template>
    <el-dialog v-model="rdata.dialogVisible" title="School Leaving Certificate" width="80%" top="5vh">
        <!-- Print Button -->
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
                    <div class="document-title">School Leaving Certificate</div>
                </div>

                <!-- Certificate Content -->
                <div class="certificate-body">
                    <div class="student-info">
                        <div class="info-row">
                            <div class="info-group">
                                <span class="label">Name of Student:</span>
                                <span class="value">{{ rdata.student.name }}</span>
                            </div>
                            <div class="info-group">
                                <span class="label">Admission No:</span>
                                <span class="value">{{ rdata.student.adminssion_number }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-group">
                                <span class="label">Father's Name:</span>
                                <span class="value">{{ rdata.student.parents?.name }}</span>
                            </div>
                            <div class="info-group">
                                <span class="label">Date of Admission:</span>
                                <span class="value">{{ convertDate(rdata.student.doa) }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-group">
                                <span class="label">Date of Birth (in Figures):</span>
                                <span class="value">{{ convertDate(rdata.student.dob) }}</span>
                            </div>
                            <div class="info-group">
                                <span class="label">Current Class:</span>
                                <span class="value">{{ rdata.student.stdclasses?.name }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-group full-width">
                                <span class="label">Date of Birth (in Words):</span>
                                <span class="value">{{ convertDateToWords(new Date(rdata.student.dob)) }}</span>
                            </div>
                        </div>

                        <div class="info-row">
                            <div class="info-group">
                                <span class="label">Date of Leaving School:</span>
                                <span class="value">{{ getCurrentDate() }}</span>
                            </div>
                            <div class="info-group">
                                <span class="label">Reason for Leaving:</span>
                                <span class="value">On Parent's Request</span>
                            </div>
                        </div>

                        <div class="conduct-section">
                            <p>This is to certify that {{ rdata.student.name }} has been a student of good conduct 
                            during {{ rdata.student.gender === 'male' ? 'his' : 'her' }} stay in the school. 
                            {{ rdata.student.gender === 'male' ? 'He' : 'She' }} bears a good moral character.</p>
                        </div>
                    </div>

                    <!-- Signature Section -->
                    <div class="signature-section">
                        <div class="signature-box">
                            <div class="line"></div>
                            <p>Class Teacher</p>
                        </div>
                        <div class="signature-box">
                            <div class="line"></div>
                            <p>Principal</p>
                        </div>
                        <div class="signature-box">
                            <div class="stamp-area">School Stamp</div>
                        </div>
                    </div>

                    <div class="issue-date">
                        Issued on: {{ getCurrentDate() }}
                    </div>
                </div>
            </div>
        </div>
    </el-dialog>
</template>

<script>
import { ref, reactive, onMounted, watch, toRefs, computed } from 'vue';
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
        const settingsResource = new Resource('settings');
        const rdata = reactive({
            dialogVisible: false,
            student: {},
            settings: {},
            query: {
                page: 1,
                per_page: 10,
                filter: {},
            }
        });

        onMounted(() => {
            rdata.dialogVisible = showschoolleavingcertificate.value;
            getStudent(stdid.value);
            getSettings();
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

        const getCurrentDate = () => {
            return moment().format('DD MMMM, YYYY');
        };
        
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

        const hasContactInfo = computed(() => {
            return rdata.settings.phone || rdata.settings.website || rdata.settings.address;
        });

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
                    @page {
                        size: A4;
                        margin: 0;
                    }
                    @media print {
                        body { margin: 0; padding: 20px; }
                        .certificate-container { box-shadow: none; }
                        .print-actions { display: none; }
                    }
                </style>
                <div class="certificate-container">${printContent}</div>
            `;
            
            window.print();
            document.body.innerHTML = originalContent;
            window.location.reload();
        };

        return {
            rdata,
            getStudent,
            convertDate,
            convertDateToWords,
            resource,
            hasContactInfo,
            printCertificate,
            getCurrentDate,  // Add this line
        };
    },
};
</script>

<style scoped>
.student-info {
    margin: 30px 0;
}

.info-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    gap: 20px;
}

.info-group {
    flex: 1;
}

.info-group.full-width {
    flex: 0 0 100%;
}

.label {
    color: #666;
    font-size: 14px;
    display: block;
    margin-bottom: 5px;
}

.value {
    color: #2c3e50;
    font-size: 16px;
    font-weight: 500;
    border-bottom: 1px dotted #bdc3c7;
    padding: 3px 0;
    display: block;
}

.conduct-section {
    margin: 40px 0;
    text-align: justify;
    line-height: 1.8;
    font-size: 16px;
}

.signature-section {
    display: flex;
    justify-content: space-between;
    margin-top: 60px;
    gap: 30px;
}

.stamp-area {
    border: 2px dashed #999;
    width: 150px;
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #666;
    font-style: italic;
}

@media print {
    @page {
        size: A4;
        margin: 0;
    }

    .certificate-container {
        padding: 15px;
        width: 210mm;
        min-height: 297mm;
        page-break-after: avoid;
        page-break-inside: avoid;
    }

    .certificate-content {
        padding: 20px;
    }

    .school-logo {
        width: 80px;
        height: 80px;
    }

    .school-name {
        font-size: 24px;
    }

    .document-title {
        font-size: 22px;
        margin: 15px 0;
    }

    .info-row {
        margin-bottom: 15px;
    }

    .label {
        font-size: 12px;
    }

    .value {
        font-size: 14px;
    }

    .conduct-section {
        font-size: 14px;
        line-height: 1.6;
        margin: 30px 0;
    }

    .print-actions {
        display: none;
    }
}
</style>
