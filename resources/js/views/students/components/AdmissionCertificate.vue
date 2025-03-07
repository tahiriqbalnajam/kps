<template>
  <el-dialog title="Admission Certificate" :modelValue="openadcer" width="80%" top="5vh" :before-close="handleClose">
    <div class="certificate-container">
      <div class="certificate-content">
        <div class="header">
          <img v-if="settings.school_logo" :src="settings.school_logo" alt="School Logo" class="school-logo"/>
          <template v-if="settings.school_name">
            <h1 class="school-name">{{ settings.school_name }}</h1>
          </template>
          <template v-if="settings.school_motto">
            <div class="school-motto">" {{ settings.school_motto }} "</div>
          </template>
          <div v-if="hasContactInfo" class="contact-info">
            <span v-if="settings.school_phone"><i class="el-icon-phone"></i> {{ settings.school_phone }}</span>
            <template v-if="settings.school_website">| <span><i class="el-icon-globe"></i> {{ settings.school_website }}</span> |</template>
            <span v-if="settings.school_email"><i class="el-icon-message"></i> {{ settings.school_email }}</span>
          </div>
          <div class="document-title">Admission Letter</div>
        </div>

        <div class="module-body" style="color: #555">
          <div class="row-fluid p-20 p-t-0 p-b-10" style="background: white; border-bottom: 1px solid #999">
            <table style="width: 100%">
              <tbody>
                <tr style="border: none">
                  <td>
                    no image
                  </td>
                  <td>
                    <div style="position: relative">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Serial No</span><br />
                      <strong class="f-12 m-l-20">{{ student.id }}</strong>
                    </div>
                    <div style="position: relative">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Registration No</span><br />
                      <strong class="f-12 m-l-20 m-dblue">{{ student.adminssion_number }}</strong>
                    </div>
                    <div style="position: relative">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Student Name</span><br />
                      <strong class="f-12 m-l-20">{{ student.name }}</strong>
                    </div>
                    <div style="position: relative">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Class</span><br />
                      <strong class="f-12 m-l-20">{{ student.stdclasses.name }}</strong>
                    </div>
                  </td>
                  <td>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Date Of Birth</span><br />
                      <strong class="f-12 m-l-20">{{ student.dob }}</strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Student Birth Form ID / NIC</span><br />
                      <strong class="f-12 m-l-20">{{ student.b_form }}</strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Gender</span><br />
                      <strong class="f-12 m-l-20">{{ student.gender }}</strong>
                    </div>
                    <div style="position: relative" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Religion</span><br />
                      <strong class="f-12 m-l-20">{{ student.religion }}</strong>
                    </div>
                  </td>
                  <td>
                    <div style="position: relative">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Date of Admission</span><br />
                      <strong class="f-12 m-l-20">{{ student.doa }}</strong>
                    </div>
                    <div style="position: relative">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Discount In Fee</span><br />
                      <strong class="f-12 m-l-20">0 %</strong>
                    </div>
                    <div style="position: relative">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Username</span><br />
                      <strong class="f-12 m-l-20">783338M521</strong>
                    </div>
                    <div style="position: relative">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Password</span><br />
                      <strong class="f-12 m-l-20">783338M521</strong>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="row-fluid p-20 p-t-5 p-b-5">
            <table class="w-100">
              <tbody>
                <tr style="border: none">
                  <td>
                    <div style="position: relative; min-height: 30px" class="text-left">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Address</span><br />
                      <strong class="f-12 m-l-20">{{ student.parents.address }}</strong>
                    </div>
                  </td>
                  <td>
                    <div class="text-right" style="line-height: 10px">
                      <strong class="f-12">SCAN QR CODE</strong><br /><span class="f-10">TO ACCESS PORTAL</span>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="row-fluid p-20 p-t-0 p-b-5" style="background: white">
            <table style="width: 100%">
              <tbody>
                <tr style="border: none">
                  <td>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Any Identification Mark?</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Blood Group</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Disease If Any?</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Cast</span><br />
                      <strong class="f-12 m-l-20">{{ student.cast }}</strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Orphan Student</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">OSC</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Previous School</span><br />
                      <strong class="f-12 m-l-20">{{ student.previous_school }}</strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Previous ID / Board Roll No</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Any Additional Note</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                  </td>
                  <td>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Father Name</span><br />
                      <strong class="f-12 m-l-20">{{ student.parents.name }}</strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Father National ID</span><br />
                      <strong class="f-12 m-l-20">{{ student.parents.nice }}</strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Education</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Mobile No</span><br />
                      <strong class="f-12 m-l-20">{{ student.parents.phone }}</strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Occupation</span><br />
                      <strong class="f-12 m-l-20">{{ student.parents.profession }}</strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Profession</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Income</span><br />
                      <strong class="f-12 m-l-20">0</strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Total Siblings</span><br />
                      <strong class="f-12 m-l-20">0</strong>
                    </div>
                    <div style="position: relative; min-height: 30px">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Family</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                  </td>
                  <td>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Mother Name</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Mother National ID</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Education</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Mobile No</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Occupation</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Profession</span><br />
                      <strong class="f-12 m-l-20"></strong>
                    </div>
                    <div style="position: relative; min-height: 30px" class="">
                      <span class="f-10 m-gray" style="border-bottom: 1.5px solid #999">Income</span><br />
                      <strong class="f-12 m-l-20">0</strong>
                    </div>
                  </td>
                  <td>
                    <div class="text-center">
                      <div style="width: 80px; margin: 0 auto" class="bg-gradient-gray m-white p-10 f-10 p-t-0 p-b-0">
                        <strong>Web Portal</strong>
                      </div>
                    </div>
                    <div class="text-center m-t-10">
                      <div style="width: 80px; margin: 0 auto" class="bg-gradient-gray m-white p-10 f-10 p-t-0 p-b-0">
                        <strong>Andriod App</strong>
                      </div>
                    </div>
                    <div class="text-center m-t-10">
                      <div style="width: 80px; margin: 0 auto" class="bg-gradient-gray m-white p-10 f-10 p-t-0 p-b-0">
                        <strong>iOS App</strong>
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="row-fluid p-20 p-t-5" style="background: white">
            <strong class="f-14 m-gray">Rules And Regulations: </strong><br />
            <div v-html="sanitizedRules" class="rules-content"></div>
          </div>
        </div>

        <div class="qr-section">
          <vue-qrcode :value="qrValue" :size="120" level="H"></vue-qrcode>
          <div class="qr-text">Scan to verify</div>
        </div>

        <div class="footer">
          <div class="signature-section">
            <div class="signature-line">
              <span>Signature of Authority</span>
              <div class="line"></div>
            </div>
            <div class="signature-line">
              <span>Institute Stamp</span>
              <div class="line"></div>
            </div>
          </div>
          <div class="print-date">
            Printed on: {{ getCurrentDate() }}
          </div>
        </div>
      </div>
    </div>
  </el-dialog>
