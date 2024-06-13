<template>
    <el-card>
      <div class="user-profile">
        <div class="box-center">
          <div class="user-name text-center">
            Tahir IQbal
          </div>
        </div>
        <div class="box-social">
          <el-descriptions
              :column="1"
              :size="50"
              border
              :style="{boxShadow: 'Light Shadow', borderRadius: 'Large Radius'}"
          >
            <el-descriptions-item label="Name">Tahir Iqbal</el-descriptions-item>
            <el-descriptions-item label="Father">Iqbal</el-descriptions-item>
            <el-descriptions-item label="Class">One</el-descriptions-item>
            <el-descriptions-item label="Reg#">4646</el-descriptions-item>
            <el-descriptions-item label="Name">Tahir Iqbal</el-descriptions-item>
          </el-descriptions>
        </div>
      </div>
    </el-card>
</template>
<script setup>
import { ref } from 'vue';
import { useRoute } from 'vue-router'
import Resource from '@/api/resource';
import { get } from 'lodash';
const student = new Resource('students');

const resData = ref({
  student: {}
});

const query = ref({
  filter: {},
})

const getStudent = async (query) => {
  const { data } = await student.list(query);
  resData.student = data.students.data[0];
};
onMounted(() => {
  const route = useRoute()
  query.filter = {
        ['id']: route.params.id,
  }
  getStudent(query);
});

</script>

<style lang="scss" scoped>
.user-profile {
  .user-name {
    font-weight: bold;
  }

  .box-center {
    padding-top: 10px;
  }

  .user-role {
    padding-top: 10px;
    font-weight: 400;
    font-size: 14px;
  }

  .box-social {
    padding-top: 30px;

    .el-table {
      border-top: 1px solid #dfe6ec;
    }
  }

  .user-follow {
    padding-top: 20px;
  }
}
</style>