<template>
  <el-card class="test-card" shadow="never">
    <template #header>
      <div class="card-header"><strong>Class Test Scores</strong></div>
    </template>
    <div v-loading="loading">
      <el-empty v-if="classList.length === 0" description="No test records" :image-size="60" />

      <el-tabs v-else v-model="activeTab" class="subject-tabs">
        <el-tab-pane
          v-for="classItem in classList"
          :key="classItem.class"
          :name="classItem.class"
        >
          <template #label>
            <div
              class="tab-label"
              :style="{
                '--score-color': scoreColor(classItem.overall),
                '--score-soft': scoreSoft(classItem.overall)
              }"
            >
              <span class="tab-name">{{ classItem.class }}</span>
              <span class="tab-pct">{{ Math.round(classItem.overall) }}%</span>
            </div>
          </template>

          <el-empty
            v-if="Object.keys(classItem.subjects || {}).length === 0"
            description="No subjects recorded"
            :image-size="50"
          />
          <el-row v-else :gutter="16">
            <el-col
              v-for="(tests, subjectName) in classItem.subjects"
              :key="subjectName"
              :xs="24"
              :sm="12"
              :md="8"
              :lg="8"
            >
              <el-card class="subject-card" shadow="never">
                <div class="subject-header">
                  <h3>{{ subjectName }}</h3>
                  <div class="subject-meta">
                    <el-tag size="small" :type="scoreTagType(subjectOverall(tests))">
                      {{ Math.round(subjectOverall(tests)) }}%
                    </el-tag>
                    <!-- Inline sparkline: trend across the subject's tests (up = green, down = red) -->
                    <div v-if="sparkOf(classItem.class, subjectName)" class="spark">
                      <svg :viewBox="'0 0 64 24'" class="spark-svg" role="img">
                        <defs>
                          <linearGradient :id="sparkOf(classItem.class, subjectName).id" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" :stop-color="sparkOf(classItem.class, subjectName).color" stop-opacity="0.3" />
                            <stop offset="100%" :stop-color="sparkOf(classItem.class, subjectName).color" stop-opacity="0" />
                          </linearGradient>
                        </defs>
                        <path :d="sparkOf(classItem.class, subjectName).area" :fill="'url(#' + sparkOf(classItem.class, subjectName).id + ')'" />
                        <path
                          :d="sparkOf(classItem.class, subjectName).line"
                          fill="none"
                          :stroke="sparkOf(classItem.class, subjectName).color"
                          stroke-width="1.4"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <circle
                          v-for="(p, i) in sparkOf(classItem.class, subjectName).points"
                          :key="i"
                          :cx="p.x"
                          :cy="p.y"
                          r="1.4"
                          :fill="p.last ? sparkOf(classItem.class, subjectName).color : '#fff'"
                          :stroke="sparkOf(classItem.class, subjectName).color"
                          stroke-width="0.8"
                          :title="p.label"
                        />
                      </svg>
                      <span class="trend" :style="{ color: sparkOf(classItem.class, subjectName).color }">
                        {{ sparkOf(classItem.class, subjectName).trendLabel }}
                      </span>
                    </div>
                  </div>
                </div>

                <el-table
                  :data="tests"
                  stripe
                  size="small"
                  empty-text="No tests recorded"
                  style="width: 100%"
                >
                  <el-table-column label="Title" prop="test_title" show-overflow-tooltip />
                  <el-table-column label="Avg" prop="average_marks" align="center" width="55" />
                  <el-table-column label="Ttl" prop="total_marks" align="center" width="50" />
                  <el-table-column label="%" align="center" width="55">
                    <template #default="scope">
                      <span class="score-pct">{{ Math.round(scope.row.percent) }}%</span>
                    </template>
                  </el-table-column>
                </el-table>
              </el-card>
            </el-col>
          </el-row>
        </el-tab-pane>
      </el-tabs>
    </div>
  </el-card>
</template>

<script>
import { getTests } from '@/api/teacher'

