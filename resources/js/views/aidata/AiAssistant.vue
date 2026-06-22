<template>
  <div class="ai-assistant">
    <el-card shadow="never" class="ai-assistant__card">
      <template #header>
        <div class="ai-assistant__header">
          <span class="ai-assistant__title">
            <i class="bi bi-robot"></i>
            AI Data Assistant
          </span>
          <el-tag size="small" type="info">students · attendance · fees</el-tag>
        </div>
      </template>

      <!-- Conversation thread -->
      <div class="ai-assistant__thread" ref="thread">
        <div v-if="!messages.length" class="ai-assistant__empty">
          Ask a question about your school data. For example:
          <ul>
            <li>“How many students are in Class 5-B?”</li>
            <li>“Who was absent this week in Class 5?”</li>
            <li>“List students with unpaid fee vouchers this month.”</li>
            <li>“What is the fee collection summary for this month?”</li>
          </ul>
        </div>

        <div
          v-for="msg in messages"
          :key="msg.id"
          class="ai-assistant__msg"
          :class="msg.role === 'user' ? 'is-user' : 'is-assistant'"
        >
          <div class="ai-assistant__bubble">
            <template v-if="msg.role === 'user'">{{ msg.content }}</template>

            <template v-else>
              <div v-if="msg.loading" class="ai-assistant__loading">
                <el-icon class="is-loading"><Loading /></el-icon>
                Thinking…
              </div>

              <template v-else>
                <p v-if="msg.content" class="ai-assistant__summary">{{ msg.content }}</p>

                <div v-if="msg.payload && msg.payload.highlights && msg.payload.highlights.length" class="ai-assistant__highlights">
                  <el-tag
                    v-for="(h, i) in msg.payload.highlights"
                    :key="i"
                    type="success"
                    effect="plain"
                    class="ai-assistant__tag"
                  >{{ h }}</el-tag>
                </div>

                <el-table
                  v-if="normalizedTable(msg.payload)"
                  :data="normalizedTable(msg.payload).rows"
                  border
                  size="small"
                  class="ai-assistant__table"
                >
                  <el-table-column
                    v-for="col in normalizedTable(msg.payload).columns"
                    :key="col"
                    :prop="col"
                    :label="col"
                    min-width="120"
                  />
                </el-table>

                <div
                  v-if="msg.payload && msg.payload.chart && msg.payload.chart.data"
                  :ref="el => { if (el) chartEls[msg.id] = el }"
                  class="ai-assistant__chart"
                ></div>

                <div v-if="msg.payload && msg.payload.sources && msg.payload.sources.length" class="ai-assistant__sources">
                  <span class="ai-assistant__sources-label">Tools used:</span>
                  <el-tag
                    v-for="(s, i) in msg.payload.sources"
                    :key="i"
                    size="small"
                    type="info"
                    class="ai-assistant__tag"
                  >{{ s }}</el-tag>
                </div>
              </template>
            </template>
          </div>
        </div>
      </div>

      <!-- Input -->
      <div class="ai-assistant__input">
        <el-input
          v-model="input"
          type="textarea"
          :rows="2"
          placeholder="Ask about students, attendance, or fees…"
          :disabled="loading"
          @keyup.enter="send"
        />
        <el-button type="primary" :loading="loading" @click="send">Send</el-button>
      </div>
    </el-card>
  </div>
</template>

<script>
import { Loading } from '@element-plus/icons-vue'
import * as echarts from 'echarts'
import { queryAgent } from '@/api/aidata'

