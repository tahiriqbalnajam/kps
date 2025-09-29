<template>
  <div class="app-container">
    <div class="filter-container"></div>
    <div class="common-layout">
      <el-container>
        <el-main>
          <el-card class="box-card">
            <el-tabs v-model="activeName" class="demo-tabs" @tab-click="handleClick">
              <el-tab-pane label="School Settings" name="first">
                <el-row :gutter="20">
                  <!-- Left Column - Form -->
                  <el-col :span="12">
                    <el-form :model="school_form" label-width="120px">
                      <el-form-item label="School Name">
                        <el-input v-model="school_form.school_name" placeholder="Enter school name" />
                      </el-form-item>

                      <el-form-item label="School Address">
                        <el-input v-model="school_form.address" type="textarea" :rows="3" placeholder="Enter school address" />
                      </el-form-item>

                      <el-form-item label="School Phone">
                        <el-input v-model="school_form.phone" placeholder="Enter school phone number" />
                      </el-form-item>

                      <el-form-item label="Opening Time">
                        <el-time-picker
                          v-model="school_form.opening_time"
                          placeholder="Select opening time"
                          format="HH:mm"
                          value-format="HH:mm"
                        />
                      </el-form-item>

                      <el-form-item label="School Logo">
                        <el-upload
                          class="avatar-uploader"
                          :show-file-list="false"
                          accept="image/*"
                          :auto-upload="false"
                          :on-change="handleLogoUpload"
                        >
                          <img v-if="school_form.school_logo" :src="`/${school_form.school_logo}`" class="avatar" />
                          <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
                        </el-upload>
                      </el-form-item>

                      <el-divider>School Information</el-divider>

                      <el-form-item label="Tagline">
                        <el-input v-model="school_form.tagline" placeholder="Enter school tagline" />
                      </el-form-item>

                      <el-form-item label="Website">
                        <el-input v-model="school_form.website" placeholder="Enter website URL" />
                      </el-form-item>

                      <el-divider>Social Media Links</el-divider>

                      <el-form-item label="Facebook">
                        <el-input v-model="school_form.facebook" placeholder="Enter Facebook URL" />
                      </el-form-item>

                      <el-form-item label="TikTok">
                        <el-input v-model="school_form.tiktok" placeholder="Enter TikTok URL" />
                      </el-form-item>

                      <el-form-item label="Instagram">
                        <el-input v-model="school_form.instagram" placeholder="Enter Instagram URL" />
                      </el-form-item>

                      <el-form-item>
                        <el-button type="primary" @click="saveSchoolSettings" :loading="form_element.updating">
                          Save Settings
                        </el-button>
                      </el-form-item>
                    </el-form>
                  </el-col>

                  <!-- Right Column - Preview -->
                  <el-col :span="12">
                    <div class="school-preview">
                      <div class="preview-header">Preview</div>
                      <div class="logo-container">
                        <img v-if="school_form.school_logo" :src="`/${school_form.school_logo}`" class="preview-logo" />
                        <div v-else class="no-logo">No Logo</div>
                      </div>
                      <div class="school-details">
                        <h2 class="school-name">{{ school_form.school_name || 'School Name' }}</h2>
                        <p class="school-tagline">{{ school_form.tagline || 'School Tagline' }}</p>
                        <p class="school-info"><i class="el-icon-phone"></i> {{ school_form.phone || 'Phone Number' }}</p>
                        <p class="school-info"><i class="el-icon-location"></i> {{ school_form.address || 'Address' }}</p>
                        <p class="school-info"><i class="el-icon-time"></i> Opens at: {{ school_form.opening_time || 'Not Set' }}</p>
                        <p class="school-info" v-if="school_form.website">
                          <i class="el-icon-link"></i> <a :href="school_form.website" target="_blank">{{ school_form.website }}</a>
                        </p>
                      </div>
                    </div>
                  </el-col>
                </el-row>
              </el-tab-pane>

              <!-- Other tab panes -->
              <el-tab-pane v-loading="form_element.updating" label="Teacher Settings" name="teacher">
                Teacher Settings
                <el-form :model="form" >
                  <el-form-item label="Allowed Leaves">
                    <el-input v-model="teacher_form.teacher_leaves_allowed" placeholder="Please input allowed leaves " />
                  </el-form-item>
                  <el-button type="primary" @click="onSubmit" :loading="form_element.updating">Save</el-button>
                </el-form>
              </el-tab-pane>
              <el-tab-pane label="Students Settings" name="second">
                <el-row :gutter="20">
                  <!-- Left Column - Form -->
                  <el-col :span="12">
                    <el-form :model="student_form" label-width="120px">
                      <el-form-item label="Admission Rules">
                        <el-input
                          v-model="student_form.admission_rules"
                          type="textarea"
                          :rows="25"
                          placeholder="Enter admission rules and regulations"
                        />
                        <div class="mt-2 text-gray-500 text-sm">
                          Use HTML tags for formatting:
                          &lt;br&gt; - new line,
                          &lt;strong&gt;text&lt;/strong&gt; - bold text,
                          &lt;h3&gt;text&lt;/h3&gt; - heading,
                          &lt;ul&gt;&lt;li&gt;item&lt;/li&gt;&lt;/ul&gt; - list
                        </div>
                        <el-form-item class="mt-4">
                          <el-button type="primary" @click="saveStudentSettings()" :loading="form_element.updating">
                            Save Rules
                          </el-button>
                        </el-form-item>
                      </el-form-item>
                    </el-form>
                  </el-col>

                  <!-- Right Column - Preview -->
                  <el-col :span="12">
                    <div class="rules-preview-container">
                      <div class="preview-header">Preview</div>
                      <div class="rules-preview" v-html="sanitizedRules"></div>
                    </div>
                  </el-col>
                </el-row>
              </el-tab-pane>
              <el-tab-pane label="User Settings" name="third">User Settings</el-tab-pane>
              <el-tab-pane label="Exam Settings" name="exam">
                <el-form :model="form" label-width="120px">
                  <el-form-item label="Result Header">
                    <el-input v-model="exam_form.result_header" type="textarea" :row="6" placeholder="HTML of header" />
                  </el-form-item>
                  <el-form-item>
                    <el-button type="primary" @click="saveExamSettings()" :loading="form_element.updating">Save</el-button>
                  </el-form-item>
                </el-form>
              </el-tab-pane>
              <el-tab-pane label="Message Settings" name="message">
                <el-form :model="message" label-width="250px">
                  <el-form-item label="Message Channel">
                    <el-select v-model="message.message_channel" filterable placeholder="Select Message Channel">
                      <el-option label="SMS" value="sms" />
                      <el-option label="WhatsApp" value="whatsapp" />
                    </el-select>
                  </el-form-item>

                  <el-form-item label="Absent SMS Template">
                    <el-input
                      v-model="message.absent_sms_template"
                      type="textarea"
                      :rows="5"
                      placeholder="Enter your custom SMS template for absent notifications."
                    />
                    <div class="mt-2 text-gray-500 text-sm">
                      Available placeholders: '[[parent_name]]', '[[student_name]]', '[[class_title]]', '[[school_name]]', '[[school_address]]', '[[school_phone]]'
                    </div>
                  </el-form-item>

                  <el-form-item label="Fee SMS Template">
                    <el-input
                      v-model="message.fee_sms_template"
                      type="textarea"
                      :rows="5"
                      placeholder="Enter your custom SMS template for fee reminders."
                    />
                    <div class="mt-2 text-gray-500 text-sm">
                      Available placeholders: '[[parent_name]]', '[[student_name]]', '[[class_title]]', '[[due_date]]', '[[amount]]', '[[school_name]]'
                      <!-- Added more relevant placeholders for fees -->
                    </div>
                  </el-form-item>

                  <el-form-item label="Test SMS Template">
                    <el-input
                      v-model="message.test_sms_template"
                      type="textarea"
                      :rows="5"
                      placeholder="Enter your custom SMS template for test results."
                    />
                    <div class="mt-2 text-gray-500 text-sm">
                      Available placeholders: '[[parent_name]]', '[[student_name]]', '[[class_title]]', '[[test_title]]', '[[obtained_marks]]', '[[total_marks]]', '[[position]]', '[[school_name]]'
                    </div>
                  </el-form-item>

                  <el-form-item>
                    <el-button type="primary" @click="saveMessageSettings()" :loading="form_element.updating">Save</el-button>
                  </el-form-item>
                </el-form>
              </el-tab-pane>
            </el-tabs>
          </el-card>
        </el-main>
      </el-container>
    </div>
  </div>
