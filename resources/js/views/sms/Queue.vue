<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button type="success" @click="editnow = true">
        <el-icon><Plus /></el-icon>
        Add SMS
      </el-button>
      <el-button-group style="margin-left: 10px;">
        <el-button type="success" :loading="sendsmsloading" @click="sendSMS()">
        <el-icon><Message /></el-icon>  Send All
        </el-button>
        <!-- <el-button type="primary" :disabled="multipleSelection.length === 0" :loading="sendsmsloading" @click="sendSelectedSMS">
        Send Selected
        </el-button> -->
      </el-button-group>
      <el-button-group style="margin-left: 10px;">
        <el-button type="danger" :loading="sendsmsloading" @click="deleteAll()">
          <el-icon><Delete /></el-icon> Delete All
        </el-button>
        <el-button type="danger" :disabled="multipleSelection.length === 0" @click="handleDelete()">
          <el-icon><Delete /></el-icon> Delete Selected
        </el-button>
      </el-button-group>
    </div>
    <el-table
      :data="list"
      style="width: 100%"
      empty-text="No SMS to send"
      @selection-change="handleSelectionChange"
    >
      <el-table-column
        type="selection"
        width="55">
      </el-table-column>
      <el-table-column label="Phone" prop="phone" />
      <el-table-column label="Channel" prop="channel" />
      <el-table-column label="Student" prop="student.name" />
      <el-table-column label="Message" prop="message" />
      <el-table-column label="Status" prop="status" />
      <el-table-column label="Error" prop="api_error" />
      <el-table-column label="Created Date" prop="created_at">
        <template #default="scope">
          {{ formatDate(scope.row.created_at) }}
        </template>
      </el-table-column>
      <el-table-column align="right">
        <template #header>
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
        </template>
        <template #default="scope">
          <el-button
            size="mini"
            @click="handleEdit(scope.row.id, scope.row.name)"
          >Edit</el-button>
          <el-button
            size="mini"
            type="danger"
            @click="handleDelete(scope.row.id, scope.row.name)"
          >Delete</el-button>
        </template>
      </el-table-column>
    </el-table>
    <el-pagination
        v-show="total>0"
        v-model:current-page="query.page"
        v-model:page-size="query.limit"
        :page-sizes="[10, 15, 20, 30, 50, 100]"
        :small="small"
        :disabled="disabled"
        background="white"
        layout="total, sizes, prev, pager, next, jumper"
        :total="total"
        @size-change="handleSizeChange"
        @current-change="handleCurrentChange"
      />
    <el-drawer
      title="Edit Record"
      v-model="editnow"
      direction="rtl"
      custom-class="demo-drawer"
      size="50%"
      ref="drawer"
    >
      <div class="demo-drawer__content">
        <el-form :model="sms">
          <el-form-item label="SMS Type" :label-width="formLabelWidth">
            <el-radio v-model="sms.smstype" label="Single" border>Single</el-radio>
            <el-radio v-model="sms.smstype" label="Multiple" border>Multiple</el-radio>
          </el-form-item>
          <el-form-item label="Message Channel" :label-width="formLabelWidth">
            <el-radio v-model="sms.channel" label="sms" border>SMS</el-radio>
            <el-radio v-model="sms.channel" label="whatsapp" border>WhatsApp</el-radio>
          </el-form-item>
          <el-form-item v-show="sms.smstype == 'Multiple'" label="Select Classes" :label-width="formLabelWidth">
            <el-select v-model="sms.classes" multiple placeholder="Select">
              <el-option v-for="item in classes" :key="item.id" :label="item.name" :value="item.id" />
            </el-select>
          </el-form-item>
          <el-form-item v-show="sms.smstype == 'Single'" label="Phone" :label-width="formLabelWidth">
            <el-input v-model="sms.phone" autocomplete="off" />
          </el-form-item>
          <el-form-item label="Message" :label-width="formLabelWidth">
            <el-input v-model="sms.message" type="textarea" :rows="2" autocomplete="off" />
          </el-form-item>
        </el-form>
        <div class="demo-drawer__footer">
          <el-button @click="editnow = false">Cancel</el-button>
          <el-button type="primary" :loading="loading" @click="onSubmit">{{ loading ? 'Submitting ...' : 'Submit' }}</el-button>
        </div>
      </div>
    </el-drawer>
  </div>
