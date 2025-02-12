<template>
  <div class="app-container">
     <div class="filter-container">
     </div>
     <div class="common-layout">
       <el-container>
         <el-header>Teacher Settings</el-header>
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
                        <el-form-item label="School Logo">
                          <el-upload
                            class="avatar-uploader"
                            :show-file-list="false"
                            accept="image/*"
                            :on-success="handleLogoUpload"
                            :auto-upload="false"
                            :on-change="handleLogoUpload"
                          >
                            <img v-if="school_form.logo" :src="`/${school_form.logo}`" class="avatar" />
                            <el-icon v-else class="avatar-uploader-icon"><Plus /></el-icon>
                          </el-upload>
                        </el-form-item>
                        <el-form-item>
                          <el-button type="primary" @click="saveSchoolSettings()" :loading="form_element.updating">Save</el-button>
                        </el-form-item>
                      </el-form>
                    </el-col>
                    
                    <!-- Right Column - Preview -->
                    <el-col :span="12">
                      <div class="school-preview">
                        <div class="preview-header">Preview</div>
                        <div class="logo-container">
                          <img v-if="school_form.logo" :src="`/${school_form.logo}`" class="preview-logo" />
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
                          <div class="social-links" v-if="school_form.facebook || school_form.tiktok || school_form.instagram">
                            <a v-if="school_form.facebook" :href="school_form.facebook" target="_blank" class="social-link">
                              <i class="el-icon-s-platform"></i>
                            </a>
                            <a v-if="school_form.tiktok" :href="school_form.tiktok" target="_blank" class="social-link">
                              <i class="el-icon-video-camera"></i>
                            </a>
                            <a v-if="school_form.instagram" :href="school_form.instagram" target="_blank" class="social-link">
                              <i class="el-icon-picture"></i>
                            </a>
                          </div>
                        </div>
                      </div>
                    </el-col>
                  </el-row>
                </el-tab-pane>
               <el-tab-pane v-loading="form_element.updating" label="Teacher Settings" name="teacher">
                 Teacher Settings
                 <el-form :model="form" >
                   <el-form-item label="Allowed Leaves">
                     <el-input v-model="teacher_form.teacher_leaves_allowed" placeholder="Please input allowed leaves " />
                   </el-form-item>
                   <el-button type="primary" @click="onSubmit" :loading="form_element.updating">Save</el-button>
                 </el-form>
               </el-tab-pane>
               <el-tab-pane label="Students Settings" name="second">Students Settings</el-tab-pane>
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
             </el-tabs>
           </el-card>
         </el-main>
       </el-container>
     </div>
 </div>
</template>
<script>
import Resource from '@/api/resource.js';

export default {
  name: 'Setting',
  data() {
    return {
      activeName: 'first',
      tab: null,
      event: null,
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
      school_form: {
        school_name: '',
        school_address: '',
        school_phone: '',
        logo: null,
        logo_url: '',
        opening_time: '', // Add this new field
        website: '',
        tagline: '',
        facebook: '',
        tiktok: '',
        instagram: ''
      }
    }
  },
  methods: {
    handleClick(t, e) {
      this.tab = t
      this.event = e
      console.log(this.tab, this.event);
      this.getList();
    },
    onSubmit() {
      this.form_element.updating = true
      this.settingResource
        .store(this.teacher_form)
        .then(response => {
          this.form_element.updating = false
          ElMessage({
            message: 'Information has been updated successfully',
            type: 'success',
            duration: 5 * 1000,
          })
        })
        .catch(error => {
          console.log(error)
          this.form_element.updating = false
        })
    },
    saveExamSettings() {
      this.form_element.updating = true
      this.settingResource
        .store(this.exam_form)
        .then(response => {
          this.form_element.updating = false
          ElMessage({
            message: 'Information has been updated successfully',
            type: 'success',
            duration: 5 * 1000,
          })
        })
        .catch(error => {
          console.log(error)
          this.form_element.updating = false
        })
    },
    async getList() {
      const { data } = await this.settingResource.list();
      this.teacher_form.teacher_leaves_allowed = data.settings.teacher_leaves_allowed;
      this.exam_form.result_header = data.settings.result_header;
      this.school_form.school_name = data.settings.school_name || '';
      this.school_form.address = data.settings.address || '';
      this.school_form.phone = data.settings.phone || '';
      this.school_form.logo = data.settings.logo || '';
      this.school_form.opening_time = data.settings.opening_time || '';
      this.school_form.website = data.settings.website || '';
      this.school_form.tagline = data.settings.tagline || '';
      this.school_form.facebook = data.settings.facebook || '';
      this.school_form.tiktok = data.settings.tiktok || '';
      this.school_form.instagram = data.settings.instagram || '';
    },
    async saveSchoolSettings() {
      this.form_element.updating = true;
      
      const formData = new FormData();
      formData.append('school_name', this.school_form.school_name);
      formData.append('address', this.school_form.address);
      formData.append('phone', this.school_form.phone);
      if (this.school_form.logo) {
        formData.append('logo', this.school_form.logo);
      }
      formData.append('opening_time', this.school_form.opening_time);
      formData.append('website', this.school_form.website);
      formData.append('tagline', this.school_form.tagline);
      formData.append('facebook', this.school_form.facebook);
      formData.append('tiktok', this.school_form.tiktok);
      formData.append('instagram', this.school_form.instagram);

      try {
        await this.settingResource.store(formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        
        ElMessage({
          message: 'School settings have been updated successfully',
          type: 'success',
          duration: 5 * 1000,
        });
      } catch (error) {
        console.log(error);
      } finally {
        this.form_element.updating = false;
      }
    },
    handleLogoUpload(file) {
      this.school_form.logo = file.raw;
      this.school_form.logo_url = URL.createObjectURL(file.raw);
    }
  },
  mounted() {
    this.getList();
  }
}
</script>

<style scoped>
.avatar-uploader .avatar {
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
}

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

.social-links {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  gap: 15px;
}

.social-link {
  color: #409EFF;
  font-size: 24px;
  transition: color 0.3s;
}

.social-link:hover {
  color: #66b1ff;
}
</style>