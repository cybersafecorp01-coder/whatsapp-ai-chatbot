const $ = (id) => document.getElementById(id);

let cursor = new Date();
let reservations = [];
let byDateISO = new Map();

function toISODateFromBR(br) {
  const m = String(br || "").match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
  if (!m) return null;
  const dd = String(m[1]).padStart(2, "0");
  const mm = String(m[2]).padStart(2, "0");
  const yyyy = m[3];
  return `${yyyy}-${mm}-${dd}`;
}
function getISO(r) {
  return r.dateISO || toISODateFromBR(r.dateBR) || null;
}
function monthTitle(d) {
  return d.toLocaleDateString("pt-BR", { month: "long", year: "numeric" })
    .replace(/^\w/, c => c.toUpperCase());
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
function openModal(reservation) {
  $("mTitle").textContent = `Reserva: ${reservation.reservationId}`;
  $("mSubtitle").textContent = `Status: ${reservation.status} • Pagamento: ${reservation.payment?.status || "—"}`;
  $("mJson").textContent = JSON.stringify(reservation, null, 2);
  showModal(true);
}

function renderSidebar(monthIsoPrefix) {
  const list = $("sideList");
  const monthItems = reservations.filter(r => {
    const iso = getISO(r);
    return iso && iso.startsWith(monthIsoPrefix);
  });

  $("monthCount").textContent = `${monthItems.length} reserva(s)`;

  if (!monthItems.length) {
    list.innerHTML = `<div class="muted">Nenhuma reserva neste mês</div>`;
    return;
  }

  monthItems.sort((a,b) => (getISO(a) || "").localeCompare(getISO(b) || ""));

  list.innerHTML = monthItems.map(r => {
    const iso = getISO(r);
    const d = iso ? new Date(iso + "T00:00:00") : null;
    const label = d ? d.toLocaleDateString("pt-BR") : (r.dateBR || "—");
    const st = String(r.status || "").toUpperCase();
    const people = r.peopleCount ?? r.people?.length ?? "—";
    return `
      <div class="side-item" data-id="${r.reservationId}">
        <div class="line">
          <b class="mono">${r.reservationId}</b>
          <span class="muted">${label}</span>
        </div>
        <div class="line" style="margin-top:6px">
          <span class="muted">Pessoas: ${people}</span>
          <span class="muted">${st}</span>
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

  const monthIsoPrefix = `${y}-${String(m+1).padStart(2,"0")}`;
  renderSidebar(monthIsoPrefix);

  const totalSlots = 42;
  for (let i = 0; i < totalSlots; i++) {
    const dayNum = i - dow + 1;

    if (dayNum < 1 || dayNum > totalDays) {
      const empty = document.createElement("div");
      empty.className = "day";
      empty.style.opacity = ".35";
      empty.innerHTML = `<div class="top"><div class="num muted">—</div><div class="meta muted"></div></div>`;
      grid.appendChild(empty);
      continue;
    }

    const iso = `${y}-${String(m+1).padStart(2,"0")}-${String(dayNum).padStart(2,"0")}`;
    const events = byDateISO.get(iso) || [];

    const day = document.createElement("div");
    day.className = "day";

    const meta = events.length ? `${events.length} reserva(s)` : "";
    day.innerHTML = `
      <div class="top">
        <div class="num">${dayNum}</div>
        <div class="meta">${meta}</div>
      </div>
      <div class="events"></div>
    `;

    const evWrap = day.querySelector(".events");
    for (const r of events.slice(0, 3)) {
      const st = String(r.status || "").toUpperCase();
      const people = r.peopleCount ?? r.people?.length ?? "—";
      const ev = document.createElement("div");
      ev.className = "event";
      ev.innerHTML = `<b class="mono">${r.reservationId}</b><span>${people} pessoa(s) • ${st}</span>`;
      ev.addEventListener("click", () => openModal(r));
      evWrap.appendChild(ev);
    }

    if (events.length > 3) {
      const more = document.createElement("div");
      more.className = "muted small";
      more.textContent = `+${events.length - 3} mais…`;
      evWrap.appendChild(more);
    }

    grid.appendChild(day);
  }
}

function bind() {
  $("prevBtn").addEventListener("click", () => {
    cursor = new Date(cursor.getFullYear(), cursor.getMonth() - 1, 1);
    renderCalendar();
  });
  $("nextBtn").addEventListener("click", () => {
    cursor = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 1);
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
