<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Venexpress — ¿Dónde está tu paquete?</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Oswald:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  :root{
    --ink:#14201B;
    --route-green:#1F4D3A;
    --route-green-dark:#122E23;
    --signal-amber:#F2A73B;
    --signal-amber-dark:#D68F26;
    --concrete:#EDEAE4;
    --paper:#F8F6F1;
    --stamp-red:#C0432B;
    --text-body:#2B2B28;
    --line:rgba(247,245,240,0.28);
    --font-display:'Bebas Neue', sans-serif;
    --font-head:'Oswald', sans-serif;
    --font-body:'IBM Plex Sans', sans-serif;
    --font-mono:'IBM Plex Mono', monospace;
  }
  *{box-sizing:border-box; margin:0; padding:0;}
  html{scroll-behavior:smooth;}
  body{
    font-family:var(--font-body);
    color:var(--text-body);
    background:var(--concrete);
    -webkit-font-smoothing:antialiased;
  }
  a{color:inherit;}
  img,svg{display:block; max-width:100%;}
  :focus-visible{outline:3px solid var(--signal-amber); outline-offset:2px;}

  .wrap{max-width:1120px; margin:0 auto; padding:0 28px;}

  /* ---------- HEADER ---------- */
  header{
    background:var(--ink);
    color:var(--paper);
    position:sticky; top:0; z-index:40;
    border-bottom:1px solid rgba(247,245,240,0.12);
  }
  .header-row{
    display:flex; align-items:center; justify-content:space-between;
    height:66px;
  }
  .logo{
    display:flex; align-items:center; gap:10px;
    font-family:var(--font-head); font-weight:700; letter-spacing:0.04em;
    font-size:19px; text-decoration:none;
  }
  .logo svg{width:30px; height:30px;}
  .logo .express{color:var(--signal-amber);}
  nav.main-nav{display:flex; align-items:center; gap:28px;}
  nav.main-nav a{
    font-family:var(--font-head); font-size:12.5px; letter-spacing:0.09em; text-transform:uppercase;
    text-decoration:none; color:rgba(248,246,241,0.75); transition:color .15s;
  }
  nav.main-nav a:hover{color:var(--paper);}
  .nav-track-btn{
    background:var(--signal-amber); color:var(--ink)!important;
    padding:9px 16px; border-radius:3px; font-weight:600;
  }
  @media (max-width:720px){ nav.main-nav .nav-link{display:none;} }

  /* ---------- HERO ---------- */
  .hero{
    position:relative;
    background:
      radial-gradient(ellipse 900px 500px at 15% -10%, rgba(242,167,59,0.10), transparent 60%),
      var(--route-green);
    color:var(--paper);
    overflow:hidden;
    padding:64px 0 96px;
  }
  .hero::before{
    /* faint asphalt grain */
    content:"";
    position:absolute; inset:0;
    background-image:radial-gradient(rgba(255,255,255,0.035) 1px, transparent 1px);
    background-size:5px 5px;
    pointer-events:none;
  }

  .route-strip{
    position:relative;
    height:64px;
    margin-bottom:44px;
    overflow:hidden;
  }
  .route-strip svg{width:100%; height:100%; overflow:visible;}
  .route-line{
    stroke:var(--line);
    stroke-width:2;
    stroke-dasharray:2 10;
    stroke-linecap:round;
  }
  .km-tick text{
    font-family:var(--font-mono); font-size:10px; fill:rgba(247,245,240,0.55); letter-spacing:0.03em;
  }
  .km-tick circle{ fill:var(--paper); opacity:0.85; }
  .truck-marker{
    animation:travel 9s linear infinite;
  }
  @keyframes travel{
    0%{ transform:translateX(0%); }
    100%{ transform:translateX(calc(100% - 0px)); }
  }
  @media (prefers-reduced-motion: reduce){
    .truck-marker{ animation:none; transform:translateX(38%); }
  }

  .km-badge{
    display:inline-flex; align-items:center; gap:8px;
    border:1px solid rgba(242,167,59,0.55);
    color:var(--signal-amber);
    font-family:var(--font-mono); font-size:12px; letter-spacing:0.12em; text-transform:uppercase;
    padding:6px 12px; border-radius:2px;
    margin-bottom:22px;
  }
  .km-badge::before{ content:"●"; font-size:8px; }

  h1.hero-title{
    font-family:var(--font-display);
    font-weight:400;
    font-size:clamp(48px, 8vw, 84px);
    line-height:0.96;
    letter-spacing:0.005em;
    max-width:11ch;
    margin-bottom:18px;
  }
  .hero-sub{
    font-family:var(--font-body);
    font-size:17px;
    color:rgba(248,246,241,0.8);
    max-width:46ch;
    margin-bottom:38px;
    line-height:1.5;
  }

  /* ticket-style search */
  .ticket{
    display:flex;
    background:var(--paper);
    border-radius:4px;
    max-width:560px;
    box-shadow:0 18px 40px rgba(0,0,0,0.28);
    position:relative;
  }
  .ticket::before, .ticket::after{
    content:"";
    position:absolute; top:50%; transform:translateY(-50%);
    width:16px; height:16px; border-radius:50%;
    background:var(--route-green);
  }
  .ticket::before{ left:calc(68% - 8px); }
  .ticket::after{ display:none; }
  .ticket-divider{
    width:0; border-left:1.5px dashed rgba(20,32,27,0.22);
    margin:10px 0;
  }
  .ticket input{
    flex:1;
    border:none; background:transparent; outline:none;
    font-family:var(--font-mono); font-size:16px; letter-spacing:0.05em;
    color:var(--ink);
    padding:18px 20px;
    min-width:0;
  }
  .ticket input::placeholder{ color:rgba(20,32,27,0.35); }
  .ticket button{
    flex-shrink:0;
    width:32%;
    border:none; cursor:pointer;
    background:var(--signal-amber);
    color:var(--ink);
    font-family:var(--font-head); font-weight:600; font-size:13px; letter-spacing:0.08em; text-transform:uppercase;
    border-radius:0 4px 4px 0;
    transition:background .15s;
  }
  .ticket button:hover{ background:var(--signal-amber-dark); }

  .try-row{
    display:flex; align-items:center; gap:12px; flex-wrap:wrap;
    margin-top:24px;
  }
  .try-label{
    font-family:var(--font-mono); font-size:11.5px; letter-spacing:0.08em; text-transform:uppercase;
    color:rgba(248,246,241,0.55);
  }
  .chip{
    font-family:var(--font-mono); font-size:12.5px; letter-spacing:0.03em;
    background:transparent; color:var(--paper);
    border:1px solid rgba(247,245,240,0.35);
    padding:6px 12px; border-radius:2px; cursor:pointer;
    transition:border-color .15s, background .15s;
  }
  .chip:hover{ border-color:var(--signal-amber); background:rgba(242,167,59,0.08); }

  /* demo result panel */
  .result-panel{
    max-width:560px;
    margin-top:22px;
    background:rgba(247,245,240,0.06);
    border:1px solid rgba(247,245,240,0.18);
    border-radius:4px;
    padding:18px 20px;
    font-family:var(--font-mono);
    font-size:13px;
    display:none;
  }
  .result-panel.show{ display:block; }
  .result-panel .rp-top{ display:flex; justify-content:space-between; color:rgba(248,246,241,0.6); margin-bottom:6px;}
  .result-panel .rp-status{ color:var(--signal-amber); font-size:15px; letter-spacing:0.04em;}

  /* ---------- HOW IT MOVES ---------- */
  .flow{ background:var(--paper); padding:88px 0; }
  .flow-head{ margin-bottom:52px; max-width:640px; }
  .eyebrow{
    font-family:var(--font-mono); font-size:12px; letter-spacing:0.14em; text-transform:uppercase;
    color:var(--route-green); margin-bottom:10px;
  }
  h2{
    font-family:var(--font-head); font-weight:600; font-size:clamp(28px,4vw,38px);
    color:var(--ink); letter-spacing:-0.01em;
  }
  .flow-list{
    display:grid; grid-template-columns:repeat(6,1fr); gap:2px;
    background:rgba(20,32,27,0.1);
    border-radius:4px; overflow:hidden;
  }
  @media (max-width:900px){ .flow-list{ grid-template-columns:1fr; } }
  .flow-step{
    background:var(--paper);
    padding:26px 18px;
    position:relative;
  }
  .flow-step .num{
    font-family:var(--font-mono); font-size:11px; color:var(--route-green);
    letter-spacing:0.08em; margin-bottom:14px; display:block;
  }
  .flow-step .code{
    font-family:var(--font-mono); font-size:10.5px; color:rgba(20,32,27,0.4);
    letter-spacing:0.03em; margin-bottom:6px; display:block;
  }
  .flow-step h3{
    font-family:var(--font-body); font-weight:600; font-size:14.5px; color:var(--ink);
    line-height:1.35;
  }
  .flow-step.final h3{ color:var(--route-green); }

  /* ---------- COVERAGE / STATS ---------- */
  .coverage{
    background:var(--ink); color:var(--paper);
    padding:80px 0;
  }
  .cov-grid{
    display:grid; grid-template-columns:1.1fr 1fr; gap:56px; align-items:center;
  }
  @media (max-width:820px){ .cov-grid{ grid-template-columns:1fr; gap:40px; } }
  .cov-grid h2{ color:var(--paper); margin-bottom:16px; }
  .cov-grid p{ color:rgba(248,246,241,0.7); max-width:48ch; line-height:1.6; font-size:15px;}
  .stat-row{
    display:grid; grid-template-columns:repeat(3,1fr); gap:1px;
    background:rgba(247,245,240,0.14);
    margin-top:34px; border-radius:4px; overflow:hidden;
  }
  .stat{ background:var(--ink); padding:20px 16px; }
  .stat .n{ font-family:var(--font-display); font-size:34px; color:var(--signal-amber); line-height:1; }
  .stat .l{ font-family:var(--font-mono); font-size:10.5px; letter-spacing:0.05em; color:rgba(248,246,241,0.55); margin-top:6px; text-transform:uppercase;}

  .role-cards{ display:flex; flex-direction:column; gap:14px; }
  .role-card{
    border:1px solid rgba(247,245,240,0.16);
    border-radius:4px; padding:18px 20px;
    display:flex; align-items:center; gap:14px;
  }
  .role-card .tag{
    font-family:var(--font-mono); font-size:10px; letter-spacing:0.08em;
    background:rgba(242,167,59,0.14); color:var(--signal-amber);
    padding:3px 8px; border-radius:2px; text-transform:uppercase; flex-shrink:0;
  }
  .role-card span.desc{ font-size:13.5px; color:rgba(248,246,241,0.75); }

  /* ---------- FOOTER ---------- */
  footer{
    background:var(--paper); border-top:1px solid rgba(20,32,27,0.1);
    padding:36px 0;
  }
  .foot-row{
    display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;
  }
  .foot-row .logo{ color:var(--ink); font-size:15px; }
  .foot-row .logo .express{ color:var(--route-green); }
  footer p{ font-size:12.5px; color:rgba(20,32,27,0.5); font-family:var(--font-mono); }
