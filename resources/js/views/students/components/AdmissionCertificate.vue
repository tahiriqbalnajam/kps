<template>
  <el-dialog title="Admission Certificate" :modelValue="openadcer" width="900px" top="5vh" :before-close="handleClose" custom-class="certificate-dialog">
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
              <img v-if="settings.school_logo" :src="settings.school_logo" alt="School Logo" class="school-logo"/>
              <div class="school-details">
                <template v-if="settings.school_name">
                  <h1 class="school-name">{{ settings.school_name }}</h1>
                </template>
                <template v-if="settings.school_motto">
                  <div class="school-motto">{{ settings.school_motto }}</div>
                </template>
                <div v-if="hasContactInfo" class="contact-info">
                  <span v-if="settings.school_phone"><i class="el-icon-phone"></i> {{ settings.school_phone }}</span>
                  <template v-if="settings.school_website"><span class="separator">|</span><span><i class="el-icon-globe"></i> {{ settings.school_website }}</span></template>
                  <span v-if="settings.school_email" class="address-block"><span class="separator">|</span><i class="el-icon-message"></i> {{ settings.school_email }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="document-title-wrapper">
            <h2 class="document-title">Admission Certificate</h2>
            <div class="title-decoration"></div>
          </div>

          <div class="certificate-body">
            <!-- Grid for Primary Details -->
            <div class="info-section-title">Student Information</div>
            <div class="student-info-grid">
              <div class="info-item">
                <span class="label">Serial No.</span>
                <span class="value">{{ student.id }}</span>
              </div>
              <div class="info-item">
                <span class="label">Registration No.</span>
                <span class="value">{{ student.adminssion_number }}</span>
              </div>
              <div class="info-item">
                <span class="label">Student Name</span>
                <span class="value">{{ student.name }}</span>
              </div>
              <div class="info-item">
                <span class="label">Class Enrolled</span>
                <span class="value">{{ student.stdclasses?.name }}</span>
              </div>
              
              <div class="info-item">
                <span class="label">Date of Birth</span>
                <span class="value">{{ student.dob }}</span>
              </div>
              <div class="info-item">
                <span class="label">B-Form / NIC</span>
                <span class="value">{{ student.b_form }}</span>
              </div>
              <div class="info-item">
                <span class="label">Gender</span>
                <span class="value">{{ student.gender }}</span>
              </div>
              <div class="info-item">
                <span class="label">Religion</span>
                <span class="value">{{ student.religion || '-' }}</span>
              </div>

              <div class="info-item">
                <span class="label">Date of Admission</span>
                <span class="value">{{ student.doa }}</span>
              </div>
              <div class="info-item">
                <span class="label">Discount In Fee</span>
                <span class="value">0 %</span>
              </div>
              <!-- Credentials block (placeholder for logic) -->
              <div class="info-item highlight-box">
                <span class="label">Portal Username</span>
                <span class="value">783338M521</span>
              </div>
              <div class="info-item highlight-box">
                <span class="label">Portal Password</span>
                <span class="value">783338M521</span>
              </div>
            </div>
            
            <div class="info-section-title">Additional Information</div>
            <div class="student-info-grid secondary-grid">
              <div class="info-item">
                <span class="label">Cast</span>
                <span class="value">{{ student.cast || '-' }}</span>
              </div>
               <div class="info-item">
                <span class="label">Previous School</span>
                <span class="value">{{ student.previous_school || '-' }}</span>
              </div>
              <div class="info-item">
                <span class="label">Father's Name</span>
                <span class="value">{{ student.parents?.name }}</span>
              </div>
              <div class="info-item">
                <span class="label">Father's NIC</span>
                <span class="value">{{ student.parents?.nice || '-' }}</span>
              </div>
              <div class="info-item">
                <span class="label">Father's Phone</span>
                <span class="value">{{ student.parents?.phone }}</span>
              </div>
              <div class="info-item">
                <span class="label">Father's Occupation</span>
                <span class="value">{{ student.parents?.profession || '-' }}</span>
              </div>
              <div class="info-item full-width">
                <span class="label">Address</span>
                <span class="value">{{ student.parents?.address }}</span>
              </div>
            </div>

            <!-- Rules -->
            <div class="rules-section" v-if="sanitizedRules">
               <strong class="rules-title">Rules And Regulations:</strong>
               <div v-html="sanitizedRules" class="rules-content"></div>
            </div>

            <!-- Footer -->
            <div class="signature-section">
                <div class="signature-box left-sig">
                    <div class="issue-date">
                        <span class="date-label">Date of Issue:</span> {{ getCurrentDate() }}
                    </div>
                </div>
                <div class="signature-box center-sig">
                    <div class="line"></div>
                    <p>Institute Stamp</p>
                </div>
                <div class="signature-box right-sig">
                    <div class="line"></div>
                    <p>Signature of Authority</p>
                </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </el-dialog>
</template>

<script>
import Resource from '@/api/resource'
import DOMPurify from 'dompurify'
const stdRes = new Resource('students')
const settingsRes = new Resource('settings')

export default {
  name: 'AdmissionCertificate',
  props: {
    openadmitcert: {
      type: Boolean,
      required: true,
    },
    stdid: {
      type: Number,
      default: null,
    },
  },
  data() {
    return {
      openadcer: true,
      student: {},
      local_studentid: null,
      settings: {},
    }
  },
  computed: {
    hasContactInfo() {
      return this.settings.school_phone || 
             this.settings.school_website || 
             this.settings.school_email
    },
    sanitizedRules() {
      return this.settings.admission_rules ? 
        DOMPurify.sanitize(this.settings.admission_rules) : 
        '';
    }
  },
  watch: {
    local_studentid: function(val, oldval) {
      if (val) {
        this.getStudent(val)
      }
    }
  },
  async created() {
    await this.getSchoolSettings()
  },
  mounted() {
    this.openadcer = this.openadmitcert
    this.local_studentid = this.stdid
  },
  methods: {
    async getSchoolSettings() {
      try {
        const { data } = await settingsRes.list()
        this.settings = data.settings
      } catch (error) {
        console.error('Failed to load school settings:', error)
      }
    },
    async getStudent(id) {
      const { data } = await stdRes.get(id);
      this.student = data.student;
    },
    handleClose() {
      this.$emit('closeAdmitCert', 'yes');
    },
    getCurrentDate() {
      return new Date().toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      })
    },
    printCertificate() {
       const printContent = this.$refs.certificateContent.innerHTML;
       const originalContent = document.body.innerHTML;
            
       document.body.innerHTML = `
           <style>
               @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Great+Vibes&family=Montserrat:wght@300;400;500;600&display=swap');
               @page { size: A4 portrait; margin: 0; }
               @media print {
                   body { margin: 0; background: none; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                   .certificate-container { 
                       box-shadow: none !important; 
                       margin: 0 !important;
                       width: 210mm !important;
                       height: 296mm !important;
                       padding: 8mm !important;
                       box-sizing: border-box !important;
                       overflow: hidden !important;
                   }
                   .certificate-border {
                       height: calc(296mm - 16mm) !important;
                       padding: 20px 30px !important;
                       box-sizing: border-box !important;
                   }
                   .header { margin-bottom: 15px !important; }
                   .school-logo { width: 80px !important; height: auto !important; }
                   .school-name { font-size: 26px !important; margin-bottom: 2px !important; }
                   .document-title { font-size: 38px !important; }
                   .document-title-wrapper { margin: 10px 0 20px 0 !important; }
                   .student-info-grid { row-gap: 12px !important; margin-bottom: 15px !important; }
                   .info-section-title { margin-bottom: 10px !important; font-size: 16px !important; padding-bottom: 2px !important; }
                   .label { font-size: 9px !important; margin-bottom: 2px !important; }
                   .value { font-size: 12px !important; }
                   .rules-section { margin-top: 15px !important; padding: 10px 15px !important; }
                   .rules-content p, .rules-content li { font-size: 11px !important; margin: 2px 0 !important; line-height: 1.3 !important; }
                   .rules-content h3 { font-size: 12px !important; margin: 5px 0 !important; }
                   .signature-section { margin-top: 25px !important; }
                   .print-actions { display: none !important; }
               }
           </style>
           <div class="certificate-container">${printContent}</div>
       `;
            
       window.print();
       document.body.innerHTML = originalContent;
       window.location.reload();
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Great+Vibes&family=Montserrat:wght@300;400;500;600&display=swap');

.certificate-wrapper {
    display: flex;
    justify-content: center;
    padding: 0 0 20px 0;
    background: transparent;
}

.print-actions {
    text-align: right;
    margin-bottom: 20px;
    padding-right: 20px;
}

.certificate-container {
    width: 100%;
    max-width: 900px;
    background: #fff;
    padding: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    position: relative;
    border-radius: 4px;
}

.certificate-border {
    border: 10px solid transparent;
    border-image: repeating-linear-gradient(45deg, #185a9d, #185a9d 10px, #43cea2 10px, #43cea2 20px) 10;
    padding: 40px 50px;
    position: relative;
    background: linear-gradient(135deg, rgba(255,255,255,1) 0%, rgba(245,249,255,1) 100%);
}

.certificate-border::before {
    content: '';
    position: absolute;
    top: 5px; right: 5px; bottom: 5px; left: 5px;
    border: 1px solid #185a9d;
    pointer-events: none;
}

.header {
    text-align: center;
    margin-bottom: 25px;
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
    color: #185a9d;
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
    color: #185a9d;
    margin: 0 5px;
}

.document-title-wrapper {
    text-align: center;
    margin: 20px 0 35px 0;
}

.document-title {
    font-family: 'Great Vibes', cursive;
    font-size: 48px;
    color: #185a9d;
    margin: 0;
    line-height: 1;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
}

.title-decoration {
    width: 60%;
    height: 2px;
    background: linear-gradient(90deg, transparent, #185a9d, transparent);
    margin: 15px auto 0;
}

.info-section-title {
    font-family: 'Cinzel', serif;
    font-size: 18px;
    color: #185a9d;
    font-weight: 600;
    border-bottom: 2px solid rgba(24, 90, 157, 0.3);
    padding-bottom: 5px;
    margin-bottom: 20px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Grid Layout */
.student-info-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    column-gap: 20px;
    row-gap: 20px;
    margin-bottom: 30px;
}

.secondary-grid {
    grid-template-columns: repeat(3, 1fr);
}

.info-item {
    display: flex;
    flex-direction: column;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding-bottom: 5px;
}

.info-item.full-width {
    grid-column: 1 / -1;
}

.highlight-box {
    background: rgba(24, 90, 157, 0.05);
    padding: 8px;
    border: 1px dashed rgba(24, 90, 157, 0.3);
    border-radius: 4px;
}

.label {
    font-family: 'Montserrat', sans-serif;
    font-size: 10px;
    text-transform: uppercase;
    color: #888;
    letter-spacing: 1px;
    margin-bottom: 3px;
}

.value {
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    color: #2c3e50;
    font-weight: 600;
}

/* Rules */
.rules-section {
    margin-top: 30px;
    padding: 20px;
    background: rgba(255, 255, 255, 0.6);
    border-left: 4px solid #185a9d;
}

.rules-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    text-transform: uppercase;
    color: #333;
    display: block;
    margin-bottom: 10px;
}

.rules-content :deep(h3) { font-size: 14px; color: #303133; margin: 10px 0 5px; }
.rules-content :deep(ul) { padding-left: 20px; margin: 5px 0; font-family: 'Montserrat', sans-serif; font-size: 13px; color: #555; }
.rules-content :deep(li) { margin: 3px 0; }
.rules-content :deep(strong) { font-weight: 600; color: #303133; }

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

.left-sig { align-items: flex-start; }
.right-sig { align-items: flex-end; }

.issue-date {
    font-family: 'Montserrat', sans-serif;
    font-size: 13px;
    color: #444;
}

.date-label {
    font-weight: 600;
    color: #185a9d;
}

.line {
    width: 200px;
    height: 1px;
    background-color: #333;
    margin-bottom: 10px;
}

.signature-box p {
    font-family: 'Montserrat', sans-serif;
    font-size: 13px;
    text-transform: uppercase;
    color: #555;
    letter-spacing: 1px;
    margin: 0;
}

@media print {
    @page { size: A4 portrait; margin: 0; }
    body, .certificate-wrapper { background: none; margin: 0; padding: 0; }
    .certificate-container { 
        box-shadow: none; 
        max-width: 100%;
        width: 210mm;
        height: 296mm;
        padding: 8mm;
        box-sizing: border-box;
        overflow: hidden;
    }
    .certificate-border {
        height: calc(296mm - 16mm);
        padding: 20px 30px;
        box-sizing: border-box;
    }
    .header { margin-bottom: 15px; }
    .school-logo { width: 80px; }
    .school-name { font-size: 26px; }
    .document-title { font-size: 38px; }
    .document-title-wrapper { margin: 10px 0 20px 0; }
    .student-info-grid { row-gap: 12px; margin-bottom: 15px; }
    .info-section-title { margin-bottom: 10px; font-size: 16px; padding-bottom: 2px; }
    .label { font-size: 9px; margin-bottom: 2px; }
    .value { font-size: 12px; }
    .rules-section { margin-top: 15px; padding: 10px 15px; }
    .rules-content :deep(p), .rules-content :deep(li) { font-size: 11px; margin: 2px 0; line-height: 1.3; }
    .rules-content :deep(h3) { font-size: 12px; margin: 5px 0; }
    .signature-section { margin-top: 25px; }
    .print-actions, :deep(.el-dialog__header), :deep(.el-dialog__close), :deep(.el-dialog__headerbtn) {
        display: none !important;
    }
}
</style>