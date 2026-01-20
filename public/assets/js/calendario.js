const $ = (id) => document.getElementById(id);

let cursor = new Date();
let reservations = [];
let byDateISO = new Map();

function getISO(r) {
    return r.dateISO || null;
}

function monthTitle(d) {
    return d.toLocaleDateString("pt-BR", { month: "long", year: "numeric" })
        .replace(/^\w/, (c) => c.toUpperCase());
}

function startOfMonth(d) { return new Date(d.getFullYear(), d.getMonth(), 1); }

function endOfMonth(d) { return new Date(d.getFullYear(), d.getMonth() + 1, 0); }

function daysInMonth(d) { return endOfMonth(d).getDate(); }

function firstDow(d) { return startOfMonth(d).getDay(); }

async function loadReservations() {
    const data = await fetch(`/api/reservations`).then(r => r.json());
    if (!data.ok) throw new Error("Falha ao carregar reservas");
    reservations = data.reservations || [];

    byDateISO = new Map();
    for (const r of reservations) {
        const iso = getISO(r);
        if (!iso) continue;
        if (!byDateISO.has(iso)) byDateISO.set(iso, []);
        byDateISO.get(iso).push(r);
    }
}

function showModal(show) {
    $("backdrop").style.display = show ? "block" : "none";
    $("modal").style.display = show ? "grid" : "none";
}

function openModal(r) {
    $("mTitle").textContent = `Reserva: ${r.reservationId}`;
    $("mJson").textContent = JSON.stringify(r, null, 2);
    showModal(true);
}

function renderSidebar(monthPrefix) {
    const list = $("sideList");
    const monthItems = reservations.filter(r => (getISO(r) || "").startsWith(monthPrefix));

    $("monthCount").textContent = `${monthItems.length} reserva(s)`;

    if (!monthItems.length) {
        list.innerHTML = `<div class="muted">Nenhuma reserva neste mês</div>`;
        return;
    }

    monthItems.sort((a, b) => (getISO(a) || "").localeCompare(getISO(b) || ""));

    list.innerHTML = monthItems.map(r => {
        const iso = getISO(r);
        const d = iso ? new Date(iso + "T00:00:00") : null;
        const label = d ? d.toLocaleDateString("pt-BR") : (r.dateBR || "—");
        const st = String(r.status || "").toUpperCase();
        return `
      <div class="stat" style="margin-bottom:10px; cursor:pointer" data-id="${r.reservationId}">
        <div style="display:flex; justify-content:space-between; gap:10px">
          <b class="mono">${r.reservationId}</b>
          <span class="muted">${label}</span>
        </div>
        <div class="muted small" style="margin-top:6px">
          Pessoas: ${r.people ?? "—"} • ${st} • ${r.paymentStatus || ""}
        </div>
      </div>
    `;
    }).join("");

    document.querySelectorAll("[data-id]").forEach(el => {
        el.addEventListener("click", () => {
            const id = el.getAttribute("data-id");
            const r = reservations.find(x => x.reservationId === id);
            if (r) openModal(r);
        });
    });
}

function renderCalendar() {
    $("title").textContent = monthTitle(cursor);
    const grid = $("grid");
    grid.innerHTML = "";

    const y = cursor.getFullYear();
    const m = cursor.getMonth();
    const totalDays = daysInMonth(cursor);
    const dow = firstDow(cursor);

    const monthPrefix = `${y}-${String(m+1).padStart(2,"0")}`;
    renderSidebar(monthPrefix);

    const totalSlots = 42;
    for (let i = 0; i < totalSlots; i++) {
        const dayNum = i - dow + 1;

        const cell = document.createElement("div");
        cell.style.borderRight = "1px solid var(--line)";
        cell.style.borderBottom = "1px solid var(--line)";
        cell.style.minHeight = "120px";
        cell.style.padding = "10px 10px";
        cell.style.background = "rgba(255,255,255,.02)";

        if (dayNum < 1 || dayNum > totalDays) {
            cell.innerHTML = `<div class="muted small">—</div>`;
            grid.appendChild(cell);
            continue;
        }

        const iso = `${y}-${String(m+1).padStart(2,"0")}-${String(dayNum).padStart(2,"0")}`;
        const events = byDateISO.get(iso) || [];

        cell.innerHTML = `
      <div style="display:flex;justify-content:space-between;align-items:center">
        <b>${dayNum}</b>
        <span class="muted small">${events.length ? `${events.length} reserva(s)` : ""}</span>
      </div>
      <div style="margin-top:8px; display:flex; flex-direction:column; gap:6px"></div>
    `;

    const box = cell.querySelector("div:last-child");
    for (const r of events.slice(0,3)){
      const ev = document.createElement("div");
      ev.className = "pill";
      ev.style.cursor = "pointer";
      ev.innerHTML = `<span class="mono">${r.reservationId}</span>&nbsp;•&nbsp;${r.people ?? "—"}p`;
      ev.addEventListener("click", () => openModal(r));
      box.appendChild(ev);
    }

    if (events.length > 3){
      const more = document.createElement("div");
      more.className = "muted small";
      more.textContent = `+${events.length - 3} mais…`;
      box.appendChild(more);
    }

    grid.appendChild(cell);
  }
}

function bind(){
  $("prevBtn").addEventListener("click", () => {
    cursor = new Date(cursor.getFullYear(), cursor.getMonth()-1, 1);
    renderCalendar();
  });
  $("nextBtn").addEventListener("click", () => {
    cursor = new Date(cursor.getFullYear(), cursor.getMonth()+1, 1);
    renderCalendar();
  });
  $("todayBtn").addEventListener("click", () => {
    const now = new Date();
    cursor = new Date(now.getFullYear(), now.getMonth(), 1);
    renderCalendar();
  });
  $("refreshBtn").addEventListener("click", async () => {
    await loadReservations();
    renderCalendar();
  });

  $("closeBtn").addEventListener("click", () => showModal(false));
  $("backdrop").addEventListener("click", () => showModal(false));
}

document.addEventListener("DOMContentLoaded", async () => {
  bind();
  await loadReservations();
  renderCalendar();
});