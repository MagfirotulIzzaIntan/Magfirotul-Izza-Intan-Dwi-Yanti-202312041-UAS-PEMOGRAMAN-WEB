// Main JS for FZ Petshop
// Tambahkan efek atau kode khusus sesuai kebutuhan

// Contoh kode alert untuk mengecek
console.log("FZ Petshop JS Loaded!");

// Contoh: Confirm sebelum delete
document.querySelectorAll('a.btn-danger').forEach(el => {
    el.addEventListener('click', e => {
        if (!confirm("Yakin mau menghapus item ini?")) {
            e.preventDefault();
        }
    });
});
