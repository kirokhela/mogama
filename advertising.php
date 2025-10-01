<?php /**
 * advertising.php — Shamandora Scouts link hub
 */ ?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>كشافة الشمندورة | روابط السوشيال</title>
  <meta name="description" content="روابط السوشيال الخاصة بكشافة الشمندورة: فيسبوك، إنستجرام، سبوتيفاي، أنغامي." />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;600;800&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body{ font-family: 'Cairo', system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, 'Helvetica Neue', Arial; }
    .hero-bg{
      background: linear-gradient(180deg, #0b2a4a 0%, #071526 55%, #040b14 100%);
    }
    .glass{ backdrop-filter: blur(10px); background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.05)); border:1px solid rgba(255,255,255,.1); }
    .btn{
      display:flex; align-items:center; gap:.9rem; width:100%;
      border-radius:1rem; padding:1rem 1.25rem; font-weight:700;
      transition: transform .08s ease, box-shadow .2s ease, opacity .2s ease;
      box-shadow: 0 10px 20px rgba(0,0,0,.25);
    }
    .btn:hover{ transform: translateY(-2px); box-shadow: 0 14px 28px rgba(0,0,0,.3); }
    .modal{ position:fixed; inset:0; display:none; align-items:center; justify-content:center; background: rgba(3,10,20,.65); z-index:50; }
    .modal.active{ display:flex; }
  </style>
