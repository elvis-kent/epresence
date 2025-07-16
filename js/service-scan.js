let accessList = [];

function startScanner() {
  const qrScanner = new Html5Qrcode("camera");
  qrScanner.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 250 },
    (decodedText) => {
      if (!accessList.includes(decodedText)) {
        accessList.push(decodedText);
        updateAccessList(decodedText);
        document.getElementById("scan-status").textContent = "✅ Accès autorisé : " + decodedText;
      } else {
        document.getElementById("scan-status").textContent = "⚠️ Déjà scanné";
      }
    },
    (error) => {
      console.warn("Erreur scan", error);
    }
  );
}

function updateAccessList(text) {
  const li = document.createElement("li");
  li.textContent = text + " — " + new Date().toLocaleTimeString();
  document.getElementById("access-list").appendChild(li);
}

window.onload = () => {
  startScanner();
};
