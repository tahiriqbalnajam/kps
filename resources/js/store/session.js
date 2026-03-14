import { defineStore } from 'pinia'
import request from '@/utils/request'

const SESSION_KEY = 'selected_session_id'

export const sessionStore = defineStore('session', {
  state: () => ({
    sessions: [],          // all sessions for the dropdown
    currentSessionId: null, // the session the user is currently viewing
    currentSession: null,   // full object of the current session
    loaded: false,
  }),

  getters: {
    sessionName: (state) => state.currentSession?.name ?? 'All Sessions',
  },

  actions: {
    /**
     * Load all sessions and pick the default.
     * Called once from Navbar on mount.
     */
    async init() {
      if (this.loaded) return
      try {
        const { data } = await request({ url: '/academic-sessions', method: 'get', params: { limit: 100 } })
        this.sessions = data.sessions?.data ?? []

        // Restore from localStorage, else fall back to the active session
        const savedId = localStorage.getItem(SESSION_KEY)
        const saved   = savedId ? this.sessions.find(s => s.id === parseInt(savedId)) : null
        const active  = this.sessions.find(s => s.is_active)
        const chosen  = saved ?? active ?? this.sessions[0] ?? null

        if (chosen) {
          this.currentSessionId = chosen.id
          this.currentSession   = chosen
        }
        this.loaded = true
      } catch (_) {
        // fail silently — session filtering just won't apply
      }
    },

    /**
     * User manually switches session in the dropdown.
     */
    setSession(id) {
      const session = this.sessions.find(s => s.id === id) ?? null
      this.currentSessionId = id
      this.currentSession   = session
      if (id) {
        localStorage.setItem(SESSION_KEY, id)
      } else {
        localStorage.removeItem(SESSION_KEY)
      }
    },

    /**
     * Clear — use when logging out.
     */
    clear() {
      this.sessions         = []
      this.currentSessionId = null
      this.currentSession   = null
      this.loaded           = false
      localStorage.removeItem(SESSION_KEY)
    },
  },
})
