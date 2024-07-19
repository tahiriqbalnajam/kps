<!-- components/Result.vue -->
<template>
    <el-card>
      <h2>Your Score</h2>
      <p>You answered correctly {{ score }}% of the questions.</p>
    </el-card>
  </template>
  
  <script>
  export default {
    props: ['score']
  };
  </script>