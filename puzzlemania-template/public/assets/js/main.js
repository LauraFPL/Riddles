function showQr() {
    document.getElementById('div_qr').style.display = "block";
    document.getElementById('qr').style.display = "none";
    document.getElementById('qr_dwn').style.display = "block";
    localStorage.setItem('showQR', 'true');
 }