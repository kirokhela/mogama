<?php
// Centered logo + button (Fawry) using your HTML classes

$pageContent = <<<HTML
<style>
  :root{
    --accent:#ffd200;      /* Fawry yellow */
    --accent-ink:#111827;  /* dark text */
    --line:#e5e7eb;
    --shadow:0 12px 28px rgba(2,6,23,.10), 0 4px 10px rgba(2,6,23,.05);
  }
  .fawry-wrap{
    min-height: 55vh;
    display:flex; flex-direction:column;
    align-items:center; justify-content:center;
    text-align:center; gap: .75rem;
    padding: 24px 16px;
  }
  .fawry-wrap img{
    width: 140px; height: 140px; object-fit: contain;
    border-radius: 999px; background:#f8fafc;
    border:1px solid var(--line); box-shadow: var(--shadow);
    padding: 10px;
  }
  .fawry-wrap span, .fawry-wrap div, .fawry-wrap p{ color:#1f2937; }

  .pay-card__body{
    display:flex; align-items:center; justify-content:center;
    padding-bottom: 24px;
  }
  .actions{
    display:flex; flex-direction:column; align-items:center; gap:.5rem;
  }
  .btn{
    display:inline-flex; align-items:center; justify-content:center; gap:.55rem;
    text-decoration:none; font-weight:800; letter-spacing:.2px;
    border-radius:12px; padding:.95rem 1.3rem;
    transition: transform .12s ease, box-shadow .18s ease, filter .18s ease;
    will-change: transform;
  }
  .btn-primary{
    background: var(--accent); color: var(--accent-ink);
    box-shadow: 0 12px 22px rgba(255,210,0,.25), 0 6px 12px rgba(0,0,0,.06);
    border:1px solid rgba(0,0,0,.04);
  }
  .btn-primary:hover{ transform: translateY(-2px); filter: brightness(.98); }
  .btn-primary:active{ transform: translateY(0) scale(.99); }
  .btn-icon{ width:18px; height:18px }
  .hint{ font-size:.85rem; color:#6b7280 }
</style>

<div class="fawry-wrap" dir="rtl">
  <img src="shamandora.png" alt="شعار الشمندورة">
  <div class="brand">كشافة الشمندورة</div>
</div>

<div class="pay-card__body">
  <div class="actions">
    <a href="/fawry.php" class="btn btn-primary" aria-label="اذهب إلى الحجز الفوري">
      اذهب إلى  الحجز الفوري
      <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
        <path d="M5 12h14M12 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
    </a>

  </div>
</div>
HTML;

include "layout.php";