export default {
  name: 'TeacherTests',
  data() {
    return {
      loading: false,
      activeTab: '',
      testData: [],
      sparks: {}
    }
  },
  computed: {
    // One tab per class, carrying its overall % across every subject's tests
    classList() {
      return (this.testData || []).map((c) => {
        const percents = []
        Object.values(c.subjects || {}).forEach((tests) => {
          ;(tests || []).forEach((t) => {
            const p = Number(t.percent)
            if (Number.isFinite(p)) percents.push(p)
          })
        })
        const overall = percents.length
          ? percents.reduce((a, b) => a + b, 0) / percents.length
          : 0
        return { ...c, overall }
      })
    }
  },
  mounted() {
    this.getTestsAvg(this.$route.params.id)
  },
  methods: {
    async getTestsAvg(teacherId) {
      this.loading = true
      try {
        const { data } = await getTests(teacherId)
        this.testData = data.tests
        this.activeTab = this.classList[0]?.class || ''
        // Build one sparkline per subject (keyed by class + subject, so the
        // same subject in different classes keeps its own chart)
        this.sparks = {}
        this.classList.forEach((c) => {
          Object.keys(c.subjects || {}).forEach((subjectName) => {
            this.sparks[c.class + '|' + subjectName] = this.buildSpark(
              c.subjects[subjectName],
              Object.keys(this.sparks).length
            )
          })
        })
      } finally {
        this.loading = false
      }
    },
    subjectOverall(tests) {
      const percents = (tests || [])
        .map((t) => Number(t.percent))
        .filter((p) => Number.isFinite(p))
      return percents.length
        ? percents.reduce((a, b) => a + b, 0) / percents.length
        : 0
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
    scoreTagType(percentage) {
      const p = Number(percentage) || 0
      if (p >= 80) return 'success'
      if (p >= 60) return 'warning'
      return 'danger'
    },
    sparkOf(className, subjectName) {
      return this.sparks[className + '|' + subjectName]
    },
    // ── Sparkline ────────────────────────────────────────────────
    // Teacher tests carry no test_date, so chart order = API order
    // (chronological from the backend); tooltips show the test title.
    sparkData(tests) {
      return (tests || []).map((t) => ({
        label: String(t.test_title || 'Test'),
        pct: Number(t.percent) || 0
      }))
    },
    buildSpark(tests, index) {
      const data = this.sparkData(tests)
      if (data.length < 2) return null

      const W = 64
      const H = 24
      const PAD_L = 1
      const PAD_R = 3
      const PAD_T = 2
      const PAD_B = 3
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

      const first = values[0]
      const last = values[lastIdx]
      const delta = Math.round(last - first)
      let trend
      if (delta > 0) trend = { delta, color: '#10b981', arrow: '▲' }
      else if (delta < 0) trend = { delta, color: '#f43f5e', arrow: '▼' }
      else trend = { delta: 0, color: '#64748b', arrow: '—' }

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
          label: `${d.label}: ${Math.round(d.pct)}%`
        })),
        trendLabel: `${trend.arrow} ${sign}${trend.delta}%`
      }
    }
  }
}
</script>

<style scoped lang="scss">
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

/* Active tab: the pill fills solid with the class's score color */
:deep(.el-tabs__item.is-active) .tab-label {
  background: var(--score-color);
  color: #fff;
}
:deep(.el-tabs__item.is-active) .tab-label .tab-pct {
  background: #fff;
  color: var(--score-color);
}

.subject-card {
  margin-bottom: 16px;

  // Compact table so rows stay on one line
  :deep(.el-table th.el-table__cell),
  :deep(.el-table td.el-table__cell) {
    padding: 6px 0;
    font-size: 12px;
  }
}

.subject-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  margin-bottom: 10px;
  flex-wrap: wrap;

  h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
  }
}

/* Tag + inline sparkline share the right side of the header row */
.subject-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-left: auto;
}

.spark {
  display: flex;
  align-items: center;
  gap: 4px;
}

.spark-svg {
  display: block;
  height: 24px;
}

.trend {
  display: inline-flex;
  align-items: center;
  font-size: 10px;
  font-weight: 700;
  white-space: nowrap;
}

.score-pct {
  font-weight: 600;
  color: #4f46e5;
}
</style>
