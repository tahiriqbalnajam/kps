<template>
  <div class="app-container">
    <div class="filter-container">
      <el-button type="success" icon="el-icon-plus" @click="editnow = true">
        Add SMS
      </el-button>
      <el-button style="margin-left: 10px;" type="success" icon="el-icon-postcard" :loading="sendsmsloading" @click="sendSMS()">
        Send SMS
      </el-button>
      <el-button style="margin-left: 10px;" type="danger" icon="el-icon-delete" :loading="sendsmsloading" @click="clearQueue()">
        Clear Queue
      </el-button>
    </div>
    <el-table
      :data="list"
      style="width: 100%"
      empty-text="No SMS to send"
    >
      <el-table-column label="Phone" prop="phone" />
      <el-table-column label="Channel" prop="channel" />
      <el-table-column label="Student" prop="student.name" />
      <el-table-column label="Message" prop="message" />
      <el-table-column label="Status" prop="status" />
      <el-table-column label="Error" prop="api_error" />
      <el-table-column align="right">
        <template slot="header" slot-scope="scope">
          <el-input ref="search" v-model="query.keyword" size="mini" placeholder="Type to search" v-on:input="debounceInput" />
        </template>
        <template slot-scope="scope">
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
              <el-option v-for="item in classes" :key="item.id" :label="item.name | uppercaseFirst" :value="item.id" />
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
import {debounce} from 'lodash';
import Resource from '@/api/resource';
import {
    completeSMS,
    getDefaultMessageChannel,
    processSMS,
    processWhatsApp,
    updateSendStatusWhatsApp
} from '@/api/general';

const smsqueuePro = new Resource('smsqueue');
const classes = new Resource('classes');
export default {
  name: '',
  components: { Pagination },
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
      const { original } = await smsqueuePro.list(this.query);
      this.list = original.smsqueue.data;
      this.total = original.smsqueue.total;
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
      this.editnow = true;
    },
    async handleDelete(id, name) {
      this.$confirm('Do you really want to delete?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(async() => {
        await smsqueuePro.destroy(id);
        this.getList();
        this.message({
          type: 'success',
          message: name+' Delete successfully'
        });
      });
    },
    async onSubmit() {
      this.loading = true;
      if(this.sms.id != '') {
        await smsqueuePro.update(this.sms.id, this.sms);
        this.editnow = false;
        this.loading = false;
        this.resetSMS();
        this.getList();
          this.defaultMessageChannel();
      } else {
        await smsqueuePro.store(this.sms).then(result => {
          this.editnow = false;
          this.loading = false;
          this.resetSMS();
          this.getList();
            this.defaultMessageChannel();
            this.$message({
            message: 'Fee added successfully.',
            type: 'success',
          });
          return;
        }).catch(() => {
          this.$message({
            message: 'Enter phone and message.',
            type: 'error',
          });
            this.defaultMessageChannel();
          this.loading = false;
          return false;
        });
      }
    },
    async sendSMS() {
      this.sendsmsloading = true;
      await processSMS().then(result => {
        this.sendsmsloading = false;
        this.getList();
        this.$message({
          message: 'SMS sent successfully.',
          type: 'success',
        });
      }).catch(() => {
        this.$message({
          message: 'Something wrong while sending sms',
          type: 'error',
        });
        this.sendsmsloading = false;
        // return false;
      });
        this.sendsmsloading = true;

        let whatsapp_records = [];
        let records = await processWhatsApp();
        console.log(records.original.records)
        for (const record of records.original.records) {
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
    sendWhatsapp(message_id, phoneNumber, message) {
      window.whatsappWebSuite.sendTextMessage(
        phoneNumber,
        message
      );

        document.addEventListener('whatsappSendResponse', (e) => {
            if (e.detail.success) {
                this.message_ids.push(message_id)
                // this.updateSendStatus(message_id, 'sent');
            } else {
                console.log('Failed to send message.');
                this.$message({
                    message: 'Unable to Send message. Please login Web WhatsApp same browser and WA Web Bridge Extension properly installed.',
                    type: 'error',
                });
            }
        });
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