</head>
<body class="min-h-screen hero-bg text-white">
  <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Header / Brand -->
    <header class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <img src="shamandora.png" alt="Shamandora Logo" class="w-14 h-14 rounded-full border border-white/20 shadow"/>
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold">كشافة الشمندورة</h1>
          <p class="text-sm sm:text-base text-white/80">روابطنا الرسمية — Official Links</p>
        </div>
      </div>
    </header>

    <!-- Links Grid -->
    <section class="mt-10 grid grid-cols-1 sm:grid-cols-2 gap-4">
      <!-- Facebook -->
      <a href="https://www.facebook.com/share/17AE3MNHam/" target="_blank" rel="noopener" class="btn bg-[#1877f2] hover:opacity-95">
        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-white/15">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12.06C22 6.48 17.52 2 11.94 2S2 6.48 2 12.06c0 5.02 3.66 9.18 8.44 9.98v-7.06H8.08v-2.92h2.36V9.86c0-2.33 1.39-3.62 3.51-3.62c1.02 0 2.09.18 2.09.18v2.3h-1.18c-1.16 0-1.53.72-1.53 1.47v1.77h2.61l-.42 2.92h-2.19v7.06C18.34 21.24 22 17.08 22 12.06Z"/></svg>
        </span>
        <div class="flex-1">
          <div class="text-lg font-bold">Facebook</div>
          <div class="text-sm opacity-90">آخر الصور والفيديوهات</div>
        </div>
        <button type="button" data-qr="https://www.facebook.com/share/17AE3MNHam/" class="open-qr text-white/90 text-xl px-2">⋯</button>
      </a>

      <!-- Instagram -->
      <a href="https://www.instagram.com/shamandora_scout?igsh=dXExOXFucTBkMDVt" target="_blank" rel="noopener" class="btn bg-gradient-to-r from-[#f58529] via-[#dd2a7b] to-[#8134af] hover:opacity-95">
        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-white/15">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Zm0 2a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7Zm5 3.5A5.5 5.5 0 1 1 6.5 13 5.5 5.5 0 0 1 12 7.5Zm0 2A3.5 3.5 0 1 0 15.5 13 3.5 3.5 0 0 0 12 9.5Zm6.25-3.75a1.25 1.25 0 1 1-1.25 1.25 1.25 1.25 0 0 1 1.25-1.25Z"/></svg>
        </span>
        <div class="flex-1">
          <div class="text-lg font-bold">Instagram</div>
          <div class="text-sm opacity-90">لقطات من الأنشطة والفعاليات</div>
        </div>
        <button type="button" data-qr="https://www.instagram.com/shamandora_scout?igsh=dXExOXFucTBkMDVt" class="open-qr text-white/90 text-xl px-2">⋯</button>
      </a>

      <!-- Spotify -->
      <a href="https://open.spotify.com/artist/6UxngCQeJnijih2mXIhb7Z?si=CUircyf5R2eM1w9a-Sb8sQ" target="_blank" rel="noopener" class="btn bg-[#1db954] hover:opacity-95">
        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-white/15">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 .001 20.001A10 10 0 0 0 12 2Zm4.47 14.37a.78.78 0 0 1-1.07.26c-2.94-1.8-6.64-2.2-10.97-1.2a.78.78 0 1 1-.34-1.52c4.73-1.08 8.9-.62 12.17 1.36a.78.78 0 0 1 .21 1.1Zm1.45-3.12a.97.97 0 0 1-1.34.32c-3.36-2.06-8.47-2.66-12.44-1.46a.97.97 0 1 1-.56-1.86c4.48-1.36 10.08-.7 13.88 1.59a.97.97 0 0 1 .46 1.41Zm.13-3.29a1.15 1.15 0 0 1-1.6.38c-3.85-2.35-10.29-2.57-14.03-1.43a1.15 1.15 0 1 1-.66-2.21c4.33-1.29 11.39-1.03 15.83 1.67a1.15 1.15 0 0 1 .46 1.59Z"/></svg>
        </span>
        <div class="flex-1">
          <div class="text-lg font-bold">Spotify</div>
          <div class="text-sm opacity-90">اسمعنا على سبوتيفاي</div>
        </div>
        <button type="button" data-qr="https://open.spotify.com/artist/6UxngCQeJnijih2mXIhb7Z?si=CUircyf5R2eM1w9a-Sb8sQ" class="open-qr text-white/90 text-xl px-2">⋯</button>
      </a>

      <!-- Anghami -->
      <a href="https://play.anghami.com/artist/15992103" target="_blank" rel="noopener" class="btn bg-[#6b3fa0] hover:opacity-95">
        <span class="inline-flex items-center justify-center w-11 h-11 rounded-xl bg-white/15">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M12.02 3c-5 0-9.06 4.06-9.06 9.06s4.06 9.06 9.06 9.06 9.06-4.06 9.06-9.06S17.02 3 12.02 3Zm4.04 12.46-3.04-6.98a1.1 1.1 0 0 0-2.03 0L7.95 15.5a1.1 1.1 0 0 0 2.03.89l.52-1.2h4.08l.52 1.2a1.1 1.1 0 0 0 2.03-.9Zm-5.84-2.7 1.14-2.65 1.14 2.65h-2.28Z"/></svg>
        </span>
        <div class="flex-1">
          <div class="text-lg font-bold">Anghami</div>
          <div class="text-sm opacity-90">اسمعنا على أنغامي</div>
        </div>
        <button type="button" data-qr="https://play.anghami.com/artist/15992103" class="open-qr text-white/90 text-xl px-2">⋯</button>
      </a>
    </section>

    <footer class="mt-10 text-center text-sm text-white/70">
      صُنع بواسطة فريق <span class="font-bold">كشافة الشمندورة</span> — © <?php echo date('Y'); ?>
    </footer>
  </main>

  <!-- QR Modal -->
  <div id="qrModal" class="modal">
    <div class="glass rounded-2xl p-6 w-[92%] max-w-sm text-center border border-white/15">
      <div class="text-lg font-bold">QR رابط سريع</div>
      <div id="qrBox" class="mx-auto mt-4 bg-white p-3 rounded-xl"></div>
      <div id="qrUrl" class="mt-2 text-xs break-all text-white/80"></div>
      <div class="mt-5 flex gap-3 justify-center">
        <button id="downloadQR" class="px-4 py-2 rounded-lg bg-cyan-500/90 hover:bg-cyan-400 font-bold">تحميل</button>
        <button id="closeQR" class="px-4 py-2 rounded-lg bg-white/10 hover:bg-white/20 font-bold">إغلاق</button>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script>
    const modal = document.getElementById('qrModal');
    const qrBox = document.getElementById('qrBox');
    const qrUrl = document.getElementById('qrUrl');
    const downloadQR = document.getElementById('downloadQR');
    const closeQR = document.getElementById('closeQR');

    function openQR(url){
      qrBox.innerHTML = '';
      qrUrl.textContent = url;
      new QRCode(qrBox, { text: url, width: 220, height: 220, correctLevel: QRCode.CorrectLevel.M });
      modal.classList.add('active');
    }

    document.querySelectorAll('.open-qr').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const url = e.currentTarget.getAttribute('data-qr');
        openQR(url);
      });
    });

    closeQR.addEventListener('click', () => modal.classList.remove('active'));
    downloadQR.addEventListener('click', () => {
      const canvas = qrBox.querySelector('canvas');
      if(!canvas) return;
      const link = document.createElement('a');
      link.download = 'shamandora-qr.png';
      link.href = canvas.toDataURL('image/png');
      link.click();
    });
    modal.addEventListener('click', (e) => { if(e.target === modal) modal.classList.remove('active'); });
    document.addEventListener('keydown', (e)=>{ if(e.key === 'Escape') modal.classList.remove('active'); });
  </script>
</body>
</html>
