document.addEventListener("DOMContentLoaded", () => {
  const prefList = document.getElementById("prefList");
  const priceDisplay = document.getElementById("priceDisplay");
  const step1 = document.getElementById("step1");
  const step2 = document.getElementById("step2");
  const toStep2 = document.getElementById("toStep2");
  const backToStep1 = document.getElementById("backToStep1");

  let prefs = JSON.parse(localStorage.getItem("prefs") || "[]");

  // Render preference list
  function renderPrefs() {
    prefList.innerHTML = "";
    const isMember = sessionStorage.getItem("isMember");
    prefs.forEach((p, i) => {
      const pax = Number(p.pax) || 1;
      const pricePer = Number(p.pricePer) || (isMember ? 11 : 13);
      const subtotal = pax * pricePer;

      prefs[i].pax = pax;
      prefs[i].pricePer = pricePer;
      prefs[i].subtotal = subtotal;

      const li = document.createElement("li");
      li.className = "pref-item";
      li.innerHTML = `
        <div class="pref-left">
          <span class="drag">&#x2630;</span>
          ${i + 1}. ${p.title}
        </div>
        <div class="pref-right">
        <div class="pax-group">
          <input type="number" min="1" value="${pax}" data-index="${i}" class="paxInput">
          <span style="font-weight:500;">Pax</span>
        </div>
          <span>$${subtotal.toFixed(2)}</span>
          <button class="btn-ghost delBtn" data-index="${i}">Remove</button>
        </div>`;
      prefList.appendChild(li);
    });

    updateTotal();
    enableReorder(); // reapply Sortable each render
  }

  // Update subtotal
  function updateTotal() {
    const isMember = sessionStorage.getItem("isMember");
    let paxTotal = 0, total = 0;

    prefs.forEach(p => {
      const price = Number(p.pricePer) || (isMember ? 11 : 13);
      const pax = Number(p.pax) || 1;
      p.pricePer = price;
      p.subtotal = price * pax;
      paxTotal += pax;
      total += p.subtotal;
    });

    priceDisplay.textContent = `$${total.toFixed(2)} total (${paxTotal} pax)`;
    localStorage.setItem("prefs", JSON.stringify(prefs));
  }

  // Drag to reorder
  function enableReorder() {
    if (window.Sortable && prefList) {
      Sortable.create(prefList, {
        animation: 150,
        handle: ".drag",
        onEnd: evt => {
          const moved = prefs.splice(evt.oldIndex, 1)[0];
          prefs.splice(evt.newIndex, 0, moved);
          localStorage.setItem("prefs", JSON.stringify(prefs));
          renderPrefs();
        }
      });
    }
  }

  // Listeners 
  prefList.addEventListener("input", e => {
    if (e.target.classList.contains("paxInput")) {
      const i = +e.target.dataset.index;
      prefs[i].pax = Math.max(1, parseInt(e.target.value) || 1);
      renderPrefs();
    }
  });

  prefList.addEventListener("click", e => {
    if (e.target.classList.contains("delBtn")) {
      const i = +e.target.dataset.index;
      prefs.splice(i, 1);
      renderPrefs();
    }
  });

  document.getElementById("prefClear").onclick = () => {
    if (!prefs.length) return;
    if (confirm("Remove all preferences?")) {
      prefs = [];
      localStorage.removeItem("prefs");
      renderPrefs();
    }
  };

  toStep2.onclick = () => {
    if (!prefs.length) return alert("Please add at least one movie before continuing.");

    // Save latest prefs (with updated pax)
    localStorage.setItem("prefs", JSON.stringify(prefs));

    // Clear old seat selections to avoid mismatches
    sessionStorage.removeItem("heldSeatsMap");
    Object.keys(sessionStorage)
      .filter(k => k.startsWith("heldSeats_"))
      .forEach(k => sessionStorage.removeItem(k));

    // Switch view and reload fresh prefs for Step 2
    step1.style.display = "none";
    step2.style.display = "block";

    setTimeout(() => {
      if (typeof window.loadSeatPrefs === "function") {
        window.loadSeatPrefs(); // defined below in bs_seats.js
      } else if (typeof render === "function") {
        render(0);
      } else {
        console.warn("render() missing — ensure bs_seats.js is loaded.");
      }
    }, 200);
  };


  backToStep1.onclick = () => {
    step2.style.display = "none";
    step1.style.display = "block";
  };

  // Initialize
  renderPrefs();
});