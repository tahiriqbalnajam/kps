<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>IDLSchool — Smart School Management Software</title>
  <meta name="description" content="All-in-one school management platform for student records, attendance, fees, exams, timetables and parent communication.">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- CDN: Bootstrap 5, Font Awesome, Swiper -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">

  <style>
    /* ══════════════════════════════════════════════════
       RESET & ROOT
    ══════════════════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --blue:       #1e40af;
      --blue-mid:   #2563eb;
      --blue-lt:    #3b82f6;
      --cyan:       #06b6d4;
      --indigo:     #4f46e5;
      --gold:       #f59e0b;
      --dark:       #0f172a;
      --dark2:      #1e293b;
      --slate:      #334155;
      --muted:      #64748b;
      --surface:    #f8fafc;
      --white:      #ffffff;
      --r-sm:       8px;
      --r:          14px;
      --r-lg:       24px;
      --sh:         0 4px 24px rgba(0,0,0,.08);
      --sh-lg:      0 12px 48px rgba(0,0,0,.14);
    }
    html { scroll-behavior: smooth; font-size: 16px; }
    body {
      font-family: 'Poppins', sans-serif;
      color: var(--dark);
      background: var(--white);
      overflow-x: hidden;
      line-height: 1.6;
    }
    a { text-decoration: none; color: inherit; }
    img { max-width: 100%; display: block; }
    ul { list-style: none; padding: 0; margin: 0; }

    /* ══════════════════════════════════════════════════
       HEADER
    ══════════════════════════════════════════════════ */
    #idl-header {
      position: fixed; top: 0; left: 0; right: 0; z-index: 1000;
      transition: background .35s, box-shadow .35s, padding .35s;
      padding: 0;
    }
    #idl-header .header-top-bar {
      background: rgba(15,23,42,.9);
      border-bottom: 1px solid rgba(255,255,255,.07);
      padding: 8px 0;
      font-size: 12.5px;
      color: rgba(255,255,255,.7);
    }
    #idl-header .header-top-bar a { color: rgba(255,255,255,.7); transition: color .2s; }
    #idl-header .header-top-bar a:hover { color: var(--cyan); }
    #idl-header .header-top-bar .social-links a {
      display: inline-flex; align-items: center; justify-content: center;
      width: 28px; height: 28px; border-radius: 50%;
      background: rgba(255,255,255,.08); transition: background .2s, color .2s;
    }
    #idl-header .header-top-bar .social-links a:hover { background: var(--cyan); color: #fff; }
    #idl-header .navbar-main {
      background: transparent;
      padding: 14px 0;
      transition: background .35s, padding .35s;
    }
    #idl-header.scrolled .header-top-bar { display: none; }
    #idl-header.scrolled .navbar-main {
      background: rgba(15,23,42,.97);
      backdrop-filter: blur(20px);
      -webkit-backdrop-filter: blur(20px);
      box-shadow: 0 4px 32px rgba(0,0,0,.35);
      padding: 10px 0;
    }
    .nav-logo img { height: 48px; filter: brightness(0) invert(1); }
    .nav-links { display: flex; align-items: center; gap: 6px; }
    .nav-links a {
      color: rgba(255,255,255,.85); font-size: 13.5px; font-weight: 500;
      padding: 7px 14px; border-radius: 8px; transition: all .2s;
    }
    .nav-links a:hover { color: #fff; background: rgba(255,255,255,.1); }
    .nav-links a.nav-cta {
      background: linear-gradient(135deg, var(--blue-lt), var(--cyan));
      color: #fff; padding: 9px 22px; font-weight: 700;
      box-shadow: 0 4px 16px rgba(59,130,246,.4);
      border-radius: 50px;
    }
    .nav-links a.nav-cta:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(59,130,246,.5); background: linear-gradient(135deg,var(--cyan),var(--blue-lt)); }
    .hamburger {
      display: none; background: rgba(255,255,255,.1); border: 1.5px solid rgba(255,255,255,.25);
      color: #fff; padding: 8px 12px; border-radius: 8px; cursor: pointer;
      font-size: 18px; transition: background .2s;
    }
    .hamburger:hover { background: rgba(255,255,255,.2); }
    .mobile-nav {
      display: none; position: fixed; inset: 0; z-index: 9999;
      background: rgba(15,23,42,.98); backdrop-filter: blur(20px);
      flex-direction: column; align-items: center; justify-content: center;
      gap: 12px; opacity: 0; transition: opacity .3s;
    }
    .mobile-nav.open { display: flex; opacity: 1; }
    .mobile-nav a { color: rgba(255,255,255,.85); font-size: 18px; font-weight: 600; padding: 12px 28px; border-radius: 10px; transition: background .2s, color .2s; }
    .mobile-nav a:hover { background: rgba(255,255,255,.1); color: #fff; }
    .mobile-nav-close {
      position: absolute; top: 20px; right: 24px;
      background: rgba(255,255,255,.1); border: none; color: #fff;
      font-size: 22px; width: 44px; height: 44px; border-radius: 50%;
      cursor: pointer; display: flex; align-items: center; justify-content: center;
      transition: background .2s;
    }
    .mobile-nav-close:hover { background: rgba(255,255,255,.2); }
    @media (max-width: 991px) {
      .nav-links { display: none; }
      .hamburger { display: inline-flex; align-items: center; }
    }

    /* ══════════════════════════════════════════════════
       HERO
    ══════════════════════════════════════════════════ */
    #home {
      position: relative; overflow: hidden;
      background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #0369a1 100%);
      min-height: 100vh;
      display: flex; align-items: center;
      padding: 180px 0 80px;
    }
    .hero-blob {
      position: absolute; border-radius: 50%; filter: blur(80px); pointer-events: none;
      animation: blobPulse 8s ease-in-out infinite;
    }
    .hero-blob-1 { width: 550px; height: 550px; background: #4f46e5; opacity: .2; top: -160px; right: -120px; animation-delay: 0s; }
    .hero-blob-2 { width: 380px; height: 380px; background: #06b6d4; opacity: .18; bottom: -80px; left: -80px; animation-delay: 3.5s; }
    .hero-blob-3 { width: 280px; height: 280px; background: #3b82f6; opacity: .15; top: 45%; left: 42%; animation-delay: 6s; }
    @keyframes blobPulse {
      0%,100% { transform: translate(0,0) scale(1); }
      40%      { transform: translate(24px,-24px) scale(1.06); }
      70%      { transform: translate(-16px,18px) scale(.94); }
    }
    /* Animated dots grid */
    .hero-dots {
      position: absolute; inset: 0; pointer-events: none;
      background-image: radial-gradient(rgba(255,255,255,.08) 1px, transparent 1px);
      background-size: 40px 40px;
    }
    .hero-content { position: relative; z-index: 2; }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.2);
      color: rgba(255,255,255,.9); border-radius: 50px;
      padding: 6px 18px; font-size: 11.5px; font-weight: 600; letter-spacing: .6px;
      text-transform: uppercase; margin-bottom: 22px;
      backdrop-filter: blur(6px);
    }
    .hero-badge i { color: var(--gold); }
    .hero-title {
      font-size: clamp(32px, 5vw, 62px); font-weight: 900;
      color: #fff; line-height: 1.1; margin-bottom: 20px;
    }
    .hero-title .grad {
      background: linear-gradient(90deg, var(--cyan) 0%, #a78bfa 100%);
      -webkit-background-clip: text; -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    .hero-subtitle {
      font-size: 16px; color: rgba(255,255,255,.72); line-height: 1.85;
      max-width: 500px; margin-bottom: 36px;
    }
    .hero-btns { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 52px; }
    .btn-glow {
      display: inline-flex; align-items: center; gap: 9px;
      background: linear-gradient(135deg, var(--blue-lt), var(--cyan));
      color: #fff; border-radius: 50px; padding: 14px 32px;
      font-weight: 700; font-size: 14.5px;
      box-shadow: 0 6px 24px rgba(59,130,246,.5);
      transition: transform .25s, box-shadow .25s;
    }
    .btn-glow:hover { transform: translateY(-3px); box-shadow: 0 12px 36px rgba(59,130,246,.65); color: #fff; }
    .btn-outline-w {
      display: inline-flex; align-items: center; gap: 9px;
      border: 2px solid rgba(255,255,255,.35); color: rgba(255,255,255,.88);
      border-radius: 50px; padding: 13px 30px;
      font-weight: 600; font-size: 14.5px;
      transition: all .25s;
    }
    .btn-outline-w:hover { background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.75); color: #fff; }
    .hero-metrics { display: flex; gap: 36px; flex-wrap: wrap; }
    .metric { padding-right: 36px; border-right: 1px solid rgba(255,255,255,.14); }
    .metric:last-child { border: none; padding: 0; }
    .metric .val { font-size: 32px; font-weight: 900; color: #fff; line-height: 1; }
    .metric .lbl { font-size: 11.5px; color: rgba(255,255,255,.55); font-weight: 500; margin-top: 4px; letter-spacing: .3px; }
    .scroll-hint {
      position: absolute; bottom: 28px; left: 50%; transform: translateX(-50%);
      z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 6px;
    }
    .scroll-hint span { font-size: 10.5px; color: rgba(255,255,255,.4); letter-spacing: 2.5px; text-transform: uppercase; }
    .scroll-mouse {
      width: 24px; height: 38px; border: 2px solid rgba(255,255,255,.3); border-radius: 14px;
      display: flex; justify-content: center; padding-top: 7px;
    }
    .scroll-wheel {
      width: 4px; height: 8px; background: rgba(255,255,255,.5); border-radius: 3px;
      animation: wheelScroll 1.6s ease-in-out infinite;
    }
    @keyframes wheelScroll { 0%,100%{transform:translateY(0);opacity:.5} 50%{transform:translateY(8px);opacity:1} }

    /* Dashboard mockup */
    .hero-visual { position: relative; z-index: 2; }
    .dash-mockup {
      background: rgba(255,255,255,.06);
      border: 1px solid rgba(255,255,255,.12);
      border-radius: 20px;
      backdrop-filter: blur(16px);
      padding: 20px;
      box-shadow: 0 24px 80px rgba(0,0,0,.4);
      animation: floatUp 5s ease-in-out infinite;
      max-width: 480px; margin-left: auto;
    }
    @keyframes floatUp { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-14px)} }
    .dash-topbar {
      display: flex; align-items: center; gap: 6px; margin-bottom: 16px;
    }
    .dash-dot { width: 10px; height: 10px; border-radius: 50%; }
    .dash-dot.r { background: #ef4444; }
    .dash-dot.y { background: #f59e0b; }
    .dash-dot.g { background: #22c55e; }
    .dash-title-bar {
      flex: 1; height: 10px; background: rgba(255,255,255,.12); border-radius: 6px; margin-left: 8px;
    }
    .dash-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 10px; margin-bottom: 14px; }
    .dash-card {
      background: rgba(255,255,255,.08); border-radius: 12px; padding: 14px 12px;
      border: 1px solid rgba(255,255,255,.1);
    }
    .dash-card .dc-label { font-size: 9.5px; color: rgba(255,255,255,.5); font-weight: 600; letter-spacing: .5px; text-transform: uppercase; margin-bottom: 6px; }
    .dash-card .dc-num {
      font-size: 22px; font-weight: 800; color: #fff;
      animation: countPulse 2s ease-in-out infinite;
    }
    .dash-card .dc-num.c1 { color: #60a5fa; }
    .dash-card .dc-num.c2 { color: #34d399; }
    .dash-card .dc-num.c3 { color: #f59e0b; }
    @keyframes countPulse { 0%,100%{opacity:1} 50%{opacity:.7} }
    .dash-chart-row { display: flex; gap: 10px; }
    .dash-chart-bar {
      flex: 1; background: rgba(255,255,255,.05); border-radius: 10px; padding: 12px;
      border: 1px solid rgba(255,255,255,.08); overflow: hidden;
    }
    .dash-chart-label { font-size: 9px; color: rgba(255,255,255,.45); font-weight: 600; letter-spacing: .5px; text-transform: uppercase; margin-bottom: 10px; }
    .bar-container { display: flex; align-items: flex-end; gap: 5px; height: 60px; }
    .bar-item {
      flex: 1; border-radius: 4px 4px 0 0;
      animation: barGrow 1.5s ease-out forwards;
      transform-origin: bottom;
    }
    .bar-item:nth-child(1) { background: #60a5fa; height: 60%; animation-delay: .1s; }
    .bar-item:nth-child(2) { background: #34d399; height: 80%; animation-delay: .2s; }
    .bar-item:nth-child(3) { background: #818cf8; height: 55%; animation-delay: .3s; }
    .bar-item:nth-child(4) { background: #60a5fa; height: 70%; animation-delay: .4s; }
    .bar-item:nth-child(5) { background: #34d399; height: 90%; animation-delay: .5s; }
    .bar-item:nth-child(6) { background: #818cf8; height: 65%; animation-delay: .6s; }
    @keyframes barGrow { from{transform:scaleY(0)} to{transform:scaleY(1)} }
    .dash-mini-list { flex: 1; background: rgba(255,255,255,.05); border-radius: 10px; padding: 12px; border: 1px solid rgba(255,255,255,.08); }
    .dash-mini-label { font-size: 9px; color: rgba(255,255,255,.45); font-weight: 600; letter-spacing: .5px; text-transform: uppercase; margin-bottom: 8px; }
    .dash-mini-row {
      display: flex; align-items: center; gap: 8px; padding: 5px 0;
      border-bottom: 1px solid rgba(255,255,255,.06);
    }
    .dash-mini-row:last-child { border: none; }
    .dash-avatar {
      width: 22px; height: 22px; border-radius: 50%; flex-shrink: 0;
    }
    .dash-mini-text { font-size: 9.5px; color: rgba(255,255,255,.6); flex: 1; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .dash-mini-badge {
      font-size: 8px; font-weight: 700; padding: 2px 7px; border-radius: 20px;
    }
    .badge-present { background: rgba(52,211,153,.2); color: #34d399; }
    .badge-absent  { background: rgba(248,113,113,.2); color: #f87171; }

    /* Floating mini-card chips */
    .float-chip {
      position: absolute; background: rgba(255,255,255,.1);
      backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,.18);
      border-radius: 14px; padding: 10px 16px; display: flex; align-items: center; gap: 10px;
      box-shadow: 0 8px 32px rgba(0,0,0,.25);
      z-index: 3;
      animation: chipFloat 4s ease-in-out infinite;
    }
    .float-chip.chip-1 { top: 20%; left: -30px; animation-delay: 0s; }
    .float-chip.chip-2 { bottom: 22%; right: -20px; animation-delay: 2s; }
    @keyframes chipFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
    .float-chip .chip-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .float-chip .chip-icon.green { background: rgba(52,211,153,.2); color: #34d399; }
    .float-chip .chip-icon.blue  { background: rgba(96,165,250,.2); color: #60a5fa; }
    .float-chip .chip-text { font-size: 11px; color: rgba(255,255,255,.8); }
    .float-chip .chip-text strong { display: block; font-size: 14px; font-weight: 800; color: #fff; }

    @media (max-width: 991px) {
      #home { padding: 140px 0 60px; }
      .hero-visual { margin-top: 48px; }
      .dash-mockup { max-width: 100%; margin: 0; }
      .float-chip { display: none; }
    }

    /* ══════════════════════════════════════════════════
       TRUSTED BY MARQUEE
    ══════════════════════════════════════════════════ */
    .trusted-section {
      padding: 30px 0;
      background: linear-gradient(90deg, #0f172a 0%, #1e293b 100%);
      overflow: hidden;
    }
    .trusted-label {
      text-align: center; font-size: 10.5px; font-weight: 700;
      letter-spacing: 3px; text-transform: uppercase;
      color: rgba(255,255,255,.3); margin-bottom: 22px;
    }
    .marquee-outer { overflow: hidden; position: relative; }
    .marquee-outer::before, .marquee-outer::after {
      content: ''; position: absolute; top: 0; bottom: 0; width: 80px; z-index: 2;
    }
    .marquee-outer::before { left: 0; background: linear-gradient(90deg,#1e293b,transparent); }
    .marquee-outer::after  { right: 0; background: linear-gradient(270deg,#1e293b,transparent); }
    .marquee-inner {
      display: flex; align-items: center; gap: 56px;
      width: max-content;
      animation: runMarquee 28s linear infinite;
    }
    .marquee-inner:hover { animation-play-state: paused; }
    .marquee-inner .school-name {
      display: flex; align-items: center; gap: 10px;
      opacity: .45; transition: opacity .3s; white-space: nowrap; flex-shrink: 0;
    }
    .marquee-inner .school-name:hover { opacity: 1; }
    .marquee-inner .school-name img { height: 44px; width: auto; filter: brightness(0) invert(1); object-fit: contain; }
    .marquee-inner .school-name span { font-size: 13px; font-weight: 600; color: #fff; }
    @keyframes runMarquee { 0%{transform:translateX(0)} 100%{transform:translateX(-50%)} }

    /* ══════════════════════════════════════════════════
       STATS BAR
    ══════════════════════════════════════════════════ */
    .stats-section {
      padding: 72px 0;
      background: var(--surface);
    }
    .stat-box {
      text-align: center; padding: 36px 24px;
      background: #fff; border-radius: var(--r);
      border: 1px solid rgba(0,0,0,.06);
      box-shadow: var(--sh);
      transition: transform .3s, box-shadow .3s;
      position: relative; overflow: hidden;
    }
    .stat-box::after {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
      background: linear-gradient(90deg, var(--blue-lt), var(--cyan));
    }
    .stat-box:hover { transform: translateY(-6px); box-shadow: var(--sh-lg); }
    .stat-icon {
      width: 58px; height: 58px; border-radius: 16px; margin: 0 auto 16px;
      display: flex; align-items: center; justify-content: center;
      background: linear-gradient(135deg, #eff6ff, #e0f2fe);
    }
    .stat-icon i { font-size: 24px; color: var(--blue-mid); }
    .stat-num {
      font-size: 44px; font-weight: 900; color: var(--dark); line-height: 1;
      letter-spacing: -1px;
    }
    .stat-num sup { font-size: 22px; font-weight: 700; vertical-align: top; margin-top: 9px; display: inline-block; }
    .stat-label { font-size: 12.5px; color: var(--muted); font-weight: 500; margin-top: 6px; }

    /* ══════════════════════════════════════════════════
       SECTION LABELS
    ══════════════════════════════════════════════════ */
    .eyebrow {
      display: inline-flex; align-items: center; gap: 8px;
      background: linear-gradient(135deg,#eff6ff,#e0f2fe);
      color: var(--blue-mid); font-size: 11px; font-weight: 700;
      letter-spacing: 2px; text-transform: uppercase;
      padding: 6px 18px; border-radius: 50px; margin-bottom: 14px;
    }
    .section-title {
      font-size: clamp(26px,3.5vw,42px); font-weight: 900;
      line-height: 1.2; color: var(--dark); margin-bottom: 14px;
    }
    .section-title .hi { color: var(--blue-mid); }
    .section-lead { font-size: 15.5px; color: var(--muted); line-height: 1.8; max-width: 560px; }
    .section-lead.center { text-align: center; margin: 0 auto; }

    /* ══════════════════════════════════════════════════
       FEATURES GRID
    ══════════════════════════════════════════════════ */
    .features-section { padding: 100px 0; background: #fff; }
    .feat-card {
      padding: 32px 26px; border-radius: var(--r);
      border: 1.5px solid transparent;
      background: linear-gradient(#fff,#fff) padding-box,
                  linear-gradient(135deg,#e0f2fe,#ede9fe) border-box;
      transition: transform .3s, box-shadow .3s;
      height: 100%;
    }
    .feat-card:hover { transform: translateY(-8px); box-shadow: 0 16px 48px rgba(37,99,235,.12); }
    .feat-icon {
      width: 60px; height: 60px; border-radius: 16px; margin-bottom: 20px;
      display: flex; align-items: center; justify-content: center;
      font-size: 26px;
    }
    .feat-card h5 { font-size: 15.5px; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
    .feat-card p  { font-size: 13.5px; color: var(--muted); line-height: 1.75; margin: 0; }

    /* ══════════════════════════════════════════════════
       MOBILE APP SECTION
    ══════════════════════════════════════════════════ */
    .mobile-section {
      padding: 100px 0;
      background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 60%, #1e293b 100%);
      position: relative; overflow: hidden;
    }
    .mobile-section .hero-dots { opacity: .5; }
    .mobile-section h2 { font-size: clamp(24px,3vw,40px); font-weight: 900; color: #fff; margin-bottom: 14px; }
    .mobile-section h2 .hi { color: var(--cyan); }
    .mobile-section p { color: rgba(255,255,255,.72); font-size: 15px; line-height: 1.8; margin-bottom: 24px; }
    .feature-tick { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 12px; }
    .feature-tick i { color: var(--cyan); font-size: 16px; margin-top: 3px; flex-shrink: 0; }
    .feature-tick span { font-size: 14.5px; color: rgba(255,255,255,.8); }
    .store-btns { display: flex; gap: 14px; flex-wrap: wrap; margin-top: 28px; }
    .store-btn {
      display: inline-flex; align-items: center; gap: 12px;
      background: rgba(255,255,255,.1); border: 1.5px solid rgba(255,255,255,.25);
      backdrop-filter: blur(6px); border-radius: 14px; padding: 12px 22px;
      color: #fff; transition: all .25s;
    }
    .store-btn:hover { background: rgba(255,255,255,.2); border-color: rgba(255,255,255,.6); transform: translateY(-3px); color: #fff; }
    .store-btn i { font-size: 26px; }
    .store-btn .sb-text small { display: block; font-size: 9px; opacity: .7; letter-spacing: .5px; text-transform: uppercase; }
    .store-btn .sb-text strong { font-size: 14px; font-weight: 700; }
    /* Phone mockup */
    .phone-mockup-wrap { display: flex; justify-content: center; align-items: center; position: relative; }
    .phone-frame {
      width: 240px; background: #0f172a; border-radius: 38px;
      border: 8px solid rgba(255,255,255,.15);
      box-shadow: 0 40px 100px rgba(0,0,0,.6), inset 0 0 0 1px rgba(255,255,255,.08);
      padding: 20px 16px; position: relative;
      animation: floatUp 5s ease-in-out infinite;
    }
    .phone-notch {
      width: 80px; height: 22px; background: #0f172a; border-radius: 0 0 18px 18px;
      margin: -20px auto 16px; position: relative; z-index: 2;
    }
    .phone-screen { background: #1e293b; border-radius: 22px; overflow: hidden; }
    .phone-app-bar { background: var(--blue-mid); padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; }
    .phone-app-bar .pa-title { font-size: 11px; font-weight: 700; color: #fff; }
    .phone-app-bar .pa-icon { font-size: 13px; color: rgba(255,255,255,.7); }
    .phone-body { padding: 12px; }
    .phone-row { display: flex; gap: 8px; margin-bottom: 8px; }
    .ph-card {
      flex: 1; background: rgba(255,255,255,.06); border-radius: 10px; padding: 10px 8px;
      border: 1px solid rgba(255,255,255,.08); text-align: center;
    }
    .ph-card .phc-num { font-size: 16px; font-weight: 800; }
    .ph-card .phc-lbl { font-size: 8px; color: rgba(255,255,255,.4); text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }
    .ph-card.c1 .phc-num { color: #60a5fa; }
    .ph-card.c2 .phc-num { color: #34d399; }
    .phone-list { margin-top: 4px; }
    .pl-item {
      display: flex; align-items: center; gap: 8px; padding: 7px 4px;
      border-bottom: 1px solid rgba(255,255,255,.05);
    }
    .pl-item:last-child { border: none; }
    .pl-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
    .pl-text { font-size: 9px; color: rgba(255,255,255,.5); flex: 1; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
    .pl-tag { font-size: 7.5px; font-weight: 700; padding: 2px 6px; border-radius: 20px; }
    .pl-tag.p { background: rgba(52,211,153,.2); color: #34d399; }
    .pl-tag.a { background: rgba(248,113,113,.2); color: #f87171; }
    /* floating notification chip on phone */
    .notif-chip {
      position: absolute; top: 60px; right: -60px;
      background: rgba(255,255,255,.1); backdrop-filter: blur(12px);
      border: 1px solid rgba(255,255,255,.2); border-radius: 12px; padding: 10px 14px;
      display: flex; align-items: center; gap: 8px;
      box-shadow: 0 8px 24px rgba(0,0,0,.3);
      animation: chipFloat 4s ease-in-out infinite;
      white-space: nowrap;
    }
    .notif-chip .nc-dot { width: 8px; height: 8px; border-radius: 50%; background: #34d399; flex-shrink: 0; }
    .notif-chip .nc-text { font-size: 10px; color: rgba(255,255,255,.8); }
    .notif-chip .nc-text strong { display: block; font-size: 11px; color: #fff; font-weight: 700; }
    @media (max-width: 768px) {
      .phone-mockup-wrap { margin-top: 48px; }
      .notif-chip { display: none; }
    }

    /* ══════════════════════════════════════════════════
       TESTIMONIALS
    ══════════════════════════════════════════════════ */
    .testi-section {
      padding: 100px 0;
      background: linear-gradient(180deg, #f8fafc 0%, #eff6ff 100%);
    }
    .testi-section .swiper { padding-bottom: 52px !important; }
    .testi-card {
      background: #fff; border-radius: var(--r-lg); padding: 36px 32px;
      box-shadow: 0 8px 40px rgba(37,99,235,.08);
      border: 1px solid rgba(37,99,235,.07);
      height: 100%;
    }
    .testi-stars { color: var(--gold); margin-bottom: 16px; font-size: 15px; letter-spacing: 2px; }
    .testi-quote { font-size: 15px; color: var(--slate); line-height: 1.8; margin-bottom: 24px; font-style: italic; }
    .testi-quote::before { content: '\201C'; color: var(--blue-lt); font-size: 28px; line-height: 0; vertical-align: -10px; margin-right: 4px; }
    .testi-author { display: flex; align-items: center; gap: 14px; }
    .testi-avatar {
      width: 46px; height: 46px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; font-weight: 800; color: #fff; flex-shrink: 0;
    }
    .testi-author-name { font-size: 14px; font-weight: 700; color: var(--dark); }
    .testi-author-role { font-size: 12px; color: var(--muted); }
    .testi-nav { display: flex; justify-content: center; gap: 14px; margin-top: 32px; }
    .testi-nav button {
      width: 46px; height: 46px; border-radius: 50%; border: 2px solid rgba(37,99,235,.2);
      background: #fff; color: var(--blue-mid); font-size: 15px;
      cursor: pointer; transition: all .25s;
      display: flex; align-items: center; justify-content: center;
    }
    .testi-nav button:hover { background: var(--blue-mid); color: #fff; border-color: var(--blue-mid); }
    .swiper-pagination-bullet { background: var(--blue-lt) !important; }
    .swiper-pagination-bullet-active { background: var(--blue-mid) !important; }

    /* ══════════════════════════════════════════════════
       CONTACT
    ══════════════════════════════════════════════════ */
    .contact-section {
      padding: 100px 0;
      background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
      position: relative; overflow: hidden;
    }
    .contact-section .hero-dots { opacity: .3; }
    .contact-card {
      background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12);
      backdrop-filter: blur(16px); border-radius: var(--r-lg); padding: 48px 40px;
    }
    .contact-card h2 { font-size: clamp(24px,3vw,38px); font-weight: 900; color: #fff; margin-bottom: 10px; }
    .contact-card .sub { font-size: 15px; color: rgba(255,255,255,.65); margin-bottom: 36px; }
    .form-floating label { font-size: 13.5px; color: rgba(255,255,255,.5); }
    .form-floating .form-control, .form-floating textarea {
      background: rgba(255,255,255,.08) !important;
      border: 1.5px solid rgba(255,255,255,.15) !important;
      color: #fff !important; border-radius: var(--r) !important;
      font-size: 14px;
    }
    .form-floating .form-control:focus, .form-floating textarea:focus {
      box-shadow: 0 0 0 3px rgba(59,130,246,.25) !important;
      border-color: var(--blue-lt) !important;
      outline: none;
    }
    .form-floating .form-control::placeholder, .form-floating textarea::placeholder { color: rgba(255,255,255,.3); }
    .form-floating .form-control:-webkit-autofill {
      -webkit-box-shadow: 0 0 0 100px rgba(255,255,255,.08) inset !important;
      -webkit-text-fill-color: #fff !important;
    }
    .contact-info-item { display: flex; align-items: flex-start; gap: 16px; margin-bottom: 28px; }
    .ci-icon {
      width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0;
      background: rgba(59,130,246,.15); border: 1px solid rgba(59,130,246,.2);
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; color: var(--blue-lt);
    }
    .ci-text strong { display: block; font-size: 13px; color: rgba(255,255,255,.5); font-weight: 500; margin-bottom: 3px; }
    .ci-text span { font-size: 15px; color: #fff; font-weight: 600; }
    .ci-text a { color: #fff; transition: color .2s; }
    .ci-text a:hover { color: var(--cyan); }

    /* ══════════════════════════════════════════════════
       FOOTER
    ══════════════════════════════════════════════════ */
    footer {
      background: #060e1d;
      padding: 56px 0 28px;
      color: rgba(255,255,255,.5);
      font-size: 13.5px;
    }
    footer .foot-logo img { height: 50px; filter: brightness(0) invert(1); margin-bottom: 14px; }
    footer .foot-desc { font-size: 13px; line-height: 1.8; max-width: 280px; margin-bottom: 20px; }
    footer .foot-social a {
      display: inline-flex; align-items: center; justify-content: center;
      width: 34px; height: 34px; border-radius: 50%;
      background: rgba(255,255,255,.07); color: rgba(255,255,255,.6);
      transition: all .2s; margin-right: 8px; font-size: 14px;
    }
    footer .foot-social a:hover { background: var(--cyan); color: #fff; }
    footer h6 { font-size: 13px; font-weight: 700; color: rgba(255,255,255,.8); letter-spacing: .5px; margin-bottom: 16px; text-transform: uppercase; }
    footer ul li { margin-bottom: 10px; }
    footer ul li a { color: rgba(255,255,255,.45); transition: color .2s; font-size: 13px; }
    footer ul li a:hover { color: var(--cyan); }
    .foot-bottom {
      border-top: 1px solid rgba(255,255,255,.07); margin-top: 40px; padding-top: 22px;
      display: flex; justify-content: space-between; align-items: center; flex-wrap: gap;
      gap: 12px;
    }

    /* ══════════════════════════════════════════════════
       SCROLL REVEAL
    ══════════════════════════════════════════════════ */
    .reveal { opacity: 0; transform: translateY(36px); transition: opacity .7s ease, transform .7s ease; }
    .reveal.up { transform: translateY(36px); }
    .reveal.left  { transform: translateX(-36px); }
    .reveal.right { transform: translateX(36px); }
    .reveal.visible { opacity: 1; transform: none !important; }

    /* ══════════════════════════════════════════════════
       UTILITY
    ══════════════════════════════════════════════════ */
    .text-cyan { color: var(--cyan) !important; }
    .bg-grad { background: linear-gradient(135deg, var(--blue-lt), var(--cyan)); }
    section { position: relative; }
  </style>
</head>

<body>

<!-- ══ HEADER ══ -->
<header id="idl-header">
  <div class="header-top-bar">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="d-flex gap-4 flex-wrap">
          <span><i class="fas fa-map-marker-alt me-1" style="color:#06b6d4"></i> IDLBridge St#7, Fort Abbas, Pakistan</span>
          <a href="tel:+923457050405"><i class="fas fa-phone me-1" style="color:#06b6d4"></i>+92-345-7050405</a>
          <a href="mailto:info@idlschool.pk"><i class="fas fa-envelope me-1" style="color:#06b6d4"></i>info@idlschool.pk</a>
        </div>
        <div class="social-links d-flex gap-2 align-items-center">
          <span style="font-size:11px;opacity:.5;margin-right:4px;">Follow Us:</span>
          <a href="https://www.facebook.com/idlschool" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="http://x.com/tahiriqbalnajam" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
          <a href="https://www.linkedin.com/company/idlbridge" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
          <a href="https://github.com/tahiriqbalnajam" aria-label="GitHub"><i class="fab fa-github"></i></a>
        </div>
      </div>
    </div>
  </div>
  <div class="navbar-main">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between">
        <a href="#home" class="nav-logo">
          <img src="{{ asset('front/images/logo.png') }}" alt="IDLSchool">
        </a>
        <nav class="nav-links" role="navigation">
          <a href="#home">Home</a>
          <a href="#features">Features</a>
          <a href="#mobile-app">Mobile App</a>
          <a href="#testimonials">Reviews</a>
          <a href="#contact">Contact</a>
          <a href="#contact" class="nav-cta"><i class="fas fa-rocket me-1"></i>Get Started</a>
        </nav>
        <button class="hamburger" id="menuOpen" aria-label="Open menu">
          <i class="fas fa-bars"></i>
        </button>
      </div>
    </div>
  </div>
</header>

<!-- Mobile Nav -->
<div class="mobile-nav" id="mobileNav">
  <button class="mobile-nav-close" id="menuClose" aria-label="Close menu"><i class="fas fa-times"></i></button>
  <a href="#home"         onclick="closeMobileNav()">Home</a>
  <a href="#features"     onclick="closeMobileNav()">Features</a>
  <a href="#mobile-app"   onclick="closeMobileNav()">Mobile App</a>
  <a href="#testimonials" onclick="closeMobileNav()">Reviews</a>
  <a href="#contact"      onclick="closeMobileNav()">Contact</a>
  <a href="#contact"      onclick="closeMobileNav()" class="btn-glow mt-3">Get Started</a>
</div>

<!-- ══ HERO ══ -->
<section id="home">
  <div class="hero-blob hero-blob-1"></div>
  <div class="hero-blob hero-blob-2"></div>
  <div class="hero-blob hero-blob-3"></div>
  <div class="hero-dots"></div>
  <div class="container">
    <div class="row align-items-center gy-5">
      <!-- Left text -->
      <div class="col-lg-6">
        <div class="hero-content">
          <div class="hero-badge">
            <i class="fas fa-star"></i> #1 School Management in Pakistan
          </div>
          <h1 class="hero-title">
            Manage Your School<br><span class="grad">Smarter &amp; Faster</span>
          </h1>
          <p class="hero-subtitle">
            All-in-one platform for student records, attendance, fee management,
            exams, timetables and real-time parent communication — built for
            Pakistani schools.
          </p>
          <div class="hero-btns">
            <a href="#contact" class="btn-glow"><i class="fas fa-play-circle"></i> Get Started Free</a>
            <a href="#features" class="btn-outline-w"><i class="fas fa-th-large"></i> Explore Features</a>
          </div>
          <div class="hero-metrics">
            <div class="metric"><div class="val" data-count="1000">0</div><div class="lbl">Students Managed</div></div>
            <div class="metric"><div class="val" data-count="10">0</div><div class="lbl">Schools Onboard</div></div>
            <div class="metric"><div class="val" data-count="15">0</div><div class="lbl">Powerful Features</div></div>
          </div>
        </div>
      </div>

      <!-- Right: dashboard mockup -->
      <div class="col-lg-6">
        <div class="hero-visual">
          <!-- Floating chips -->
          <div class="float-chip chip-1">
            <div class="chip-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="chip-text"><strong>98%</strong>Attendance Rate</div>
          </div>
          <div class="float-chip chip-2">
            <div class="chip-icon blue"><i class="fas fa-bolt"></i></div>
            <div class="chip-text"><strong>Live</strong>Real-time Sync</div>
          </div>

          <div class="dash-mockup">
            <div class="dash-topbar">
              <div class="dash-dot r"></div>
              <div class="dash-dot y"></div>
              <div class="dash-dot g"></div>
              <div class="dash-title-bar"></div>
            </div>
            <div class="dash-grid">
              <div class="dash-card">
                <div class="dc-label">Students</div>
                <div class="dc-num c1">1,248</div>
              </div>
              <div class="dash-card">
                <div class="dc-label">Present</div>
                <div class="dc-num c2">1,197</div>
              </div>
              <div class="dash-card">
                <div class="dc-label">Fee Due</div>
                <div class="dc-num c3">₨ 42k</div>
              </div>
            </div>
            <div class="dash-chart-row">
              <div class="dash-chart-bar">
                <div class="dash-chart-label">Monthly Attendance</div>
                <div class="bar-container">
                  <div class="bar-item"></div>
                  <div class="bar-item"></div>
                  <div class="bar-item"></div>
                  <div class="bar-item"></div>
                  <div class="bar-item"></div>
                  <div class="bar-item"></div>
                </div>
              </div>
              <div class="dash-mini-list">
                <div class="dash-mini-label">Recent</div>
                <div class="dash-mini-row">
                  <div class="dash-avatar" style="background:linear-gradient(135deg,#60a5fa,#818cf8)"></div>
                  <div class="dash-mini-text">Ahmed Khan</div>
                  <span class="dash-mini-badge badge-present">P</span>
                </div>
                <div class="dash-mini-row">
                  <div class="dash-avatar" style="background:linear-gradient(135deg,#34d399,#06b6d4)"></div>
                  <div class="dash-mini-text">Sara Malik</div>
                  <span class="dash-mini-badge badge-present">P</span>
                </div>
                <div class="dash-mini-row">
                  <div class="dash-avatar" style="background:linear-gradient(135deg,#f59e0b,#ef4444)"></div>
                  <div class="dash-mini-text">Bilal Raza</div>
                  <span class="dash-mini-badge badge-absent">A</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="scroll-hint">
    <span>Scroll</span>
    <div class="scroll-mouse"><div class="scroll-wheel"></div></div>
  </div>
</section>

<!-- ══ TRUSTED BY ══ -->
<section class="trusted-section">
  <p class="trusted-label">Trusted by leading schools across Pakistan</p>
  <div class="marquee-outer">
    <div class="marquee-inner">
      <!-- Real logos -->
      <div class="school-name">
        <img src="{{ asset('front/images/kphs.png') }}" alt="KPHS">
      </div>
      <div class="school-name">
        <img src="{{ asset('front/images/qayadat.png') }}" alt="Qayadat School">
      </div>
      <!-- Text names for schools without logos -->
      <div class="school-name"><span><i class="fas fa-graduation-cap me-2" style="color:#06b6d4"></i>Golden Gate High School</span></div>
      <div class="school-name"><span><i class="fas fa-graduation-cap me-2" style="color:#06b6d4"></i>Binat ul Husna Academy</span></div>
      <div class="school-name"><span><i class="fas fa-graduation-cap me-2" style="color:#06b6d4"></i>Noor Public School</span></div>
      <div class="school-name"><span><i class="fas fa-graduation-cap me-2" style="color:#06b6d4"></i>Al-Falah Institute</span></div>
      <!-- Duplicate for seamless loop -->
      <div class="school-name">
        <img src="{{ asset('front/images/kphs.png') }}" alt="KPHS">
      </div>
      <div class="school-name">
        <img src="{{ asset('front/images/qayadat.png') }}" alt="Qayadat School">
      </div>
      <div class="school-name"><span><i class="fas fa-graduation-cap me-2" style="color:#06b6d4"></i>Golden Gate High School</span></div>
      <div class="school-name"><span><i class="fas fa-graduation-cap me-2" style="color:#06b6d4"></i>Binat ul Husna Academy</span></div>
      <div class="school-name"><span><i class="fas fa-graduation-cap me-2" style="color:#06b6d4"></i>Noor Public School</span></div>
      <div class="school-name"><span><i class="fas fa-graduation-cap me-2" style="color:#06b6d4"></i>Al-Falah Institute</span></div>
    </div>
  </div>
</section>

<!-- ══ STATS ══ -->
<section class="stats-section">
  <div class="container">
    <div class="row g-4 justify-content-center">
      <div class="col-6 col-lg-3">
        <div class="stat-box reveal">
          <div class="stat-icon"><i class="fas fa-users"></i></div>
          <div class="stat-num"><span class="count-up" data-target="1000">0</span><sup>+</sup></div>
          <div class="stat-label">Students Managed</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-box reveal" style="transition-delay:.1s">
          <div class="stat-icon"><i class="fas fa-school"></i></div>
          <div class="stat-num"><span class="count-up" data-target="10">0</span><sup>+</sup></div>
          <div class="stat-label">Schools Onboard</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-box reveal" style="transition-delay:.2s">
          <div class="stat-icon"><i class="fas fa-bolt"></i></div>
          <div class="stat-num"><span class="count-up" data-target="15">0</span><sup>+</sup></div>
          <div class="stat-label">Powerful Features</div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="stat-box reveal" style="transition-delay:.3s">
          <div class="stat-icon"><i class="fas fa-star"></i></div>
          <div class="stat-num"><span class="count-up" data-target="99">0</span><sup>%</sup></div>
          <div class="stat-label">Client Satisfaction</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ FEATURES ══ -->
<section id="features" class="features-section">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <div class="eyebrow"><i class="fas fa-th-large"></i> Platform Features</div>
      <h2 class="section-title">Everything Your School Needs,<br><span class="hi">All in One Place</span></h2>
      <p class="section-lead center mt-3">From daily attendance to annual exams — IDLSchool handles every administrative task so you can focus on education.</p>
    </div>
    <div class="row g-4">
      <div class="col-md-6 col-lg-4 reveal" style="transition-delay:.05s">
        <div class="feat-card">
          <div class="feat-icon" style="background:linear-gradient(135deg,#dbeafe,#bfdbfe)"><i class="fas fa-user-check" style="color:#2563eb"></i></div>
          <h5>Smart Attendance</h5>
          <p>QR-code and manual attendance for students & teachers. Real-time reports, monthly summaries and absent notifications.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 reveal" style="transition-delay:.1s">
        <div class="feat-card">
          <div class="feat-icon" style="background:linear-gradient(135deg,#d1fae5,#a7f3d0)"><i class="fas fa-money-bill-wave" style="color:#059669"></i></div>
          <h5>Fee Management</h5>
          <p>Generate fee vouchers, track payments, send reminders via SMS/WhatsApp and view outstanding dues at a glance.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 reveal" style="transition-delay:.15s">
        <div class="feat-card">
          <div class="feat-icon" style="background:linear-gradient(135deg,#ede9fe,#ddd6fe)"><i class="fas fa-file-alt" style="color:#7c3aed"></i></div>
          <h5>Exam & Results</h5>
          <p>Create exams, enter marks, auto-calculate grades and generate beautiful result reports and award lists.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 reveal" style="transition-delay:.2s">
        <div class="feat-card">
          <div class="feat-icon" style="background:linear-gradient(135deg,#fef3c7,#fde68a)"><i class="fas fa-calendar-alt" style="color:#d97706"></i></div>
          <h5>Timetable Builder</h5>
          <p>Drag-and-drop timetable creation with conflict detection. Assign teachers, periods and subjects effortlessly.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 reveal" style="transition-delay:.25s">
        <div class="feat-card">
          <div class="feat-icon" style="background:linear-gradient(135deg,#fee2e2,#fecaca)"><i class="fas fa-comments" style="color:#dc2626"></i></div>
          <h5>Parent Communication</h5>
          <p>Keep parents informed with automated SMS & WhatsApp alerts for attendance, fees, results and diary updates.</p>
        </div>
      </div>
      <div class="col-md-6 col-lg-4 reveal" style="transition-delay:.3s">
        <div class="feat-card">
          <div class="feat-icon" style="background:linear-gradient(135deg,#e0f2fe,#bae6fd)"><i class="fas fa-chart-bar" style="color:#0284c7"></i></div>
          <h5>Reports & Analytics</h5>
          <p>Comprehensive dashboards with attendance graphs, fee collection charts and student performance insights.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ MOBILE APP ══ -->
<section id="mobile-app" class="mobile-section">
  <div class="hero-dots"></div>
  <div class="container" style="position:relative;z-index:2">
    <div class="row align-items-center gy-5">
      <div class="col-lg-6 reveal left">
        <div class="eyebrow" style="background:rgba(6,182,212,.15);color:var(--cyan)"><i class="fas fa-mobile-alt"></i> Mobile App</div>
        <h2>School Management<br>in Your <span class="hi">Pocket</span></h2>
        <p>Our parent and teacher apps bring the full power of IDLSchool to iOS and Android. Stay connected wherever you are.</p>
        <div class="feature-tick"><i class="fas fa-check-circle"></i><span>Live attendance notifications for parents</span></div>
        <div class="feature-tick"><i class="fas fa-check-circle"></i><span>View fee vouchers & payment history</span></div>
        <div class="feature-tick"><i class="fas fa-check-circle"></i><span>School diary & homework updates daily</span></div>
        <div class="feature-tick"><i class="fas fa-check-circle"></i><span>Exam results and subject-wise scores</span></div>
        <div class="feature-tick"><i class="fas fa-check-circle"></i><span>Raise and track complaints easily</span></div>
        <div class="store-btns">
          <a href="#contact" class="store-btn">
            <i class="fab fa-google-play"></i>
            <div class="sb-text"><small>Available on</small><strong>Google Play</strong></div>
          </a>
          <a href="#contact" class="store-btn">
            <i class="fab fa-apple"></i>
            <div class="sb-text"><small>Download on</small><strong>App Store</strong></div>
          </a>
        </div>
      </div>
      <div class="col-lg-6 reveal right">
        <div class="phone-mockup-wrap">
          <div class="phone-frame">
            <div class="phone-notch"></div>
            <div class="phone-screen">
              <div class="phone-app-bar">
                <span class="pa-title">IDLSchool Parent</span>
                <i class="fas fa-bell pa-icon"></i>
              </div>
              <div class="phone-body">
                <div class="phone-row">
                  <div class="ph-card c1"><div class="phc-num">98%</div><div class="phc-lbl">Attendance</div></div>
                  <div class="ph-card c2"><div class="phc-num">A+</div><div class="phc-lbl">Last Exam</div></div>
                </div>
                <div class="phone-list">
                  <div class="pl-item">
                    <div class="pl-dot" style="background:#34d399"></div>
                    <div class="pl-text">Ahmed — Present today</div>
                    <div class="pl-tag p">P</div>
                  </div>
                  <div class="pl-item">
                    <div class="pl-dot" style="background:#60a5fa"></div>
                    <div class="pl-text">Fee voucher for May</div>
                    <div class="pl-tag p">New</div>
                  </div>
                  <div class="pl-item">
                    <div class="pl-dot" style="background:#f59e0b"></div>
                    <div class="pl-text">Math test result: 87/100</div>
                    <div class="pl-tag p">★</div>
                  </div>
                  <div class="pl-item">
                    <div class="pl-dot" style="background:#f87171"></div>
                    <div class="pl-text">Diary: Urdu homework due</div>
                    <div class="pl-tag a">!</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="notif-chip">
            <div class="nc-dot"></div>
            <div class="nc-text"><strong>Attendance Marked</strong>Ahmed is at school ✓</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ TESTIMONIALS ══ -->
<section id="testimonials" class="testi-section">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <div class="eyebrow"><i class="fas fa-heart"></i> Kind Words</div>
      <h2 class="section-title">Kind Words From<br><span class="hi">Our Schools</span></h2>
    </div>

    <div class="swiper" id="testiSwiper">
      <div class="swiper-wrapper">
        <div class="swiper-slide">
          <div class="testi-card">
            <div class="testi-stars">★★★★★</div>
            <p class="testi-quote">IDLSchool transformed how we manage our school. Fee collection used to take days — now it's done in minutes. The SMS alerts keep parents informed automatically.</p>
            <div class="testi-author">
              <div class="testi-avatar" style="background:linear-gradient(135deg,#2563eb,#06b6d4)">M</div>
              <div><div class="testi-author-name">Mr. Muhammad Asif</div><div class="testi-author-role">Principal, KPHS Fort Abbas</div></div>
            </div>
          </div>
        </div>
        <div class="swiper-slide">
          <div class="testi-card">
            <div class="testi-stars">★★★★★</div>
            <p class="testi-quote">The parent app is a game changer. Parents check attendance and fees themselves — no more daily calls. The IDLSchool team provided excellent support throughout setup.</p>
            <div class="testi-author">
              <div class="testi-avatar" style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">S</div>
              <div><div class="testi-author-name">Mrs. Saba Iqbal</div><div class="testi-author-role">Administrator, Qayadat School</div></div>
            </div>
          </div>
        </div>
        <div class="swiper-slide">
          <div class="testi-card">
            <div class="testi-stars">★★★★★</div>
            <p class="testi-quote">Exam result generation that used to take three days now takes thirty minutes. The award list feature is beautiful and parents love seeing their children recognised.</p>
            <div class="testi-author">
              <div class="testi-avatar" style="background:linear-gradient(135deg,#d97706,#f59e0b)">A</div>
              <div><div class="testi-author-name">Mr. Abdul Rehman</div><div class="testi-author-role">Head Teacher, Al-Falah Institute</div></div>
            </div>
          </div>
        </div>
        <div class="swiper-slide">
          <div class="testi-card">
            <div class="testi-stars">★★★★★</div>
            <p class="testi-quote">QR attendance is brilliant. Students scan on entry and parents get a notification instantly. Our staff also use it and payroll calculation is now fully automated.</p>
            <div class="testi-author">
              <div class="testi-avatar" style="background:linear-gradient(135deg,#059669,#34d399)">F</div>
              <div><div class="testi-author-name">Miss Fatima Noor</div><div class="testi-author-role">Principal, Noor Public School</div></div>
            </div>
          </div>
        </div>
      </div>
      <div class="swiper-pagination"></div>
    </div>

    <div class="testi-nav">
      <button id="testiPrev" aria-label="Previous"><i class="fas fa-arrow-left"></i></button>
      <button id="testiNext" aria-label="Next"><i class="fas fa-arrow-right"></i></button>
    </div>
  </div>
</section>

<!-- ══ CONTACT ══ -->
<section id="contact" class="contact-section">
  <div class="hero-dots"></div>
  <div class="container" style="position:relative;z-index:2">
    <div class="row gy-5">
      <div class="col-lg-5 reveal left">
        <div class="eyebrow" style="background:rgba(6,182,212,.15);color:var(--cyan)"><i class="fas fa-envelope"></i> Get In Touch</div>
        <h2 style="font-size:clamp(24px,3vw,40px);font-weight:900;color:#fff;margin-bottom:12px;">Let's Modernise<br>Your School</h2>
        <p style="color:rgba(255,255,255,.65);font-size:15px;line-height:1.8;margin-bottom:40px;">Book a free demo and see how IDLSchool can save your team hours every week.</p>

        <div class="contact-info-item">
          <div class="ci-icon"><i class="fas fa-map-marker-alt"></i></div>
          <div class="ci-text"><strong>Address</strong><span>IDLBridge St#7, H#273, Fort Abbas, Punjab, Pakistan</span></div>
        </div>
        <div class="contact-info-item">
          <div class="ci-icon"><i class="fas fa-phone"></i></div>
          <div class="ci-text"><strong>Phone</strong><span><a href="tel:+923457050405">+92-345-7050405</a></span></div>
        </div>
        <div class="contact-info-item">
          <div class="ci-icon"><i class="fas fa-envelope"></i></div>
          <div class="ci-text"><strong>Email</strong><span><a href="mailto:info@idlschool.pk">info@idlschool.pk</a></span></div>
        </div>
      </div>

      <div class="col-lg-7 reveal right">
        <div class="contact-card">
          <h2>Book a Free Demo</h2>
          <p class="sub">Fill in the form and we'll contact you within 24 hours.</p>
          <form id="contactForm">
            <div class="row g-3">
              <div class="col-md-6">
                <div class="form-floating">
                  <input type="text" class="form-control" id="cName" placeholder="Your Name" required>
                  <label for="cName">Your Name</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating">
                  <input type="text" class="form-control" id="cSchool" placeholder="School Name" required>
                  <label for="cSchool">School Name</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating">
                  <input type="email" class="form-control" id="cEmail" placeholder="Email Address">
                  <label for="cEmail">Email Address</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating">
                  <input type="tel" class="form-control" id="cPhone" placeholder="Phone Number" required>
                  <label for="cPhone">Phone Number</label>
                </div>
              </div>
              <div class="col-12">
                <div class="form-floating">
                  <textarea class="form-control" id="cMsg" placeholder="Message" style="height:110px"></textarea>
                  <label for="cMsg">Your Message (optional)</label>
                </div>
              </div>
              <div class="col-12">
                <button type="submit" class="btn-glow w-100" style="justify-content:center;border:none;cursor:pointer">
                  <i class="fas fa-paper-plane"></i> Send Request
                </button>
              </div>
            </div>
          </form>
          <div id="formSuccess" style="display:none;margin-top:16px;padding:14px 20px;background:rgba(52,211,153,.15);border:1px solid rgba(52,211,153,.3);border-radius:10px;color:#34d399;font-size:14px;font-weight:600;">
            <i class="fas fa-check-circle me-2"></i> Message sent! We'll be in touch within 24 hours.
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══ FOOTER ══ -->
<footer>
  <div class="container">
    <div class="row gy-5">
      <div class="col-lg-4">
        <div class="foot-logo"><img src="{{ asset('front/images/logo.png') }}" alt="IDLSchool"></div>
        <p class="foot-desc">Smart school management software built for Pakistani schools. Attendance, fees, exams, timetables and parent communication — all in one platform.</p>
        <div class="foot-social">
          <a href="https://www.facebook.com/idlschool" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="http://x.com/tahiriqbalnajam" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
          <a href="https://www.linkedin.com/company/idlbridge" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
          <a href="https://github.com/tahiriqbalnajam" aria-label="GitHub"><i class="fab fa-github"></i></a>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <h6>Product</h6>
        <ul>
          <li><a href="#features">Features</a></li>
          <li><a href="#mobile-app">Mobile App</a></li>
          <li><a href="#testimonials">Reviews</a></li>
          <li><a href="#contact">Pricing</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-3">
        <h6>Modules</h6>
        <ul>
          <li><a href="#features">Attendance</a></li>
          <li><a href="#features">Fee Management</a></li>
          <li><a href="#features">Exam & Results</a></li>
          <li><a href="#features">Timetable</a></li>
          <li><a href="#features">School Diary</a></li>
        </ul>
      </div>
      <div class="col-lg-3">
        <h6>Contact</h6>
        <ul>
          <li><a href="mailto:info@idlschool.pk"><i class="fas fa-envelope me-2 text-cyan"></i>info@idlschool.pk</a></li>
          <li><a href="tel:+923457050405"><i class="fas fa-phone me-2 text-cyan"></i>+92-345-7050405</a></li>
          <li style="color:rgba(255,255,255,.45)"><i class="fas fa-map-marker-alt me-2 text-cyan"></i>Fort Abbas, Punjab, Pakistan</li>
        </ul>
      </div>
    </div>
    <div class="foot-bottom">
      <span>&copy; {{ date('Y') }} IDLSchool by IDLBridge. All rights reserved.</span>
      <span>Made with <i class="fas fa-heart" style="color:#ef4444"></i> in Pakistan</span>
    </div>
  </div>
</footer>

<!-- CDN JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
(function(){
  'use strict';

  /* ── Sticky header ── */
  var header = document.getElementById('idl-header');
  window.addEventListener('scroll', function(){
    header.classList.toggle('scrolled', window.scrollY > 60);
  });

  /* ── Mobile nav ── */
  var mobileNav = document.getElementById('mobileNav');
  document.getElementById('menuOpen').addEventListener('click', function(){
    mobileNav.classList.add('open');
    document.body.style.overflow = 'hidden';
  });
  document.getElementById('menuClose').addEventListener('click', closeMobileNav);
  mobileNav.addEventListener('click', function(e){
    if(e.target === mobileNav) closeMobileNav();
  });
  function closeMobileNav(){
    mobileNav.classList.remove('open');
    document.body.style.overflow = '';
  }
  window.closeMobileNav = closeMobileNav;

  /* ── Smooth anchor scroll ── */
  document.querySelectorAll('a[href^="#"]').forEach(function(a){
    a.addEventListener('click', function(e){
      var target = document.querySelector(this.getAttribute('href'));
      if(target){ e.preventDefault(); target.scrollIntoView({behavior:'smooth', block:'start'}); }
    });
  });

  /* ── Scroll reveal ── */
  var revealEls = document.querySelectorAll('.reveal');
  var revealObs = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
      if(entry.isIntersecting){ entry.target.classList.add('visible'); }
    });
  }, { threshold: 0.12 });
  revealEls.forEach(function(el){ revealObs.observe(el); });

  /* ── Count-up animation ── */
  function animateCount(el){
    var target = parseInt(el.getAttribute('data-target'), 10);
    var duration = 1800;
    var start = null;
    function step(ts){
      if(!start) start = ts;
      var progress = Math.min((ts - start) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3); // ease out cubic
      el.textContent = Math.floor(eased * target).toLocaleString();
      if(progress < 1) requestAnimationFrame(step);
      else el.textContent = target.toLocaleString();
    }
    requestAnimationFrame(step);
  }
  var countEls = document.querySelectorAll('.count-up');
  var countObs = new IntersectionObserver(function(entries){
    entries.forEach(function(entry){
      if(entry.isIntersecting){
        animateCount(entry.target);
        countObs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.5 });
  countEls.forEach(function(el){ countObs.observe(el); });

  /* Hero metrics animate on load */
  document.querySelectorAll('.metric .val[data-count]').forEach(function(el){
    var target = parseInt(el.getAttribute('data-count'), 10);
    var duration = 1600;
    var start = null;
    var suffix = el.nextElementSibling ? '' : '';
    function step(ts){
      if(!start) start = ts;
      var p = Math.min((ts - start) / duration, 1);
      var v = Math.floor((1 - Math.pow(1 - p, 3)) * target);
      el.textContent = v.toLocaleString() + (p >= 1 ? '+' : '');
      if(p < 1) requestAnimationFrame(step);
    }
    setTimeout(function(){ requestAnimationFrame(step); }, 800);
  });

  /* ── Swiper ── */
  var swiper = new Swiper('#testiSwiper', {
    loop: true,
    spaceBetween: 28,
    autoplay: { delay: 4500, disableOnInteraction: false },
    pagination: { el: '.swiper-pagination', clickable: true },
    breakpoints: {
      0:   { slidesPerView: 1 },
      768: { slidesPerView: 1 },
      992: { slidesPerView: 2 }
    }
  });
  document.getElementById('testiPrev').addEventListener('click', function(){ swiper.slidePrev(); });
  document.getElementById('testiNext').addEventListener('click', function(){ swiper.slideNext(); });

  /* ── Contact form ── */
  document.getElementById('contactForm').addEventListener('submit', function(e){
    e.preventDefault();
    document.getElementById('formSuccess').style.display = 'block';
    this.reset();
    setTimeout(function(){ document.getElementById('formSuccess').style.display = 'none'; }, 5000);
  });

})();
</script>
</body>
</html>
