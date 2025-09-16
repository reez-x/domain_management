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
    id,
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
  background-color: #165eb1ff; /* biru aktif */
  color: white;
}

.pagination a:hover:not(.active) {
  background-color: #334155; /* abu hover */
}



    </style>
</head>
<body>
    <h1>Domain Management Dashboard</h1>
    <div class="chips" style="margin-bottom: 20px;">
        <span class="chip">Jam lokal: <span id="clock" class="mono"></span></span>
        <span class="chip">Timezone: Asia/Jakarta (browser local)</span>
      </div>
    <!-- Filter Form -->
    <form method="get" style="display:flex; flex-wrap:nowrap; gap:10px; align-items:center; max-width:100%; margin-bottom:20px; overflow-x:auto;">
        <input type="text" id="searchInput" name="search" placeholder="Cari nama / provider..." 
              value="<?= htmlspecialchars($search) ?>" style="padding:10px; border-radius:8px; border:none; background:#161b22; color:white; min-width:200px; flex:1;">

        <button type="submit" style="padding:10px 15px; border-radius:8px; border:none; background:#0284c7; color:white; cursor:pointer;">Cari</button>

        <select name="type" onchange="this.form.submit()" style="padding:10px; border-radius:8px; border:none; background:#161b22; color:white;">
            <option value="">Semua Tipe</option>
            <option value="Domain" <?= $type=="Domain"?"selected":"" ?>>Domain</option>
            <option value="Hosting" <?= $type=="Hosting"?"selected":"" ?>>Hosting</option>
        </select>

        <select name="status" onchange="this.form.submit()" style="padding:10px; border-radius:8px; border:none; background:#161b22; color:white;">
            <option value="">Semua Status</option>
            <option value="JatuhTempo"  <?= $status=="JatuhTempo"?"selected":"" ?>>Jatuh tempo ≤ 30</option>
            <option value="Expired" <?= $status=="Expired"?"selected":"" ?>>Expired</option>
            <option value="AutoRenew" <?= $status=="AutoRenew"?"selected":"" ?>>Auto-Renew ON</option>
        </select>

        <select name="sort" onchange="this.form.submit()" style="padding:10px; border-radius:8px; border:none; background:#161b22; color:white;">
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
                    <td><?= $row['auto'] == 1 ? 'ON' : 'OFF' ?></td>
                    <td><?= htmlspecialchars($row['catatan']) ?></td>
                    <td>

                    <button class="btn btn-edit" 
                      data-id="<?= $row['id'] ?>"
                      data-type="<?= htmlspecialchars($row['tipe']) ?>"
                      data-name="<?= htmlspecialchars($row['nama']) ?>"
                      data-provider="<?= htmlspecialchars($row['provider']) ?>"
                      data-expire="<?= $row['expire'] ?>"
                      data-serviceid="<?= $row['service_id'] ?? '' ?>"
                      data-cost="<?= $row['cost'] ?? '' ?>"
                      data-currency="<?= $row['currency'] ?? '' ?>"
                      data-auto="<?= $row['auto'] ?>"
                      data-note="<?= htmlspecialchars($row['catatan'] ?? '') ?>"
                    >Edit</button>

                    <button class="btn btn-plus" onclick="openPlusOneYearModal(<?= $row['id'] ?>)">+1y</button>

                    <form method="post" action="delete.php" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" class="btn btn-del">Hapus</button>
                    </form>
                </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="8">Tidak ada data</td></tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- MODAL EDIT -->

    <div id="editModal" class="modal" style="display:none;">
        <div class="modal-content">
          <span class="modal-close" id="btnCloseEditModal">&times;</span>
          
          <!-- Form -->
          <div class="card">
            <form id="editForm" method="post" action="update.php" class="row" autocomplete="off">
              <input type="hidden" id="editId" name="id" />

              <div class="col">
                <label for="editType">Tipe</label>
                <select id="editType" name="type" required>
                  <option value="domain">Domain</option>
                  <option value="hosting">Hosting</option>
                </select>
              </div>

              <div class="col">
                <label for="editName">Nama</label>
                <input id="editName" name="name" placeholder="contohku.com / VPS Project" required />
              </div>

              <div class="col">
                <label for="editProvider">Registrar / Provider</label>
                <input id="editProvider" name="provider" placeholder="Niagahoster / Cloudflare / IDCloudHost" />
              </div>

              <div class="col">
                <label for="editServiceId">ID Layanan (opsional)</label>
                <input id="editServiceId" name="serviceId" placeholder="INV-123 / SID-456" />
              </div>

              <div class="col">
                <label for="editExpiry">Tanggal Expire</label>
                <input id="editExpiry" name="expiry" type="date" required />
              </div>

              <div class="col">
                <label for="editCost">Biaya (opsional)</label>
                <input id="editCost" name="cost" type="number" min="0" step="0.01" placeholder="0" />
              </div>

              <div class="col">
                <label for="editCurrency">Mata Uang</label>
                <input id="editCurrency" name="currency" placeholder="IDR / USD" />
              </div>

              <div class="col" style="align-self:end">
                <label>
                  <input id="editAutoRenew" name="autoRenew" type="checkbox" /> Auto-Renew ON
                </label>
              </div>

              <div class="col" style="flex:1 1 100%">
                <label for="editNote">Catatan</label>
                <textarea id="editNote" name="note" placeholder="Keterangan tambahan…"></textarea>
              </div>

              <div class="col" style="flex:1 1 100%;display:flex;gap:8px;justify-content:flex-end">
                <button type="button" class="secondary" id="btnCancelEdit">Batal</button>
                <button type="submit" id="btnSaveEdit">Simpan</button>
              </div>
            </form>
          </div>
        </div>
      </div>

