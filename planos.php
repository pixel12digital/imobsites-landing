<?php
require __DIR__ . '/config.php';

$apiUrl = PANEL_API_BASE_URL . '/api/plans/public-list.php';
$plans = [];
$fetchError = null;

function getApiJson(string $endpoint, int $timeoutSeconds = 10)
{
    if (!function_exists('curl_init')) {
        error_log('[plans.debug] cURL extension is not available.');
        return null;
    }

    $ch = curl_init($endpoint);

    if ($ch === false) {
        error_log('[plans.debug] Failed to initialize cURL.');
        return null;
    }

    error_log('[plans.debug] Requesting endpoint: ' . $endpoint);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeoutSeconds,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_HTTPGET => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'User-Agent: ImobSites-Landing/1.0',
        ],
    ]);

    $response = curl_exec($ch);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $responseSize = is_string($response) ? strlen($response) : 0;

    curl_close($ch);

    if ($response === false) {
        error_log(sprintf('[plans.debug] cURL exec failed (errno %d): %s', $curlErrno, $curlError ?: 'unknown error'));
        return null;
    }

    if ($httpCode >= 400 || $httpCode === 0) {
        error_log(sprintf('[plans.debug] Unexpected HTTP status %d. Body preview: %s', $httpCode, substr($response, 0, 500)));
        return null;
    }

    error_log(sprintf('[plans.debug] Request completed (HTTP %d, %d bytes).', $httpCode, $responseSize));

    $decoded = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('[plans.debug] JSON decode failed: ' . json_last_error_msg());
        return null;
    }

    return $decoded;
}

if (!filter_var($apiUrl, FILTER_VALIDATE_URL)) {
    $fetchError = 'URL da API de planos inválida. Verifique a constante PANEL_API_BASE_URL.';
} else {
    error_log('[plans.debug] Tentando carregar planos de: ' . $apiUrl);
    $decoded = getApiJson($apiUrl);

    if ($decoded === null) {
        $fetchError = 'Não foi possível carregar os planos neste momento. Tente novamente em alguns instantes.';
    } elseif (isset($decoded['success']) && $decoded['success'] === true && isset($decoded['plans']) && is_array($decoded['plans'])) {
        $plans = $decoded['plans'];
        error_log(sprintf('[plans.debug] Loaded %d plans from API (plans key).', count($plans)));
    } elseif (isset($decoded['success']) && $decoded['success'] === false) {
        $fetchError = $decoded['message'] ?? 'Não foi possível carregar os planos.';
        error_log('[plans.debug] API returned error: ' . ($decoded['message'] ?? 'sem mensagem'));
    } elseif (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
        $plans = $decoded;
        error_log(sprintf('[plans.debug] Loaded %d plans from API (root array).', count($plans)));
    } else {
        error_log('[plans.debug] Formato inesperado da resposta de planos: ' . substr(json_encode($decoded), 0, 500));
        $fetchError = 'Recebemos uma resposta inesperada do servidor ao listar os planos.';
        $plans = [];
    }
}

