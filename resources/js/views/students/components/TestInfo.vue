<template>
  <el-card class="test-card" shadow="never">
    <template #header>
      <div class="card-header"><strong>Test Scores</strong></div>
    </template>
    <div v-loading="loading">
      <el-empty v-if="subjectList.length === 0" description="No test records" :image-size="60" />

      <el-tabs v-else v-model="activeTab" class="subject-tabs">
        <el-tab-pane
          v-for="subject in subjectList"
          :key="subject.subject"
          :name="subject.subject"
        >
          <template #label>
            <div
              class="tab-label"
              :style="{
                '--score-color': scoreColor(subject.overall_percentage),
                '--score-soft': scoreSoft(subject.overall_percentage)
              }"
            >
              <span class="tab-name">{{ subject.subject }}</span>
              <span class="tab-pct">{{ Math.round(subject.overall_percentage || 0) }}%</span>
            </div>
          </template>

          <div class="subject-content">
            <div class="tests-table">
              <el-table
                :data="subject.tests || []"
                stripe
                size="small"
                empty-text="No tests recorded"
                style="width: 100%"
              >
                <el-table-column label="Date" width="100">
                  <template #default="scope">
                    {{ formatDate(scope.row.test_date) }}
                  </template>
                </el-table-column>
                <el-table-column label="Total Marks" prop="total_marks" align="center" width="90" />
                <el-table-column label="Obtained" prop="score" align="center" width="80" />
                <el-table-column label="%" align="center" width="70">
                  <template #default="scope">
                    <span class="score-pct">{{ Math.round(scope.row.percentage) }}%</span>
                  </template>
                </el-table-column>
              </el-table>
            </div>

            <div class="score-side">
              <el-progress
                type="dashboard"
                :percentage="Math.round(subject.overall_percentage || 0)"
                :width="84"
                :color="scoreColor(subject.overall_percentage)"
              >
                <template #default="{ percentage }">
                  <span class="percentage-value">{{ percentage }}%</span>
                  <span class="percentage-label">Overall</span>
                </template>
              </el-progress>

              <!-- Progress sparkline: trend across tests (up = green, down = red) -->
              <div v-if="sparks[subject.subject]" class="spark">
                <svg :viewBox="'0 0 112 64'" class="spark-svg" role="img">
                  <defs>
                    <linearGradient :id="sparks[subject.subject].id" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" :stop-color="sparks[subject.subject].color" stop-opacity="0.35" />
                      <stop offset="100%" :stop-color="sparks[subject.subject].color" stop-opacity="0" />
                    </linearGradient>
                  </defs>
                  <path :d="sparks[subject.subject].area" :fill="'url(#' + sparks[subject.subject].id + ')'" />
                  <path
                    :d="sparks[subject.subject].line"
                    fill="none"
                    :stroke="sparks[subject.subject].color"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <circle
                    v-for="(p, i) in sparks[subject.subject].points"
                    :key="i"
                    :cx="p.x"
                    :cy="p.y"
                    r="2.4"
                    :fill="p.last ? sparks[subject.subject].color : '#fff'"
                    :stroke="sparks[subject.subject].color"
                    stroke-width="1.2"
                    :title="p.label"
                  />
                </svg>
                <span class="trend" :style="{ color: sparks[subject.subject].color }">
                  {{ sparks[subject.subject].trendLabel }}
                </span>
              </div>
            </div>
          </div>
        </el-tab-pane>
      </el-tabs>
    </div>
  </el-card>
</template>

<script>
import moment from 'moment'
import { getSubjectWiseScores } from '@/api/student'

