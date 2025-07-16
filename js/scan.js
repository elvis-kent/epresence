let scannedStudents = [];

function startScanner() {
  const qrScanner = new Html5Qrcode("camera");
  qrScanner.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 250 },
    (decodedText) => {
      if (!scannedStudents.includes(decodedText)) {
        scannedStudents.push(decodedText);
        updateList(decodedText);
        document.getElementById("scan-status").textContent = "✅ Étudiant scanné : " + decodedText;
      } else {
        document.getElementById("scan-status").textContent = "⚠️ Étudiant déjà scanné.";
      }
    },
    (error) => {
      console.warn("Erreur de scan", error);
    }
  );
}

function updateList(text) {
  const li = document.createElement("li");
  li.textContent = text + " — " + new Date().toLocaleTimeString();
  document.getElementById("scanned-list").appendChild(li);
}

function exportPresence() {
  const blob = new Blob([scannedStudents.join("\n")], { type: "text/plain" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = "presence.txt";
  a.click();
}

window.onload = () => {
  startScanner();
};
