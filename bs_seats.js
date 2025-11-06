document.addEventListener("DOMContentLoaded", () => {
  const grid = document.getElementById("seatGrid");
  const showSel = document.getElementById("seatShowSelect");
  const holdBtn = document.getElementById("holdSelected");
  const clearBtn = document.getElementById("clearSelection");
  const back1 = document.getElementById("backToStep1");
  const step1 = document.getElementById("step1");
  const step2 = document.getElementById("step2");
  const step3 = document.getElementById("step3Details");

  const COLORS = { available:"#c8e7d8", selected:"#cbbbe2", booked:"#f2b6b6", held:"#fceabb" };
  const ROWS = 10, COLS = 16, LETTERS = "ABCDEFGHIJ".split("");

  let prefs = JSON.parse(localStorage.getItem("prefs") || "[]");
  if (!prefs.length) return;
  // fetch the seat data from API
  async function fetchSeats(pref) {
    const params = new URLSearchParams({
      movie: pref.title,
      date: pref.date,
      time: pref.time,
      hall: pref.hall
    });
    const res = await fetch(`api/seats.php?${params}`);
    const data = await res.json();
    return data ? data : [];
  }

  // Populate dropdown in Step 2
  showSel.innerHTML = prefs.map((p,i)=>`<option value="${i}">${p.title}</option>`).join("");
  let heldSeatsMap = JSON.parse(sessionStorage.getItem("heldSeatsMap") || "{}");

  // render seatmap
  window.render = async function render(id) {
    grid.innerHTML = "";
    const pref = prefs[id];
    const held = heldSeatsMap[id] || [];
    console.log("Fetching seats for:", pref);
    const seatsData = await fetchSeats(pref);
    console.log("Seats data:", seatsData);

    // determine booked seats
    const booked = seatsData.seats.filter(s => s.is_booked === 1).map(s => s.seat_no);
    console.log("Booked seats:", booked);

    // build grid
    for (let r=0; r<ROWS; r++) {
      const rowLabel = document.createElement("div");
      rowLabel.className = "bs_row_label";
      rowLabel.textContent = LETTERS[r];
      grid.appendChild(rowLabel);

      for (let c=1; c<=COLS; c++) {
        const seatNo = `${LETTERS[r]}${c}`;
        const sid = `${r}-${c}`;
        const seat = document.createElement("div");
        seat.className = "bs_seat";
        seat.dataset.seat = sid;
        seat.dataset.row = LETTERS[r];
        seat.dataset.col = c;

        if (booked.includes(seatNo)) {
          seat.style.background = COLORS.booked;
          seat.classList.add("disabled");
        } else if (held.includes(sid)) {
          seat.style.background = COLORS.selected;
        } else {
          seat.style.background = COLORS.available;
        }

        grid.appendChild(seat);
      }
    }
    holdBtn.disabled = true;
    console.log(`Rendering seatmap for: ${pref.title}`);
  };

  // render first preference initially
  render(0);

  // when dropdown changes
  showSel.addEventListener("change", e => render(parseInt(e.target.value)));

  // seat click
  grid.onclick = e => {
    const seat = e.target.closest(".bs_seat");
    if (!seat) return;
    const id = parseInt(showSel.value);
    const pref = prefs[id];
    const pax = parseInt(pref.pax || 1);
    const sid = seat.dataset.seat;
    let arr = heldSeatsMap[id] || [];

    if (arr.includes(sid)) {
      arr = arr.filter(x=>x!==sid);
      seat.style.background = COLORS.available;
    } else {
      if (arr.length >= pax) {
        alert(`You can select only ${pax} seat(s) for ${pref.title}.`);
        return;
      }
      arr.push(sid);
      seat.style.background = COLORS.selected;
    }
    heldSeatsMap[id] = arr;
    sessionStorage.setItem("heldSeatsMap", JSON.stringify(heldSeatsMap));
    holdBtn.disabled = arr.length !== pax;
  };

  // clear seats
  clearBtn.onclick = () => {
    const id = parseInt(showSel.value);
    heldSeatsMap[id] = [];
    sessionStorage.setItem("heldSeatsMap", JSON.stringify(heldSeatsMap));
    render(id);
  };

  // back to step 1
  back1.onclick = () => {
    step2.style.display = "none";
    step1.style.display = "block";
  };

  // hold / continue logic
  holdBtn.onclick = () => {
    const id = parseInt(showSel.value);
    const pref = prefs[id];
    const pax = parseInt(pref.pax || 1);
    const sel = heldSeatsMap[id] || [];

    if (sel.length < pax) {
      alert(`Please select ${pax} seat(s) for ${pref.title}.`);
      return;
    }
    if (sel.length > pax) {
      alert(`Too many seats selected for ${pref.title}.`);
      return;
    }

    // save seats for this movie
    sessionStorage.setItem(`heldSeats_${id}`, JSON.stringify(sel));
    heldSeatsMap[id] = sel;
    sessionStorage.setItem("heldSeatsMap", JSON.stringify(heldSeatsMap));
    alert(`${pref.title} seats successfully held.`);

    // find next movie that isn’t held yet
    const next = prefs.findIndex((_,i)=>!sessionStorage.getItem(`heldSeats_${i}`));
    if (next !== -1) {
      showSel.value = next;
      render(next);
      alert(`Please select seats for “${prefs[next].title}”.`);
      return;
    }

    // all movies held
    alert("All seats selected! Proceeding to details.");
    step2.style.display = "none";
    step3.style.display = "block";
    sessionStorage.setItem("heldSeatsMap", JSON.stringify(heldSeatsMap));
  };

  // maintain sync if Step 1 changes pax later
  window.addEventListener("storage", e => {
    if (e.key === "prefs") {
      prefs = JSON.parse(e.newValue || "[]");
      showSel.innerHTML = prefs.map((p,i)=>`<option value="${i}">${p.title}</option>`).join("");
      heldSeatsMap = {};
      sessionStorage.setItem("heldSeatsMap","{}");
      render(0);
    }
  });
});