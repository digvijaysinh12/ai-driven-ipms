;(function () {
  const NS = (window.InternTaskWorkspace = window.InternTaskWorkspace || {})

  function showQuestion(qid) {
    const qidStr = String(qid)

    document.querySelectorAll('.ws-question-content').forEach((el) => {
      el.style.display = el.getAttribute('data-qid') === qidStr ? 'block' : 'none'
    })
    document.querySelectorAll('.ws-answer-panel').forEach((el) => {
      el.style.display = el.getAttribute('data-qid') === qidStr ? 'block' : 'none'
    })

    document.querySelectorAll('.ws-nav-item').forEach((btn) => {
      const isActive = btn.getAttribute('data-qid') === qidStr
      btn.classList.toggle('ring-2', isActive)
      btn.classList.toggle('ring-indigo-100', isActive)
      btn.classList.toggle('border-indigo-200', isActive)
    })

    const label = document.getElementById('wsActiveLabel')
    if (label) {
      const cfg = window.INTERN_TASK_WORKSPACE
      const list = (cfg && cfg.questions) || []
      const idx = list.findIndex((q) => String(q.id) === qidStr)
      label.textContent = idx >= 0 ? `Item ${idx + 1} / ${list.length}` : `Item ${qidStr}`
    }
  }

  function wireSidebarClicks(state, onNavigate) {
    document.querySelectorAll('.ws-nav-item').forEach((btn) => {
      btn.addEventListener('click', () => {
        const qid = btn.getAttribute('data-qid')
        if (!qid) return
        state.currentQid = String(qid)
        showQuestion(qid)
        onNavigate && onNavigate()
      })
    })
  }

  function updateNavDots(cfg, state) {
    const autosave = NS.Autosave
    const dotEls = Array.from(document.querySelectorAll('.ws-nav-dot'))
    dotEls.forEach((dot) => {
      const qid = dot.getAttribute('data-qid')
      if (!qid) return
      const status = autosave.computeStatusForQuestion(state, qid)
      dot.className =
        status === 'completed'
          ? 'ws-nav-dot w-2.5 h-2.5 rounded-full bg-emerald-500'
          : status === 'in_progress'
            ? 'ws-nav-dot w-2.5 h-2.5 rounded-full bg-amber-400'
            : 'ws-nav-dot w-2.5 h-2.5 rounded-full bg-slate-300'
    })
  }

  function updateProgress(cfg, state) {
    const autosave = NS.Autosave
    const total = (cfg.questions || []).length
    let done = 0
    let started = 0
    let fresh = 0

    ;(cfg.questions || []).forEach((q) => {
      const status = autosave.computeStatusForQuestion(state, String(q.id))
      if (status === 'completed') done++
      else if (status === 'in_progress') started++
      else fresh++
    })

    const pct = total > 0 ? Math.round((done / total) * 100) : 0

    const bar = document.getElementById('wsProgressBar')
    const text = document.getElementById('wsProgressText')
    const pctEl = document.getElementById('wsProgressPct')
    if (bar) bar.style.width = `${pct}%`
    if (text) text.textContent = `${done} / ${total}`
    if (pctEl) pctEl.textContent = `${pct}%`

    const doneEl = document.getElementById('wsDoneCount')
    const startedEl = document.getElementById('wsStartedCount')
    const newEl = document.getElementById('wsNewCount')
    const completionPctEl = document.getElementById('wsCompletionPct')
    const completionBarEl = document.getElementById('wsCompletionBar')
    if (doneEl) doneEl.textContent = String(done)
    if (startedEl) startedEl.textContent = String(started)
    if (newEl) newEl.textContent = String(fresh)
    if (completionPctEl) completionPctEl.textContent = `${pct}%`
    if (completionBarEl) completionBarEl.style.width = `${pct}%`

    return { total, done, started, fresh, pct }
  }

  NS.Navigation = {
    showQuestion,
    wireSidebarClicks,
    updateNavDots,
    updateProgress,
  }
})()
