<template>
  <el-row>
    <el-col :span="24" class="camera-selection">
      <el-select v-model="selectedCamera" placeholder="Select Camera" @change="onCameraChange">
        <el-option label="Front Camera" value="user"></el-option>
        <el-option label="Rear Camera" value="environment"></el-option>
      </el-select>
    </el-col>
    <el-col :span="24" class="qrcode-container">
      <qrcode-stream
        :paused="paused"
        :constraints="selectedConstraints"
        :track="trackFunctionSelected.value"
        :formats="selectedBarcodeFormats"
        @error="onError"
        @detect="onDetect"
        @camera-on="onCameraReady"
      >
        <div class="loading-indicator" v-if="loading"></div>
      </qrcode-stream>
    </el-col>
  </el-row>
</template>

<script>
import { QrcodeStream, QrcodeDropZone, QrcodeCapture } from 'vue-qrcode-reader';
import { onlineAtt } from '@/api/teacher';
export default {
    name: 'QrcodeAtt',
    components: {
        QrcodeStream,
        QrcodeDropZone,
        QrcodeCapture,
    },
    data() {
        return {
            paused: false,
            loading: true,
            result: '',
            selectedCamera: 'user', // Default to front camera
            selectedConstraints: { facingMode: 'user' },
            defaultConstraintOptions: {
                facingMode: 'user',
            },
            constraintOptions: [],
            trackFunctionOptions: [
                { text: 'nothing (default)', value: undefined },
                { text: 'outline', value: this.paintOutline },
                { text: 'centered text', value: this.paintCenterText },
                { text: 'bounding box', value: this.paintBoundingBox }
            ],
            trackFunctionSelected: { text: 'outline', value: this.paintOutline },
            barcodeFormats: {
                aztec: false,
                code_128: false,
                code_39: false,
                code_93: false,
                codabar: false,
                databar: false,
                databar_expanded: false,
                data_matrix: false,
                dx_film_edge: false,
                ean_13: false,
                ean_8: false,
                itf: false,
                maxi_code: false,
                micro_qr_code: false,
                pdf417: false,
                qr_code: true,
                rm_qr_code: false,
                upc_a: false,
                upc_e: false,
                linear_codes: false,
                matrix_codes: false
            },
            error: ''

        };
    },
    computed: {
        selectedBarcodeFormats() {
        return Object.keys(this.barcodeFormats).filter((format) => this.barcodeFormats[format]);
        }
    },
    methods: {
        onCameraChange(value) {
            this.selectedConstraints = { facingMode: value };
        },
        onError(error) {
            console.error(error);
        },
        async onDetect(detectedCodes) {
            console.log(detectedCodes);
            this.result = JSON.stringify(detectedCodes.map((code) => code.rawValue)).replace(/\["|"\]/g, '');
            console.log(this.result);
            const res = await onlineAtt(this.result);
            if(res.success){
                this.$message({
                    type: 'success',
                    message: "Attendance marked successfully",
                });
            } else {
                this.$message({
                    type: 'error',
                    message: res.error,
                });
            }

            this.paused = true
            await this.timeout(500)
            this.paused = false
        },
        timeout(ms) {
            return new Promise((resolve) => {
                window.setTimeout(resolve, ms)
            })
        },
        async onCameraReady() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                const videoDevices = devices.filter(({ kind }) => kind === 'videoinput');
                this.constraintOptions = [
                ...this.defaultConstraintOptions,
                ...videoDevices.map(({ deviceId, label }) => ({
                    label: `${label} (ID: ${deviceId})`,
                    constraints: { deviceId }
                }))
                ];
                this.error = '';
            } catch (err) {
                this.onError(err);
            }
        },
        paintOutline(detectedCodes, ctx) {
            for (const detectedCode of detectedCodes) {
                const [firstPoint, ...otherPoints] = detectedCode.cornerPoints;
                ctx.strokeStyle = 'red';
                ctx.beginPath();
                ctx.moveTo(firstPoint.x, firstPoint.y);
                for (const { x, y } of otherPoints) {
                ctx.lineTo(x, y);
                }
                ctx.lineTo(firstPoint.x, firstPoint.y);
                ctx.closePath();
                ctx.stroke();
            }
        },
        paintBoundingBox(detectedCodes, ctx) {
            for (const detectedCode of detectedCodes) {
                const { boundingBox: { x, y, width, height } } = detectedCode;
                ctx.lineWidth = 2;
                ctx.strokeStyle = '#007bff';
                ctx.strokeRect(x, y, width, height);
            }
        },
        paintCenterText(detectedCodes, ctx) {
            for (const detectedCode of detectedCodes) {
                const { boundingBox, rawValue } = detectedCode;
                const centerX = boundingBox.x + boundingBox.width / 2;
                const centerY = boundingBox.y + boundingBox.height / 2;
                const fontSize = Math.max(12, (50 * boundingBox.width) / ctx.canvas.width);
                ctx.font = `bold ${fontSize}px sans-serif`;
                ctx.textAlign = 'center';
                ctx.lineWidth = 3;
                ctx.strokeStyle = '#35495e';
                ctx.strokeText(detectedCode.rawValue, centerX, centerY);
                ctx.fillStyle = '#5cb984';
                ctx.fillText(rawValue, centerX, centerY);
            }
        },
        onError(err) {
            this.error = `[${err.name}]:`;
            if (err.name === 'NotAllowedError') {
                this.error += 'you need to grant camera access permission';
            } else if (err.name === 'NotFoundError') {
                this.error += 'no camera on this device';
            } else if (err.name === 'NotSupportedError') {
                this.error += 'secure context required (HTTPS, localhost)';
            } else if (err.name === 'NotReadableError') {
                this.error += 'is the camera already in use?';
            } else if (err.name === 'OverconstrainedError') {
                this.error += 'installed cameras are not suitable';
            } else if (err.name === 'StreamApiNotSupportedError') {
                this.error += 'Stream API is not supported in this browser';
            } else if (err.name === 'InsecureContextError') {
                this.error += 'Camera access is only permitted in secure context. Use HTTPS or localhost rather than HTTP.';
            } else {
                this.error += err.message;
            }
            this.$message({
                type: 'error',
                message: this.error,
            });
        }
    },
    mounted() {
        this.constraintOptions = this.defaultConstraintOptions;
    },
};
</script>

<style scoped>
.camera-selection {
  margin-bottom: 10px;
  text-align: center;
}

.qrcode-container {
  position: relative;
}

.circle-overlay {
  position: absolute;
  top: 50%;
  left: 50%;
  width: 200px;
  height: 200px;
  margin-top: -100px; /* Half of the height */
  margin-left: -100px; /* Half of the width */
  border: 2px solid red;
  border-radius: 50%;
  pointer-events: none; /* Allow clicks to pass through */
  background-color: rgba(128, 128, 128, 0.297)
}
</style>