export default {
  name: 'AiAssistant',
  components: { Loading },
  data() {
    return {
      input: '',
      loading: false,
      messages: [],
      nextId: 1,
      chartEls: {},
      chartInstances: [],
    }
  },
  beforeUnmount() {
    this.chartInstances.forEach(i => i && i.dispose && i.dispose())
    this.chartInstances = []
  },
  methods: {
    async send() {
      const text = (this.input || '').trim()
      if (!text || this.loading) return

      this.messages.push({ id: this.nextId++, role: 'user', content: text })
      this.input = ''

      const assistantId = this.nextId++
      this.messages.push({ id: assistantId, role: 'assistant', content: '', loading: true, payload: null })
      this.loading = true
      this.scrollToBottom()

      try {
        const res = await queryAgent(text)
        const payload = res && res.data ? res.data : res

        const msg = this.messages.find(m => m.id === assistantId)
        if (msg) {
          msg.loading = false
          msg.payload = payload
          msg.content = (payload && payload.summary) || ''
        }

        this.$nextTick(() => this.renderChart(assistantId, payload && payload.chart))
      } catch (e) {
        const msg = this.messages.find(m => m.id === assistantId)
        if (msg) {
          msg.loading = false
          msg.content = 'Sorry, the request failed. Please try again.'
          msg.error = true
        }
      } finally {
        this.loading = false
        this.scrollToBottom()
      }
    },

    normalizedTable(payload) {
      if (!payload || !payload.table) return null
      const table = payload.table
      if (!table.columns || !table.columns.length || !table.rows || !table.rows.length) return null

      const columns = table.columns
      const rows = table.rows.map(row => {
        if (Array.isArray(row)) {
          const obj = {}
          columns.forEach((c, i) => { obj[c] = row[i] })
          return obj
        }
        return row
      })

      return { columns, rows }
    },

    renderChart(id, chart) {
      const el = this.chartEls[id]
      if (!el || !chart || !chart.type || !chart.data) return

      try {
        const data = (chart.data || []).map(d => ({
          name: d.name || d.label || d.x || (d.category ?? ''),
          value: Number(d.value ?? d.y ?? 0),
        }))

        let option = {}
        if (chart.type === 'pie') {
          option = { tooltip: { trigger: 'item' }, series: [{ type: 'pie', radius: '60%', data }] }
        } else {
          option = {
            tooltip: {},
            xAxis: { type: 'category', data: data.map(d => d.name) },
            yAxis: { type: 'value' },
            series: [{ type: chart.type === 'line' ? 'line' : 'bar', data: data.map(d => d.value), smooth: true }],
          }
        }

        const inst = echarts.init(el)
        inst.setOption(option)
        this.chartInstances.push(inst)
      } catch (e) {
        // Chart rendering is best-effort; never fail the message.
      }
    },

    scrollToBottom() {
      this.$nextTick(() => {
        const t = this.$refs.thread
        if (t) t.scrollTop = t.scrollHeight
      })
    },
  },
}
</script>

<style scoped>
.ai-assistant__card { max-width: 100%; }
.ai-assistant__header { display: flex; align-items: center; justify-content: space-between; }
.ai-assistant__title { font-weight: 600; font-size: 16px; }
.ai-assistant__title i { margin-right: 6px; }

.ai-assistant__thread {
  min-height: 320px;
  max-height: 60vh;
  overflow-y: auto;
  padding: 8px 4px;
}
.ai-assistant__empty { color: #909399; padding: 12px; }
.ai-assistant__empty ul { margin: 8px 0 0; padding-left: 20px; }

.ai-assistant__msg { margin-bottom: 16px; display: flex; }
.ai-assistant__msg.is-user { justify-content: flex-end; }
.ai-assistant__msg.is-assistant { justify-content: flex-start; }

.ai-assistant__bubble {
  max-width: 80%;
  padding: 10px 14px;
  border-radius: 8px;
  background: #f5f7fa;
}
.ai-assistant__msg.is-user .ai-assistant__bubble {
  background: #ecf5ff;
}
.ai-assistant__summary { margin: 0 0 8px; line-height: 1.5; }
.ai-assistant__loading { color: #909399; }
.ai-assistant__highlights, .ai-assistant__sources { margin-bottom: 8px; }
.ai-assistant__sources-label { font-size: 12px; color: #909399; margin-right: 6px; }
.ai-assistant__tag { margin: 0 6px 6px 0; }
.ai-assistant__table { margin: 8px 0; }
.ai-assistant__chart { width: 100%; height: 260px; margin: 8px 0; }

.ai-assistant__input { display: flex; gap: 10px; align-items: flex-end; margin-top: 12px; }
.ai-assistant__input .el-button { height: 40px; }
</style>