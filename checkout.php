<?php
require __DIR__ . '/config.php';

$planCode = isset($_GET['plan']) ? trim($_GET['plan']) : null;
$mode = isset($_GET['mode']) ? trim($_GET['mode']) : null;

$plansEndpoint = PANEL_API_BASE_URL . '/api/plans/public-list.php';
$orderEndpoint = PANEL_API_BASE_URL . '/api/orders/create.php';
$availablePlans = [];
$selectedPlan = null;
$fetchError = null;
$orderSuccess = null;
$orderError = null;

function fetchPlans(string $endpoint): array
{
    $httpOptions = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'Accept: application/json',
                'User-Agent: ImobSites-Landing/1.0',
            ],
            'timeout' => 10,
        ],
    ];

    $context = stream_context_create($httpOptions);
    $response = @file_get_contents($endpoint, false, $context);

    if ($response === false) {
        throw new RuntimeException('Não foi possível carregar os planos, tente novamente em instantes.');
    }

    $decoded = json_decode($response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Resposta inválida do servidor ao carregar os planos.');
    }

    if (isset($decoded['success']) && $decoded['success'] === true && isset($decoded['plans']) && is_array($decoded['plans'])) {
        return $decoded['plans'];
    }

    if (isset($decoded['success']) && $decoded['success'] === false) {
        $message = $decoded['message'] ?? 'Não foi possível carregar os planos.';
        throw new RuntimeException($message);
    }

    if (isset($decoded['data']) && is_array($decoded['data'])) {
        return $decoded['data'];
    }

    if (is_array($decoded)) {
        return $decoded;
    }

    throw new RuntimeException('Nenhum plano disponível no momento.');
}

try {
    if (!filter_var($plansEndpoint, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('URL da API de planos inválida. Ajuste a configuração.');
    }

    $availablePlans = fetchPlans($plansEndpoint);
    if (!empty($planCode)) {
        foreach ($availablePlans as $plan) {
            if (($plan['code'] ?? null) === $planCode) {
                $selectedPlan = $plan;
                break;
            }
        }
    }

    if ($selectedPlan === null && !empty($availablePlans)) {
        $selectedPlan = $availablePlans[0];
        $planCode = $selectedPlan['code'] ?? null;
    }
} catch (RuntimeException $exception) {
    $fetchError = $exception->getMessage();
}

// Trata submissão do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $fetchError === null) {
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerEmail = trim($_POST['customer_email'] ?? '');
    $customerPhone = trim($_POST['customer_phone'] ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $acceptTerms = isset($_POST['accept_terms']);
    $planCodePost = trim($_POST['plan_code'] ?? '');

    $errors = [];

    if ($customerName === '') {
        $errors[] = 'Informe seu nome completo.';
    }

    if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Informe um e-mail válido.';
    }

    if ($customerPhone === '') {
        $errors[] = 'Informe um telefone ou WhatsApp para contato.';
    }

    if (!$acceptTerms) {
        $errors[] = 'É necessário aceitar os termos de uso e política de privacidade.';
    }

    if ($planCodePost === '') {
        $errors[] = 'Selecione um plano para continuar.';
    }

    if (!isset($availablePlans) || empty($availablePlans)) {
        $errors[] = 'Não há planos disponíveis para contratação.';
    }

    if (!empty($errors)) {
        $orderError = implode(' ', $errors);
    } else {
        $payload = [
            'plan_code' => $planCodePost,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_phone' => $customerPhone,
            'company_name' => $companyName,
            'source' => 'landing_site',
        ];

        $httpOptions = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'User-Agent: ImobSites-Landing/1.0',
                ],
                'content' => json_encode($payload),
                'timeout' => 15,
            ],
        ];

        if (!filter_var($orderEndpoint, FILTER_VALIDATE_URL)) {
            $orderError = 'URL da API de pedidos inválida. Ajuste a configuração.';
        } else {
            $context = stream_context_create($httpOptions);
            $response = @file_get_contents($orderEndpoint, false, $context);

            if ($response === false) {
                $orderError = 'Não foi possível finalizar o pedido agora. Tente novamente em alguns instantes.';
            } else {
                $decodedResponse = json_decode($response, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    $orderError = 'Recebemos uma resposta inesperada ao criar o pedido.';
                } elseif (!isset($decodedResponse['success']) || $decodedResponse['success'] !== true) {
                    $orderError = $decodedResponse['message'] ?? 'Não foi possível finalizar seu pedido.';
                } else {
                    $orderId = $decodedResponse['order_id'] ?? null;
                    $paymentUrl = $decodedResponse['payment_url'] ?? null;
                    $message = $decodedResponse['message'] ?? 'Pedido criado com sucesso!';

                    $orderSuccess = [
                        'message' => $message,
                        'order_id' => $orderId,
                        'payment_url' => $paymentUrl,
                    ];
                }
            }
        }
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