</template>

<script>
import Resource from '@/api/resource';
import { Plus } from '@element-plus/icons-vue';
import DOMPurify from 'dompurify';

export default {
  name: 'Setting',
  components: {
    Plus
  },
  data() {
    return {
      activeName: 'first',
      settingResource: new Resource('settings'),
      form_element: {
        updating: false,
      },
      teacher_form: {
        teacher_leaves_allowed: 0,
      },
      exam_form: {
        result_header: '',
      },
      message: {
          message_channel: '',
          absent_sms_template: '',
          fee_sms_template: '', // Add fee_sms_template property
          test_sms_template: '',
      },
      school_form: {
        school_name: '',
        address: '',
        phone: '',
        school_logo: null,
        opening_time: '',
        website: '',
        tagline: '',
        facebook: '',
        tiktok: '',
        instagram: ''
      },
      student_form: {
        admission_rules: '',
      },
    }
  },
  computed: {
    sanitizedRules() {
      return this.student_form.admission_rules ?
        DOMPurify.sanitize(this.student_form.admission_rules) :
        'Rules preview will appear here...';
    }
  },
  mounted() {
    this.getList();
  },
  methods: {
    handleClick(tab) {
      console.log(tab.props.name);
      this.getList();
    },
    async getList() {
      try {
        const { data } = await this.settingResource.list();
        this.loadSettings(data.settings);
      } catch (error) {
        console.error('Failed to load settings:', error);
        this.$message.error('Failed to load settings');
      }
    },
    loadSettings(settings) {
      // School settings
      this.school_form.school_name = settings.school_name || '';
      this.school_form.address = settings.address || '';
      this.school_form.phone = settings.phone || '';
      this.school_form.opening_time = settings.opening_time || '';
      this.school_form.website = settings.website || '';
      this.school_form.tagline = settings.tagline || '';
      this.school_form.facebook = settings.facebook || '';
      this.school_form.tiktok = settings.tiktok || '';
      this.school_form.instagram = settings.instagram || '';
      this.school_form.school_logo = settings.school_logo || null;

      // Other settings
      this.teacher_form.teacher_leaves_allowed = settings.teacher_leaves_allowed || 0;
      this.exam_form.result_header = settings.result_header || '';
      this.student_form.admission_rules = settings.admission_rules || '';
      this.message.message_channel = settings.message_channel || '';
      this.message.absent_sms_template = settings.absent_sms_template || '';
      this.message.fee_sms_template = settings.fee_sms_template || ''; // Load fee_sms_template
      this.message.test_sms_template = settings.test_sms_template || ''; // Load test_sms_template
    },
    handleLogoUpload(file) {
      if (file && file.raw) {
        this.school_form.school_logo = file.raw;
      } else {
        this.$message.error('Failed to process the uploaded file');
      }
    },
    async saveSchoolSettings() {
      this.form_element.updating = true;
      try {
        const formData = new FormData();

        // Add all form fields
        Object.keys(this.school_form).forEach(key => {
          if (key === 'school_logo' && this.school_form[key] instanceof File) {
            formData.append(key, this.school_form[key]);
          } else if (key !== 'school_logo') {
            formData.append(key, this.school_form[key] || '');
          }
        });

        await this.settingResource.store(formData);
        await this.getList();
        this.$message.success('Settings updated successfully');
      } catch (error) {
        console.error('Error saving settings:', error);
        this.$message.error('Failed to save settings');
      } finally {
        this.form_element.updating = false;
      }
    },
    async saveMessageSettings() {
      this.form_element.updating = true;
      try {
        const formData = new FormData();

        // Add all form fields from the message object
        Object.keys(this.message).forEach(key => {
            formData.append(key, this.message[key] || '');
        });

        await this.settingResource.store(formData);
        await this.getList(); // Refresh settings after save
        this.$message.success('Message settings updated successfully');
      } catch (error) {
        console.error('Error saving message settings:', error);
        this.$message.error('Failed to save message settings');
      } finally {
        this.form_element.updating = false;
      }
    }
  }
}
</script>

