<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Domain</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #0f172a; color: #e2e8f0; }
        h1 { margin-bottom: 20px; }
        .filters { margin-bottom: 20px; }
        input, select {
            padding: 8px;
            margin: 5px;
            border-radius: 5px;
            border: 1px solid #334155;
            background: #1e293b;
            color: #e2e8f0;
        }
        table { border-collapse: collapse; width: 100%; margin-top: 20px; }
        th, td { border: 1px solid #334155; padding: 10px; text-align: center; }
        th { background-color: #1e293b; color: #f1f5f9; }
        tr:nth-child(even) { background-color: #1e293b; }
        .pagination { margin-top: 20px; text-align: center; }
        .pagination button {
            background: #1e293b;
            border: 1px solid #334155;
            color: #e2e8f0;
            margin: 0 5px;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
        }
        .pagination button.active {
            background: #3b82f6;
            border-color: #2563eb;
            color: white;
        }
    </style>
</head>
<body>
    <h1>Domain Management Dashboard</h1>

    <div class="filters">
        <input type="text" id="searchInput" placeholder="Cari nama / provider / tag..." onkeyup="applyFilters()">
        
        <select id="typeFilter" onchange="applyFilters()">
            <option value="">Semua Tipe</option>
            <option value="Personal">Personal</option>
            <option value="Business">Business</option>
        </select>
        
        <select id="statusFilter" onchange="applyFilters()">
            <option value="">Semua Status</option>
            <option value="Active">Active</option>
            <option value="Expired">Expired</option>
            <option value="Pending">Pending</option>
        </select>
        
        <select id="sortFilter" onchange="applyFilters()">
            <option value="asc">Urut: Hari Tersisa ↑</option>
            <option value="desc">Urut: Hari Tersisa ↓</option>
        </select>
    </div>

    <table id="domainTable">
        <thead>
            <tr>
                <th>Domain</th>
                <th>Tipe</th>
                <th>Status</th>
                <th>Expiry Date</th>
                <th>Hari Tersisa</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>example.com</td><td>Business</td><td>Active</td><td>2025-12-31</td><td>470</td></tr>
            <tr><td>mydomain.net</td><td>Personal</td><td>Expired</td><td>2024-05-10</td><td>-120</td></tr>
            <tr><td>project.org</td><td>Business</td><td>Active</td><td>2026-01-20</td><td>490</td></tr>
            <tr><td>website.id</td><td>Personal</td><td>Pending</td><td>2025-03-15</td><td>180</td></tr>
            <tr><td>startup.co</td><td>Business</td><td>Active</td><td>2025-11-10</td><td>400</td></tr>
            <tr><td>blogku.me</td><td>Personal</td><td>Expired</td><td>2023-12-01</td><td>-300</td></tr>
            <tr><td>shoponline.io</td><td>Business</td><td>Pending</td><td>2025-07-05</td><td>250</td></tr>
            <tr><td>company.biz</td><td>Business</td><td>Active</td><td>2026-02-14</td><td>520</td></tr>
            <tr><td>gamehub.gg</td><td>Personal</td><td>Active</td><td>2025-08-22</td><td>330</td></tr>
            <tr><td>event.co.id</td><td>Business</td><td>Pending</td><td>2024-11-30</td><td>60</td></tr>
        </tbody>
    </table>

    <div class="pagination" id="pagination"></div>

    <script>
        let currentPage = 1;
        let rowsPerPage = 5;

        function applyFilters() {
            let search = document.getElementById("searchInput").value.toLowerCase();
            let type = document.getElementById("typeFilter").value;
            let status = document.getElementById("statusFilter").value;
            let sort = document.getElementById("sortFilter").value;
            
            let table = document.getElementById("domainTable").getElementsByTagName("tbody")[0];
            let rows = Array.from(table.getElementsByTagName("tr"));

            rows.forEach(row => {
                let domain = row.cells[0].innerText.toLowerCase();
                let tipe = row.cells[1].innerText;
                let stat = row.cells[2].innerText;

                let matchSearch = domain.indexOf(search) > -1;
                let matchType = !type || tipe === type;
                let matchStatus = !status || stat === status;

                row.dataset.visible = (matchSearch && matchType && matchStatus) ? "true" : "false";
            });

            // Sorting
            let visibleRows = rows.filter(r => r.dataset.visible === "true");
            visibleRows.sort((a, b) => {
                let valA = parseInt(a.cells[4].innerText);
                let valB = parseInt(b.cells[4].innerText);
                return sort === "asc" ? valA - valB : valB - valA;
            });
            visibleRows.forEach(r => table.appendChild(r));

            currentPage = 1; 
            displayTable();
        }

        function displayTable() {
            let table = document.getElementById("domainTable").getElementsByTagName("tbody")[0];
            let rows = Array.from(table.getElementsByTagName("tr")).filter(r => r.dataset.visible !== "false");

            let start = (currentPage - 1) * rowsPerPage;
            let end = start + rowsPerPage;

            rows.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? "" : "none";
            });

            setupPagination(rows.length);
        }

        function setupPagination(totalRows) {
            let pageCount = Math.ceil(totalRows / rowsPerPage);
            let pagination = document.getElementById("pagination");
            pagination.innerHTML = "";

            for (let i = 1; i <= pageCount; i++) {
                let btn = document.createElement("button");
                btn.innerText = i;
                btn.classList.add(i === currentPage ? "active" : "");
                btn.addEventListener("click", function() {
                    currentPage = i;
                    displayTable();
                });
                pagination.appendChild(btn);
            }
        }

        // Init
        window.onload = () => {
            let rows = document.getElementById("domainTable").getElementsByTagName("tbody")[0].getElementsByTagName("tr");
            Array.from(rows).forEach(r => r.dataset.visible = "true");
            applyFilters();
        };
    </script>
</body>
</html>
