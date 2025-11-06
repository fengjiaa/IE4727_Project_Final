(function() {
  const list = document.getElementById('prefList');
  if (!list) return;

  const prefs = JSON.parse(localStorage.getItem('prefs') || '[]');
  if (prefs.length === 0) {
    list.innerHTML = '<p>No preferences yet. Go to <a href="movies-schedule.html">Movies & Schedule</a>.</p>';
  }

  prefs.forEach(p => {
    const li = document.createElement('li');
    li.className = 'card';
    li.style.marginBottom = '8px';
    li.dataset.id = p.id;
    li.dataset.title = p.title;
    li.dataset.date = p.date;
    li.dataset.time = p.time;
    li.dataset.hall = p.hall;

    li.innerHTML = `
      <div style="display:flex;justify-content:space-between;align-items:center;gap:12px">
        <div>
          <span class="pref-title"><strong>${p.title}</strong></span> —
          <span class="pref-date">${p.date}</span>
          <span class="pref-time">${p.time}</span> •
          <span class="pref-hall">${p.hall}</span>
        </div>
        <button class="btn-ghost" type="button">×</button>
      </div>
    `;

    li.querySelector('button').onclick = () => {
      const arr = JSON.parse(localStorage.getItem('prefs') || '[]').filter(x => x.id !== p.id);
      localStorage.setItem('prefs', JSON.stringify(arr));
      location.reload();
    };

    list.appendChild(li);
  });

  let dragEl = null;

  function save() {
    const items = [...list.querySelectorAll('li[data-id]')].map((li, i) => ({
      id: li.dataset.id,
      title: li.dataset.title,
      date: li.dataset.date,
      time: li.dataset.time,
      hall: li.dataset.hall,
      rank: i + 1
    }));
    localStorage.setItem('prefs', JSON.stringify(items));
  }

  function start(e) {
    dragEl = this;
    this.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
  }

  function end() {
    this.classList.remove('dragging');
    save();
  }

  function over(e) {
    e.preventDefault();
    const t = e.target.closest('li[data-id]');
    if (!t || t === dragEl) return;
    const r = t.getBoundingClientRect();
    const next = (e.clientY - r.top) / r.height > 0.5;
    list.insertBefore(dragEl, next ? t.nextSibling : t);
  }

  [...list.querySelectorAll('li[data-id]')].forEach(li => {
    li.draggable = true;
    li.addEventListener('dragstart', start);
    li.addEventListener('dragend', end);
  });

  list.addEventListener('dragover', over);

  document.getElementById('prefClear')?.addEventListener('click', () => {
    localStorage.removeItem('prefs');
    location.reload();
  });
})();