<!-- Modal Konfirmasi +1 Tahun -->
<div id="plusOneYearModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
  <div class="modal-content" style="background:#0f172a; padding:20px; border-radius:10px; width:400px; text-align:center; position:relative;">
    <span id="closePlusOneYearModal" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; color:white;">&times;</span>
    <h2>Konfirmasi</h2>
    <p style="margin:20px 0;">Apakah Anda yakin ingin menambah <strong>1 tahun</strong> pada domain ini?</p>
    
    <input type="hidden" id="plusOneYearDomainId">

    <div style="display:flex; justify-content:center; gap:10px;">
      <button id="confirmPlusOneYear" style="background:#0284c7;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;">Ya</button>
      <button id="cancelPlusOneYear" style="background:#334155;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;">Batal</button>
    </div>
  </div>
</div>

<!-- Modal Notifikasi -->
<div id="notificationModal" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center;">
  <div class="modal-content" style="background:#0f172a; padding:20px; border-radius:10px; width:400px; text-align:center; position:relative;">
    <span id="closeNotificationModal" style="position:absolute; top:10px; right:15px; cursor:pointer; font-size:20px; color:white;">&times;</span>
    <h2 id="notificationTitle" style="color:white; margin-bottom:10px;"></h2>
    <p id="notificationMessage" style="color:#cbd5e1;"></p>
    <div style="margin-top:20px;">
      <button id="notificationOkBtn" style="background:#0284c7;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;">OK</button>
    </div>
  </div>
</div>

<!-- Modal Import -->
<div id="importModal" class="modal">
  <div class="modal-content">
    <span class="modal-close" onclick="document.getElementById('importModal').style.display='none'">&times;</span>
    <h3>Import Data</h3>
    <form action="import.php" method="post" enctype="multipart/form-data">
      <label for="file">Pilih file (CSV/JSON):</label>
      <input type="file" name="file" id="file" accept=".csv,.json" required>
      <br><br>
      <button type="submit" name="import">Upload & Cek</button>
    </form>
  </div>
</div>

<?php if (isset($_GET['show_import_confirm'])): ?>
<div class="modal" style="display:flex;">
  <div class="modal-content">
    <h3>Konfirmasi Import</h3>
    <?php if (!empty($_SESSION['duplicates']) && $_SESSION['duplicates'] > 0): ?>
      <p>Ditemukan <b><?= $_SESSION['duplicates'] ?></b> data duplikat.</p>
      <form action="save_import.php" method="post">
        <button type="submit" name="action" value="overwrite">Timpa Semua</button>
        <button type="submit" name="action" value="skip">Pertahankan Lama</button>
      </form>
    <?php else: ?>
      <p>Tidak ada data duplikat. Lanjutkan import?</p>
      <form action="save_import.php" method="post">
        <button type="submit" name="action" value="insert">Konfirmasi</button>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

    <!-- Pagination -->
    <div id="pagination" class="pagination"></div>

    
    <script>
        const editModal = document.getElementById('editModal');
        const btnCloseEdit = document.getElementById('btnCloseEditModal');
        const btnCancelEdit = document.getElementById('btnCancelEdit');

        // tombol edit di tabel
        document.querySelectorAll('.btn-edit').forEach(btn => {
          btn.addEventListener('click', () => {
            document.getElementById('editId').value = btn.dataset.id;
            document.getElementById('editType').value = btn.dataset.type;
            document.getElementById('editName').value = btn.dataset.name;
            document.getElementById('editProvider').value = btn.dataset.provider;
            document.getElementById('editServiceId').value = btn.dataset.serviceId || '';
            document.getElementById('editExpiry').value = btn.dataset.expire;
            document.getElementById('editCost').value = btn.dataset.cost || '';
            document.getElementById('editCurrency').value = btn.dataset.currency || '';
            document.getElementById('editNote').value = btn.dataset.note || '';
            document.getElementById('editAutoRenew').checked = btn.dataset.auto === '1';
            editModal.style.display = 'flex';
          });
        });

        // tutup modal
        btnCloseEdit.addEventListener('click', () => editModal.style.display = 'none');
        btnCancelEdit.addEventListener('click', () => editModal.style.display = 'none');

        // klik di luar modal
        window.addEventListener('click', e => {
          if(e.target === editModal) editModal.style.display = 'none';
        });
        // Pagination

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

