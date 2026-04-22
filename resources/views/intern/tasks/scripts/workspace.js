;(function () {
  const cfg = window.INTERN_TASK_WORKSPACE
  if (!cfg || !cfg.taskId) return

  const NS = (window.InternTaskWorkspace = window.InternTaskWorkspace || {})

  function init() {
    const autosave = NS.Autosave
    const nav = NS.Navigation
    const submit = NS.Submit

    const state = autosave.loadState(cfg)

    // Restore answers into inputs
    Object.keys(state.answers || {}).forEach((qid) => {
      autosave.setInputValueForQid(qid, state.answers[qid])
    })

    // Restore current question
    if (state.currentQid) {
      nav.showQuestion(state.currentQid)
    } else if (cfg.questions && cfg.questions[0]) {
      state.currentQid = String(cfg.questions[0].id)
      nav.showQuestion(state.currentQid)
    }

    const onChange = () => {
      nav.updateNavDots(cfg, state)
      nav.updateProgress(cfg, state)
      if (window.lucide) window.lucide.createIcons()
    }

    autosave.wireAnswerInputs(cfg, state, onChange)
    nav.wireSidebarClicks(state, () => {
      state.updatedAt = new Date().toISOString()
      autosave.saveState(cfg, state)
      onChange()
    })
    submit.wireSubmit(cfg, state)

    // Coding console UI-only
    document.querySelectorAll('[data-run-code]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const qid = btn.getAttribute('data-qid')
        const consoleEl = document.querySelector(`[data-console][data-qid="${CSS.escape(String(qid))}"]`)
        if (!consoleEl) return
        consoleEl.textContent = 'Run Code is UI-only in this workspace.'
      })
    })
    document.querySelectorAll('[data-clear-console]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const qid = btn.getAttribute('data-qid')
        const consoleEl = document.querySelector(`[data-console][data-qid="${CSS.escape(String(qid))}"]`)
        if (!consoleEl) return
        consoleEl.textContent = 'Output will appear here.'
      })
    })

    // Initial indicators
    if (state.__restored && state.updatedAt) {
      autosave.setSessionPill('saved', 'Restored')
      autosave.setSavedPill(
        `Restored ${new Date(state.updatedAt).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`,
      )
    } else {
      autosave.setSessionPill('idle', 'Ready')
      autosave.setSavedPill('Not saved yet')
    }

    onChange()
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init)
  } else {
    init()
  }
})()
