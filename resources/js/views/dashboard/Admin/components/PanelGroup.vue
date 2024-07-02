<template>
  <el-row :gutter="40" class="panel-group">
    <el-col :xs="12" :sm="12" :md="3" :lg="3" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-description">
          <div class="card-panel-text">Total Students</div>
          <span class="card-panel-num">{{ data.total_students }}</span>
          <!--<count-to :start-val="0" :end-val="102400" :duration="2600" class="card-panel-num" />-->
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :md="3" :lg="3" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-description">
          <div class="card-panel-text">Total Absent</div>
          <span class="card-panel-num">{{ data.total_absent_students }}</span>
          <!--<count-to :start-val="0" :end-val="81212" :duration="3000" class="card-panel-num" />-->
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :md="3" :lg="3" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-description">
          <div class="card-panel-text">Total Teachers</div>
          <span class="card-panel-num">{{ data.total_teachers }}</span>
          <!--<count-to :start-val="0" :end-val="81212" :duration="3000" class="card-panel-num" />-->
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :md="3" :lg="3" class="card-panel-col">
      <div class="card-panel">
        <div class="card-panel-description">
          <div class="card-panel-text">Absent Teachers</div>
          <el-row :gutter="20" justify="center">
            <el-col :span="6">
              <span class="card-panel-num">{{ data.total_absent_teachers }}</span>
            </el-col>
            <el-col :span="18">
              <el-scrollbar height="30px">
                <span v-for="teacher in data.absent_teachers" :key="teacher">{{ teacher }}</span>
              </el-scrollbar>
            </el-col>
          </el-row>
          
          
          <!--<count-to :start-val="0" :end-val="81212" :duration="3000" class="card-panel-num" />-->
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel" @click="handleSetLineChartData('purchases')">
        <div class="card-panel-icon-wrapper icon-money">
          <icon class-name="currency-dollar card-panel-icon"/>
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">Purchases</div>
          <span class="card-panel-num">9280</span>
          <!--<count-to :start-val="0" :end-val="9280" :duration="3200" class="card-panel-num" />-->
        </div>
      </div>
    </el-col>
    <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
      <div class="card-panel" @click="handleSetLineChartData('shoppings')">
        <div class="card-panel-icon-wrapper icon-shopping">
          <icon class-name="shop card-panel-icon"/>
        </div>
        <div class="card-panel-description">
          <div class="card-panel-text">Shoppings</div>
          <span class="card-panel-num">13600</span>
          <!-- <CountTo :start-val="0" :end-val="13600" class="card-panel-num" />-->
        </div>
      </div>
    </el-col>
  </el-row>
</template>

<script setup>
import Resource from '@/api/resource';
let dashRes = new Resource('dashboard');
import { ref } from 'vue';

const getData = async () => {
  try {
    const response = await dashRes.list(); // Assuming there is a method called getData in the dashRes object
    return response.data;
  } catch (error) {
    console.error(error);
    return null;
  }
};

const data = ref(null);

getData().then((result) => {
  if (result) {
    data.value = result;
  }
});

const emit = defineEmits(['handleSetLineChartData'])
const handleSetLineChartData = (type) => {
  emit('handleSetLineChartData', type)
}
</script>

<style lang="scss" scoped>
.panel-group {
  margin-top: 18px;

  .card-panel-col {
    margin-bottom: 32px;
  }

  .card-panel {
    height: 108px;
    cursor: pointer;
    font-size: 12px;
    position: relative;
    overflow: hidden;
    color: #666;
    background: #fff;
    box-shadow: 4px 4px 40px rgba(0, 0, 0, 0.05);
    border-color: rgba(0, 0, 0, 0.05);

    &:hover {
      .card-panel-icon-wrapper {
        color: #fff;
      }

      .icon-people {
        background: #40c9c6;
      }

      .icon-message {
        background: #36a3f7;
      }

      .icon-money {
        background: #f4516c;
      }

      .icon-shopping {
        background: #34bfa3;
      }
    }

    .icon-people {
      color: #40c9c6;
    }

    .icon-message {
      color: #36a3f7;
    }

    .icon-money {
      color: #f4516c;
    }

    .icon-shopping {
      color: #34bfa3;
    }

    .card-panel-icon-wrapper {
      float: left;
      margin: 14px 0 0 14px;
      padding: 16px;
      transition: all 0.38s ease-out;
      border-radius: 6px;
    }

    .card-panel-icon {
      float: left;
      font-size: 48px;
    }

    .card-panel-description {
      float: right;
      font-weight: bold;
      margin: 26px;
      margin-left: 0px;

      .card-panel-text {
        line-height: 18px;
        color: rgba(0, 0, 0, 0.45);
        font-size: 16px;
        margin-bottom: 12px;
      }

      .card-panel-num {
        font-size: 20px;
      }
    }
  }
}

@media (max-width: 550px) {
  .card-panel-description {
    display: none;
  }

  .card-panel-icon-wrapper {
    float: none !important;
    width: 100%;
    height: 100%;
    margin: 0 !important;

    .svg-icon {
      display: block;
      margin: 14px auto !important;
      float: none !important;
    }
  }
}
</style>
