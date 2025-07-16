function filterLogs() {
  const input = document.getElementById("filter-user").value.toLowerCase();
  const rows = document.querySelectorAll("#logs-table tbody tr");

  rows.forEach(row => {
    const userCell = row.cells[2].textContent.toLowerCase();
    row.style.display = userCell.includes(input) ? "" : "none";
  });
}
