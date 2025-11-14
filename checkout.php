<?php
require __DIR__ . '/config.php';

$planCode = isset($_GET['plan']) ? trim($_GET['plan']) : null;
$mode = isset($_GET['mode']) ? trim($_GET['mode']) : null;

$plansEndpoint = rtrim(PANEL_API_BASE_URL, '/') . '/api/plans/public-list.php';
$orderEndpoint = rtrim(PANEL_API_BASE_URL, '/') . '/api/orders/create.php';
$availablePlans = [];
$selectedPlan = null;
$fetchError = null;
$orderSuccess = null;
$orderError = null;
$paymentMethod = null;
$checkoutSuccess = false;

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
    $planCodePost = trim($_POST['plan_code'] ?? '');
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerEmail = trim($_POST['customer_email'] ?? '');
    $customerWhatsapp = trim($_POST['customer_whatsapp'] ?? '');
    $customerCpfCnpj = trim($_POST['customer_cpf_cnpj'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'pix');
    $acceptTerms = isset($_POST['accept_terms']);

    $errors = [];

    if ($planCodePost === '') {
        $errors[] = 'Selecione um plano para continuar.';
    }

    if ($customerName === '') {
        $errors[] = 'Informe seu nome completo.';
    }

    if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Informe um e-mail válido.';
    }

    if ($customerWhatsapp === '') {
        $errors[] = 'Informe um telefone ou WhatsApp para contato.';
    }

    if ($customerCpfCnpj === '') {
        $errors[] = 'Informe seu CPF ou CNPJ.';
    }

    if (!in_array($paymentMethod, ['pix', 'credit_card'], true)) {
        $errors[] = 'Selecione uma forma de pagamento válida.';
    }

    if (!$acceptTerms) {
        $errors[] = 'É necessário aceitar os termos de uso e política de privacidade.';
    }

    // Validações específicas para cartão
    if ($paymentMethod === 'credit_card') {
        $cardHolderName = trim($_POST['card_holder_name'] ?? '');
        $cardNumber = trim($_POST['card_number'] ?? '');
        $cardExpiryMonth = trim($_POST['card_expiry_month'] ?? '');
        $cardExpiryYear = trim($_POST['card_expiry_year'] ?? '');
        $cardCcv = trim($_POST['card_ccv'] ?? '');
        $cardPostalCode = trim($_POST['card_postal_code'] ?? '');
        $cardAddressNumber = trim($_POST['card_address_number'] ?? '');

        if ($cardHolderName === '') {
            $errors[] = 'Informe o nome do titular do cartão.';
        }
        if (preg_replace('/\D+/', '', $cardNumber) === '') {
            $errors[] = 'Informe o número do cartão.';
        }
        if ($cardExpiryMonth === '' || $cardExpiryYear === '') {
            $errors[] = 'Informe a data de validade do cartão.';
        }
        if ($cardCcv === '') {
            $errors[] = 'Informe o código de segurança (CCV) do cartão.';
        }
        if (preg_replace('/\D+/', '', $cardPostalCode) === '') {
            $errors[] = 'Informe o CEP do titular do cartão.';
        }
        if ($cardAddressNumber === '') {
            $errors[] = 'Informe o número do endereço do titular do cartão.';
        }
    }

    if (!empty($errors)) {
        $orderError = implode(' ', $errors);
    } else {
        // Montar payload base
        $payload = [
            'plan_code' => $planCodePost,
            'customer_name' => $customerName,
            'customer_email' => $customerEmail,
            'customer_whatsapp' => preg_replace('/\D+/', '', $customerWhatsapp),
            'customer_cpf_cnpj' => preg_replace('/\D+/', '', $customerCpfCnpj),
            'payment_method' => $paymentMethod,
        ];

        // Adicionar dados do cartão se necessário
        if ($paymentMethod === 'credit_card') {
            $payload['payment_installments'] = (int)($_POST['payment_installments'] ?? 1);

            $payload['card'] = [
                'holderName' => trim($_POST['card_holder_name'] ?? ''),
                'number' => preg_replace('/\D+/', '', $_POST['card_number'] ?? ''),
                'expiryMonth' => $_POST['card_expiry_month'] ?? '',
                'expiryYear' => $_POST['card_expiry_year'] ?? '',
                'ccv' => $_POST['card_ccv'] ?? '',
                'postalCode' => preg_replace('/\D+/', '', $_POST['card_postal_code'] ?? ''),
                'addressNumber' => trim($_POST['card_address_number'] ?? ''),
                'cpfCnpj' => preg_replace('/\D+/', '', $customerCpfCnpj),
                'email' => $customerEmail,
                'mobilePhone' => preg_replace('/\D+/', '', $customerWhatsapp),
            ];
        }

        // Fazer chamada à API usando cURL
        if (!function_exists('curl_init')) {
            $orderError = 'Não foi possível processar seu pedido. Serviço temporariamente indisponível.';
        } elseif (!filter_var($orderEndpoint, FILTER_VALIDATE_URL)) {
            $orderError = 'URL da API de pedidos inválida. Ajuste a configuração.';
        } else {
            $ch = curl_init($orderEndpoint);

            if ($ch === false) {
                $orderError = 'Não foi possível processar seu pedido no momento. Tente novamente em alguns instantes.';
            } else {
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => json_encode($payload),
                    CURLOPT_HTTPHEADER => [
                        'Content-Type: application/json',
                        'Accept: application/json',
                        'User-Agent: ImobSites-Landing/1.0',
                    ],
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => true,
                ]);

                $response = curl_exec($ch);
                $curlError = curl_error($ch);
                $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($response === false || !empty($curlError)) {
                    $orderError = 'Não foi possível processar seu pedido no momento. Tente novamente em alguns instantes.';
                } else {
                    $data = json_decode($response, true);

                    if (!is_array($data)) {
                        $orderError = 'Não foi possível processar seu pedido no momento. Tente novamente em alguns instantes.';
                    } elseif (isset($data['success']) && $data['success'] === true) {
                        $orderId = $data['order_id'] ?? null;
                        $paymentMethodFromApi = $data['payment_method'] ?? $paymentMethod;
                        $paymentUrl = $data['payment_url'] ?? null;
                        $pixPayload = $data['pix_payload'] ?? null;
                        $pixQrCode = $data['pix_qr_code_image'] ?? null;
                        $boletoLine = $data['boleto_line'] ?? null;
                        $status = $data['status'] ?? null;

                        $orderSuccess = [
                            'order_id' => $orderId,
                            'payment_url' => $paymentUrl,
                            'payment_method' => $paymentMethodFromApi,
                            'pix_payload' => $pixPayload,
                            'pix_qr_code_image' => $pixQrCode,
                            'boleto_line' => $boletoLine,
                            'status' => $status,
                        ];
                        $checkoutSuccess = true;
                    } else {
                        $orderError = $data['message'] ?? 'Não foi possível finalizar seu pedido.';
                    }
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

      .radio-group {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
      }

      .radio-option {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 18px;
        border: 2px solid var(--color-border-soft);
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        flex: 1;
        min-width: 120px;
      }

      .radio-option:hover {
        border-color: var(--color-primary);
        background: rgba(28, 59, 90, 0.05);
      }

      .radio-option input[type="radio"] {
        width: auto;
        margin: 0;
      }

      .radio-option input[type="radio"]:checked + label {
        font-weight: 600;
        color: var(--color-primary);
      }

      .radio-option:has(input[type="radio"]:checked) {
        border-color: var(--color-primary);
        background: rgba(28, 59, 90, 0.08);
      }

      .card-fields {
        display: grid;
        gap: 20px;
        padding: 20px;
        background: rgba(28, 59, 90, 0.03);
        border-radius: 16px;
        border: 1px solid var(--color-border-soft);
        margin-top: 8px;
      }

      .card-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
      }

      .card-row-full {
        grid-template-columns: 1fr;
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
        text-decoration: none;
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
        margin-bottom: 24px;
      }

      .alert-error {
        background: #fff5f5;
        color: #b4232c;
      }

      .alert-success {
        background: #ecfdf3;
        color: #027a48;
      }

      .alert-success p {
        margin: 0 0 12px 0;
      }

      .alert-success p:last-child {
        margin-bottom: 0;
      }

      .checkout-success-container {
        margin-bottom: 32px;
      }

      .checkout-pix-section,
      .checkout-boleto-section,
      .checkout-card-section {
        margin-top: 24px;
      }

      .checkout-pix-section h3,
      .checkout-boleto-section h3,
      .checkout-card-section h3 {
        margin-top: 0;
        color: var(--color-primary);
      }

      .checkout-pix-qr {
        text-align: center;
        margin: 24px 0;
        padding: 20px;
        background: #f8fafc;
        border-radius: var(--radius-base);
        border: 1px solid var(--color-border-soft);
      }

      .checkout-pix-qr img {
        max-width: 280px;
        width: 100%;
        height: auto;
        margin: 0 auto;
        display: block;
      }

      .checkout-pix-copy {
        display: grid;
        gap: 12px;
        margin-top: 16px;
      }

      .checkout-pix-copy textarea {
        font-family: monospace;
        font-size: 0.9rem;
        resize: vertical;
        min-height: 80px;
      }

      .checkout-boleto-line {
        display: grid;
        gap: 12px;
        margin-top: 16px;
      }

      .checkout-boleto-line input {
        font-family: monospace;
        font-size: 1rem;
        text-align: center;
        letter-spacing: 1px;
      }

      .btn-sm {
        padding: 12px 20px;
        font-size: 0.95rem;
      }

      .btn-outline-success {
        background: transparent;
        border: 2px solid var(--color-success);
        color: var(--color-success);
      }

      .btn-outline-success:hover {
        background: var(--color-success);
        color: #fff;
        transform: translateY(-2px);
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

        .card-row {
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

        .radio-group {
          flex-direction: column;
        }

        .radio-option {
          min-width: auto;
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
        <?php elseif (!empty($checkoutSuccess) && $checkoutSuccess === true && $orderSuccess !== null): ?>
          <div class="checkout-success-container">
            <div class="alert alert-success">
              <h2 style="margin-top: 0; margin-bottom: 12px;">Pedido criado com sucesso!</h2>
              <p>Enviamos os detalhes do pagamento para o seu e-mail.</p>
              <p>Você pode usar as opções abaixo para concluir o pagamento agora.</p>
              <?php if (!empty($orderSuccess['order_id'])): ?>
                <p style="margin-top: 16px; margin-bottom: 0;"><strong>Nº do pedido:</strong> <?php echo htmlspecialchars((string) $orderSuccess['order_id'], ENT_QUOTES, 'UTF-8'); ?></p>
              <?php endif; ?>
            </div>

            <?php
            $paymentMethod = $orderSuccess['payment_method'] ?? null;
            $orderId = $orderSuccess['order_id'] ?? null;
            $paymentUrl = $orderSuccess['payment_url'] ?? null;
            $pixPayload = $orderSuccess['pix_payload'] ?? null;
            $pixQrCode = $orderSuccess['pix_qr_code_image'] ?? null;
            $boletoLine = $orderSuccess['boleto_line'] ?? null;
            ?>

            <?php if ($paymentMethod === 'pix'): ?>
              <div class="checkout-pix-section card">
                <h3>Pague com Pix</h3>
                <p>Abra o app do seu banco e escaneie o QR Code abaixo:</p>

                <?php if (!empty($pixQrCode)): ?>
                  <div class="checkout-pix-qr">
                    <img
                      src="<?php echo strpos($pixQrCode, 'data:image') === 0 ? htmlspecialchars($pixQrCode, ENT_QUOTES, 'UTF-8') : 'data:image/png;base64,' . htmlspecialchars($pixQrCode, ENT_QUOTES, 'UTF-8'); ?>"
                      alt="QR Code Pix"
                    >
                  </div>
                <?php endif; ?>

                <?php if (!empty($pixPayload)): ?>
                  <p style="margin-top: 24px;">Se preferir, copie o código Pix "copia e cola":</p>
                  <div class="checkout-pix-copy">
                    <textarea id="pixPayload" readonly rows="3"><?php echo htmlspecialchars($pixPayload, ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <button type="button" class="btn btn-sm" onclick="copyPixPayload()">Copiar código Pix</button>
                  </div>
                <?php endif; ?>

                <?php if (!empty($paymentUrl)): ?>
                  <p class="checkout-pix-link" style="margin-top: 20px;">
                    <a href="<?php echo htmlspecialchars($paymentUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="btn btn-secondary">
                      Ver página de pagamento Pix
                    </a>
                  </p>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <?php if ($paymentMethod === 'boleto'): ?>
              <div class="checkout-boleto-section card">
                <h3>Pague com boleto</h3>
                <p>Use o código abaixo no seu internet banking ou app:</p>

                <?php if (!empty($boletoLine)): ?>
                  <div class="checkout-boleto-line">
                    <input
                      type="text"
                      id="boletoLine"
                      readonly
                      value="<?php echo htmlspecialchars($boletoLine, ENT_QUOTES, 'UTF-8'); ?>"
                    >
                    <button type="button" class="btn btn-sm" onclick="copyBoletoLine()">Copiar linha digitável</button>
                  </div>
                <?php endif; ?>

                <?php if (!empty($paymentUrl)): ?>
                  <p class="checkout-boleto-link" style="margin-top: 20px;">
                    <a href="<?php echo htmlspecialchars($paymentUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="btn btn-secondary">
                      Ver boleto completo
                    </a>
                  </p>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <?php if ($paymentMethod === 'credit_card'): ?>
              <div class="checkout-card-section card">
                <h3>Pagamento com cartão realizado</h3>
                <p>Estamos processando a confirmação junto à administradora. Você receberá um e-mail com as instruções de acesso à plataforma.</p>

                <?php if (!empty($orderId)): ?>
                  <p style="margin-top: 16px;"><strong>Nº do pedido:</strong> <?php echo htmlspecialchars((string) $orderId, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <?php if (!empty($paymentUrl)): ?>
                  <p class="checkout-card-link" style="margin-top: 20px;">
                    <a href="<?php echo htmlspecialchars($paymentUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="btn btn-secondary">
                      Ver detalhes do pagamento
                    </a>
                  </p>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <?php
            $waNumber = defined('SUPPORT_WHATSAPP_NUMBER') ? SUPPORT_WHATSAPP_NUMBER : '5547999999999';
            $waText = "Olá, acabei de fazer um pedido no ImobSites e gostaria de confirmar o pagamento. Nº do pedido: " . ($orderId ?: 'N/A');
            $waUrl = "https://wa.me/{$waNumber}?text=" . urlencode($waText);
            ?>
            <div class="checkout-support-whatsapp" style="margin-top: 24px; text-align: center;">
              <a href="<?php echo htmlspecialchars($waUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener" class="btn btn-outline-success">
                Falar com suporte pelo WhatsApp
              </a>
            </div>
          </div>
        <?php elseif ($orderError !== null): ?>
          <div class="alert alert-error"><?php echo htmlspecialchars($orderError, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($fetchError === null && $orderSuccess === null): ?>
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
                  <input type="text" id="customer_name" name="customer_name" placeholder="Ex.: Ana Correia" value="<?php echo htmlspecialchars($_POST['customer_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required />
                </div>

                <div>
                  <label for="customer_email">E-mail</label>
                  <input type="email" id="customer_email" name="customer_email" placeholder="nome@empresa.com" value="<?php echo htmlspecialchars($_POST['customer_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required />
                </div>

                <div>
                  <label for="customer_whatsapp">Telefone / WhatsApp</label>
                  <input type="text" id="customer_whatsapp" name="customer_whatsapp" placeholder="(11) 99999-9999" value="<?php echo htmlspecialchars($_POST['customer_whatsapp'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required />
                </div>

                <div>
                  <label for="customer_cpf_cnpj">CPF ou CNPJ</label>
                  <input type="text" id="customer_cpf_cnpj" name="customer_cpf_cnpj" placeholder="000.000.000-00 ou 00.000.000/0000-00" value="<?php echo htmlspecialchars($_POST['customer_cpf_cnpj'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required />
                </div>

                <div>
                  <label>Forma de pagamento</label>
                  <div class="radio-group">
                    <div class="radio-option">
                      <input type="radio" id="payment_pix" name="payment_method" value="pix" <?php echo (!isset($_POST['payment_method']) || ($_POST['payment_method'] ?? 'pix') === 'pix') ? 'checked' : ''; ?> required />
                      <label for="payment_pix">PIX</label>
                    </div>
                    <div class="radio-option">
                      <input type="radio" id="payment_card" name="payment_method" value="credit_card" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'credit_card') ? 'checked' : ''; ?> />
                      <label for="payment_card">Cartão de Crédito</label>
                    </div>
                  </div>
                </div>

                <div id="card-fields" class="card-fields" style="display: <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'credit_card') ? 'grid' : 'none'; ?>;">
                  <div>
                    <label for="card_holder_name">Nome do titular do cartão</label>
                    <input type="text" id="card_holder_name" name="card_holder_name" placeholder="Nome como está no cartão" value="<?php echo htmlspecialchars($_POST['card_holder_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                  </div>

                  <div>
                    <label for="card_number">Número do cartão</label>
                    <input type="text" id="card_number" name="card_number" placeholder="0000 0000 0000 0000" maxlength="19" value="<?php echo htmlspecialchars($_POST['card_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                  </div>

                  <div class="card-row">
                    <div>
                      <label for="card_expiry_month">Mês</label>
                      <select id="card_expiry_month" name="card_expiry_month">
                        <option value="">Mês</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                          <option value="<?php echo sprintf('%02d', $i); ?>" <?php echo (isset($_POST['card_expiry_month']) && $_POST['card_expiry_month'] == sprintf('%02d', $i)) ? 'selected' : ''; ?>>
                            <?php echo sprintf('%02d', $i); ?>
                          </option>
                        <?php endfor; ?>
                      </select>
                    </div>
                    <div>
                      <label for="card_expiry_year">Ano</label>
                      <select id="card_expiry_year" name="card_expiry_year">
                        <option value="">Ano</option>
                        <?php
                        $currentYear = (int)date('Y');
                        for ($i = $currentYear; $i <= $currentYear + 10; $i++):
                        ?>
                          <option value="<?php echo $i; ?>" <?php echo (isset($_POST['card_expiry_year']) && $_POST['card_expiry_year'] == $i) ? 'selected' : ''; ?>>
                            <?php echo $i; ?>
                          </option>
                        <?php endfor; ?>
                      </select>
                    </div>
                  </div>

                  <div class="card-row">
                    <div>
                      <label for="card_ccv">Código de segurança (CCV)</label>
                      <input type="text" id="card_ccv" name="card_ccv" placeholder="123" maxlength="4" value="<?php echo htmlspecialchars($_POST['card_ccv'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                    </div>
                    <div>
                      <label for="payment_installments">Parcelas</label>
                      <select id="payment_installments" name="payment_installments">
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                          <option value="<?php echo $i; ?>" <?php echo (isset($_POST['payment_installments']) && $_POST['payment_installments'] == $i) ? 'selected' : ($i === 1 ? 'selected' : ''); ?>>
                            <?php echo $i === 1 ? 'À vista' : $i . 'x'; ?>
                          </option>
                        <?php endfor; ?>
                      </select>
                    </div>
                  </div>

                  <div>
                    <label for="card_postal_code">CEP do titular</label>
                    <input type="text" id="card_postal_code" name="card_postal_code" placeholder="00000-000" value="<?php echo htmlspecialchars($_POST['card_postal_code'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                  </div>

                  <div>
                    <label for="card_address_number">Número do endereço</label>
                    <input type="text" id="card_address_number" name="card_address_number" placeholder="123" value="<?php echo htmlspecialchars($_POST['card_address_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
                  </div>
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
      document.addEventListener('DOMContentLoaded', function() {
        const paymentPix = document.getElementById('payment_pix');
        const paymentCard = document.getElementById('payment_card');
        const cardFields = document.getElementById('card-fields');

        function toggleCardFields() {
          if (paymentCard.checked) {
            cardFields.style.display = 'grid';
            // Tornar campos obrigatórios
            cardFields.querySelectorAll('input, select').forEach(function(field) {
              field.setAttribute('required', 'required');
            });
          } else {
            cardFields.style.display = 'none';
            // Remover obrigatoriedade
            cardFields.querySelectorAll('input, select').forEach(function(field) {
              field.removeAttribute('required');
            });
          }
        }

        paymentPix.addEventListener('change', toggleCardFields);
        paymentCard.addEventListener('change', toggleCardFields);

        // Máscaras
        const whatsappInput = document.getElementById('customer_whatsapp');
        const cpfCnpjInput = document.getElementById('customer_cpf_cnpj');
        const cardNumberInput = document.getElementById('card_number');
        const cardCcvInput = document.getElementById('card_ccv');
        const cardPostalCodeInput = document.getElementById('card_postal_code');

        if (whatsappInput) {
          whatsappInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
              value = value.replace(/^(\d{2})(\d{4,5})(\d{4})$/, '($1) $2-$3');
            }
            e.target.value = value;
          });
        }

        if (cpfCnpjInput) {
          cpfCnpjInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
              value = value.replace(/^(\d{3})(\d{3})(\d{3})(\d{2})$/, '$1.$2.$3-$4');
            } else {
              value = value.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
            }
            e.target.value = value;
          });
        }

        if (cardNumberInput) {
          cardNumberInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
            e.target.value = value;
          });
        }

        if (cardCcvInput) {
          cardCcvInput.addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '');
          });
        }

        if (cardPostalCodeInput) {
          cardPostalCodeInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
              value = value.replace(/^(\d{5})(\d{3})$/, '$1-$2');
            }
            e.target.value = value;
          });
        }
      });

      function copyPixPayload() {
        const el = document.getElementById('pixPayload');
        if (!el) return;

        const text = el.value || el.innerText;
        if (!text) return;

        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(function () {
            alert('Código Pix copiado para a área de transferência.');
          }).catch(function () {
            alert('Não foi possível copiar automaticamente. Copie o código manualmente.');
          });
        } else {
          // fallback simples
          el.select();
          document.execCommand('copy');
          alert('Código Pix copiado para a área de transferência.');
        }
      }

      function copyBoletoLine() {
        const el = document.getElementById('boletoLine');
        if (!el) return;

        const text = el.value || el.innerText;
        if (!text) return;

        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(text).then(function () {
            alert('Linha digitável copiada para a área de transferência.');
          }).catch(function () {
            alert('Não foi possível copiar automaticamente. Copie o código manualmente.');
          });
        } else {
          el.select();
          document.execCommand('copy');
          alert('Linha digitável copiada para a área de transferência.');
        }
      }
    </script>
  </body>
</html>
