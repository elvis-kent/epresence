function filterStudents() {
  const input = document.getElementById("searchInput").value.toLowerCase();
  const rows = document.querySelectorAll("#students-table tbody tr");

  rows.forEach(row => {
    const name = row.cells[1].textContent.toLowerCase();
    const matricule = row.cells[2].textContent.toLowerCase();
    const match = name.includes(input) || matricule.includes(input);
    row.style.display = match ? "" : "none";
  });
}