export default {
  name: 'TestInfo',
  data() {
    return {
      loading: false,
      activeTab: '',
      subjects: {},
      sparks: {}
    }
  },
  computed: {
    subjectList() {
      const subs = this.subjects || {}
      return Array.isArray(subs) ? subs : Object.values(subs)
    }
  },
  mounted() {
    this.getScores(this.$route.params.id)
  },
  methods: {
    async getScores(stdid) {
      this.loading = true
      try {
        const { data } = await getSubjectWiseScores(stdid)
        this.subjects = data.results
        this.activeTab = this.subjectList[0]?.subject || ''
        this.sparks = {}
        this.subjectList.forEach((s, i) => {
          this.sparks[s.subject] = this.buildSpark(s, i)
        })
      } finally {
        this.loading = false
      }
    },
    formatDate(date) {
      return date ? moment(date).format('DD MMM, YYYY') : '—'
    },
    scoreColor(percentage) {
      const p = Number(percentage) || 0
      if (p >= 80) return '#10b981'
      if (p >= 60) return '#f59e0b'
      return '#f43f5e'
    },
    scoreSoft(percentage) {
      // 10% alpha tint of the score color — always matches its solid version
      return this.scoreColor(percentage) + '1a'
    },
    // ── Sparkline ────────────────────────────────────────────────
    // Per-test percentages in chronological order (missing percentage
    // falls back to score/total_marks).
    sparkData(subject) {
      const tests = (subject.tests || []).slice()
      tests.sort((a, b) =>
        String(a.test_date || '').localeCompare(String(b.test_date || ''))
      )
      return tests.map((t) => {
        const raw = Number(t.percentage)
        const pct = Number.isFinite(raw)
          ? raw
          : Number(t.score) && Number(t.total_marks)
            ? (Number(t.score) / Number(t.total_marks)) * 100
            : 0
        return { date: t.test_date, pct }
      })
    },
    trendInfo(subject) {
      const values = this.sparkData(subject).map((d) => d.pct)
      if (values.length < 2) return null
      const first = values[0]
      const last = values[values.length - 1]
      const delta = Math.round(last - first)
      if (delta > 0) return { delta, color: '#10b981', arrow: '▲' }
      if (delta < 0) return { delta, color: '#f43f5e', arrow: '▼' }
      return { delta: 0, color: '#64748b', arrow: '—' }
    },
    buildSpark(subject, index) {
      const data = this.sparkData(subject)
      const trend = this.trendInfo(subject)
      if (data.length < 2 || !trend) return null

      const W = 112
      const H = 64
      const PAD_L = 2
      const PAD_R = 5
      const PAD_T = 5
      const PAD_B = 6
      const values = data.map((d) => d.pct)
      let min = Math.min(...values)
      let max = Math.max(...values)
      if (max - min < 1) {
        min -= 5
        max += 5
      }
      const x = (i) => PAD_L + (i * (W - PAD_L - PAD_R)) / (data.length - 1)
      const y = (v) => PAD_T + (1 - (v - min) / (max - min)) * (H - PAD_T - PAD_B)
      const pts = values.map((v, i) => [x(i), y(v)])
      const lastIdx = pts.length - 1

      // Smooth curve through the points (quadratic midpoints technique)
      let line = `M ${pts[0][0]},${pts[0][1]}`
      for (let i = 1; i < lastIdx; i++) {
        const mx = (pts[i][0] + pts[i + 1][0]) / 2
        const my = (pts[i][1] + pts[i + 1][1]) / 2
        line += ` Q ${pts[i][0]},${pts[i][1]} ${mx},${my}`
      }
      line += ` T ${pts[lastIdx][0]},${pts[lastIdx][1]}`

      const baseline = H - PAD_B
      const area = `${line} L ${pts[lastIdx][0]},${baseline} L ${pts[0][0]},${baseline} Z`

      const sign = trend.delta > 0 ? '+' : ''
      return {
        id: `spark-${index}`,
        color: trend.color,
        line,
        area,
        points: data.map((d, i) => ({
          x: x(i),
          y: y(d.pct),
          last: i === lastIdx,
          label: `${this.formatDate(d.date)}: ${Math.round(d.pct)}%`
        })),
        trendLabel: `${trend.arrow} ${sign}${trend.delta}%`
      }
    }
  }
}
</script>

<style scoped>
.subject-tabs {
  /* Hide the default underline — the pill IS the tab indicator */
  :deep(.el-tabs__active-bar) {
    display: none;
  }
  :deep(.el-tabs__nav-wrap::after) {
    height: 0;
  }
  :deep(.el-tabs__item) {
    padding: 4px 4px 8px;
    height: auto;
  }
  :deep(.el-tabs__content) {
    padding-top: 12px;
  }
}

.tab-label {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 3px 6px 3px 12px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 600;
  line-height: 1.6;
  background: var(--score-soft);
  color: var(--score-color);
  transition: all 0.2s ease;
  white-space: nowrap;
}

.tab-pct {
  min-width: 38px;
  text-align: center;
  border-radius: 999px;
  padding: 0 7px;
  background: var(--score-color);
  color: #fff;
  font-size: 11px;
  line-height: 19px;
}

/* Active tab: the pill fills solid with the subject's score color */
:deep(.el-tabs__item.is-active) .tab-label {
  background: var(--score-color);
  color: #fff;
}
:deep(.el-tabs__item.is-active) .tab-label .tab-pct {
  background: #fff;
  color: var(--score-color);
}

/* Table flexes, right side keeps a fixed size so ring + sparkline always fit */
.subject-content {
  display: flex;
  align-items: center;
  gap: 16px;
}

.tests-table {
  flex: 1;
  min-width: 0;
}

.score-side {
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 10px;
}

.spark {
  text-align: center;
}

.spark-svg {
  display: block;
}

.trend {
  display: inline-flex;
  align-items: center;
  font-size: 12px;
  font-weight: 700;
  margin-top: 2px;
}

.percentage-value {
  font-size: 16px;
  font-weight: 700;
  color: #1e293b;
}

.percentage-label {
  display: block;
  margin-top: 2px;
  font-size: 12px;
  color: #64748b;
}

.score-pct {
  font-weight: 600;
  color: #4f46e5;
}
</style>