</style>
</head>
<body>

<header>
  <div class="wrap header-row">
    <a href="#" class="logo">
      <svg viewBox="0 0 24 24" fill="none"><path d="M2 7h11v9H2z" fill="#F2A73B"/><path d="M13 10h4l4 3.2V16h-8z" fill="#F8F6F1"/><circle cx="6.5" cy="17.5" r="2" fill="#14201B" stroke="#F8F6F1" stroke-width="1"/><circle cx="17" cy="17.5" r="2" fill="#14201B" stroke="#F8F6F1" stroke-width="1"/></svg>
      VEN<span class="express">EXPRESS</span>
    </a>
    <nav class="main-nav">
      <a href="#" class="nav-link">Tarifas</a>
      <a href="#flow" class="nav-link">Cómo viaja</a>
      <a href="#" class="nav-link">Aliados</a>
      <a href="#track" class="nav-track-btn">Rastrear envío</a>
    </nav>
  </div>
</header>

<section class="hero" id="track">
  <div class="wrap">

    <div class="route-strip" aria-hidden="true">
      <svg viewBox="0 0 1000 64" preserveAspectRatio="none">
        <line class="route-line" x1="0" y1="32" x2="1000" y2="32"/>
        <g class="km-tick"><circle cx="20" cy="32" r="3"/><text x="12" y="54">KM 0</text></g>
        <g class="km-tick"><circle cx="280" cy="32" r="3"/><text x="258" y="54">KM 180</text></g>
        <g class="km-tick"><circle cx="560" cy="32" r="3"/><text x="538" y="54">KM 410</text></g>
        <g class="km-tick"><circle cx="840" cy="32" r="3"/><text x="810" y="54">KM 690</text></g>
        <g class="truck-marker">
          <rect x="0" y="20" width="17" height="12" rx="1.5" fill="#F2A73B"/>
          <rect x="15" y="24" width="8" height="8" rx="1" fill="#F8F6F1"/>
          <circle cx="5" cy="34" r="2" fill="#14201B" stroke="#F8F6F1" stroke-width="0.8"/>
          <circle cx="18" cy="34" r="2" fill="#14201B" stroke="#F8F6F1" stroke-width="0.8"/>
        </g>
      </svg>
    </div>

    <div class="km-badge">Km 0 · Punto de control nacional</div>
    <h1 class="hero-title">¿Dónde está<br>tu paquete?</h1>
    <p class="hero-sub">Escribe tu número de guía y sigue su ruta real: desde la agencia donde lo entregaste hasta la puerta de quien lo recibe.</p>

    <form class="ticket" id="track-form">
      <input type="text" id="guide-input" placeholder="VX-00000" aria-label="Número de guía" autocomplete="off">
      <span class="ticket-divider"></span>
      <button type="submit">Buscar</button>
    </form>

    <div class="try-row">
      <span class="try-label">Probar con:</span>
      <button type="button" class="chip" data-code="VX-83920">VX-83920</button>
      <button type="button" class="chip" data-code="VX-11204">VX-11204</button>
      <button type="button" class="chip" data-code="VX-77310">VX-77310</button>
    </div>

    <div class="result-panel" id="result-panel">
      <div class="rp-top"><span>GUÍA <span id="rp-code">VX-83920</span></span><span id="rp-eta">Entrega estimada: hoy</span></div>
      <div class="rp-status" id="rp-status">EN TRÁNSITO NACIONAL — Hub Valencia → Hub Maracaibo</div>
    </div>

  </div>
