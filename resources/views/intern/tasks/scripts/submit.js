;(function () {
  const NS = (window.InternTaskWorkspace = window.InternTaskWorkspace || {})

  function qs(id) {
    return document.getElementById(id)
  }

  function openModal(id) {
    const el = qs(id)
    if (!el) return
    el.classList.remove('hidden')
    el.setAttribute('aria-hidden', 'false')
    if (window.lucide) window.lucide.createIcons()
  }

  function closeModal(id) {
    const el = qs(id)
    if (!el) return
    el.classList.add('hidden')
    el.setAttribute('aria-hidden', 'true')
  }

  function wireModalClose() {
    document.querySelectorAll('[data-modal-close]').forEach((el) => {
      el.addEventListener('click', () => {
        const id = el.getAttribute('data-modal-close')
        if (id) closeModal(id)
      })
    })
  }

  function escapeHtml(s) {
    return String(s)
      .replaceAll('&', '&amp;')
      .replaceAll('<', '&lt;')
      .replaceAll('>', '&gt;')
      .replaceAll('"', '&quot;')
      .replaceAll("'", '&#039;')
  }

  function renderReview(cfg, state) {
    const progress = NS.Navigation.updateProgress(cfg, state)

    const doneEl = qs('wsReviewDone')
    const startedEl = qs('wsReviewStarted')
    const newEl = qs('wsReviewNew')
    const pctEl = qs('wsReviewPct')
    const barEl = qs('wsReviewBar')
    if (doneEl) doneEl.textContent = String(progress.done)
    if (startedEl) startedEl.textContent = String(progress.started)
    if (newEl) newEl.textContent = String(progress.fresh)
    if (pctEl) pctEl.textContent = `${progress.pct}%`
    if (barEl) barEl.style.width = `${progress.pct}%`

    const list = qs('wsReviewList')
    if (!list) return
    list.innerHTML = ''

    ;(cfg.questions || []).forEach((q, idx) => {
      const qid = String(q.id)
      const status = NS.Autosave.computeStatusForQuestion(state, qid)
      const dot =
        status === 'completed'
          ? 'bg-emerald-500'
          : status === 'in_progress'
            ? 'bg-amber-400'
            : 'bg-slate-300'
      const label =
        status === 'completed'
          ? 'Done'
          : status === 'in_progress'
            ? 'Started'
            : 'New'

      const btn = document.createElement('button')
      btn.type = 'button'
      btn.className =
        'w-full text-left rounded-2xl border border-slate-100 bg-white hover:bg-slate-50 transition-colors px-3 py-3 flex items-start justify-between gap-3'
      btn.innerHTML = `
        <div class="min-w-0">
          <div class="text-xs font-black text-slate-800">Item ${idx + 1}</div>
          <div class="text-[11px] font-medium text-slate-500 truncate">${escapeHtml(q.question || '')}</div>
        </div>
        <div class="shrink-0 flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full ${dot}"></span>
          <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">${label}</span>
        </div>
      `
      btn.addEventListener('click', () => {
        state.currentQid = qid
        NS.Navigation.showQuestion(qid)
        NS.Autosave.scheduleSave(cfg, state)
        closeModal('wsReviewModal')
      })
      list.appendChild(btn)
    })
  }

  function renderSubmit(cfg, state) {
    const progress = NS.Navigation.updateProgress(cfg, state)
    const total = progress.total
    const done = progress.done
    const pct = progress.pct

    const summary = qs('wsSubmitSummary')
    const bar = qs('wsSubmitBar')
    if (summary) summary.textContent = `${done} / ${total} answered`
    if (bar) bar.style.width = `${pct}%`

    const answeredEl = qs('wsSubmitAnswered')
    const unansweredEl = qs('wsSubmitUnanswered')
    if (answeredEl) answeredEl.textContent = `${done} item(s)`
    if (unansweredEl) unansweredEl.textContent = `${total - done} item(s)`

    const err = qs('wsSubmitError')
    if (err) err.classList.add('hidden')
  }

  async function submit(cfg, state) {
    const form = qs('wsForm')
    const err = qs('wsSubmitError')
    if (!form) return

    if (err) {
      err.textContent = ''
      err.classList.add('hidden')
    }

    const fd = new FormData(form)

    // Additional compatibility payload (does not interfere with `answers[...]`).
    fd.set('answers_json', JSON.stringify(state.answers || {}))
    fd.set('workspace_version', String(cfg.version || 1))

    // Some backends expect top-level keys by qid; provide as extra fields.
    Object.keys(state.answers || {}).forEach((qid) => {
      const v = state.answers[qid]
      if (v == null) return
      fd.set(String(qid), String(v))
    })

    try {
      const res = await fetch(cfg.submitUrl, {
        method: 'POST',
        body: fd,
        headers: {
          Accept: 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
        },
      })

      const text = await res.text()
      let data = null
      try {
        data = JSON.parse(text)
      } catch {
        data = { status: 'error', message: 'Server returned an invalid response.' }
      }

      if (!res.ok || data.status === 'error') {
        throw new Error(data.message || 'Submission failed.')
      }

      localStorage.removeItem(`intern.task.workspace:${cfg.taskId}`)
      window.location.href = cfg.resultsUrl
    } catch (e) {
      if (err) {
        err.textContent = e && e.message ? e.message : 'Submission failed.'
        err.classList.remove('hidden')
      } else {
        alert(e && e.message ? e.message : 'Submission failed.')
      }
    }
  }

  function wireSubmit(cfg, state) {
    const openSubmit = () => {
      renderSubmit(cfg, state)
      openModal('wsSubmitModal')
    }
    const openReview = () => {
      renderReview(cfg, state)
      openModal('wsReviewModal')
    }

    ;['wsOpenSubmit', 'wsOpenSubmitBottom'].forEach((id) => {
      const el = qs(id)
      if (!el) return
      el.addEventListener('click', openSubmit)
    })
    ;['wsOpenReview', 'wsOpenReviewBottom', 'wsOpenReviewSide'].forEach((id) => {
      const el = qs(id)
      if (!el) return
      el.addEventListener('click', openReview)
    })

    const confirm = qs('wsConfirmSubmit')
    if (confirm) {
      confirm.addEventListener('click', async () => {
        confirm.disabled = true
        confirm.classList.add('opacity-70', 'cursor-not-allowed')
        await submit(cfg, state)
        confirm.disabled = false
        confirm.classList.remove('opacity-70', 'cursor-not-allowed')
      })
    }

    wireModalClose()
  }

  NS.Submit = {
    openModal,
    closeModal,
    wireSubmit,
    renderReview,
    renderSubmit,
  }
})()
