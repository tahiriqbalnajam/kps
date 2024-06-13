<template>
    <el-row :gutter="20">
      <el-col :span="6">
        <student-info />
      </el-col>
      <el-col :span="9">
        <el-card>
          <el-row :gutter="20">
            <el-col :span="8">
              <el-card>
                <template #header>
                  PRESENTS
                </template>
                <el-text>asdfas</el-text>
              </el-card>
            </el-col>
            <el-col :span="8">
              <el-card>
                <div class="box-center">
                  <div class="user-name">afa</div>
                  <div class="user-role">asdfa</div>
                </div>
              </el-card>
            </el-col>
            <el-col :span="8">
              <el-card>
                <div class="box-center">
                  <div class="user-name">afa</div>
                  <div class="user-role">asdfa</div>
                </div>
              </el-card>
            </el-col>
          </el-row>
        </el-card>     
      </el-col>
      <el-col :span="9">
        Exam report
      </el-col>
    </el-row>
</template>

<script setup>

import StudentInfo from './components/StudentInfo.vue'
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router'

// reactive state
const count = ref(0)

// functions that mutate state and trigger updates
function increment() {
  count.value++
}

// lifecycle hooks
onMounted(() => {
  console.log(`The initial count is ${count.value}.`)
})
</script>

<style scoped>
/* Add your component styles here */
</style>
