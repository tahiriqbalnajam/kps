<template>
  <div class="app-container">
    <qrcode-stream></qrcode-stream>
  </div>
</template>
<script>
import { QrcodeStream, QrcodeDropZone, QrcodeCapture } from 'vue-qrcode-reader'
import Resource from '@/api/resource';
const resourcePro = new Resource('resource');
export default {
  name: 'TeacherAttendance',
  props: [],
  components: { QrcodeStream, QrcodeDropZone, QrcodeCapture },
  directives: { },
  data() {
    return {
    };
  },
  computed: {
  },
  created() {
  },
  methods: {
  },
};
</script>
<style  scoped>
</style>
