<?php
// header.php
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Domain Management App</title>
    <style>
    :root{
      --bg:#0f172a;       /* slate-900 */
      --card:#111827;     /* gray-900 */
      --muted:#94a3b8;    /* slate-400 */
      --text:#e5e7eb;     /* gray-200 */
      --accent:#22d3ee;   /* cyan-400 */
      --good:#22c55e;     /* green-500 */
      --warn:#fbbf24;     /* amber-400 */
      --bad:#ef4444;      /* red-500 */
      --chip:#1f2937;     /* gray-800 */
      --border:#1f2937;   /* gray-800 */
      --input:#0b1221;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0;font:14px/1.5 system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, sans-serif;background:linear-gradient(180deg,#0b1024,#0a122e 60%,#0b132a);color:var(--text)}
    .container{max-width:1100px;margin:24px auto;padding:0 16px}
    .header{display:flex;flex-wrap:wrap;gap:16px;align-items:center;justify-content:space-between;margin-bottom:16px}
    .title{font-size:20px;font-weight:700;letter-spacing:.3px}
    .muted{color:var(--muted)}
    .card{background:rgba(17,24,39,.7);backdrop-filter: blur(6px);border:1px solid var(--border);border-radius:14px;padding:16px}
    .row{display:flex;flex-wrap:wrap;gap:12px}
    .col{flex:1 1 220px}
    label{display:block;margin-bottom:6px;color:var(--muted);font-size:12px}
    input,select,textarea{width:100%;background:var(--input);border:1px solid var(--border);color:var(--text);border-radius:10px;padding:10px 12px;outline:none}
    input[type="date"]{padding:8px 10px}
    textarea{min-height:62px;resize:vertical}
    .actions{display:flex;gap:8px;flex-wrap:wrap}
    button,.btn{background:#0ea5e9;color:white;border:0;border-radius:12px;padding:10px 14px;cursor:pointer;font-weight:600}
    button.secondary{background:#334155}
    button.ghost{background:transparent;border:1px dashed var(--border);color:var(--muted)}
    .toolbar{display:flex;flex-wrap:wrap;gap:8px;align-items:center;justify-content:space-between;margin:14px 0}
    .chips{display:flex;flex-wrap:wrap;gap:8px}
    .chip{background:var(--chip);padding:6px 10px;border-radius:999px;border:1px solid var(--border);font-size:12px}
    .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin:14px 0}
    .stat{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:12px}
    .stat .num{font-size:18px;font-weight:800}
    .stat small{color:var(--muted)}
    table{width:100%;border-collapse:separate;border-spacing:0 8px}
    thead th{color:var(--muted);font-weight:600;text-align:left;font-size:12px;padding:0 10px}
    tbody tr{background:var(--card);border:1px solid var(--border)}
    tbody td{padding:10px}
    tbody tr{border-radius:12px}
    tbody tr td:first-child{border-top-left-radius:12px;border-bottom-left-radius:12px}
    tbody tr td:last-child{border-top-right-radius:12px;border-bottom-right-radius:12px}
    .badge{padding:3px 8px;border-radius:999px;font-size:12px;border:1px solid var(--border)}
    .badge.domain{background:#0b2a3a}
    .badge.hosting{background:#2a193a}
    .days{font-weight:800}
    .days.good{color:var(--good)}
    .days.warn{color:var(--warn)}
    .days.bad{color:var(--bad)}
    .row-actions{display:flex;gap:6px}
    .row-actions button{padding:6px 10px;border-radius:8px;font-size:12px}
    .srch{min-width:220px}
    .footer{margin:24px 0;color:var(--muted);font-size:12px;text-align:center}
    .hidden{display:none !important}
    .link{color:var(--accent);text-decoration:none}
    .note{color:var(--muted);font-size:12px}
    .pill{display:inline-block;padding:2px 8px;border-radius:999px;border:1px solid var(--border);background:#0b1a2a;font-size:11px}
    .mono{font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace}

    /* Modal Overlay */
.modal {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.6);
  display: none;            /* default hidden */
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

/* Modal Content */
.modal-content {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 16px;
  padding: 30px;
  width: 90%;
  max-width: 600px;
  position: relative;
}

/* Tombol Close "X" */
.modal-close {
  position: absolute;
  top: 10px;
  right: 14px;
  font-size: 20px;
  font-weight: bold;
  color: var(--muted);
  cursor: pointer;
}

  </style>
</head>
<body>
<header>
    <div class="header">
      <div>
        <div class="title">Expiry Manager <span class="pill">offline • tanpa notifikasi</span></div>
        <div class="muted">Kelola masa aktif domain & hosting • data tersimpan lokal di browser (localStorage)</div>
      </div>
      <div class="actions">
        <button id="btnExportCsv" title="Ekspor CSV">Ekspor CSV</button>
        <button id="btnExportJson" class="secondary" title="Ekspor JSON">Ekspor JSON</button>
        <label class="btn ghost" for="fileImport">Impor</label>
        <input id="fileImport" type="file" accept=".csv,.json" class="hidden" />

        <!-- button untuk popup -->
        <button id="btnAddItem" style="background:#22c55e">+ Tambah Item</button>

            <!-- Tombol Logout -->
        <div>
            <form action="logout.php" method="post" style="display:inline;">
                <button type="submit" 
                        style="background:#ef4444; color:#fff; border:none; padding:10px 20px; border-radius:8px; cursor:pointer;">
                    Logout
                </button>
            </form>
        </div>

      </div>
    </div>
</header>
<main>