function formatCurrency(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function formatBillingCycle(?string $cycle, ?int $months): string
{
    if (!empty($cycle)) {
        return ucwords(str_replace('_', ' ', $cycle));
    }

    if (!empty($months)) {
        return $months === 1 ? 'Mensal' : sprintf('A cada %d meses', $months);
    }

    return 'Ciclo personalizado';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Planos ImobSites — Escolha o melhor para seu negócio</title>
    <meta
      name="description"
      content="Conheça os planos ImobSites, compare funcionalidades e contrate o plano ideal para corretores e imobiliárias que querem vender mais com organização."
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
        --shadow-soft: 0 10px 30px rgba(15, 23, 42, 0.08);
        --shadow-hover: 0 16px 40px rgba(15, 23, 42, 0.12);
        --radius-base: 18px;
        --radius-pill: 999px;
        --max-width: 1100px;
        --header-height: 78px;
        --transition-base: 0.3s ease;
      }

      *,
      *::before,
      *::after {
        box-sizing: border-box;
      }

      body {
        margin: 0;
        font-family: "Plus Jakarta Sans", "Outfit", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        background-color: var(--color-bg);
        color: var(--color-text-main);
        line-height: 1.6;
      }

      a {
        text-decoration: none;
        color: inherit;
      }

      header {
        position: sticky;
        top: 0;
        z-index: 1000;
        background-color: var(--color-surface);
        box-shadow: 0 8px 20px rgba(28, 59, 90, 0.08);
      }

      .container {
        width: min(100%, calc(var(--max-width) + 48px));
        margin: 0 auto;
        padding: 0 24px;
      }

      .header-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        height: var(--header-height);
      }

      .logo img {
        height: 42px;
      }

      nav {
        display: flex;
        align-items: center;
        gap: 28px;
      }

      nav a {
        font-weight: 500;
        color: var(--color-text-muted);
        transition: color var(--transition-base);
      }

      nav a:hover,
      nav a.active {
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
      }

      main {
        padding: 100px 0 80px;
      }

      h1 {
        font-size: clamp(2.1rem, 4vw, 2.8rem);
        color: var(--color-primary);
        margin-bottom: 16px;
      }

      .hero-subtitle {
        color: var(--color-text-muted);
        font-size: 1.1rem;
        margin-bottom: 32px;
      }

      .plans-grid {
        display: grid;
        gap: 28px;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        margin-top: 40px;
      }

      .plan-card {
        background: var(--color-surface);
        border-radius: 26px;
        padding: 32px;
        box-shadow: var(--shadow-soft);
        display: grid;
        gap: 18px;
        border: 1px solid var(--color-border-soft);
        position: relative;
        transition: transform var(--transition-base), box-shadow var(--transition-base);
      }

      .plan-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-hover);
      }

      .plan-card.featured {
        border: 2px solid var(--color-primary);
        transform: translateY(-6px);
      }

      .plan-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: var(--radius-pill);
        padding: 8px 18px;
        font-weight: 600;
        font-size: 0.85rem;
        background: rgba(28, 59, 90, 0.12);
        color: var(--color-primary);
      }

      .plan-card.featured .plan-badge {
        background: rgba(247, 147, 30, 0.12);
        color: var(--color-accent);
      }

      .plan-price {
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--color-primary);
      }

      .plan-cycle {
        color: var(--color-text-muted);
        font-weight: 500;
      }

      .plan-description {
        color: var(--color-text-muted);
      }

      .plan-features {
        padding-left: 1.2rem;
        margin: 0;
        display: grid;
        gap: 10px;
        color: var(--color-text-muted);
      }

      .plan-actions {
        display: grid;
        gap: 12px;
      }

      .comparison {
        margin-top: 72px;
        background: linear-gradient(135deg, rgba(28, 59, 90, 0.08), rgba(28, 59, 90, 0));
        border-radius: 30px;
        padding: 48px;
        box-shadow: var(--shadow-soft);
      }

      .comparison h2 {
        margin-top: 0;
        color: var(--color-primary);
      }

      .comparison-grid {
        margin-top: 30px;
        display: grid;
        gap: 24px;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
      }

      .comparison-card {
        background: var(--color-surface);
        border-radius: var(--radius-base);
        padding: 24px;
        box-shadow: inset 0 0 0 1px rgba(28, 59, 90, 0.08);
      }

      .support-box {
        margin-top: 60px;
        text-align: center;
        color: var(--color-text-muted);
      }

      footer {
        padding: 40px 0;
        text-align: center;
        color: var(--color-text-muted);
        font-size: 0.9rem;
      }

      .alert {
        background: #fff5f5;
        color: #b4232c;
        border-radius: var(--radius-base);
        padding: 18px 24px;
        box-shadow: var(--shadow-soft);
        margin-top: 32px;
      }

      @media (max-width: 768px) {
        main {
          padding-top: 80px;
        }

        nav {
          display: none;
        }

        header {
          position: static;
        }
      }
    </style>
  </head>
  <body>
    <header>
      <div class="container header-inner">
        <a href="index.php" class="logo" aria-label="Voltar para a página inicial">
          <img src="assets/logo-imobsites.svg" alt="ImobSites" />
        </a>
        <nav>
          <a href="index.php">Início</a>
          <a href="planos.php" class="active">Planos</a>
          <a href="index.php#demo">Demonstração</a>
          <a href="index.php#faq">FAQ</a>
          <a href="checkout.php" class="btn btn-primary" style="padding: 12px 22px;">Contratar com consultor</a>
        </nav>
      </div>
    </header>

    <main>
      <section>
        <div class="container">
          <h1>Planos pensados para cada estágio da sua operação</h1>
          <p class="hero-subtitle">
            Receba um site profissional, CRM simples e suporte especializado. Escolha o plano que combina com seu momento e conclua a contratação em poucos cliques.
          </p>

          <?php if ($fetchError !== null): ?>
            <div class="alert"><?php echo htmlspecialchars($fetchError, ENT_QUOTES, 'UTF-8'); ?></div>
          <?php elseif (empty($plans)): ?>
            <div class="alert">Nenhum plano disponível no momento. Fale com a equipe ImobSites para receber uma proposta personalizada.</div>
          <?php else: ?>
            <div class="plans-grid">
              <?php foreach ($plans as $plan): ?>
                <?php
                  $code = $plan['code'] ?? null;
                  $name = $plan['name'] ?? 'Plano ImobSites';
                  $descriptionShort = $plan['description_short'] ?? null;
                  $pricePerMonth = isset($plan['price_per_month']) ? (float) $plan['price_per_month'] : null;
                  $totalAmount = isset($plan['total_amount']) ? (float) $plan['total_amount'] : null;
                  $billingCycle = formatBillingCycle($plan['billing_cycle'] ?? null, $plan['months'] ?? null);
                  $isFeatured = !empty($plan['is_featured']);
                  $featuresRaw = $plan['features'] ?? [];
                  $features = [];

                  if (is_array($featuresRaw)) {
                      $features = $featuresRaw;
                  } elseif (is_string($featuresRaw)) {
                      $decodedFeatures = json_decode($featuresRaw, true);
                      if (json_last_error() === JSON_ERROR_NONE && is_array($decodedFeatures)) {
                          $features = $decodedFeatures;
                      } else {
                          $features = array_filter(array_map('trim', explode(PHP_EOL, $featuresRaw)));
                      }
                  }
                ?>
                <article class="plan-card<?php echo $isFeatured ? ' featured' : ''; ?>">
                  <?php if ($isFeatured): ?>
                    <span class="plan-badge">Mais escolhido</span>
                  <?php elseif (!empty($plan['plan_label'])): ?>
                    <span class="plan-badge"><?php echo htmlspecialchars($plan['plan_label'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <?php endif; ?>

                  <h2><?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?></h2>

                  <?php if ($pricePerMonth !== null && $pricePerMonth > 0): ?>
                    <div class="plan-price"><?php echo formatCurrency($pricePerMonth); ?></div>
                    <div class="plan-cycle"><?php echo htmlspecialchars($billingCycle, ENT_QUOTES, 'UTF-8'); ?></div>
                  <?php elseif ($totalAmount !== null && $totalAmount > 0): ?>
                    <div class="plan-price"><?php echo formatCurrency($totalAmount); ?></div>
                    <div class="plan-cycle">Pagamento único</div>
                  <?php else: ?>
                    <div class="plan-price">Sob consulta</div>
                    <div class="plan-cycle">Entre em contato para condições exclusivas</div>
                  <?php endif; ?>

                  <?php if (!empty($descriptionShort)): ?>
                    <p class="plan-description"><?php echo htmlspecialchars($descriptionShort, ENT_QUOTES, 'UTF-8'); ?></p>
                  <?php endif; ?>

                  <?php if (!empty($features)): ?>
                    <ul class="plan-features">
                      <?php foreach ($features as $feature): ?>
                        <li><?php echo htmlspecialchars((string) $feature, ENT_QUOTES, 'UTF-8'); ?></li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>

                  <div class="plan-actions">
                    <?php if (!empty($code)): ?>
                      <a href="checkout.php?plan=<?php echo urlencode((string) $code); ?>" class="btn btn-primary">Contratar agora</a>
                      <a href="checkout.php?plan=<?php echo urlencode((string) $code); ?>&amp;mode=consultor" class="btn btn-secondary">Falar com especialista</a>
                    <?php else: ?>
                      <a href="checkout.php" class="btn btn-primary">Contratar agora</a>
                    <?php endif; ?>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <div class="comparison">
            <h2>O que todos os planos incluem</h2>
            <div class="comparison-grid">
              <div class="comparison-card">
                <h3>Site Imobiliário Otimizado</h3>
                <p>Layouts modernos, SEO-friendly, integração com WhatsApp e captação de leads em todas as páginas.</p>
              </div>
              <div class="comparison-card">
                <h3>CRM Para Corretores</h3>
                <p>Funil visual, registro de interações e foco em produtividade — do primeiro contato ao fechamento.</p>
              </div>
              <div class="comparison-card">
                <h3>Suporte Parceiro</h3>
                <p>Onboarding guiado, treinamentos e acompanhamento para sua operação escalar com segurança.</p>
              </div>
            </div>
          </div>

          <div class="support-box">
            <p>Tem dúvidas sobre qual plano contratar? Envie uma mensagem para nossa equipe e vamos indicar a melhor escolha para você.</p>
            <a href="checkout.php?mode=consultor" class="btn btn-secondary">Falar com consultor ImobSites</a>
          </div>
        </div>
      </section>
    </main>

    <footer>
      <div class="container">© <?php echo date('Y'); ?> ImobSites. Todos os direitos reservados.</div>
    </footer>
  </body>
</html>