<div id="itemModal" class="modal">
      <div class="modal-content">
        <span class="modal-close" id="btnCloseModal">&times;</span>
        
        <!-- Form -->
        <div class="card">
          <form id="itemForm" method="post" action="create.php" class="row" autocomplete="off">
            <input type="hidden" id="itemId" />
            <div class="col">
              <label for="type">Tipe</label>
              <select id="type" name="type" required>
                <option value="domain">Domain</option>
                <option value="hosting">Hosting</option>
              </select>
            </div>

            <div class="col">
              <label for="name">Nama</label>
              <input id="name" name="name" placeholder="contohku.com / VPS Project" required />
            </div>
            <div class="col">
              <label for="provider">Registrar / Provider</label>
              <input id="provider" name="provider" placeholder="Niagahoster / Cloudflare / IDCloudHost" />
            </div>
            <div class="col">
              <label for="serviceId">ID Layanan (opsional)</label>
              <input id="serviceId" name="serviceId" placeholder="INV-123 / SID-456" />
            </div>
            <div class="col">
              <label for="expiry">Tanggal Expire</label>
              <input id="expiry" name="expiry" type="date" required />
            </div>
            <div class="col">
              <label for="cost">Biaya (opsional)</label>
              <input id="cost" name="cost" type="number" min="0" step="0.01" placeholder="0" />
            </div>
            <div class="col">
              <label for="currency">Mata Uang</label>
              <input id="currency" name="currency" placeholder="IDR / USD" />
            </div>
            <div class="col" style="align-self:end">
              <label><input id="autoRenew" name="autoRenew" type="checkbox" /> Auto-Renew ON</label>
            </div>
            <div class="col" style="flex:1 1 100%">
              <label for="note">Catatan</label>
              <textarea id="note" name="note" placeholder="Keterangan tambahan…"></textarea>
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
    (function() {
        // Fungsi untuk menampilkan jam lokal Asia/Jakarta
        function updateClock() {
            const options = {
                timeZone: "Asia/Jakarta",
                year: "numeric",
                month: "2-digit",
                day: "2-digit",
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
                hour12: false
            };

            const formatter = new Intl.DateTimeFormat('en-GB', options);
            const parts = formatter.formatToParts(new Date());

            // ambil tiap bagian
            let y, m, d, h, min, s;
            parts.forEach(p => {
                if (p.type === 'year') y = p.value;
                if (p.type === 'month') m = p.value;
                if (p.type === 'day') d = p.value;
                if (p.type === 'hour') h = p.value;
                if (p.type === 'minute') min = p.value;
                if (p.type === 'second') s = p.value;
            });

            document.getElementById('clock').textContent = `${y}-${m}-${d} ${h}:${min}:${s}`;
        }

        // jalankan langsung dan tiap detik
        updateClock();
        setInterval(updateClock, 1000);
    })();
</script>

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

  const plusOneYearModal = document.getElementById("plusOneYearModal");
  const closePlusOneYearModal = document.getElementById("closePlusOneYearModal");
  const cancelPlusOneYear = document.getElementById("cancelPlusOneYear");
  const confirmPlusOneYear = document.getElementById("confirmPlusOneYear");

  // Buka modal
  function openPlusOneYearModal(domainId) {
    document.getElementById("plusOneYearDomainId").value = domainId;
    plusOneYearModal.style.display = "flex";
  }

  // Tutup modal
  function closePlusYearModal() {
    plusOneYearModal.style.display = "none";
  }

  closePlusOneYearModal.onclick = closePlusYearModal;
  cancelPlusOneYear.onclick = closePlusYearModal;

  confirmPlusOneYear.onclick = function() {
  const domainId = document.getElementById("plusOneYearDomainId").value;

  fetch('update_expire.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `id=${domainId}`
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification("Sukses", "Tanggal expire berhasil ditambah 1 tahun!", true);
    } else {
      showNotification("Gagal", "Tidak dapat memperbarui tanggal expire!", false);
    }
  })
  .catch(err => {
    console.error(err);
    showNotification("Error", "Terjadi kesalahan saat memperbarui data!", false);
  });

  closePlusYearModal();
};


  const notificationModal = document.getElementById("notificationModal");
  const closeNotificationModal = document.getElementById("closeNotificationModal");
  const notificationOkBtn = document.getElementById("notificationOkBtn");

  function showNotification(title, message, reload = false) {
    document.getElementById("notificationTitle").innerText = title;
    document.getElementById("notificationMessage").innerText = message;
    notificationModal.style.display = "flex";

    notificationOkBtn.onclick = function() {
      notificationModal.style.display = "none";
      if (reload) {
        location.reload(); 
      }
    };
  }

  closeNotificationModal.onclick = () => {
    notificationModal.style.display = "none";
  };

  window.onclick = (e) => {
    if (e.target === notificationModal) {
      notificationModal.style.display = "none";
    }
  };

// Import Modal
const importModal = document.getElementById("importModal");
const btnOpenImport = document.getElementById("btnOpenImport");
const closeImport = document.getElementById("closeImport");

btnOpenImport.onclick = function() {
  importModal.style.display = "flex";
}
closeImport.onclick = function() {
  importModal.style.display = "none";
}
window.onclick = function(event) {
  if (event.target == importModal) {
    importModal.style.display = "none";
  }
}

</script>
</body>
</html>