</section>

<section class="flow" id="flow">
  <div class="wrap">
    <div class="flow-head">
      <div class="eyebrow">Trazabilidad de punta a punta</div>
      <h2>Así viaja tu paquete, sin zonas ciegas</h2>
    </div>
    <div class="flow-list">
      <div class="flow-step">
        <span class="num">01</span>
        <span class="code">RECIBIDO_AGENCIA</span>
        <h3>Lo entregas en una agencia aliada</h3>
      </div>
      <div class="flow-step">
        <span class="num">02</span>
        <span class="code">RECOLECTADO_VENEXPRESS</span>
        <h3>Un chofer Venexpress lo recolecta</h3>
      </div>
      <div class="flow-step">
        <span class="num">03</span>
        <span class="code">EN_HUB</span>
        <h3>Pasa por el hub de clasificación</h3>
      </div>
      <div class="flow-step">
        <span class="num">04</span>
        <span class="code">EN_TRANSITO_NACIONAL</span>
        <h3>Viaja por ruta nacional a su destino</h3>
      </div>
      <div class="flow-step">
        <span class="num">05</span>
        <span class="code">LISTO_RETIRO</span>
        <h3>Queda listo para retiro en destino</h3>
      </div>
      <div class="flow-step final">
        <span class="num">06</span>
        <span class="code">ENTREGADO</span>
        <h3>Entregado a quien lo recibe</h3>
      </div>
    </div>
  </div>
