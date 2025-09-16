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

    <!-- CSS -->

    <style>
        body { 
          font-family: Arial, sans-serif; 
          background: #0f172a; 
          color: #fff; 
          padding: 20px; 
        }

        h1 { 
          margin-bottom: 20px; 
        }
        .filters { 
          display: flex; 
          flex-direction: row; 
          gap: 10px; 
          margin-bottom: 20px; 
          max-width: 400px; 
        }

        .filters input, 
        .filters select, 
        .filters button {
          flex: none;
        }

        .filters input[type="text"] {
          width: 250px;
        }

        select, input { 
          padding: 10px; 
          border-radius: 8px; 
          border: none; 
          background: #161b22; 
          color: #fff; 
          width: 100%; 
        }

        button { 
          padding: 10px; 
          border-radius: 8px; 
          border: none; 
          background: #0284c7; 
          color: #fff; 
          cursor: pointer; 
        }

        table { 
          border-collapse: collapse; 
          width: 100%; 
          background: #1e293b; 
          border-radius: 10px; 
          overflow: hidden; 
        }

        th, td { 
          padding: 12px; 
          text-align: center; 
        }

        th { 
          background-color: #334155; 
        }

        tr:nth-child(even) { 
          background-color: #1e293b; 
        }

        tr:nth-child(odd) { 
          background-color: #0f172a; 
        }
        .label-hosting { 
          background: #3f1d4a; 
          color: #fff; 
          padding: 4px 10px; 
          border-radius: 20px; 
          font-size: 12px; 
        }

        .label-domain  { 
          background: #0f3a46; 
          color: #fff; 
          padding: 4px 10px; 
          border-radius: 20px; 
          font-size: 12px; 
        }

        .btn { 
          padding: 6px 12px; 
          margin: 2px; 
          border: none; 
          border-radius: 6px; 
          cursor: pointer; 
          font-size: 14px; 
        }

        .btn-edit { 
          background: #475569; 
          color: #fff; 
        }

        .btn-plus { 
          background: #0284c7; 
          color: #fff; 
        }

        .btn-del  { 
          background: #334155; 
          color: #fff; 
        }

        .red { 
          color: #ef4444; 
          font-weight: bold; 
        }

        .yellow { 
          color: #facc15; 
          font-weight: bold; 
        }

        .green { 
          color: #22c55e; 
          font-weight: bold; 
        }

        #pagination { 
          margin-top: 20px; 
          display: flex; 
          justify-content: center;
        }

        #pagination button {
          margin: 2px;
          padding: 6px 10px;
          border: 2px solid #0284c7;   
          border-radius: 5px;
          background: transparent;     
          color: #0284c7;              
          cursor: pointer;
          transition: all 0.2s ease;
        }
        #pagination button.active {
          background: #0284c7;
          color: #fff;
          font-weight: bold;
          transform: scale(1.05);
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
                    <!-- Tombol Edit di tabel -->
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

                    <button class="btn btn-plus">+1y</button>

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

    <!-- Modal Form Edit mirip form tambah -->
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

        <!-- Pagination -->
        <div id="pagination"></div>
        
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

                for (let i = 1; i <= totalPages; i++) {
                    let btn = document.createElement("button");
                    btn.innerText = i;
                    if (i === currentPage) btn.classList.add("active");
                    btn.onclick = function () {
                        currentPage = i;
                        displayTable();
                    };
                    pagination.appendChild(btn);
                }
            }

            window.onload = function() {
                displayTable();
            };

        </script>

    <?php include 'footer.php'; ?>

    <!-- Modal Form tambah item -->
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