<style scoped>
.avatar-uploader {
  text-align: center;
}

.avatar {
  width: 178px;
  height: 178px;
  display: block;
}

.avatar-uploader .el-upload {
  border: 1px dashed var(--el-border-color);
  border-radius: 6px;
  cursor: pointer;
  position: relative;
  overflow: hidden;
  transition: var(--el-transition-duration-fast);
}

.avatar-uploader .el-upload:hover {
  border-color: var(--el-color-primary);
}

.avatar-uploader-icon {
  font-size: 28px;
  color: #8c939d;
  width: 178px;
  height: 178px;
  text-align: center;
  line-height: 178px;
}

/* Preview Styles */
.school-preview {
  padding: 20px;
  border: 1px solid #ebeef5;
  border-radius: 4px;
  background: #fff;
}

.preview-header {
  font-size: 16px;
  font-weight: bold;
  margin-bottom: 20px;
  color: #409EFF;
  text-align: center;
}

.logo-container {
  text-align: center;
  margin-bottom: 20px;
}

.preview-logo {
  max-width: 200px;
  max-height: 200px;
  object-fit: contain;
}

.no-logo {
  width: 200px;
  height: 200px;
  line-height: 200px;
  text-align: center;
  background: #f5f7fa;
  color: #909399;
  margin: 0 auto;
}

.school-details {
  text-align: center;
}

.school-name {
  font-size: 24px;
  color: #303133;
  margin-bottom: 15px;
}

.school-tagline {
  font-size: 16px;
  color: #606266;
  margin: 10px 0 20px;
  font-style: italic;
}

.school-info {
  margin: 10px 0;
  color: #606266;
}

.school-info i {
  margin-right: 8px;
  color: #409EFF;
}
</style>
