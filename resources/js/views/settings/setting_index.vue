<template>
  <div class="app-container">
    <el-form ref="form" :model="settings" label-width="120px" size="mini">
      <el-tabs v-model="activeName">
        <el-tab-pane label="Shop settings" name="shop_settings">
          <el-row>
            <el-col :span="24">
              <el-card shadow="always">
                <el-row>
                  <el-col :span="24">
                    <el-form-item label="Shop Name">
                      <el-input v-model="settings.company_name" />
                    </el-form-item>
                    <el-form-item label="Address">
                      <el-input v-model="settings.address" />
                    </el-form-item>
                    <el-form-item label="Phone#">
                      <el-input v-model="settings.phone" />
                    </el-form-item>
                    <el-form-item label="Invoice Footer">
                      <el-input v-model="settings.invoice_footer" type="textarea" :rows="2" placeholder="Enter invoice footer text here" />
                    </el-form-item>
                  </el-col>
                </el-row>
              </el-card>
            </el-col>
          </el-row>
        </el-tab-pane>
        <el-tab-pane label="Sale Settings" name="second">
          <el-row>
            <el-col :span="24">
              <el-card shadow="always">
                <el-row>
                  <el-col :span="24">
                    <el-form-item label="Show Disc2">
                      <el-switch
                        v-model="settings.show_disc2"
                        active-value="show"
                        inactive-value="dont"
                      />
                    </el-form-item>
                    <el-form-item label="Show Bonus">
                      <el-switch
                        v-model="settings.show_bonus"
                        active-value="show"
                        inactive-value="dont"
                      />
                    </el-form-item>
                    <el-form-item label="Show Expiry">
                      <el-switch
                        v-model="settings.show_expiry"
                        active-value="show"
                        inactive-value="dont"
                      />
                    </el-form-item>
                  </el-col>
                </el-row>
              </el-card>
            </el-col>
          </el-row>
        </el-tab-pane>
        <el-tab-pane label="Print Settings" name="third">
          <el-row>
            <el-col :span="24">
              <el-card shadow="always">
                <el-row>
                  <el-col :span="24">
                    <el-form-item label="Sale Print Size">
                      <el-select v-model="settings.print_size" placeholder="Select Size">
                        <el-option
                          v-for="item in print_sizes"
                          :key="item.value"
                          :label="item.label"
                          :value="item.value"
                        />
                      </el-select>
                    </el-form-item>
                  </el-col>
                </el-row>
              </el-card>
            </el-col>
          </el-row>
        </el-tab-pane>
        <el-tab-pane label="Size Color" name="fourth">
          <el-row>
            <el-col :span="24">
              <el-card shadow="always">
                <el-row>
                  <el-col :span="24">
                    <el-form-item label="Sale Print Size">
                      <el-input v-model="sizecolor.color" placeholder="Enter color" />
                      <el-input v-model="sizecolor.size" placeholder="Enter size" />
                      <el-button type="primary" @click="addSizeColor()">Add Size/Color</el-button>
                    </el-form-item>
                  </el-col>
                </el-row>
              </el-card>
            </el-col>
          </el-row>
        </el-tab-pane>
        <el-tab-pane label="Backup" name="fifth">
          <el-row>
            <el-col :span="24">
              <el-card shadow="always">
                <el-row>
                  <el-col :span="24">
                    <el-form-item label="Backup">
                      <el-button type="danger" :loading="loading" @click="backup()">Click to Backup</el-button>
                    </el-form-item>
                  </el-col>
                </el-row>
              </el-card>
            </el-col>
          </el-row>
        </el-tab-pane>
      </el-tabs>
      <el-row style="margin-top:20px;">
        <el-col :span="24">
          <el-button type="primary" size="medium" @click="saveSettings()">Save Settings</el-button>
        </el-col>
      </el-row>
    </el-form>
  </div>
</template>
<script>
import Resource from '@/api/resource';
import { backup } from '@/api/helper';
const setng = new Resource('settings');
const sizecolorRes = new Resource('variants');
export default {
  name: '',
  components: { },
  directives: { },
  data() {
    return {
      loading: false,
      print_sizes: [
        {
          value: 'a4',
          label: 'A4 or big printer',
        },
        {
          value: 'twoinch',
          label: '2 Inch',
        },
        {
          value: 'threeinch',
          label: '3 Inch',
        },
      ],
      activeName: 'shop_settings',
      settings: {
        company_name: '',
        address: '',
        phone: '',
        show_disc2: '',
        print_size: '',
      },
      sizecolor: {
        color: '',
        size: '',
      },
    };
  },
  computed: {
  },
  created() {
    this.getList();
  },
  methods: {
    async getList() {
      const { data } = await setng.list();
      this.settings = data.settings;
    },
    async saveSettings() {
      const { data } = await setng.update('1', this.settings);
      this.$message({
        type: 'success',
        message: 'Settings saved successfully.',
      });
    },
    async addSizeColor() {
      if (this.sizecolor.size == '' || this.sizecolor.color == '') {
        alert('enter values');
        return;
      }
      await sizecolorRes.store(this.sizecolor);
      this.sizecolor.color = '';
      this.sizecolor.size = '';
    },
    async backup() {
      this.loading = true;
      await backup();
      this.loading = false;
      this.$message({
        type: 'success',
        message: 'Backup generated "storage/app/Laravel".',
      });
    },
  },
};
</script>
<style  scoped>
</style>
