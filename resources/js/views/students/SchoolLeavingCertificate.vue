<template>
    <el-dialog v-model="rdata.dialogVisible" title="School Leaving Certificate" width="850px" top="5vh" custom-class="certificate-dialog">
        <!-- Print Button -->
        <div class="print-actions">
            <el-button type="primary" size="large" @click="printCertificate" plain>
                <i class="el-icon-printer"></i> Print Certificate
            </el-button>
        </div>

        <div class="certificate-wrapper" ref="certificateContent">
            <div class="certificate-container">
                <div class="certificate-border">
                    <!-- Header Section -->
                    <div class="header">
                        <div class="header-content">
                            <img v-if="rdata.settings.school_logo" :src="rdata.settings.school_logo" alt="School Logo" class="school-logo"/>
                            <div class="school-details">
                                <template v-if="rdata.settings.school_name">
                                    <h1 class="school-name">{{ rdata.settings.school_name }}</h1>
                                </template>
                                <template v-if="rdata.settings.tagline">
                                    <div class="school-motto">{{ rdata.settings.tagline }}</div>
                                </template>
                                <div v-if="hasContactInfo" class="contact-info">
                                    <span v-if="rdata.settings.phone"><i class="el-icon-phone"></i> {{ rdata.settings.phone }}</span>
                                    <template v-if="rdata.settings.website"><span class="separator">|</span><span><i class="el-icon-globe"></i> {{ rdata.settings.website }}</span></template>
                                    <span v-if="rdata.settings.address" class="address-block"><span class="separator">|</span><i class="el-icon-location"></i> {{ rdata.settings.address }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="document-title-wrapper">
                        <h2 class="document-title">School Leaving Certificate</h2>
                        <div class="title-decoration"></div>
                    </div>

                    <!-- Certificate Content -->
                    <div class="certificate-body">
                        <div class="student-info-grid">
                            <div class="info-item">
                                <span class="label">Name of Student</span>
                                <span class="value">{{ rdata.student.name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Admission No.</span>
                                <span class="value">{{ rdata.student.adminssion_number }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Father's Name</span>
                                <span class="value">{{ rdata.student.parents?.name }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Date of Admission</span>
                                <span class="value">{{ convertDate(rdata.student.doa) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Date of Birth (Figures)</span>
                                <span class="value">{{ convertDate(rdata.student.dob) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Current Class</span>
                                <span class="value">{{ rdata.student.stdclasses?.name }}</span>
                            </div>
                            <div class="info-item full-width">
                                <span class="label">Date of Birth (Words)</span>
                                <span class="value">{{ convertDateToWords(new Date(rdata.student.dob)) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Date of Leaving School</span>
                                <span class="value">{{ getCurrentDate() }}</span>
                            </div>
                            <div class="info-item">
                                <span class="label">Reason for Leaving</span>
                                <span class="value">On Parent's Request</span>
                            </div>
                        </div>

                        <div class="conduct-section">
                            <p>This is to certify that <strong>{{ rdata.student.name }}</strong> has been a student of good conduct 
                            during {{ rdata.student.gender?.toLowerCase() === 'male' ? 'his' : (rdata.student.gender?.toLowerCase() === 'female' ? 'her' : 'his/her') }} stay in the school. 
                            {{ rdata.student.gender?.toLowerCase() === 'male' ? 'He' : (rdata.student.gender?.toLowerCase() === 'female' ? 'She' : 'He/She') }} bears a good moral character and has shown commendable dedication.</p>
                        </div>

                        <!-- Signature Section -->
                        <div class="signature-section">
                            <div class="signature-box left-sig">
                                <div class="issue-date">
                                    <span class="date-label">Date of Issue:</span> {{ getCurrentDate() }}
                                </div>
                            </div>
                            <div class="signature-box right-sig">
                                <div class="line"></div>
                                <p>Principal Signature</p>
                            </div>
                        </div>
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
                        size: A4 portrait;
                        margin: 0;
                    }
                    @media print {
                        body { 
                            margin: 0; 
                            background: none; 
                        }
                        .certificate-container { 
                            box-shadow: none; 
                            margin: 0;
                            width: 210mm;
                            min-height: 297mm;
                            padding: 10mm;
                            box-sizing: border-box;
                        }
                        .certificate-border {
                            height: calc(297mm - 20mm);
                            box-sizing: border-box;
                        }
                        .print-actions { display: none !important; }
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
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Great+Vibes&family=Montserrat:wght@300;400;500;600&display=swap');

/* Certificate Wrapper */
.certificate-wrapper {
    display: flex;
    justify-content: center;
    padding: 0 0 20px 0;
    background: transparent;
}

/* Print Actions */
.print-actions {
    text-align: right;
    margin-bottom: 20px;
    padding-right: 20px;
}

/* Main Container */
.certificate-container {
    width: 100%;
    max-width: 850px;
    background: #fff;
    padding: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    position: relative;
    border-radius: 4px;
}

/* Inner Border */
.certificate-border {
    border: 10px solid transparent;
    border-image: repeating-linear-gradient(45deg, #b8860b, #b8860b 10px, #daa520 10px, #daa520 20px) 10;
    padding: 40px 50px;
    position: relative;
    background: linear-gradient(135deg, rgba(255,255,255,1) 0%, rgba(250,248,240,1) 100%);
}

.certificate-border::before {
    content: '';
    position: absolute;
    top: 5px; right: 5px; bottom: 5px; left: 5px;
    border: 1px solid #b8860b;
    pointer-events: none;
}

/* Header */
.header {
    text-align: center;
    margin-bottom: 30px;
}

.header-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.school-logo {
    width: 100px;
    height: auto;
    object-fit: contain;
}

.school-details {
    text-align: center;
}

.school-name {
    font-family: 'Cinzel', serif;
    font-size: 32px;
    color: #2c3e50;
    margin: 0 0 5px 0;
    text-transform: uppercase;
    letter-spacing: 2px;
    font-weight: 700;
}

.school-motto {
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    font-style: italic;
    color: #555;
    margin-bottom: 10px;
    letter-spacing: 1px;
}

.contact-info {
    font-family: 'Montserrat', sans-serif;
    font-size: 12px;
    color: #666;
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 10px;
}

.separator {
    color: #b8860b;
    margin: 0 5px;
}

/* Title */
.document-title-wrapper {
    text-align: center;
    margin: 30px 0 40px 0;
}

.document-title {
    font-family: 'Great Vibes', cursive;
    font-size: 48px;
    color: #b8860b;
    margin: 0;
    line-height: 1;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.title-decoration {
    width: 60%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #b8860b, transparent);
    margin: 15px auto 0;
}

/* Grid Layout for Info */
.student-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    column-gap: 50px;
    row-gap: 25px;
    margin-bottom: 40px;
}

.info-item {
    display: flex;
    flex-direction: column;
    border-bottom: 1px solid #e0d8c3;
    padding-bottom: 5px;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.label {
    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    text-transform: uppercase;
    color: #888;
    letter-spacing: 1px;
    margin-bottom: 5px;
}

.value {
    font-family: 'Cinzel', serif;
    font-size: 18px;
    color: #2c3e50;
    font-weight: 600;
}

/* Conduct text */
.conduct-section {
    font-family: 'Montserrat', sans-serif;
    font-size: 16px;
    line-height: 2;
    color: #333;
    text-align: justify;
    margin-bottom: 50px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.5);
    border-left: 4px solid #b8860b;
}

/* Signatures */
.signature-section {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-top: 50px;
}

.signature-box {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.left-sig {
    align-items: flex-start;
}

.right-sig {
    align-items: flex-end;
}

.issue-date {
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    color: #444;
}

.date-label {
    font-weight: 600;
    color: #b8860b;
}

.line {
    width: 200px;
    height: 1px;
    background-color: #333;
    margin-bottom: 10px;
}

.signature-box p {
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    text-transform: uppercase;
    color: #555;
    letter-spacing: 1px;
    margin: 0;
}

/* Stamp area removed */

/* Print Styles */
@media print {
    @page {
        size: A4 portrait;
        margin: 0;
    }

    body {
        background: none;
    }

    .certificate-wrapper {
        padding: 0;
        background: none;
    }

    .certificate-container {
        box-shadow: none;
        max-width: 100%;
        width: 210mm;
        height: 297mm;
        padding: 10mm;
        box-sizing: border-box;
    }

    .certificate-border {
        height: calc(297mm - 20mm);
        box-sizing: border-box;
    }

    .print-actions, :deep(.el-dialog__header), :deep(.el-dialog__close), :deep(.el-dialog__headerbtn) {
        display: none !important;
    }
}
</style>