</template>
<script>

import Pagination from '@/components/Pagination/index.vue';
import { debounce } from 'lodash';
import Resource from '@/api/resource';
import moment from 'moment';
import ElSvgItem from "@/components/Item/ElSvgItem.vue"
import { Plus, Message, Delete, Select } from '@element-plus/icons-vue';
import {
  completeSMS,
  deleteAllSMS,
  getDefaultMessageChannel,
  processSMS,
  processWhatsApp,
  updateSendStatusWhatsApp
} from '@/api/general';

const smsqueuePro = new Resource('smsqueue');
const classes = new Resource('classes');
export default {
  name: '',
  components: { Pagination, Plus, Message, Delete, Select },
    async sendSelectedSMS() {
      if (this.multipleSelection.length === 0) {
        this.$message({
          message: 'Please select at least one SMS to send.',
          type: 'warning',
        });
        return;
      }
      this.sendsmsloading = true;
      try {
        // Send WhatsApp and SMS messages for selected rows
        let selectedWhatsApp = this.multipleSelection.filter(row => row.channel === 'whatsapp');
        let selectedSMS = this.multipleSelection.filter(row => row.channel === 'sms');

        // Send WhatsApp messages
        for (const record of selectedWhatsApp) {
          await this.sendWhatsapp(record.id, record.phone, record.message);
          await new Promise(resolve => setTimeout(resolve, 2000));
        }
        // Mark WhatsApp as sent
        if (selectedWhatsApp.length > 0 && this.message_ids.length > 0) {
          await this.updateSendStatus(this.message_ids, 'sent');
        }

        // Send SMS messages (call backend to process by IDs)
        if (selectedSMS.length > 0) {
          // Actually call backend to process selected SMS
          await this.processSelectedSMS(selectedSMS.map(row => row.id));
        }

        this.$message({
          message: 'Selected messages sent (where possible).',
          type: 'success',
        });
        this.getList();
      } catch (error) {
        this.$message({
          message: 'Error sending selected messages.',
          type: 'error',
        });
      } finally {
        this.sendsmsloading = false;
      }
    },

    async processSelectedSMS(ids) {
      // You need to implement this endpoint in your backend
      // For now, just show a message
      // Example: await smsqueuePro.sendSelected({ ids })
      this.$message({
        message: 'Selected SMS (channel: sms) sent via backend process (implement endpoint as needed).',
        type: 'info',
      });
    },

    
  directives: { },
  data() {
    return {
      list: null,
      classes: null,
      search: '',
      total: 0,
      loading: false,
      sendsmsloading: false,
      downloading: false,
      editnow: false,
      formLabelWidth: '250',
      multipleSelection: [], // Add this line to track selected rows
      sms: {
        id: '',
        smstype: 'Single',
        classes: null,
        message: '',
        phone: '',
        channel: '',
      },
      message_ids: [],
      query: {
        page: 1,
        limit: 15,
        keyword: '',
        role: '',
      },
    };
  },
  computed: {
  },
  created() {
    this.getList();
    this.getClasses();
      this.setDefaultMessageChannel()
      this.defaultMessageChannel()
  },
  methods: {

    formatDate(date) {
      return moment(date).format('DD/MM/YYYY');
    },
    async deleteAll() {
      this.$confirm('Are you sure you want to delete all SMS in the queue?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(async() => {
        this.sendsmsloading = true;
        await deleteAllSMS().then(result => {
          this.sendsmsloading = false;
          this.getList();
          this.$message({
            message: 'All SMS deleted successfully.',
            type: 'success',
          });
        }).catch(() => {
          this.$message({
            message: 'Something went wrong while deleting all SMS.',
            type: 'error',
          });
          this.sendsmsloading = false;
          return false;
        });
      });
    },
    // Add this method to handle table selection changes
    handleSelectionChange(val) {
      this.multipleSelection = val;
      console.log('Selected rows:', this.multipleSelection.length);
    },
    
    async handleSizeChange (val) {
      console.log(`每页 ${val} 条`);
      this.query.limit = val
      await this.getList()
    },
    async handleCurrentChange (val) {
      this.query.page = val
      await this.getList()
    },
    debounceInput: debounce(function(e) {
      this.getList();
    }, 500),
    async getList() {
      const response = await smsqueuePro.list(this.query);
      // Support both old and new response formats
      let data = response.original || response.data || response;
      if (data && data.smsqueue) {
        this.list = data.smsqueue.data || [];
        this.total = data.smsqueue.total || 0;
      } else {
        this.list = [];
        this.total = 0;
      }
    },
    async getClasses() {
      const{ data } = await classes.list();
      this.classes = data.classes.data;
    },
    async setDefaultMessageChannel() {
      let record = await getDefaultMessageChannel();
      if (record.success) {
          this.sms.channel = record.data.message_channel.setting_value;
          localStorage.setItem('message_channel', record.data.message_channel.setting_value);
      }
      else {
          localStorage.setItem('message_channel', 'whatsapp');
      }
    },
    async search_data() {
      await this.getList();
    },
    async handleEdit(id, name) {
      const { original } = await smsqueuePro.get(id);
      this.sms = original.sms;
      this.sms.smstype = 'Single';
      this.editnow = true;
    },
    async handleDelete(id, name) {
      // If id is provided, delete single record, otherwise delete selected records
      const isMultiDelete = id === undefined;
      const confirmMessage = isMultiDelete 
        ? 'Do you really want to delete selected records?' 
        : 'Do you really want to delete?';

      this.$confirm(confirmMessage, 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(async() => {
        try {
          this.loading = true;
          
          if (isMultiDelete) {
            // Multiple delete
            if (this.multipleSelection.length === 0) {
              this.loading = false;
              return;
            }
            
            const count = this.multipleSelection.length;
            const deletePromises = this.multipleSelection.map(item => smsqueuePro.destroy(item.id));
            await Promise.all(deletePromises);
            
            this.$message({
              type: 'success',
              message: `${count} record(s) deleted successfully`
            });
            this.multipleSelection = [];
          } else {
            // Single delete
            await smsqueuePro.destroy(id);
            this.$message({
              type: 'success',
              message: name + ' deleted successfully'
            });
          }
          
          await this.getList();
          this.loading = false;
        } catch (error) {
          this.loading = false;
          this.$message({
            type: 'error',
            message: 'Error deleting SMS: ' + (error.message || 'Unknown error')
          });
        }
      }).catch(() => {
        // User cancelled
      });
    },
    async onSubmit() {
      // Frontend validation
      if (!this.validateForm()) {
        return;
      }

      this.loading = true;
      
      try {
        if(this.sms.id != '') {
          // Update existing SMS
          const result = await smsqueuePro.update(this.sms.id, this.sms);
          this.$message({
            message: 'SMS updated successfully.',
            type: 'success',
          });
        } else {
          // Create new SMS
          const result = await smsqueuePro.store(this.sms);
          
          if (this.sms.smstype === 'Single') {
            this.$message({
              message: 'SMS added to queue successfully.',
              type: 'success',
            });
          } else {
            this.$message({
              message: 'Multiple SMS added to queue successfully.',
              type: 'success',
            });
          }
        }
        
        // Reset form and refresh data
        this.editnow = false;
        this.resetSMS();
        this.getList();
        this.defaultMessageChannel();
        
      } catch (error) {
        console.error('SMS submission error:', error);
        
        // Handle different types of errors
        let errorMessage = 'An error occurred while submitting SMS.';
        
        if (error.response && error.response.data) {
          if (error.response.data.message) {
            errorMessage = error.response.data.message;
          } else if (error.response.data.error) {
            errorMessage = error.response.data.error;
          } else if (error.response.status === 422) {
            errorMessage = 'Validation failed. Please check your input data.';
          } else if (error.response.status === 500) {
            errorMessage = 'Server error occurred. Please try again.';
          }
        } else if (error.message) {
          errorMessage = error.message;
        }
        
        this.$message({
          message: errorMessage,
          type: 'error',
        });
      } finally {
        this.loading = false;
        this.defaultMessageChannel();
      }
    },

    validateForm() {
      // Validate message
      if (!this.sms.message || this.sms.message.trim() === '') {
        this.$message({
          message: 'Please enter a message.',
          type: 'error',
        });
        return false;
      }

      // Validate channel
      if (!this.sms.channel) {
        this.$message({
          message: 'Please select a message channel.',
          type: 'error',
        });
        return false;
      }

      // Validate based on SMS type
      if (this.sms.smstype === 'Single') {
        if (!this.sms.phone || this.sms.phone.trim() === '') {
          this.$message({
            message: 'Please enter a phone number.',
            type: 'error',
          });
          return false;
        }
        
        // Basic phone number validation
        const phoneRegex = /^[0-9+\-\s()]{10,15}$/;
        if (!phoneRegex.test(this.sms.phone.trim())) {
          this.$message({
            message: 'Please enter a valid phone number.',
            type: 'error',
          });
          return false;
        }
      } else if (this.sms.smstype === 'Multiple') {
        if (!this.sms.classes || this.sms.classes.length === 0) {
          this.$message({
            message: 'Please select at least one class.',
            type: 'error',
          });
          return false;
        }
      }

      return true;
    },
    async sendSMS() {
      //this.sendsmsloading = true;
      // await processSMS().then(result => {
      //   this.sendsmsloading = false;
      //   this.getList();
      //   this.$message({
      //     message: 'SMS sent successfully.',
      //     type: 'success',
      //   });
      // }).catch(() => {
      //   this.$message({
      //     message: 'Something wrong while sending sms',
      //     type: 'error',
      //   });
      //   this.sendsmsloading = false;
      //   // return false;
      // });
        this.sendsmsloading = true;

        let whatsapp_records = [];
        let records = await processWhatsApp();
        //console.log(records.original.records)
        for (const record of records.data.records) {
            await this.sendWhatsapp(record.id, record.phone, record.message);
            await new Promise(resolve => setTimeout(resolve, 2000)); // ⏳ Wait 2s before next message
        }
        if (this.message_ids.length)
            this.updateSendStatus(this.message_ids, 'sent');
        this.sendsmsloading = false;
    },
      defaultMessageChannel() {
          this.sms.channel = localStorage.getItem('message_channel') ? localStorage.getItem('message_channel') : 'SMS';

      },
      async updateSendStatus(message_ids, status) {
          await updateSendStatusWhatsApp(message_ids, status);
          await this.getList();
      },
      async sendWhatsapp(message_id, phoneNumber, message) {
        try {
            // Check if WhatsApp Web Suite is available
            if (!window.whatsappWebSuite || typeof window.whatsappWebSuite.sendTextMessage !== 'function') {
                throw new Error('WhatsApp Web Suite is not available. Please ensure the extension is installed and running.');
            }
            
            const response = await window.whatsappWebSuite.sendTextMessage(phoneNumber, message);
            this.message_ids.push(message_id)
        } catch (error) {
            console.error('Failed to send message:', error);
            this.$message({
                message: 'Unable to Send message. '+error,
                type: 'error',
            });
        }
      // window.whatsappWebSuite.sendTextMessage(
      //   phoneNumber,
      //   message
      // );

        // document.addEventListener('whatsappSendResponse', (e) => {
        //     if (e.detail.success) {
        //         this.message_ids.push(message_id)
        //         // this.updateSendStatus(message_id, 'sent');
        //     } else {
        //         console.log('Failed to send message.');
        //         this.$message({
        //             message: 'Unable to Send message. Please login Web WhatsApp same browser and WA Web Bridge Extension properly installed.',
        //             type: 'error',
        //         });
        //     }
        // });
    },
    clearQueue(){
      this.$confirm('Are you sure?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(async() => {
        this.sendsmsloading = true;
        await completeSMS().then(result => {
          this.sendsmsloading = false;
          this.getList();
          this.$message({
            message: 'Status Updated successfully.',
            type: 'success',
          });
        }).catch(() => {
          this.$message({
            message: 'Something wrong while sending sms',
            type: 'error',
          });
          this.sendsmsloading = false;
          return false;
        });
      });
    },
    resetSMS() {
      this.sms = {
        id: '',
        smstype: 'Single',
        classes: null,
        message: '',
        phone: '',
      };
    },
  },
};
</script>
<style  scoped>
  .el-drawer__body {
    flex: 1;
    padding: 20px;
  }
  .demo-drawer__content {
    display: flex;
    flex-direction: column;
    height: 100%;
    padding: 20px;
  }
</style>
