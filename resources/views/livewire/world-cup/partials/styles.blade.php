<style>
    .wc-page { background:#fff; }

    .wc-wrap { max-width:1100px; margin:0 auto; padding:2.5rem 2rem 4rem; }

    .wc-tabs { display:flex; gap:2px; border-bottom:1px solid #eceef1; margin-bottom:2rem; }
    .wc-tab { padding:12px 22px; font-size:14px; font-weight:700; color:#9aa1ad; cursor:pointer; border:none; background:none; border-bottom:2px solid transparent; letter-spacing:.02em; text-decoration:none; }
    .wc-tab:hover { color:#262c39; }
    .wc-tab.active { color:#262c39; border-bottom-color:#458bc8; }

    .wc-empty { text-align:center; background:#f6f7f9; border:1px solid #eceef1; border-radius:16px; padding:3.5rem 2rem; }
    .wc-empty .wc-emoji { font-size:44px; }
    .wc-empty h3 { font-size:22px; color:#262c39; margin:.75rem 0 .35rem; }
    .wc-empty p { color:#667085; font-size:15px; }

    .wc-tablewrap { overflow-x:auto; border:1px solid #eceef1; border-radius:16px; }
    table.wc-table { width:100%; border-collapse:collapse; font-size:14px; min-width:820px; }
    .wc-table thead th { text-align:left; font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#9aa1ad; padding:14px 16px; border-bottom:1px solid #eceef1; white-space:nowrap; }
    .wc-table tbody td { padding:16px; border-bottom:1px solid #f1f2f4; vertical-align:top; }
    .wc-table tbody tr:last-child td { border-bottom:none; }
    .wc-table tbody tr.wc-r1 { background:rgba(245,193,66,.10); }
    .wc-table tbody tr.wc-r2 { background:rgba(160,168,180,.12); }

    .wc-pos { font-size:22px; font-weight:800; color:#262c39; text-align:center; width:54px; }
    .wc-entry { font-weight:700; color:#262c39; font-size:15px; }
    .wc-line { display:flex; align-items:center; gap:8px; padding:2px 0; white-space:nowrap; }
    .wc-line .flag { font-size:18px; }
    .wc-line .grp { font-size:11px; font-weight:700; color:#9aa1ad; }
    .wc-muted { color:#667085; }
    .wc-elim { opacity:.45; }
    .wc-elim .wc-tname { text-decoration:line-through; }
    .wc-elim .flag { text-decoration:line-through; }
    .wc-badge { display:inline-flex; align-items:center; gap:3px; background:#e24b4a; color:#fff; font-size:11px; font-weight:700; padding:1px 7px; border-radius:9px; }
    .wc-badge-card { display:inline-flex; align-items:center; gap:3px; font-size:11px; font-weight:700; padding:1px 6px; border-radius:9px; }
    .wc-badge-yellow { background:#fef3c7; color:#92670c; }
    .wc-badge-red { background:#fee2e2; color:#b42318; }

    .wc-pts { white-space:nowrap; }
    .wc-pts .wc-total { font-size:20px; font-weight:800; color:#262c39; }

    .wc-cards { display:grid; gap:12px; }
    .wc-card { border:1px solid #eceef1; border-radius:14px; padding:16px 18px; }
    .wc-row { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    .wc-teams { display:flex; align-items:center; gap:14px; font-size:15px; font-weight:600; color:#262c39; }
    .wc-teams .score { font-weight:800; color:#262c39; background:#f4f4f4; border-radius:8px; padding:2px 10px; }
    .wc-teams .vs { color:#c2c6cd; font-weight:700; font-size:12px; }
    .wc-meta { text-align:right; }
    .wc-grp-pill { display:inline-block; font-size:11px; font-weight:700; color:#262c39; background:#eef0f3; border-radius:7px; padding:1px 8px; margin-bottom:3px; }
    .wc-when { font-size:13px; color:#667085; }
    .wc-scorers { margin-top:8px; font-size:13px; color:#667085; }

    .wc-block { margin-top:12px; border-top:1px dashed #eceef1; padding-top:10px; }
    .wc-block .lbl { font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#9aa1ad; margin-bottom:5px; }
    .wc-award { font-size:13.5px; color:#475467; padding:1px 0; }
    .wc-award strong { color:#262c39; }
    .wc-award .pts { color:#2e7d32; font-weight:700; }
    .wc-watch { font-size:13.5px; color:#475467; line-height:1.6; }
    .wc-watch strong { color:#262c39; }

    .wc-key { text-align:center; color:#667085; font-size:14px; border-top:1px solid #eceef1; padding-top:1.75rem; margin-top:1rem; }
    .wc-key strong { color:#262c39; }

    .wc-pagination { display:flex; align-items:center; justify-content:center; gap:16px; margin-top:1.5rem; }
    .wc-page-btn { padding:8px 16px; font-size:13px; font-weight:700; color:#262c39; background:#f4f4f4; border:1px solid #eceef1; border-radius:9px; cursor:pointer; }
    .wc-page-btn:hover:not(:disabled) { background:#eceef1; }
    .wc-page-btn:disabled { opacity:.45; cursor:default; }
    .wc-page-info { font-size:13px; color:#667085; font-weight:600; }

    /* Tournament progress accent bar */
    .wc-progress { margin-bottom:2rem; }
    .wc-progress-text { font-size:13px; color:#667085; margin-bottom:8px; letter-spacing:.01em; }
    .wc-progress-text strong { color:#262c39; font-weight:800; }
    .wc-progress-text .sep { color:#c2c6cd; margin:0 6px; }
    .wc-progress-bar { display:flex; height:8px; border-radius:6px; overflow:hidden; background:#eceef1; }
    .wc-progress-done { background:linear-gradient(90deg,#3b82c4,#4aaa6e); height:100%; }
    .wc-progress-live { background:#22c55e; height:100%; animation:wc-prog-pulse 1.2s ease-in-out infinite; }
    @keyframes wc-prog-pulse { 0%,100% { opacity:1; } 50% { opacity:.4; } }

    /* Live Now */
    .wc-live { margin-bottom:2.25rem; }
    .wc-live-head { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
    .wc-live-head h2 { font-size:18px; font-weight:800; color:#262c39; letter-spacing:.01em; }
    .wc-live-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:14px; }
    .wc-live-card { border:2px solid #22c55e; background:linear-gradient(135deg,#f0fdf4,#dcfce7); border-radius:16px; padding:16px 18px; box-shadow:0 1px 3px rgba(34,197,94,.12); }
    .wc-live-top { display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .wc-live-badge { display:inline-flex; align-items:center; gap:7px; color:#1f9d55; font-size:12px; font-weight:800; letter-spacing:.07em; text-transform:uppercase; }
    .wc-live-badge .dot { width:9px; height:9px; border-radius:50%; background:#1f9d55; animation:wc-pulse 1.2s ease-in-out infinite; }
    @keyframes wc-pulse { 0%,100% { opacity:1; box-shadow:0 0 0 0 rgba(31,157,85,.5); } 50% { opacity:.5; box-shadow:0 0 0 6px rgba(31,157,85,0); } }
    .wc-live-score { display:flex; align-items:center; justify-content:center; gap:14px; flex-wrap:wrap; margin:14px 0 2px; }
    .wc-live-team { display:flex; align-items:center; gap:8px; font-size:16px; font-weight:700; color:#262c39; }
    .wc-live-team .flag { font-size:24px; }
    .wc-live-num { font-size:30px; font-weight:800; color:#fff; background:#262c39; border-radius:10px; padding:2px 14px; white-space:nowrap; }
    .wc-live-scorers { text-align:center; margin-top:8px; font-size:13px; color:#667085; }
    .wc-live-stakes { margin-top:12px; border-top:1px dashed #86efac; padding-top:10px; text-align:center; }

    @media (max-width:600px) {
        .wc-tab { padding:12px 14px; }
    }
</style>
