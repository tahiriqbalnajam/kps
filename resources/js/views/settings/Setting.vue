<script setup>
    import { reactive } from 'vue';
    import moment from 'moment';
    import Resource from '@/api/resource.js';
//    import HeadControls from '@/components/HeadControls.vue';
    // import ref from 'vue';
    import TabsPaneContext from 'element-plus';
    import { ref } from 'vue'
    // import TabsPaneContext from 'element-plus'

    const activeName = ref('first')

    const tab = ref(null)
    const event = ref(null)
    const handleClick = (t, e) => {
      tab.value = t
      event.value = e
      console.log(tab.value, event.value);
      getList();
    }

    let settingResource = new Resource('settings');
    const $this =  reactive({
                form_element:{
                  updating:false,
                },
                teacher_form: {
                  teacher_leaves_allowed: 0,
                },
                exam_form: {
                  result_header: '',
                },
            })
    const onSubmit = () => {
      $this.form_element.updating = true
      settingResource
      .store($this.teacher_form)
      .then(response => {
        $this.form_element.updating = false
        ElMessage({
          message: 'Information has been updated successfully',
          type: 'success',
          duration: 5 * 1000,
        })
      })
      .catch(error => {
        console.log(error)
        $this.form_element.updating = false
      })
    }
    const saveExamSettings = () => {
      $this.form_element.updating = true
      settingResource
      .store($this.exam_form)
      .then(response => {
        $this.form_element.updating = false
        ElMessage({
          message: 'Information has been updated successfully',
          type: 'success',
          duration: 5 * 1000,
        })
      })
      .catch(error => {
        console.log(error)
        $this.form_element.updating = false
      })
    }
    const getList = async() => {
        const {data} = await settingResource.list();
        $this.teacher_form.teacher_leaves_allowed = await data.settings.teacher_leaves_allowed;
        $this.exam_form.result_header = await data.settings.result_header;
    }
    getList();
</script>


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
                  <el-tab-pane v-loading="$this.form_element.updating" label="Teacher Settings" name="first">
                    Teacher Settings
                    <el-form :model="form" >
                      <el-form-item label="Allowed Leaves">
                        <el-input v-model="$this.teacher_form.teacher_leaves_allowed" placeholder="Please input allowed leaves " />
                      </el-form-item>
                      <el-button type="primary" @click="onSubmit" :loading="$this.form_element.updating">Save</el-button>
                    </el-form>
                  </el-tab-pane>
                  <el-tab-pane label="Students Settings" name="second">Students Settings</el-tab-pane>
                  <el-tab-pane label="User Settings" name="third">User Settings</el-tab-pane>
                  <el-tab-pane label="Exam Settings" name="exam">
                    <el-form :model="form" label-width="120px">
                      <el-form-item label="Result Header">
                        <el-input v-model="$this.exam_form.result_header" type="textarea" :row="6" placeholder="HTML of header" />
                      </el-form-item>
                      <el-form-item>
                        <el-button type="primary" @click="saveExamSettings()" :loading="$this.form_element.updating">Save</el-button>
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


<style  scoped>
</style>