function renderPlanOptions(array $plans, ?string $selectedCode): string
{
    $options = '';
    foreach ($plans as $plan) {
        $code = $plan['code'] ?? '';
        $name = $plan['name'] ?? 'Plano ImobSites';
        $billingCycle = formatBillingCycle($plan['billing_cycle'] ?? null, $plan['months'] ?? null);
        $pricePerMonth = isset($plan['price_per_month']) ? (float) $plan['price_per_month'] : null;
        $totalAmount = isset($plan['total_amount']) ? (float) $plan['total_amount'] : null;

        $priceLabel = 'Sob consulta';
        if ($pricePerMonth !== null && $pricePerMonth > 0) {
            $priceLabel = formatCurrency($pricePerMonth) . ' · ' . $billingCycle;
        } elseif ($totalAmount !== null && $totalAmount > 0) {
            $priceLabel = formatCurrency($totalAmount) . ' · Pagamento único';
        }

        $selected = ($code !== '' && $code === $selectedCode) ? ' selected' : '';
        $options .= sprintf(
            '<option value="%s"%s>%s — %s</option>',
            htmlspecialchars($code, ENT_QUOTES, 'UTF-8'),
            $selected,
            htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($priceLabel, ENT_QUOTES, 'UTF-8')
        );
    }

    return $options;
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Checkout ImobSites — Finalize sua contratação</title>
    <meta
      name="description"
      content="Finalize a contratação da plataforma ImobSites em poucos cliques. Informe seus dados, escolha o plano e receba o link de pagamento."
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
        --color-warning: #f97316;
        --shadow-soft: 0 10px 30px rgba(15, 23, 42, 0.08);
        --radius-base: 18px;
        --radius-pill: 999px;
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
        color: inherit;
      }

      header {
        background: var(--color-surface);
        box-shadow: 0 8px 20px rgba(28, 59, 90, 0.08);
      }

      .container {
        width: min(100%, 980px);
        margin: 0 auto;
        padding: 0 24px;
      }

      .header-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 0;
      }

      .logo img {
        height: 40px;
      }

      nav a {
        margin-left: 24px;
        font-weight: 500;
        color: var(--color-text-muted);
        text-decoration: none;
      }

      nav a:hover {
        color: var(--color-primary);
      }

      main {
        padding: 80px 0;
      }

      h1 {
        font-size: clamp(2.1rem, 4vw, 2.7rem);
        color: var(--color-primary);
        margin-bottom: 12px;
      }

      .subtitle {
        font-size: 1.1rem;
        color: var(--color-text-muted);
        margin-bottom: 36px;
      }

      .checkout-grid {
        display: grid;
        gap: 32px;
        grid-template-columns: 2fr 1fr;
        align-items: start;
      }

      .card {
        background: var(--color-surface);
        border-radius: 26px;
        padding: 32px;
        box-shadow: var(--shadow-soft);
      }

      form {
        display: grid;
        gap: 20px;
      }

      label {
        font-weight: 600;
        color: var(--color-primary);
        margin-bottom: 6px;
        display: block;
      }

      input,
      select,
      textarea {
        width: 100%;
        padding: 14px;
        border-radius: 16px;
        border: 1px solid var(--color-border-soft);
        background: #f8fafc;
        font-size: 1rem;
      }

      input:focus,
      select:focus,
      textarea:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(28, 59, 90, 0.12);
      }

      .checkbox-group {
        display: flex;
        align-items: flex-start;
        gap: 12px;
      }

      .checkbox-group input[type="checkbox"] {
        width: auto;
        margin-top: 4px;
      }

      .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        border-radius: var(--radius-pill);
        padding: 16px 28px;
        font-weight: 600;
        cursor: pointer;
        transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
        border: none;
      }

      .btn-primary {
        background: var(--color-accent);
        color: #fff;
        box-shadow: 0 12px 30px rgba(247, 147, 30, 0.25);
      }

      .btn-primary:hover {
        transform: translateY(-2px);
        background: #e6850f;
        box-shadow: 0 18px 36px rgba(247, 147, 30, 0.35);
      }

      .btn-secondary {
        background: rgba(28, 59, 90, 0.08);
        color: var(--color-primary);
      }

      .alert {
        padding: 18px 20px;
        border-radius: var(--radius-base);
        font-weight: 500;
      }

      .alert-error {
        background: #fff5f5;
        color: #b4232c;
      }

      .alert-success {
        background: #ecfdf3;
        color: #027a48;
      }

      .plan-summary h2 {
        margin-top: 0;
        color: var(--color-primary);
      }

      .plan-price {
        margin-top: 12px;
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--color-primary);
      }

      .plan-cycle {
        color: var(--color-text-muted);
      }

      .plan-features {
        margin-top: 20px;
        padding-left: 1.2rem;
        color: var(--color-text-muted);
        display: grid;
        gap: 8px;
      }

      footer {
        padding: 40px 0;
        text-align: center;
        color: var(--color-text-muted);
        font-size: 0.9rem;
      }

      @media (max-width: 900px) {
        .checkout-grid {
          grid-template-columns: 1fr;
        }
      }

      @media (max-width: 720px) {
        nav {
          display: none;
        }

        .container {
          padding: 0 18px;
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
          <a href="planos.php">Planos</a>
          <a href="index.php#demo">Demonstração</a>
          <a href="index.php#faq">FAQ</a>
          <a href="mailto:contato@imobsites.com" class="btn btn-secondary" style="padding: 12px 22px;">Falar com consultor</a>
        </nav>
      </div>
    </header>

    <main>
      <div class="container">
        <h1>Quase lá! Vamos ativar seu ImobSites</h1>
        <p class="subtitle">Revise o plano escolhido, insira seus dados e finalize a contratação com pagamento seguro.</p>

        <?php if ($fetchError !== null): ?>
          <div class="alert alert-error"><?php echo htmlspecialchars($fetchError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php elseif ($orderError !== null): ?>
          <div class="alert alert-error"><?php echo htmlspecialchars($orderError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php elseif ($orderSuccess !== null): ?>
          <div class="alert alert-success">
            <p><?php echo htmlspecialchars($orderSuccess['message'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php if (!empty($orderSuccess['payment_url'])): ?>
              <p><a href="<?php echo htmlspecialchars($orderSuccess['payment_url'], ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-primary" target="_blank" rel="noopener">Acessar link de pagamento</a></p>
            <?php endif; ?>
            <?php if (!empty($orderSuccess['order_id'])): ?>
              <p>Código do pedido: <?php echo htmlspecialchars((string) $orderSuccess['order_id'], ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <p>Um e-mail com os próximos passos foi enviado para você. Nossa equipe pode entrar em contato para garantir o onboarding perfeito.</p>
          </div>
        <?php endif; ?>

        <?php if ($fetchError === null && $orderSuccess === null): ?>
          <div class="alert alert-error" data-checkout-error style="display:none;">
            Não foi possível finalizar o pedido agora. Tente novamente em alguns instantes.
          </div>

          <div class="checkout-grid">
            <div class="card">
              <form id="checkout-form" method="post" novalidate>
                <input type="hidden" name="plan_code" value="<?php echo htmlspecialchars((string) $planCode, ENT_QUOTES, 'UTF-8'); ?>" />

                <div>
                  <label for="plan">Plano</label>
                  <select id="plan" name="plan_code" onchange="window.location.href='checkout.php?plan=' + encodeURIComponent(this.value);">
                    <?php echo renderPlanOptions($availablePlans, $planCode); ?>
                  </select>
                </div>

                <div>
                  <label for="customer_name">Seu nome completo</label>
                  <input type="text" id="customer_name" name="customer_name" placeholder="Ex.: Ana Correia" required />
                </div>

                <div>
                  <label for="customer_email">E-mail</label>
                  <input type="email" id="customer_email" name="customer_email" placeholder="nome@empresa.com" required />
                </div>

                <div>
                  <label for="customer_phone">Telefone / WhatsApp</label>
                  <input type="text" id="customer_phone" name="customer_phone" placeholder="(11) 99999-9999" required />
                </div>

                <div>
                  <label for="company_name">Nome da imobiliária / empresa (opcional)</label>
                  <input type="text" id="company_name" name="company_name" placeholder="Nome fantasia" />
                </div>

                <div class="checkbox-group">
                  <input type="checkbox" id="accept_terms" name="accept_terms" value="1" required />
                  <label for="accept_terms" style="font-weight: 500; color: var(--color-text-muted);">
                    Eu li e concordo com os <a href="index.php#faq">termos de uso</a> e <a href="index.php#faq">política de privacidade</a>.
                  </label>
                </div>

                <div style="display: grid; gap: 12px;">
                  <button type="submit" class="btn btn-primary">Finalizar contratação</button>
                  <?php if ($mode === 'consultor'): ?>
                    <a href="mailto:contato@imobsites.com?subject=Quero%20falar%20com%20um%20consultor%20ImobSites" class="btn btn-secondary">Prefiro falar com um consultor</a>
                  <?php else: ?>
                    <a href="checkout.php?mode=consultor<?php echo $planCode ? '&amp;plan=' . urlencode($planCode) : ''; ?>" class="btn btn-secondary">Preciso de ajuda antes de contratar</a>
                  <?php endif; ?>
                </div>
              </form>
            </div>

            <?php if ($selectedPlan !== null): ?>
              <?php
                $selectedFeaturesRaw = $selectedPlan['features'] ?? [];
                $selectedFeatures = [];
                if (is_array($selectedFeaturesRaw)) {
                    $selectedFeatures = $selectedFeaturesRaw;
                } elseif (is_string($selectedFeaturesRaw)) {
                    $decodedFeatures = json_decode($selectedFeaturesRaw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedFeatures)) {
                        $selectedFeatures = $decodedFeatures;
                    } else {
                        $selectedFeatures = array_filter(array_map('trim', explode(PHP_EOL, $selectedFeaturesRaw)));
                    }
                }
              ?>
              <aside class="card plan-summary">
                <h2><?php echo htmlspecialchars($selectedPlan['name'] ?? 'Plano ImobSites', ENT_QUOTES, 'UTF-8'); ?></h2>
                <?php
                  $pricePerMonth = isset($selectedPlan['price_per_month']) ? (float) $selectedPlan['price_per_month'] : null;
                  $totalAmount = isset($selectedPlan['total_amount']) ? (float) $selectedPlan['total_amount'] : null;
                  $billingCycle = formatBillingCycle($selectedPlan['billing_cycle'] ?? null, $selectedPlan['months'] ?? null);
                ?>
                <?php if ($pricePerMonth !== null && $pricePerMonth > 0): ?>
                  <div class="plan-price"><?php echo formatCurrency($pricePerMonth); ?></div>
                  <div class="plan-cycle"><?php echo htmlspecialchars($billingCycle, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php elseif ($totalAmount !== null && $totalAmount > 0): ?>
                  <div class="plan-price"><?php echo formatCurrency($totalAmount); ?></div>
                  <div class="plan-cycle">Pagamento único</div>
                <?php else: ?>
                  <div class="plan-price">Sob consulta</div>
                <?php endif; ?>

                <?php if (!empty($selectedPlan['description_short'])): ?>
                  <p style="margin-top: 12px; color: var(--color-text-muted);">
                    <?php echo htmlspecialchars($selectedPlan['description_short'], ENT_QUOTES, 'UTF-8'); ?>
                  </p>
                <?php endif; ?>

                <?php if (!empty($selectedFeatures)): ?>
                  <ul class="plan-features">
                    <?php foreach ($selectedFeatures as $feature): ?>
                      <li><?php echo htmlspecialchars((string) $feature, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </aside>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </main>

    <footer>
      <div class="container">© <?php echo date('Y'); ?> ImobSites. Todos os direitos reservados.</div>
    </footer>

    <script>
      // Função utilitária para exibir erro no checkout
      function mostrarErroCheckout(msg) {
        const el = document.querySelector('[data-checkout-error]');
        if (!el) {
          alert(msg); // fallback se não achar o elemento
          return;
        }

        el.textContent = msg;
        el.style.display = 'block';
      }

      // Função para limpar erro
      function limparErroCheckout() {
        const el = document.querySelector('[data-checkout-error]');
        if (el) {
          el.textContent = '';
          el.style.display = 'none';
        }
      }

      // Handler único de submit do formulário
      document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('checkout-form');
        if (!form) {
          console.error('[checkout.debug] Formulário #checkout-form não encontrado.');
          return;
        }

        form.addEventListener('submit', async function (event) {
          event.preventDefault();
          limparErroCheckout();
          console.log('[checkout.debug] Submit disparado');

          // Coletar campos
          const planSelectElement = form.querySelector('select[name="plan_code"]');
          const planHiddenInput = form.querySelector('input[type="hidden"][name="plan_code"]');
          const nomeInput = form.querySelector('[name="customer_name"]');
          const emailInput = form.querySelector('[name="customer_email"]');
          const whatsappInput = form.querySelector('[name="customer_phone"]');
          const empresaInput = form.querySelector('[name="company_name"]');
          const termosCheckbox = form.querySelector('[name="accept_terms"]');

          // Obter o código do plano (prioriza o select se existir, senão usa o hidden input)
          let planCode = null;
          if (planSelectElement && planSelectElement.value) {
            planCode = planSelectElement.value;
          } else if (planHiddenInput && planHiddenInput.value) {
            planCode = planHiddenInput.value;
          }

          const customerName = nomeInput ? nomeInput.value.trim() : '';
          const customerEmail = emailInput ? emailInput.value.trim() : '';
          const customerWhatsapp = whatsappInput ? whatsappInput.value.trim() : '';
          const companyName = empresaInput ? empresaInput.value.trim() : '';
          const termosAceitos = termosCheckbox ? termosCheckbox.checked : false;

          console.log('[checkout.debug] Dados coletados:', {
            planCode,
            customerName,
            customerEmail,
            customerWhatsapp,
            companyName,
            termosAceitos
          });

          // Validação básica
          if (!planCode) {
            mostrarErroCheckout('Selecione um plano para continuar.');
            console.warn('[checkout.debug] Validação falhou: planCode vazio');
            return;
          }

          if (!customerName || !customerEmail || !customerWhatsapp) {
            mostrarErroCheckout('Preencha seu nome, e-mail e telefone/WhatsApp para continuar.');
            console.warn('[checkout.debug] Validação falhou: campos obrigatórios vazios');
            return;
          }

          // Validação básica de e-mail
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(customerEmail)) {
            mostrarErroCheckout('Informe um e-mail válido.');
            console.warn('[checkout.debug] Validação falhou: e-mail inválido');
            return;
          }

          if (!termosAceitos) {
            mostrarErroCheckout('Você precisa aceitar os termos de uso e a política de privacidade para finalizar a contratação.');
            console.warn('[checkout.debug] Validação falhou: termos não aceitos');
            return;
          }

          const payload = {
            plan_code: planCode,
            customer_name: customerName,
            customer_email: customerEmail,
            customer_whatsapp: customerWhatsapp,
            company_name: companyName
          };

          console.log('[checkout.debug] Enviando requisição para criar pedido...', payload);

          try {
            const apiUrl = '<?php echo PANEL_API_BASE_URL; ?>/api/orders/create.php';
            console.log('[checkout.debug] URL da API:', apiUrl);

            const response = await fetch(apiUrl, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify(payload)
            });

            let data = null;
            try {
              data = await response.json();
            } catch (e) {
              console.error('[checkout.debug] Erro ao parsear JSON da resposta:', e);
            }

            console.log('[checkout.debug] Resposta da API de pedido:', {
              status: response.status,
              data
            });

            if (!response.ok || !data) {
              mostrarErroCheckout('Não foi possível finalizar o pedido agora. Tente novamente em alguns instantes.');
              return;
            }

            if (data.success === true && data.payment_url) {
              // Sucesso: redireciona para o payment_url do Asaas
              console.log('[checkout.debug] Pedido criado com sucesso, redirecionando para:', data.payment_url);
              window.location.href = data.payment_url;
              return;
            }

            if (data.success === false && data.message) {
              // Erro de negócio vindo da API (ex.: plano inválido, erro Asaas, etc.)
              mostrarErroCheckout(data.message);
              return;
            }

            // Qualquer outra situação inesperada
            mostrarErroCheckout('Recebemos uma resposta inesperada do servidor ao finalizar o pedido.');
          } catch (error) {
            console.error('[checkout.debug] Erro de rede ao criar pedido:', error);
            mostrarErroCheckout('Não foi possível finalizar o pedido agora. Verifique sua conexão e tente novamente.');
          }
        });
      });
    </script>
  </body>
</html>

