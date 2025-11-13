<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ImobSites — Plataforma para Corretores e Imobiliárias</title>
    <meta
      name="description"
      content="ImobSites é a plataforma completa para corretores e imobiliárias que querem centralizar imóveis, leads e resultados em um só painel."
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <style>
      :root {
        --color-bg: #f5f7fa;
        --color-surface: #ffffff;
        --color-primary: #1c3b5a;
        --color-accent: #f7931e;
        --color-text-main: #0f172a;
        --color-text-muted: #5a6473;
        --color-border-soft: #e2e6ee;
        --color-success: #22c55e;
        --color-danger-soft: #fee2e2;
        --shadow-soft: 0 10px 30px rgba(15, 23, 42, 0.08);
        --shadow-hover: 0 16px 40px rgba(15, 23, 42, 0.12);
        --radius-base: 18px;
        --radius-pill: 999px;
        --max-width: 1200px;
        --header-height: 78px;
        --transition-base: 0.3s ease;
      }

      *,
      *::before,
      *::after {
        box-sizing: border-box;
      }

      html {
        scroll-behavior: smooth;
      }

      body {
        margin: 0;
        font-family: "Plus Jakarta Sans", "Outfit", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background-color: var(--color-bg);
        color: var(--color-text-main);
        line-height: 1.6;
        overflow-x: hidden;
      }

      img {
        max-width: 100%;
        display: block;
      }

      a {
        text-decoration: none;
        color: inherit;
      }

      .container {
        width: min(100%, calc(var(--max-width) + 48px));
        margin: 0 auto;
        padding: 0 24px;
      }

      header {
        position: sticky;
        top: 0;
        z-index: 1000;
        background-color: var(--color-surface);
        box-shadow: 0 8px 20px rgba(28, 59, 90, 0.08);
      }

      .header-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: var(--header-height);
      }

      .logo {
        display: flex;
        align-items: center;
        gap: 12px;
      }

      .logo img {
        height: 42px;
        object-fit: contain;
      }

      nav {
        display: flex;
        align-items: center;
        gap: 32px;
      }

      nav a {
        font-weight: 500;
        color: var(--color-text-muted);
        transition: color var(--transition-base);
      }

      nav a:hover {
        color: var(--color-primary);
      }

      .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border-radius: var(--radius-pill);
        padding: 14px 28px;
        font-weight: 600;
        transition: transform var(--transition-base), box-shadow var(--transition-base), background var(--transition-base);
        cursor: pointer;
      }

      .btn-primary {
        background: var(--color-accent);
        color: #fff;
        box-shadow: 0 12px 30px rgba(247, 147, 30, 0.25);
      }

      .btn-primary:hover {
        transform: scale(1.03) translateY(-2px);
        background: #e6850f;
        box-shadow: 0 18px 36px rgba(247, 147, 30, 0.35);
      }

      .btn-secondary {
        color: var(--color-primary);
        font-weight: 600;
      }

      .menu-toggle {
        display: none;
        background: none;
        border: none;
        padding: 8px;
        cursor: pointer;
      }

      .menu-toggle span {
        width: 24px;
        height: 2px;
        background: var(--color-primary);
        display: block;
        margin: 5px 0;
        transition: transform 0.3s ease, opacity 0.3s ease;
      }

      .hero {
        padding: 110px 0 80px;
        position: relative;
      }

      .hero::before {
        content: "";
        position: absolute;
        inset: 0;
        background: radial-gradient(circle at top right, rgba(28, 59, 90, 0.12), rgba(28, 59, 90, 0));
        z-index: -1;
      }

      .hero-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 60px;
        align-items: center;
      }

      .badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(247, 147, 30, 0.12);
        color: var(--color-accent);
        padding: 10px 18px;
        border-radius: var(--radius-pill);
        font-weight: 600;
        font-size: 0.95rem;
        letter-spacing: 0.02em;
      }

      .hero h1 {
        font-size: clamp(2.8rem, 5vw, 3.6rem);
        line-height: 1.15;
        margin: 24px 0;
        color: var(--color-primary);
      }

      .hero p {
        font-size: 1.1rem;
        color: var(--color-text-muted);
        margin-bottom: 28px;
      }

      .hero-bullets {
        display: grid;
        gap: 14px;
        margin-bottom: 36px;
      }

      .hero-bullets li {
        list-style: none;
        display: flex;
        gap: 14px;
        align-items: flex-start;
        color: var(--color-text-main);
        font-weight: 500;
      }

      .hero-bullets li::before {
        content: "✔";
        color: var(--color-primary);
        font-weight: 700;
        margin-top: 2px;
      }

      .hero-cta {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 18px;
      }

      .hero-cta a.btn-secondary {
        font-weight: 600;
        color: var(--color-primary);
        display: inline-flex;
        align-items: center;
      }

      .hero-cta a.btn-secondary::before {
        content: "▶";
        font-size: 0.88rem;
        margin-right: 8px;
      }

      .hero-visual {
        position: relative;
      }

      .dashboard-mockup {
        background: linear-gradient(155deg, rgba(28, 59, 90, 0.95), rgba(28, 59, 90, 0.6));
        border-radius: 26px;
        padding: 38px;
        color: #fff;
        box-shadow: 0 30px 50px rgba(28, 59, 90, 0.25);
        position: relative;
        overflow: hidden;
        animation: float 8s ease-in-out infinite;
      }

      .dashboard-mockup::after {
        content: "";
        position: absolute;
        inset: 0;
        background-image: url("data:image/svg+xml,%3Csvg width='400' height='400' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='dots' x='0' y='0' width='20' height='20' patternUnits='userSpaceOnUse'%3E%3Ccircle cx='2' cy='2' r='2' fill='%23ffffff14'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='400' height='400' fill='url(%23dots)'/%3E%3C/svg%3E");
        opacity: 0.45;
      }

      .dashboard-content {
        position: relative;
        display: grid;
        gap: 22px;
      }

      .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 12px;
      }

      .dashboard-grid {
        display: grid;
        gap: 18px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
      }

      .dashboard-card {
        background: rgba(255, 255, 255, 0.12);
        border-radius: 18px;
        padding: 18px;
        backdrop-filter: blur(8px);
      }

      .mobile-card {
        position: absolute;
        bottom: -30px;
        right: -32px;
        width: 220px;
        background: #ffffff;
        border-radius: 24px;
        padding: 20px;
        box-shadow: 0 25px 45px rgba(15, 23, 42, 0.25);
        border: 1px solid rgba(255, 255, 255, 0.06);
        animation: floatAlt 9s ease-in-out infinite;
      }

      .mobile-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
      }

      section {
        padding: 110px 0;
        position: relative;
      }

      .section-title {
        font-size: clamp(2.1rem, 4vw, 2.8rem);
        margin-bottom: 20px;
        color: var(--color-primary);
        text-align: left;
      }

      .section-subtitle {
        font-size: 1.05rem;
        color: var(--color-text-muted);
        margin-bottom: 40px;
      }

      .section-center {
        text-align: center;
      }

      .cards-grid {
        display: grid;
        gap: 28px;
      }

      .cards-grid.cols-2 {
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      }

      .cards-grid.cols-3 {
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      }

      .card {
        background: var(--color-surface);
        border-radius: var(--radius-base);
        box-shadow: var(--shadow-soft);
        padding: 32px;
        transition: transform var(--transition-base), box-shadow var(--transition-base);
        position: relative;
        overflow: hidden;
      }

      .card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
      }

      .card h3 {
        font-size: 1.45rem;
        margin-bottom: 18px;
        color: var(--color-primary);
      }

      .card p,
      .card li {
        color: var(--color-text-muted);
      }

      .card ul {
        margin: 0;
        padding-left: 1.2rem;
        display: grid;
        gap: 10px;
      }

      .card-icon {
        width: 58px;
        height: 58px;
        border-radius: 16px;
        background: rgba(28, 59, 90, 0.1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.9rem;
        margin-bottom: 18px;
      }

      .section-muted {
        background: #eef2f8;
      }

      .section-gradient {
        background: linear-gradient(135deg, rgba(28, 59, 90, 0.08), rgba(28, 59, 90, 0));
      }

      .section-dark {
        background: var(--color-primary);
        color: #fff;
      }

      .pain-points {
        background: linear-gradient(135deg, #edf2f8 0%, #f5f7fa 100%);
      }

      .highlight-box {
        background: var(--color-surface);
        border-left: 4px solid var(--color-accent);
        padding: 28px 32px;
        border-radius: var(--radius-base);
        box-shadow: var(--shadow-soft);
        margin-top: 48px;
        font-weight: 600;
        color: var(--color-primary);
        white-space: pre-line;
      }

      .two-column {
        display: grid;
        gap: 60px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: center;
      }

      .diagram {
        position: relative;
        padding: 48px;
        background: linear-gradient(145deg, rgba(28, 59, 90, 0.08), rgba(28, 59, 90, 0));
        border-radius: var(--radius-base);
        box-shadow: var(--shadow-soft);
      }

      .diagram-center {
        background: var(--color-primary);
        color: #fff;
        width: 160px;
        height: 160px;
        border-radius: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.2rem;
        margin: 0 auto;
        text-align: center;
        position: relative;
        z-index: 1;
      }

      .diagram-arrow {
        position: absolute;
        width: 120px;
        height: 120px;
        border-radius: 26px;
        background: var(--color-surface);
        box-shadow: var(--shadow-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        color: var(--color-primary);
      }

      .diagram-arrow::after {
        content: "";
        position: absolute;
        width: 60px;
        height: 2px;
        background: var(--color-primary);
      }

      .diagram-arrow:nth-child(2) {
        top: 40px;
        left: 20px;
      }

      .diagram-arrow:nth-child(2)::after {
        right: -60px;
      }

      .diagram-arrow:nth-child(3) {
        top: 40px;
        right: 20px;
      }

      .diagram-arrow:nth-child(3)::after {
        left: -60px;
      }

      .diagram-arrow:nth-child(4) {
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
      }

      .diagram-arrow:nth-child(4)::after {
        top: -40px;
        left: 50%;
        width: 2px;
        height: 40px;
      }

      .timeline {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 24px;
        margin-top: 50px;
      }

      .timeline-step {
        position: relative;
        padding: 28px;
        background: var(--color-surface);
        border-radius: var(--radius-base);
        box-shadow: var(--shadow-soft);
        text-align: left;
        transition: transform var(--transition-base), box-shadow var(--transition-base);
      }

      .timeline-step:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-hover);
      }

      .timeline-step::before {
        content: attr(data-step);
        position: absolute;
        top: -18px;
        left: 16px;
        background: var(--color-primary);
        color: #fff;
        border-radius: var(--radius-pill);
        padding: 6px 16px;
        font-weight: 600;
        font-size: 0.9rem;
      }

      .plans {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 28px;
        margin-top: 50px;
      }

      .plan-card {
        padding: 36px;
        border-radius: 24px;
        background: var(--color-surface);
        box-shadow: var(--shadow-soft);
        display: grid;
        gap: 22px;
        position: relative;
      }

      .plan-card.featured {
        border: 2px solid var(--color-primary);
        box-shadow: 0 24px 48px rgba(28, 59, 90, 0.18);
        transform: translateY(-12px);
      }

      .plan-highlight {
        font-weight: 600;
        color: var(--color-accent);
        background: rgba(247, 147, 30, 0.12);
        padding: 12px 18px;
        border-radius: var(--radius-pill);
        justify-self: flex-start;
      }

      .demo-section {
        background: var(--color-primary);
        color: #fff;
        border-radius: 32px;
        padding: 80px 72px;
        display: grid;
        gap: 40px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        align-items: center;
      }

      .demo-cards {
        display: grid;
        gap: 20px;
      }

      .demo-card {
        background: rgba(255, 255, 255, 0.09);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: var(--radius-base);
        padding: 24px;
      }

      .video-mock {
        position: relative;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 25px 55px rgba(0, 0, 0, 0.35);
      }

      .video-mock::after {
        content: "▶";
        position: absolute;
        inset: 0;
        margin: auto;
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: rgba(247, 147, 30, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.6rem;
        box-shadow: 0 12px 24px rgba(247, 147, 30, 0.3);
      }

      .who-section {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 32px;
      }

      .who-card {
        border-radius: 24px;
        padding: 36px;
        background: var(--color-surface);
        box-shadow: var(--shadow-soft);
      }

      .who-card.positive {
        border: 2px solid rgba(34, 197, 94, 0.35);
      }

      .who-card.negative {
        border: 2px solid rgba(254, 226, 226, 0.85);
      }

      .who-card ul {
        padding-left: 1.2rem;
        display: grid;
        gap: 12px;
      }

      .who-card.positive li::marker {
        content: "✔ ";
        color: var(--color-success);
        font-size: 1.1rem;
      }

      .who-card.negative li::marker {
        content: "✖ ";
        color: #ef4444;
        font-size: 1.1rem;
      }

      .faq {
        max-width: 900px;
        margin: 0 auto;
        display: grid;
        gap: 18px;
      }

      .faq-item {
        border-radius: var(--radius-base);
        background: var(--color-surface);
        box-shadow: var(--shadow-soft);
        overflow: hidden;
      }

      .faq-question {
        width: 100%;
        background: none;
        border: none;
        padding: 22px 28px;
        text-align: left;
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--color-primary);
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
      }

      .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease, opacity 0.35s ease;
        opacity: 0;
        padding: 0 28px;
      }

      .faq-item.active .faq-answer {
        max-height: 400px;
        opacity: 1;
        padding-bottom: 24px;
      }

      .faq-item.active .faq-question span {
        transform: rotate(45deg);
      }

      .faq-question span {
        transition: transform 0.3s ease;
        font-size: 1.4rem;
        color: var(--color-accent);
      }

      .cta-final {
        background: linear-gradient(120deg, rgba(28, 59, 90, 0.95), rgba(247, 147, 30, 0.9));
        color: #fff;
        border-radius: 30px;
        padding: 72px;
        text-align: center;
        box-shadow: 0 32px 60px rgba(15, 23, 42, 0.28);
      }

      .cta-final h2 {
        font-size: clamp(2.4rem, 5vw, 3rem);
        margin-bottom: 24px;
      }

      .cta-final p {
        margin-bottom: 36px;
        font-size: 1.15rem;
        max-width: 720px;
        margin-inline: auto;
      }

      .cta-final .btn-secondary {
        color: #fff;
        text-decoration: underline;
        font-weight: 600;
      }

      .fixed-cta {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 998;
        display: none;
      }

      .fixed-cta .btn {
        padding: 16px 32px;
        font-size: 1rem;
      }

      .floating {
        transform: translateY(0);
      }

      .scroll-reveal {
        opacity: 0;
        transform: translateY(40px);
        transition: opacity 0.7s ease, transform 0.7s ease;
      }

      .scroll-reveal.visible {
        opacity: 1;
        transform: translateY(0);
      }

      footer {
        padding: 40px 0;
        text-align: center;
        color: var(--color-text-muted);
        font-size: 0.9rem;
      }

      @keyframes float {
        0%,
        100% {
          transform: translateY(0px);
        }
        50% {
          transform: translateY(-10px);
        }
      }

      @keyframes floatAlt {
        0%,
        100% {
          transform: translateY(0px);
        }
        50% {
          transform: translateY(-12px);
        }
      }

      @media (max-width: 1024px) {
        nav {
          position: fixed;
          inset: var(--header-height) 0 auto 0;
          background: var(--color-surface);
          flex-direction: column;
          gap: 0;
          align-items: flex-start;
          padding: 18px 24px;
          transform: translateY(-120%);
          box-shadow: 0 20px 40px rgba(15, 23, 42, 0.18);
          transition: transform 0.35s ease;
        }

        nav.open {
          transform: translateY(0);
        }

        nav a {
          padding: 14px 0;
          width: 100%;
        }

        .menu-toggle {
          display: inline-flex;
          flex-direction: column;
          justify-content: center;
        }

        .hero {
          padding-top: 120px;
        }

        .hero-grid,
        .two-column,
        .demo-section {
          grid-template-columns: 1fr;
        }

        .hero-visual {
          order: -1;
        }

        .mobile-card {
          position: relative;
          bottom: auto;
          right: auto;
          margin: 24px auto 0;
        }

        .diagram {
          margin-top: 36px;
        }

        .timeline {
          grid-template-columns: repeat(3, minmax(0, 1fr));
        }
      }

      @media (max-width: 768px) {
        .hero {
          padding: 100px 0 80px;
        }

        .hero h1 {
          font-size: clamp(2.2rem, 8vw, 2.9rem);
        }

        .hero-bullets li {
          font-size: 1rem;
        }

        section {
          padding: 90px 0;
        }

        .timeline {
          grid-template-columns: 1fr;
        }

        .timeline-step::before {
          left: 22px;
        }

        .diagram {
          padding: 36px 24px;
        }

        .diagram-center {
          width: 140px;
          height: 140px;
        }

        .demo-section {
          padding: 60px 32px;
        }

        .who-section {
          grid-template-columns: 1fr;
        }

        .cta-final {
          padding: 56px 28px;
        }

        .header-inner {
          padding: 0 10px;
        }

        .fixed-cta {
          display: block;
          width: calc(100% - 32px);
        }

        .fixed-cta .btn {
          width: 100%;
        }
      }

      @media (max-width: 480px) {
        .badge {
          font-size: 0.85rem;
        }

        .hero-bullets {
          gap: 12px;
        }

        .hero-cta {
          width: 100%;
        }

        .hero-cta .btn {
          width: 100%;
          justify-content: center;
        }

        .diagram {
          display: grid;
          gap: 18px;
        }

        .diagram-arrow {
          position: static;
          transform: none;
          width: auto;
          height: auto;
          padding: 20px;
        }

        .diagram-arrow::after {
          display: none;
        }
      }
    </style>
  </head>
  <body>
    <div class="fixed-cta">
      <a href="planos.php" class="btn btn-primary">👉 Ver planos e contratar agora</a>
    </div>
    <header>
      <div class="container header-inner">
        <div class="logo">
          <img src="assets/logo-imobsites.svg" alt="ImobSites" />
        </div>
        <nav id="primary-nav">
          <a href="#recursos">Recursos</a>
          <a href="planos.php">Planos</a>
          <a href="#demo">Demonstração</a>
          <a href="#faq">FAQ</a>
          <a href="planos.php" class="btn btn-primary btn-small">Contratar agora</a>
        </nav>
        <button class="menu-toggle" aria-label="Abrir menu" aria-expanded="false">
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>
    </header>

    <main>
      <section class="hero" id="inicio">
        <div class="container hero-grid">
          <div class="hero-content scroll-reveal" data-reveal="right">
            <div class="badge">Plataforma para corretores e imobiliárias</div>
            <h1>Pare de perder leads e imóveis: centralize tudo em um só painel</h1>
            <p>
              ImobSites é a plataforma feita para corretores que querem gerenciar imóveis, clientes e resultados em um único lugar – com site profissional,
              CRM simples e dados em tempo real, sem complicar sua rotina.
            </p>
            <ul class="hero-bullets">
              <li>Publique o imóvel uma vez e replique para site, redes sociais e portais de anúncio</li>
              <li>Acompanhe cada lead: de onde veio, quem atendeu e em que etapa está</li>
              <li>Veja, em números, quais imóveis e campanhas realmente trazem resultado</li>
              <li>Organize fotos, documentos e tours virtuais direto do app do corretor</li>
            </ul>
            <div class="hero-cta">
              <a href="planos.php" class="btn btn-primary">👉 Ver planos e contratar agora</a>
              <a href="#demo" class="btn-secondary">Assistir tour guiado da plataforma</a>
            </div>
          </div>
          <div class="hero-visual scroll-reveal" data-reveal="left">
            <div class="dashboard-mockup">
              <div class="dashboard-content">
                <div class="dashboard-header">
                  <strong>Painel ImobSites</strong>
                  <span>Hoje</span>
                </div>
                <div class="dashboard-grid">
                  <div class="dashboard-card">
                    <h4>Visão geral</h4>
                    <p>Leads novos: 18</p>
                    <p>Imóveis em destaque: 6</p>
                  </div>
                  <div class="dashboard-card">
                    <h4>Leads por canal</h4>
                    <p>Site: 9</p>
                    <p>Portais: 5</p>
                    <p>Redes: 4</p>
                  </div>
                  <div class="dashboard-card">
                    <h4>Funil</h4>
                    <p>Interessados: 32</p>
                    <p>Propostas: 9</p>
                    <p>Fechados: 3</p>
                  </div>
                  <div class="dashboard-card">
                    <h4>Imóvel mais visto</h4>
                    <p>Casa Jardim Azul</p>
                    <p>Visitas semana: 148</p>
                  </div>
                </div>
              </div>
              <div class="mobile-card">
                <div class="mobile-card-header">
                  <strong>Leads ativos</strong>
                  <span>• • •</span>
                </div>
                <p>João Santos — Interesse: Cobertura Centro</p>
                <p>Próximo passo: Enviar proposta atualizada</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="pain-points scroll-reveal" id="recursos">
        <div class="container">
          <h2 class="section-title">O problema não é só ‘falta de cliente’. É falta de processo e de controle.</h2>
          <p class="section-subtitle">
            O dia a dia do corretor é uma maratona: visitas, ligações, WhatsApp explodindo, grupos, planilhas, portais, redes sociais… e, no meio disso tudo, muita
            oportunidade se perde pelo caminho.
          </p>
          <p class="section-subtitle"><strong>Algumas das dores mais comuns hoje:</strong></p>
          <div class="cards-grid cols-2">
            <article class="card">
              <div class="card-icon">📬</div>
              <h3>Leads se perdem no caminho</h3>
              <p>
                Contatos chegam de vários canais (site, OLX, Zap, Instagram, WhatsApp) e ficam espalhados, sem funil claro, sem lembrete de follow-up e sem histórico
                organizado.
              </p>
            </article>
            <article class="card">
              <div class="card-icon">⏱️</div>
              <h3>Demora no atendimento e falta de retorno</h3>
              <p>
                Muitos leads chegam à noite ou fim de semana e levam horas ou dias para receber resposta – tempo suficiente para fechar com outro corretor.
              </p>
            </article>
            <article class="card">
              <div class="card-icon">🌐</div>
              <h3>Falta de um site próprio que realmente vende</h3>
              <p>
                Sem um site otimizado, o corretor fica refém de portais e redes sociais, dependente de algoritmos e concorrendo com dezenas de anúncios na mesma tela.
              </p>
            </article>
            <article class="card">
              <div class="card-icon">🌀</div>
              <h3>Publicação duplicada e trabalho repetido</h3>
              <p>
                Cadastra o imóvel no site, depois no portal A, depois no portal B, depois adapta para redes sociais… cada alteração de preço ou foto vira um sufoco.
              </p>
            </article>
            <article class="card">
              <div class="card-icon">📁</div>
              <h3>Documentação bagunçada</h3>
              <p>
                Contratos, propostas, laudos, certidões… espalhados em pastas, e-mails e WhatsApp. Burocracia consome tempo que deveria ser usado para vender.
              </p>
            </article>
            <article class="card">
              <div class="card-icon">🧠</div>
              <h3>Tudo em planilha ou ‘na cabeça’</h3>
              <p>
                Sem sistema, fica difícil enxergar o funil de vendas, saber quais corretores convertem mais, quais campanhas trazem leads qualificados e quais imóveis
                não giram.
              </p>
            </article>
          </div>
          <div class="highlight-box">
            Você não precisa ‘trabalhar mais’.
