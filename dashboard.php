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
        .filters { display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px; max-width: 400px; }
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
        #pagination { margin-top: 20px; }
        #pagination button { margin: 2px; padding: 6px 10px; border: none; border-radius: 5px; }
        #pagination button.active { background: #0284c7; color: #fff; }
    </style>
</head>
<body>
    <h1>Domain Management Dashboard</h1>

    <!-- Tombol Logout -->
<div style="margin-bottom:20px;">
    <form action="logout.php" method="post" style="display:inline;">
        <button type="submit" 
                style="background:#ef4444; color:#fff; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">
            Logout
        </button>
    </form>
</div>

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
                        <button class="btn btn-edit">Edit</button>
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

    <!-- Pagination -->
    <div id="pagination"></div>
    
    <script>
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
</body>
</html>
