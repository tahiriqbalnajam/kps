<template>
  <el-card v-if="user.name">
    <el-tabs v-model="activeActivity" @tab-click="handleClick">
      <el-tab-pane v-loading="updating" :label="t('user.account')" name="second">
        <el-form :model="user" label-width="120px">
          <el-form-item :label="t('user.name')">
            <el-input v-model="user.name" :disabled="disabled"/>
          </el-form-item>
          <el-form-item :label="t('user.email')">
            <el-input v-model="user.email" :disabled="disabled"/>
          </el-form-item>
          
          <!-- Password change section -->
          <el-divider content-position="left">Change Password</el-divider>
          <el-form-item label="New Password">
            <el-input 
              v-model="passwordForm.password" 
              type="password" 
              show-password
              placeholder="Enter new password"
            />
          </el-form-item>
          <el-form-item label="Confirm Password">
            <el-input 
              v-model="passwordForm.password_confirmation" 
              type="password" 
              show-password
              placeholder="Confirm new password"
            />
          </el-form-item>
          <!-- End password change section -->
          <el-form-item>
            <el-button type="primary" @click="onSubmit">
              {{t('form.save')}}
            </el-button>
          </el-form-item>
        </el-form>
      </el-tab-pane>
    </el-tabs>
  </el-card>
</template>

<script setup>
import UserResource from '@/api/user'
import dayjs from 'dayjs'
import {ElMessage} from "element-plus"
import {useI18n} from "vue-i18n";

const props = defineProps({
  user: {
    type: Object,
    default: () => {
      return {
        name: '',
        email: '',
        avatar: '',
        roles: [],
      }
    },
  },
})

const {t} = useI18n({useScope: 'global'})

// Add passwordForm data
const passwordForm = reactive({
  password: '',
  password_confirmation: ''
})

const userResource = new UserResource('users')
const resData = reactive({
  activeActivity: 'second',
  disabled: computed(() => {
    if (props.user.id) {
      getTimeLines()
    }
    return props.user.roles && props.user.roles.includes('admin')
  }),
  updating: false,
  timeLinesData: [],
  timeLinesParams: {
    page: 1,
    per_page: 10,
  },
  timeLinesPagination: {
    total: 0,
    currentPage: 1,
    pageSize: 10
  }
})

const handleClick = (tab, event) => {

}

// Add password validation function
const validatePassword = () => {
  if (passwordForm.password && !passwordForm.password_confirmation) {
    ElMessage.error('Please confirm your password')
    return false
  }
  
  if (passwordForm.password && passwordForm.password_confirmation && 
      passwordForm.password !== passwordForm.password_confirmation) {
    ElMessage.error('Passwords do not match')
    return false
  }
  
  return true
}

const onSubmit = () => {
  // Validate password if it's being changed
  if (passwordForm.password && !validatePassword()) {
    return
  }
  
  resData.updating = true
  let params = {
    name: props.user.name,
    email: props.user.email,
    sex: props.user.sex
  }
  
  if (props.user.birthday) {
    params.birthday = dayjs(props.user.birthday).format('YYYY-MM-DD HH:mm:ss')
  }
  
  // Add password to params if it's being changed
  if (passwordForm.password) {
    params.password = passwordForm.password
    params.password_confirmation = passwordForm.password_confirmation
  }
  
  userResource
      .update(props.user.id, params)
      .then(response => {
        resData.updating = false
        
        // Reset password fields after successful update
        passwordForm.password = ''
        passwordForm.password_confirmation = ''
        
        ElMessage({
          message: 'User information has been updated successfully',
          type: 'success',
          duration: 5 * 1000,
        })
      })
      .catch(error => {
        console.log(error)
        resData.updating = false
      })
}

const getTimeLines = () => {
  userResource.logs(props.user.id, resData.timeLinesParams).then((res) => {
    resData.timeLinesData = res.data
    resData.timeLinesPagination = res.pages
  })
}

const {activeActivity, updating, disabled, timeLinesData, timeLinesPagination} = toRefs(resData)
</script>

<style lang="scss" scoped>
.user-activity {
  .user-block {
    .username, .description {
      display: block;
      margin-left: 50px;
      padding: 2px 0;
    }

    img {
      width: 40px;
      height: 40px;
      float: left;
    }

    :after {
      clear: both;
    }

    .img-circle {
      border-radius: 50%;
      border: 2px solid #d2d6de;
      padding: 2px;
    }

    span {
      font-weight: 500;
      font-size: 12px;
    }
  }

  .post {
    font-size: 14px;
    border-bottom: 1px solid #d2d6de;
    margin-bottom: 15px;
    padding-bottom: 15px;
    color: #666;

    .image {
      width: 100%;
    }

    .user-images {
      padding-top: 20px;
    }
  }

  .list-inline {
    padding-left: 0;
    margin-left: -5px;
    list-style: none;

    li {
      display: inline-block;
      padding-right: 5px;
      padding-left: 5px;
      font-size: 13px;
    }

    .link-black {
      &:hover, &:focus {
        color: #999;
      }
    }
  }

  .el-carousel__item h3 {
    color: #475669;
    font-size: 14px;
    opacity: 0.75;
    line-height: 200px;
    margin: 0;
  }

  .el-carousel__item:nth-child(2n) {
    background-color: #99a9bf;
  }

  .el-carousel__item:nth-child(2n+1) {
    background-color: #d3dce6;
  }
}
</style>