Você precisa de um painel que organize seu trabalho e mostre, com clareza, onde estão as oportunidades.
          </div>
        </div>
      </section>

      <section id="pilares" class="scroll-reveal">
        <div class="container section-center">
          <h2 class="section-title">A plataforma que une os três pilares do corretor moderno</h2>
          <div class="cards-grid cols-3">
            <article class="card">
              <div class="card-icon">🏠</div>
              <h3>Pilar 1 – Gerenciamento de imóveis</h3>
              <p><strong>Tudo que seu imóvel precisa, em um único cadastro</strong></p>
              <ul>
                <li>Cadastro rápido de imóveis com campos intuitivos</li>
                <li>Fotos feitas direto pelo app do corretor (sem precisar ir na galeria)</li>
                <li>Integração com câmera do celular: tira a foto, salva no imóvel na hora</li>
                <li>Registro de vídeos e tour virtual direto no painel, pronto para embutir no site</li>
                <li>Organização por status (disponível, reservado, vendido, locado)</li>
                <li>Destaque automático no site para imóveis mais visitados ou com melhor performance</li>
              </ul>
            </article>
            <article class="card">
              <div class="card-icon">🤝</div>
              <h3>Pilar 2 – Gerenciamento de clientes e negociações</h3>
              <p><strong>Um CRM simples, pensado para corretores (e não para engenheiros)</strong></p>
              <ul>
                <li>Ficha de cada cliente com histórico de contatos, imóveis de interesse e observações</li>
                <li>Linha do tempo da negociação: quem falou com quem, quando e por qual canal</li>
                <li>Tarefas de follow-up: lembretes para retorno, visitas e envio de propostas</li>
                <li>Funil visual de oportunidades (interessado → em atendimento → proposta → fechamento)</li>
                <li>Distribuição organizada de leads entre corretores / equipe</li>
              </ul>
              <p>
                Tudo em linguagem simples, sem termos técnicos complicados. O painel vira seu ‘caderno’ digital de negócios.
              </p>
            </article>
            <article class="card">
              <div class="card-icon">📊</div>
              <h3>Pilar 3 – Mapeamento de resultados (dados e estatísticas)</h3>
              <p><strong>Decisões baseadas em dados, não em ‘achismo’</strong></p>
              <ul>
                <li>Quantidade de visitas por imóvel (no site e nas campanhas)</li>
                <li>Origem do tráfego: Google, redes sociais, portais, indicação, etc.</li>
                <li>Taxa de conversão por canal (quantos leads, quantas visitas, quantas propostas)</li>
                <li>Relatórios de desempenho por corretor (quem atende, quem converte, quem deixa lead parado)</li>
              </ul>
              <p>
                Você passa a enxergar quais imóveis giram, quais canais trazem cliente de verdade e onde estão os gargalos do seu atendimento.
              </p>
            </article>
          </div>
        </div>
      </section>

      <section class="section-gradient scroll-reveal" id="multiplos-canais">
        <div class="container two-column">
          <div>
            <h2 class="section-title">Cadastre uma vez. Publique em todos os lugares.</h2>
            <p class="section-subtitle">Chega de copiar e colar anúncio de imóvel para cada portal e rede social.</p>
            <p>
              A ideia da plataforma é permitir que, a partir de um único cadastro, você possa:
              <br />- Publicar o imóvel no seu site profissional
              <br />- Gerar automaticamente o anúncio otimizado para redes sociais (com título, descrição e link)
              <br />- Ter integração com portais de mercado (como ZapImóveis, OLX e similares), conforme as conexões ativas na sua conta
            </p>
            <div class="highlight-box" style="margin-top: 32px; border-left-color: var(--color-primary); background: rgba(255, 255, 255, 0.92);">
              Obs.: a integração com cada portal dependerá das APIs e parcerias disponíveis. A proposta da ImobSites é centralizar ao máximo o trabalho para você
              publicar tudo em poucos cliques.
            </div>
            <p style="margin-top: 28px; font-weight: 600;">Resultado: menos tempo cadastrando, mais tempo atendendo clientes.</p>
          </div>
          <div class="diagram">
            <div class="diagram-arrow">Site</div>
            <div class="diagram-arrow">Redes sociais</div>
            <div class="diagram-arrow">Portais</div>
            <div class="diagram-center">ImobSites</div>
          </div>
        </div>
      </section>

      <section class="section-muted scroll-reveal" id="documentacao">
        <div class="container two-column">
          <div class="dashboard-mockup" style="animation: none; background: linear-gradient(155deg, rgba(28, 59, 90, 0.85), rgba(28, 59, 90, 0.55));">
            <div class="dashboard-content">
              <div class="dashboard-header">
                <strong>Cofre de documentos</strong>
                <span>Negociação #1245</span>
              </div>
              <div class="dashboard-grid">
                <div class="dashboard-card">
                  <h4>Contratos</h4>
                  <p>Contrato de compra e venda.pdf</p>
                  <p>Atualizado: ontem</p>
                </div>
                <div class="dashboard-card">
                  <h4>Propostas</h4>
                  <p>Proposta cliente João.pdf</p>
                  <p>Proposta contra-oferta.pdf</p>
                </div>
                <div class="dashboard-card">
                  <h4>Laudos</h4>
                  <p>Laudo vistoria técnica.pdf</p>
                  <p>Certidão negativa atual.pdf</p>
                </div>
                <div class="dashboard-card">
                  <h4>Pendências</h4>
                  <p>Enviar certidão atualizada</p>
                  <p>Assinatura do comprador</p>
                </div>
              </div>
            </div>
          </div>
          <div>
            <h2 class="section-title">Toda a papelada em um único lugar (e ligada ao negócio certo)</h2>
            <p class="section-subtitle">
              Em vez de PDF solto em e-mail, WhatsApp e pastas aleatórias, cada negociação passa a ter seu próprio ‘cofre de documentos’:
            </p>
            <ul class="hero-bullets" style="margin-bottom: 28px;">
              <li>Upload de contratos, propostas, comprovantes, laudos e certidões</li>
              <li>Anexos vinculados ao imóvel e/ou ao cliente</li>
              <li>Histórico de versões e observações internas</li>
              <li>Visão rápida do que está pendente para concretizar a venda ou locação</li>
            </ul>
            <p style="font-weight: 600;">
              Você sabe exatamente o que falta para assinar, sem precisar caçar arquivos em três aplicativos diferentes.
            </p>
          </div>
        </div>
      </section>

      <section class="scroll-reveal" id="fluxo">
        <div class="container section-center">
          <h2 class="section-title">Do anúncio ao fechamento: como a plataforma entra na sua rotina</h2>
          <div class="timeline">
            <div class="timeline-step" data-step="1">
              <h3>Cadastre o imóvel</h3>
              <p>Preencha os dados principais, tire as fotos direto no app, grave o tour virtual e salve.</p>
            </div>
            <div class="timeline-step" data-step="2">
              <h3>Publique em um clique</h3>
              <p>O imóvel vai para o site, gera material para redes sociais e pode ser enviado para portais integrados.</p>
            </div>
            <div class="timeline-step" data-step="3">
              <h3>Receba e organize os leads</h3>
              <p>
                Cada pedido de informação entra no painel com origem (site, portal, redes, WhatsApp) e fica registrado em um funil simples.
              </p>
            </div>
            <div class="timeline-step" data-step="4">
              <h3>Atenda com método</h3>
              <p>Você registra conversas, marca próximos passos, agenda visitas e não perde o timing de retorno.</p>
            </div>
            <div class="timeline-step" data-step="5">
              <h3>Anexe documentos e avance na negociação</h3>
              <p>À medida que o negócio evolui, contratos e documentos vão sendo anexados à ficha da negociação.</p>
            </div>
            <div class="timeline-step" data-step="6">
              <h3>Acompanhe resultados em tempo real</h3>
              <p>
                Relatórios mostram quais imóveis e canais estão performando melhor – e onde você precisa ajustar rota.
              </p>
            </div>
          </div>
        </div>
      </section>

      <section class="section-muted scroll-reveal" id="planos">
        <div class="container section-center">
          <h2 class="section-title">Escolha o plano que acompanha o seu momento como corretor</h2>
          <div class="plans">
            <article class="plan-card">
              <h3>Plano 1 – Para quem está começando (individual)</h3>
              <p>Pensado para o corretor autônomo que quer dar o primeiro passo no digital com segurança.</p>
              <ul>
                <li>Site profissional com sua marca</li>
                <li>Cadastro de imóveis ilimitado</li>
                <li>Painel simples de leads e negociações</li>
                <li>Relatório básico de visitas e origem do tráfego</li>
                <li>Suporte por e-mail / WhatsApp em horário comercial</li>
              </ul>
              <div class="plan-highlight">Perfeito para começar a organizar seus imóveis e clientes.</div>
              <div class="placeholder" style="color: var(--color-text-muted); font-size: 0.95rem;">Preço em breve</div>
            </article>
            <article class="plan-card featured">
              <h3>Plano 2 – Para quem já tem carteira ativa</h3>
              <p>Para corretores e pequenas equipes que precisam de mais controle e dados.</p>
              <ul>
                <li>Tudo do Plano 1, mais:</li>
                <li>Funil visual de negociações e tarefas de follow-up</li>
                <li>Dashboards com estatísticas avançadas (imóvel mais visto, campanha que mais gera lead, etc.)</li>
                <li>Espaço ampliado para documentação</li>
                <li>Recursos avançados de publicação em múltiplos canais (conforme integrações ativas)</li>
              </ul>
              <div class="plan-highlight">Ideal para quem já tem fluxo de clientes e quer parar de perder oportunidade.</div>
              <div class="placeholder" style="color: var(--color-text-muted); font-size: 0.95rem;">Preço em breve</div>
            </article>
            <article class="plan-card">
              <h3>Plano 3 – Para equipes e operação mais robusta</h3>
              <p>Para imobiliárias e times de corretores que precisam enxergar performance em nível de equipe.</p>
              <ul>
                <li>Tudo do Plano 2, mais:</li>
                <li>Gestão de usuários e permissões (corretores, administradores)</li>
                <li>Relatórios por corretor (quantos leads, quantas visitas, quantos fechamentos)</li>
                <li>Painel de metas e resultados da equipe</li>
                <li>Prioridade no suporte e onboarding guiado</li>
              </ul>
              <div class="plan-highlight">Feito para quem quer transformar o time em uma máquina de vendas organizada.</div>
              <div class="placeholder" style="color: var(--color-text-muted); font-size: 0.95rem;">Preço em breve</div>
            </article>
          </div>
        </div>
      </section>

      <section class="scroll-reveal" id="demo">
        <div class="container">
          <div class="demo-section">
            <div>
              <h2 class="section-title" style="color: #fff;">Veja a plataforma funcionando antes de decidir</h2>
              <p>
                Não vamos oferecer ‘período grátis’ que vira mais uma coisa para você lembrar de cancelar.
                Em vez disso, você terá:
              </p>
              <div class="demo-cards">
                <div class="demo-card">
                  <h3>Acesso a uma demonstração guiada do site</h3>
                  <p>Entre em uma versão demo do site para navegar como se fosse um cliente procurando imóvel.</p>
                </div>
                <div class="demo-card">
                  <h3>Tour em vídeo do painel do corretor e do CRM</h3>
                  <p>
                    Assista em poucos minutos como é cadastrar um imóvel, receber um lead, registrar negociações e acompanhar resultados.
                  </p>
                </div>
                <div class="demo-card">
                  <h3>Prints comentados dos principais recursos</h3>
                  <p>
                    Cada tela importante com uma explicação rápida do que ela faz, sem jargão técnico.
                  </p>
                </div>
              </div>
              <a href="planos.php" class="btn btn-primary" style="margin-top: 32px;">Quero contratar depois da demonstração</a>
            </div>
            <div class="video-mock">
              <img src="https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=900&q=80" alt="Demonstração da plataforma" />
            </div>
          </div>
        </div>
      </section>

      <section class="scroll-reveal" id="quem-e">
        <div class="container">
          <h2 class="section-title section-center">Para quem é a ImobSites</h2>
          <div class="who-section">
            <div class="who-card positive">
              <h3>É para você se:</h3>
              <ul>
                <li>Está cansado de planilhas, grupos de WhatsApp e informações espalhadas</li>
                <li>Quer um site bonito, rápido e organizado, com seus imóveis em destaque</li>
                <li>Precisa de um painel simples para controlar leads e negociações</li>
                <li>Valoriza ver em números o que funciona e o que não funciona no seu marketing</li>
                <li>Quer parar de cadastrar o mesmo imóvel em 3, 4 lugares diferentes</li>
              </ul>
            </div>
            <div class="who-card negative">
              <h3>Não é para você se:</h3>
              <ul>
                <li>Prefere continuar controlando tudo ‘na cabeça’ e em planilhas</li>
                <li>Não quer investir em imagem profissional no digital</li>
                <li>Não pretende responder leads com agilidade nem seguir um processo mínimo</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

      <section class="section-muted scroll-reveal" id="faq">
        <div class="container section-center">
          <h2 class="section-title">Perguntas frequentes</h2>
          <div class="faq">
            <div class="faq-item">
              <button class="faq-question">
                Preciso entender de tecnologia para usar a plataforma?
                <span>+</span>
              </button>
              <div class="faq-answer">
                <p>
                  Não. O painel foi pensado para corretores, com telas simples, em português, e um passo a passo lógico. Em poucos dias você já estará navegando com
                  segurança.
                </p>
              </div>
            </div>
            <div class="faq-item">
              <button class="faq-question">
                Funciona para corretor autônomo ou só para imobiliária?
                <span>+</span>
              </button>
              <div class="faq-answer">
                <p>
                  Funciona para os dois. Você pode começar sozinho e, conforme crescer, evoluir para um plano com mais usuários e recursos de equipe.
                </p>
              </div>
            </div>
            <div class="faq-item">
              <button class="faq-question">
                Consigo usar pelo celular?
                <span>+</span>
              </button>
              <div class="faq-answer">
                <p>
                  Sim. O painel foi desenhado para ser usado no computador e no celular, inclusive com recurso de tirar fotos e registrar tours virtuais direto do app.
                </p>
              </div>
            </div>
            <div class="faq-item">
              <button class="faq-question">
                Vocês publicam automaticamente em todos os portais?
                <span>+</span>
              </button>
              <div class="faq-answer">
                <p>
                  A plataforma é preparada para integrar com portais e redes por meio de recursos específicos (APIs, feeds, etc.). A disponibilidade pode variar conforme
                  o portal e o seu plano. A ideia é reduzir ao máximo o retrabalho na publicação.
                </p>
              </div>
            </div>
            <div class="faq-item">
              <button class="faq-question">
                Posso migrar meu site atual para ImobSites?
                <span>+</span>
              </button>
              <div class="faq-answer">
                <p>
                  Sim. Podemos orientar na migração dos seus domínios e, sempre que possível, aproveitar parte das informações já existentes.
                </p>
              </div>
            </div>
            <div class="faq-item">
              <button class="faq-question">
                Como vejo os resultados das minhas campanhas?
                <span>+</span>
              </button>
              <div class="faq-answer">
                <p>
                  O painel mostra relatórios com visitas aos imóveis, origem do tráfego e conversões, ajudando você a entender quais canais trazem os melhores leads.
                </p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="scroll-reveal" id="cta-final">
        <div class="container">
          <div class="cta-final">
            <h2>Organize hoje o que está travando suas vendas</h2>
            <p>
              Você não precisa de mais um app.
              Você precisa de uma plataforma que junte imóveis, clientes e resultados em um só lugar.
            </p>
            <a href="planos.php" class="btn btn-primary">👉 Ver planos e contratar agora</a>
            <p style="margin-top: 24px;">
              <a href="#demo" class="btn-secondary">Ou assistir à demonstração primeiro</a>
            </p>
          </div>
        </div>
      </section>
    </main>

    <footer>
      <div class="container">
        © <span id="year"></span> ImobSites. Todos os direitos reservados.
      </div>
    </footer>

    <script>
      const nav = document.getElementById("primary-nav");
      const menuToggle = document.querySelector(".menu-toggle");
      const header = document.querySelector("header");

      menuToggle.addEventListener("click", () => {
        const expanded = menuToggle.getAttribute("aria-expanded") === "true";
        menuToggle.setAttribute("aria-expanded", String(!expanded));
        nav.classList.toggle("open");
        menuToggle.classList.toggle("active");
      });

      nav.querySelectorAll("a").forEach((link) => {
        link.addEventListener("click", () => {
          nav.classList.remove("open");
          menuToggle.setAttribute("aria-expanded", "false");
          menuToggle.classList.remove("active");
        });
      });

      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            if (entry.isIntersecting) {
              entry.target.classList.add("visible");
              observer.unobserve(entry.target);
            }
          });
        },
        {
          threshold: 0.15,
        }
      );

      document.querySelectorAll(".scroll-reveal").forEach((el) => observer.observe(el));

      const headerHeight = header.offsetHeight;
      document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
        anchor.addEventListener("click", function (e) {
          const targetId = this.getAttribute("href");
          if (targetId.length > 1) {
            e.preventDefault();
            const targetEl = document.querySelector(targetId);
            if (targetEl) {
              const offsetTop = targetEl.getBoundingClientRect().top + window.scrollY - headerHeight + 10;
              window.scrollTo({
                top: offsetTop,
                behavior: "smooth",
              });
            }
          }
        });
      });

      const yearSpan = document.getElementById("year");
      yearSpan.textContent = new Date().getFullYear();

      const faqItems = document.querySelectorAll(".faq-item");
      faqItems.forEach((item) => {
        const question = item.querySelector(".faq-question");
        question.addEventListener("click", () => {
          const isActive = item.classList.contains("active");
          faqItems.forEach((q) => q.classList.remove("active"));
          if (!isActive) {
            item.classList.add("active");
          }
        });
      });
    </script>
  </body>
</html>