</template>

<script>
import VueQrcode from 'qrcode.vue'
import Resource from '@/api/resource'
import DOMPurify from 'dompurify'
const stdRes = new Resource('students')
const settingsRes = new Resource('settings')

export default {
  name: 'AdmissionCertificate',
  components: {
    VueQrcode
  },
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
      qrValue: ''
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
    },
    student: {
      handler(val) {
        if (val.id) {
          this.qrValue = `${this.settings.school_website || 'https://yourschool.com'}/verify-student/${val.id}`
        }
      },
      deep: true
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
      console.log(data);
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
    }
  }
}
</script>

<style scoped>
.certificate-container {
  background: #fff;
  padding: 30px;
  box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.certificate-content {
  border: 2px solid #333;
  padding: 20px;
  position: relative;
}

.header {
  text-align: center;
  margin-bottom: 30px;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.school-logo {
  width: 80px;
  height: 80px;
  object-fit: contain;
}

.school-name {
  font-size: 28px;
  font-weight: bold;
  color: #2c3e50;
  margin: 10px 0;
}

.school-motto {
  font-style: italic;
  color: #7f8c8d;
  margin: 5px 0;
}

.contact-info {
  display: flex;
  gap: 10px;
  align-items: center;
  justify-content: center;
  flex-wrap: wrap;
}

.document-title {
  font-size: 24px;
  font-weight: bold;
  color: #2980b9;
  margin: 20px 0;
  text-transform: uppercase;
  border-bottom: 2px solid #3498db;
  padding-bottom: 10px;
}

.qr-section {
  position: absolute;
  top: 20px;
  right: 20px;
  text-align: center;
}

.qr-text {
  font-size: 12px;
  margin-top: 5px;
  color: #666;
}

.signature-section {
  display: flex;
  justify-content: space-between;
  margin-top: 50px;
  padding-top: 20px;
  border-top: 1px solid #eee;
}

.signature-line {
  text-align: center;
}

.signature-line span {
  font-size: 14px;
  color: #666;
}

.line {
  width: 200px;
  height: 1px;
  background: #000;
  margin-top: 30px;
}

.print-date {
  text-align: right;
  font-size: 12px;
  color: #666;
  margin-top: 20px;
}

td {
  padding: 10px;
  vertical-align: top;
}

.info-label {
  font-size: 12px;
  color: #666;
  border-bottom: 1px solid #ddd;
  padding-bottom: 3px;
}

.info-value {
  font-size: 14px;
  color: #333;
  padding: 5px 0;
  font-weight: 500;
}

@media print {
  .certificate-container {
    box-shadow: none;
  }
  
  .el-dialog {
    margin: 0;
    padding: 0;
    width: 100% !important;
  }
}

.rules-content :deep(h3) {
  font-size: 16px;
  color: #303133;
  margin: 15px 0 10px;
}

.rules-content :deep(ul) {
  padding-left: 20px;
  margin: 10px 0;
}

.rules-content :deep(li) {
  margin: 5px 0;
}

.rules-content :deep(strong) {
  font-weight: 600;
  color: #303133;
}
</style>