;(function () {
  const NS = (window.InternTaskWorkspace = window.InternTaskWorkspace || {})

  function nowIso() {
    return new Date().toISOString()
  }

  function storageKey(taskId) {
    return `intern.task.workspace:${taskId}`
  }

  function safeJsonParse(input) {
    try {
      return JSON.parse(input)
    } catch {
      return null
    }
  }

  function isEmptyValue(value) {
    if (value === null || value === undefined) return true
    if (typeof value === 'string') return value.trim().length === 0
    return false
  }

  function computeStatusForQuestion(state, qid) {
    const touched = !!(state.touched && state.touched[qid])
    const value = state.answers ? state.answers[qid] : undefined
    if (!isEmptyValue(value)) return 'completed'
    if (state.filesSelected && state.filesSelected[qid]) return 'completed'
    if (touched) return 'in_progress'
    return 'not_started'
  }

  function buildInitialState(cfg) {
    return {
      version: cfg.version || 1,
      taskId: cfg.taskId,
      currentQid: cfg.questions && cfg.questions[0] ? String(cfg.questions[0].id) : null,
      answers: {},
      touched: {},
      filesSelected: {},
      createdAt: nowIso(),
      updatedAt: null,
      __restored: false,
    }
  }

  function loadState(cfg) {
    const raw = localStorage.getItem(storageKey(cfg.taskId))
    if (!raw) return buildInitialState(cfg)
    const parsed = safeJsonParse(raw)
    if (!parsed || typeof parsed !== 'object') return buildInitialState(cfg)

    const base = buildInitialState(cfg)
    const merged = {
      ...base,
      ...parsed,
      answers: { ...(base.answers || {}), ...(parsed.answers || {}) },
      touched: { ...(base.touched || {}), ...(parsed.touched || {}) },
      filesSelected: {},
      __restored: true,
    }

    if (!merged.currentQid) merged.currentQid = base.currentQid
    return merged
  }

  function saveState(cfg, state) {
    const toSave = {
      version: state.version,
      taskId: state.taskId,
      currentQid: state.currentQid,
      answers: state.answers || {},
      touched: state.touched || {},
      createdAt: state.createdAt || nowIso(),
      updatedAt: state.updatedAt || nowIso(),
    }
    localStorage.setItem(storageKey(cfg.taskId), JSON.stringify(toSave))
  }

  function setSessionPill(mode, text) {
    const pill = document.getElementById('wsSessionPill')
    if (!pill) return
    const label = pill.querySelector('.ws-session-text')
    if (label) label.textContent = text || 'Ready'

    const base = 'inline-flex items-center gap-2 px-3 py-2 rounded-2xl text-xs font-black'
    if (mode === 'saving') {
      pill.className = `${base} bg-amber-50 border border-amber-100 text-amber-800`
      return
    }
    if (mode === 'saved') {
      pill.className = `${base} bg-emerald-50 border border-emerald-100 text-emerald-800`
      return
    }
    pill.className = `${base} bg-slate-50 border border-slate-100 text-slate-600`
  }

  function setSavedPill(text) {
    const el = document.getElementById('wsSavedPill')
    if (!el) return
    el.textContent = text || '—'
  }

  function formatTime(ts) {
    try {
      const d = new Date(ts)
      const h = d.getHours()
      const m = String(d.getMinutes()).padStart(2, '0')
      return `${h}:${m}`
    } catch {
      return ''
    }
  }

  let saveTimer = null
  function scheduleSave(cfg, state, onAfterSave) {
    setSessionPill('saving', 'Saving…')
    setSavedPill('Saving…')
    clearTimeout(saveTimer)
    saveTimer = setTimeout(() => {
      state.updatedAt = nowIso()
      saveState(cfg, state)
      setSessionPill('saved', `Saved ${formatTime(state.updatedAt)}`)
      setSavedPill(`Saved ${formatTime(state.updatedAt)}`)
      onAfterSave && onAfterSave()
    }, 180)
  }

  function setInputValueForQid(qid, value) {
    const selector = `[data-answer-input][data-qid="${CSS.escape(String(qid))}"]`
    const els = Array.from(document.querySelectorAll(selector))
    if (els.length === 0) return

    const first = els[0]
    if (first.type === 'radio') {
      els.forEach((el) => {
        el.checked = String(el.value) === String(value)
      })
      return
    }

    els.forEach((el) => {
      el.value = value == null ? '' : String(value)
    })
  }

  function readInputValueForQid(qid) {
    const selector = `[data-answer-input][data-qid="${CSS.escape(String(qid))}"]`
    const els = Array.from(document.querySelectorAll(selector))
    if (els.length === 0) return ''

    const first = els[0]
    if (first.type === 'radio') {
      const checked = els.find((el) => el.checked)
      return checked ? checked.value : ''
    }

    return first.value || ''
  }

  function wireAnswerInputs(cfg, state, onChange) {
    const form = document.getElementById('wsForm')
    if (!form) return

    form.addEventListener('focusin', (e) => {
      const el = e.target
      const qid = el && el.getAttribute && el.getAttribute('data-qid')
      if (!qid) return
      state.touched[qid] = true
      onChange && onChange()
    })

    form.addEventListener('input', (e) => {
      const el = e.target
      const qid = el && el.getAttribute && el.getAttribute('data-qid')
      if (!qid) return
      if (el.hasAttribute && el.hasAttribute('data-file-input')) return

      state.touched[qid] = true
      state.answers[qid] = readInputValueForQid(qid)
      scheduleSave(cfg, state, onChange)
    })

    form.addEventListener('change', (e) => {
      const el = e.target
      const qid = el && el.getAttribute && el.getAttribute('data-qid')
      if (!qid) return

      state.touched[qid] = true

      if (el.hasAttribute && el.hasAttribute('data-file-input')) {
        const file = el.files && el.files[0]
        state.filesSelected[qid] = !!file
        const label = document.querySelector(`[data-file-label][data-qid="${CSS.escape(String(qid))}"]`)
        if (label) label.textContent = file ? file.name : ''
        onChange && onChange()
        return
      }

      state.answers[qid] = readInputValueForQid(qid)
      scheduleSave(cfg, state, onChange)
    })
  }

  NS.Autosave = {
    loadState,
    saveState,
    scheduleSave,
    computeStatusForQuestion,
    setInputValueForQid,
    wireAnswerInputs,
    isEmptyValue,
    setSessionPill,
    setSavedPill,
  }
})()
