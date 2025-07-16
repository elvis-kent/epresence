const horaires = {
  l1: [
    ["Lundi", "08:00 - 10:00", "Maths", "B201", "M. Ilunga"],
    ["Mardi", "10:00 - 12:00", "Informatique", "B101", "Mme. Kalala"],
  ],
  l2: [
    ["Lundi", "10:00 - 12:00", "Algorithmique", "B202", "M. Tshibanda"],
    ["Mercredi", "14:00 - 16:00", "BD", "B102", "Mme. Kanyinda"],
  ],
  l3: [
    ["Jeudi", "08:00 - 10:00", "Sécurité", "C301", "M. Mbuyi"],
    ["Vendredi", "10:00 - 12:00", "Projet", "B204", "Mme. Mayele"],
  ]
};

function loadSchedule() {
  const promo = document.getElementById("promotion-select").value;
  const table = document.querySelector("#schedule-table tbody");
  table.innerHTML = "";

  horaires[promo].forEach(row => {
    const tr = document.createElement("tr");
    row.forEach(cell => {
      const td = document.createElement("td");
      td.textContent = cell;
      tr.appendChild(td);
    });
    table.appendChild(tr);
  });
}

window.onload = loadSchedule;