</section>

<section class="coverage">
  <div class="wrap cov-grid">
    <div>
      <div class="eyebrow" style="color:var(--signal-amber)">Red nacional</div>
      <h2>Una guía, tres personas, una sola ruta</h2>
      <p>Cada envío conecta a un aliado que lo recibe, un chofer que lo mueve y un administrador que audita la tarifa y la tasa. Todo queda registrado guía por guía.</p>
      <div class="stat-row">
        <div class="stat"><div class="n">24</div><div class="l">Estados / hr</div></div>
        <div class="stat"><div class="n">6</div><div class="l">Etapas de ruta</div></div>
        <div class="stat"><div class="n">100%</div><div class="l">Rastreo público</div></div>
      </div>
    </div>
    <div class="role-cards">
      <div class="role-card"><span class="tag">Aliado</span><span class="desc">Recibe el paquete en taquilla y genera la guía con tarifa calculada por peso volumétrico.</span></div>
      <div class="role-card"><span class="tag">Chofer</span><span class="desc">Escanea el QR en cada parada, incluso sin conexión, y sincroniza al recuperar señal.</span></div>
      <div class="role-card"><span class="tag">Admin</span><span class="desc">Supervisa la tasa BCV, las matrices de tarifa y el estado de la red completa.</span></div>
    </div>
  </div>
</section>

<footer>
  <div class="wrap foot-row">
    <a href="#" class="logo">VEN<span class="express">EXPRESS</span></a>
    <p>Servicio nacional de encomiendas · Venezuela</p>
  </div>
</footer>

<script>
  const form = document.getElementById('track-form');
  const input = document.getElementById('guide-input');
  const panel = document.getElementById('result-panel');
  const rpCode = document.getElementById('rp-code');
  const rpStatus = document.getElementById('rp-status');
  const rpEta = document.getElementById('rp-eta');

  const demoData = {
    'VX-83920': { status: 'EN TRÁNSITO NACIONAL — Hub Valencia → Hub Maracaibo', eta: 'Entrega estimada: hoy' },
    'VX-11204': { status: 'LISTO PARA RETIRO — Agencia Barquisimeto Centro', eta: 'Retiro habilitado' },
    'VX-77310': { status: 'ENTREGADO — Recibido por destinatario', eta: 'Entregado ayer, 4:12 p.m.' }
  };

  function showResult(code){
    const data = demoData[code] || { status: 'RECIBIDO EN AGENCIA — Esperando recolección', eta: 'Aún sin ruta asignada' };
    rpCode.textContent = code;
    rpStatus.textContent = data.status;
    rpEta.textContent = data.eta;
    panel.classList.add('show');
  }

  document.querySelectorAll('.chip').forEach(chip=>{
    chip.addEventListener('click', ()=>{
      const code = chip.dataset.code;
      input.value = code;
      showResult(code);
    });
  });

  form.addEventListener('submit', (e)=>{
    e.preventDefault();
    const code = input.value.trim().toUpperCase() || 'VX-00000';
    showResult(code);
  });
</script>

</body>
</html>
