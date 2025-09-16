<?php 

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'header.php'; 
include 'function.php';

//function
$sql = "SELECT 
    item_type AS tipe, 
    name AS nama, 
    provider, 
    expire_date AS expire, 
    DATEDIFF(expire_date, CURDATE()) AS hari, 
    auto_renew AS auto, 
    note AS catatan
    FROM domains";

    $data = getData($sql);

$search = strtolower($_GET['search'] ?? "");
$type   = $_GET['type']   ?? "";
$status = $_GET['status'] ?? "";
$sort   = $_GET['sort']   ?? "asc";

$filtered = array_filter($data, function($item) use ($search, $type, $status) {
    $matchSearch = $search === "" || 
                   strpos(strtolower($item["nama"]), $search) !== false ||
                   strpos(strtolower($item["provider"]), $search) !== false;
    $matchType   = $type === "" || strtolower($item["tipe"]) === strtolower($type);
    $matchStatus = $status === "" || $item["auto"] === $status;
    return $matchSearch && $matchType && $matchStatus;
});

usort($filtered, function($a, $b) use ($sort) {
    return $sort === "asc" ? $a["hari"] <=> $b["hari"] : $b["hari"] <=> $a["hari"];
});
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Domain</title>
    <style>
        body { font-family: Arial, sans-serif; background: #0f172a; color: #fff; padding: 20px; }
        h1 { margin-bottom: 20px; }
        .filters { display: flex; flex-direction: row; gap: 10px; margin-bottom: 20px; max-width: 400px; }
        .filters input, 
        .filters select, 
        .filters button {
          flex: none;
        }
        .filters input[type="text"] {
          width: 250px;
        }
        select, input { padding: 10px; border-radius: 8px; border: none; background: #161b22; color: white; width: 100%; }
        button { padding: 10px; border-radius: 8px; border: none; background: #0284c7; color: white; cursor: pointer; }
        table { border-collapse: collapse; width: 100%; background: #1e293b; border-radius: 10px; overflow: hidden; }
        th, td { padding: 12px; text-align: center; }
        th { background-color: #334155; }
        tr:nth-child(even) { background-color: #1e293b; }
        tr:nth-child(odd) { background-color: #0f172a; }
        .label-hosting { background: #3f1d4a; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 12px; }
        .label-domain  { background: #0f3a46; color: #fff; padding: 4px 10px; border-radius: 20px; font-size: 12px; }
        .btn { padding: 6px 12px; margin: 2px; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; }
        .btn-edit { background: #475569; color: #fff; }
        .btn-plus { background: #0284c7; color: #fff; }
        .btn-del  { background: #334155; color: #fff; }
        .red { color: #ef4444; font-weight: bold; }
        .yellow { color: #facc15; font-weight: bold; }
        .green { color: #22c55e; font-weight: bold; }

#pagination {
  display: flex;
  justify-content: center; /* bikin konten ke tengah */
  margin-top: 20px;
}

.pagination {
  display: flex;
  gap: 4px; /* jarak antar tombol */
}

.pagination a {
  color: white; /* teks default */
  padding: 8px 16px;
  text-decoration: none;
  border-radius: 5px;
  background-color: transparent;
  transition: 0.3s;
}

.pagination a.active {
  background-color: #0284c7; /* biru aktif */
  color: white;
}

.pagination a:hover:not(.active) {
  background-color: #334155; /* abu hover */
}



    </style>
</head>
<body>
    <h1>Domain Management Dashboard</h1>

    <!-- Filter Form -->
    <form method="get" class="filters">
        <div style="display:flex; gap:10px;">
            <input type="text" id="searchInput" name="search" placeholder="Cari nama / provider..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Cari</button>
        </div>

        <select name="type" onchange="this.form.submit()">
            <option value="">Semua Tipe</option>
            <option value="Domain" <?= $type=="Domain"?"selected":"" ?>>Domain</option>
            <option value="Hosting" <?= $type=="Hosting"?"selected":"" ?>>Hosting</option>
        </select>

        <select name="status" onchange="this.form.submit()">
            <option value="">Semua Status</option>
            <option value="JatuhTempo"  <?= $status=="JatuhTempo"?"selected":"" ?>>Jatuh tempo ≤ 30</option>
            <option value="Expired" <?= $status=="Expired"?"selected":"" ?>>Expired</option>
            <option value="AutoRenew" <?= $status=="AutoRenew"?"selected":"" ?>>Auto-Renew ON</option>
        </select>

        <select name="sort" onchange="this.form.submit()">
            <option value="asc"   <?= $sort=="asc"?"selected":"" ?>>Urut: Hari Tersisa ↑</option>
            <option value="desc"  <?= $sort=="desc"?"selected":"" ?>>Urut: Hari Tersisa ↓</option>
            <option value="nama_asc"  <?= $sort=="nama_asc"?"selected":"" ?>>Urut: Nama A-Z</option>
            <option value="nama_desc" <?= $sort=="nama_desc"?"selected":"" ?>>Urut: Nama Z-A</option>
            <option value="tgl_asc"   <?= $sort=="tgl_asc"?"selected":"" ?>>Urut: Tanggal ↑</option>
            <option value="tgl_desc"  <?= $sort=="tgl_desc"?"selected":"" ?>>Urut: Tanggal ↓</option>
        </select>

    </form>

    <!-- Table -->
    <table id="domainTable">
        <thead>
            <tr>
                <th>Tipe</th>
                <th>Nama</th>
                <th>Provider</th>
                <th>Expire</th>
                <th>Hari</th>
                <th>Auto</th>
                <th>Catatan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if (count($filtered) > 0): ?>
            <?php foreach ($filtered as $row): ?>
                <tr>
                    <td><span class="<?= strtolower($row['tipe']) == 'hosting' ? 'label-hosting' : 'label-domain' ?>">
                        <?= htmlspecialchars($row['tipe']) ?></span></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['provider']) ?></td>
                    <td><?= $row['expire'] ?></td>
                    <td class="<?= $row['hari'] < 0 ? 'red' : ($row['hari'] <= 30 ? 'yellow' : 'green') ?>">
                        <?= $row['hari'] ?></td>
                    <td><?= $row['auto'] ?></td>
                    <td><?= htmlspecialchars($row['catatan']) ?></td>
                    <td>
                    <button class="btn btn-edit" 
                            onclick="openEditModal(
                                '<?= htmlspecialchars($row['tipe']) ?>',
                                '<?= htmlspecialchars($row['nama']) ?>',
                                '<?= htmlspecialchars($row['provider']) ?>',
                                '', 
                                '<?= $row['expire'] ?>',
                                '', 
                                '', 
                                '<?= $row['auto'] ?>',
                                '<?= htmlspecialchars($row['catatan']) ?>'
                            )">Edit</button>
                    <button class="btn btn-plus">+1y</button>
                    <button class="btn btn-del">Hapus</button>
                </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="8">Tidak ada data</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Modal Form -->
    <div id="editModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); justify-content:center; align-items:center;">
    <div class="modal-content" style="background:#0f172a; padding:20px; border-radius:10px; width:650px; position:relative;">
    <span id="closeModal" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; color:white;">&times;</span>
    <h2>Edit Item</h2>
    <form id="editForm" style="display:flex; flex-wrap:wrap; gap:15px;">
      <div style="flex:1 1 48%;">
        <label>Tipe</label>
        <select id="editType" required>
          <option value="Domain">Domain</option>
          <option value="Hosting">Hosting</option>
        </select>
      </div>
      <div style="flex:1 1 48%;">
        <label>Nama</label>
        <input id="editName" placeholder="contohku.com / VPS Project" required />
      </div>
      <div style="flex:1 1 48%;">
        <label>Registrar / Provider</label>
        <input id="editProvider" placeholder="Niagahoster / Cloudflare / IDCloudHost" />
      </div>
      <div style="flex:1 1 48%;">
        <label>ID Layanan (opsional)</label>
        <input id="editServiceId" placeholder="INV-123 / SID-456" />
      </div>
      <div style="flex:1 1 48%;">
        <label>Tanggal Expire</label>
        <input id="editExpire" type="date" required />
      </div>
      <div style="flex:1 1 48%;">
        <label>Biaya (opsional)</label>
        <input id="editCost" type="number" min="0" step="0.01" placeholder="0" />
      </div>
      <div style="flex:1 1 48%;">
        <label>Mata Uang</label>
        <input id="editCurrency" placeholder="IDR / USD" />
      </div>
      <div style="flex:1 1 48%; display:flex; align-items:center; gap:10px;">
        <input id="editAuto" type="checkbox" />
        <label>Auto-Renew ON</label>
      </div>
      <div style="flex:1 1 100%;">
        <label>Catatan</label>
        <textarea id="editNote" placeholder="Keterangan tambahan…"></textarea>
      </div>
      <div style="flex:1 1 100%; display:flex; justify-content:flex-end; gap:10px;">
        <button type="button" id="btnCancel" style="background:#334155;color:white;padding:10px;border:none;border-radius:6px;cursor:pointer;">Batal</button>
        <button type="submit" style="background:#0284c7;color:white;padding:10px;border:none;border-radius:6px; cursor:pointer;">Simpan</button>
      </div>
    </form>
  </div>
</div>

    <!-- Pagination -->
    <div id="pagination" class="pagination"></div>

    
    <script>
        const editModal = document.getElementById("editModal");
const closeModal = document.getElementById("closeModal");

function openEditModal(tipe, nama, provider, serviceId, expire, cost, currency, auto, note) {
    document.getElementById("editType").value = tipe;
    document.getElementById("editName").value = nama;
    document.getElementById("editProvider").value = provider;
    document.getElementById("editExpire").value = expire;
    document.getElementById("editAuto").checked = (auto === "ON");
    document.getElementById("editNote").value = note;

    editModal.style.display = "flex";
}

// Tutup modal
closeModal.onclick = () => editModal.style.display = "none";
window.onclick = (e) => { if(e.target === editModal) editModal.style.display = "none"; }

// Submit form
document.getElementById("editForm").onsubmit = function(e){
    e.preventDefault();
    // Ambil data dari form
    let tipe = document.getElementById("editType").value;
    let nama = document.getElementById("editName").value;
    let provider = document.getElementById("editProvider").value;
    let expire = document.getElementById("editExpire").value;
    let auto = document.getElementById("editAuto").checked ? "ON" : "OFF";
    let note = document.getElementById("editNote").value;

    // TODO: kirim data via AJAX ke PHP untuk update db
    console.log({tipe, nama, provider, expire, auto, note});

    // Tutup modal setelah submit
    editModal.style.display = "none";
}

    let currentPage = 1;
    const rowsPerPage = 10;

    function displayTable() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let table = document.getElementById("domainTable");
        let tr = table.getElementsByTagName("tbody")[0].getElementsByTagName("tr");
        let filteredRows = [];

        for (let i = 0; i < tr.length; i++) {
            let rowText = tr[i].innerText.toLowerCase();
            if (rowText.indexOf(input) > -1) {
                filteredRows.push(tr[i]);
            }
        }

        // pagination
        let start = (currentPage - 1) * rowsPerPage;
        let end = start + rowsPerPage;

        for (let i = 0; i < tr.length; i++) {
            tr[i].style.display = "none"; // hide semua dulu
        }
        for (let i = 0; i < filteredRows.length; i++) {
            if (i >= start && i < end) {
                filteredRows[i].style.display = "";
            }
        }

        setupPagination(filteredRows.length);
    }

    function setupPagination(totalRows) {
        let pagination = document.getElementById("pagination");
        pagination.innerHTML = "";
        let totalPages = Math.ceil(totalRows / rowsPerPage);

        if (totalPages <= 1) return; // tidak perlu pagination kalau cuma 1 halaman

        // tombol « (prev)
        if (currentPage > 1) {
            let prev = document.createElement("a");
            prev.innerHTML = "&laquo;";
            prev.href = "#";
            prev.onclick = function (e) {
                e.preventDefault();
                currentPage--;
                displayTable();
            };
            pagination.appendChild(prev);
        }

        // nomor halaman
        for (let i = 1; i <= totalPages; i++) {
            let link = document.createElement("a");
            link.innerText = i;
            link.href = "#";
            if (i === currentPage) link.classList.add("active");
            link.onclick = function (e) {
                e.preventDefault();
                currentPage = i;
                displayTable();
            };
            pagination.appendChild(link);
        }

        // tombol » (next)
        if (currentPage < totalPages) {
            let next = document.createElement("a");
            next.innerHTML = "&raquo;";
            next.href = "#";
            next.onclick = function (e) {
                e.preventDefault();
                currentPage++;
                displayTable();
            };
            pagination.appendChild(next);
        }
    }

    window.onload = function() {
        displayTable();
    };

    </script>

<?php include 'footer.php'; ?>

<!-- Modal Form -->
<div id="itemModal" class="modal">
  <div class="modal-content">
    <span class="modal-close" id="btnCloseModal">&times;</span>
    <!-- Form -->
    <div class="card">
      <form id="itemForm" class="row" autocomplete="off">
        <input type="hidden" id="itemId" />
        <div class="col">
          <label for="type">Tipe</label>
          <select id="type" required>
            <option value="domain">Domain</option>
            <option value="hosting">Hosting</option>
          </select>
        </div>
        <div class="col">
          <label for="name">Nama</label>
          <input id="name" placeholder="contohku.com / VPS Project" required />
        </div>
        <div class="col">
          <label for="provider">Registrar / Provider</label>
          <input id="provider" placeholder="Niagahoster / Cloudflare / IDCloudHost" />
        </div>
        <div class="col">
          <label for="serviceId">ID Layanan (opsional)</label>
          <input id="serviceId" placeholder="INV-123 / SID-456" />
        </div>
        <div class="col">
          <label for="expiry">Tanggal Expire</label>
          <input id="expiry" type="date" required />
        </div>
        <div class="col">
          <label for="cost">Biaya (opsional)</label>
          <input id="cost" type="number" min="0" step="0.01" placeholder="0" />
        </div>
        <div class="col">
          <label for="currency">Mata Uang</label>
          <input id="currency" placeholder="IDR / USD" />
        </div>
        <div class="col" style="align-self:end">
          <label><input id="autoRenew" type="checkbox" /> Auto-Renew ON</label>
        </div>
        <div class="col" style="flex:1 1 100%">
          <label for="note">Catatan</label>
          <textarea id="note" placeholder="Keterangan tambahan…"></textarea>
        </div>
        <div class="col" style="flex:1 1 100%;display:flex;gap:8px;justify-content:flex-end">
          <button type="button" class="secondary" id="btnReset">Bersihkan</button>
          <button type="submit" id="btnSave">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const modal = document.getElementById("itemModal");
  const btnOpen = document.getElementById("btnAddItem");
  const btnClose = document.getElementById("btnCloseModal");

  btnOpen.onclick = () => {
    modal.style.display = "flex"; // tampilkan modal
  };

  btnClose.onclick = () => {
    modal.style.display = "none"; // sembunyikan modal
  };

  // Tutup jika klik luar modal
  window.onclick = (e) => {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  };
</script>
</body>
</html>
