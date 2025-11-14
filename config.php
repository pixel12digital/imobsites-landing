<?php
/**
 * Configuração da API do Painel ImobSites
 * 
 * Para desenvolvimento local, você pode:
 * 1. Usar a URL de produção (padrão)
 * 2. Ou configurar uma URL local/staging alterando PANEL_API_BASE_URL abaixo
 */

// Detectar ambiente (opcional - pode ser configurado via variável de ambiente)
$isLocal = (
    ($_SERVER['SERVER_NAME'] ?? '') === 'localhost' ||
    strpos($_SERVER['SERVER_NAME'] ?? '', '127.0.0.1') !== false ||
    strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
    strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false
);

// URL base do painel/API
if (!defined('PANEL_API_BASE_URL')) {
    // Padrão: produção
    define('PANEL_API_BASE_URL', 'https://painel.imobsites.com.br');
    
    // Para desenvolvimento local, descomente e ajuste se necessário:
    // if ($isLocal) {
    //     define('PANEL_API_BASE_URL', 'http://localhost/imobsites/api'); // Exemplo: API local
    //     // ou
    //     // define('PANEL_API_BASE_URL', 'https://staging.painel.imobsites.com.br'); // Exemplo: staging
    // }
}

// Configurações de autenticação para a API (opcional, para ambiente sandbox/testes)
// Descomente e configure conforme necessário:
// define('PANEL_API_KEY', 'sua-api-key-aqui');
// define('PANEL_API_TOKEN', 'seu-token-aqui');

// Header de autenticação opcional (exemplos comuns):
// - X-API-Key: chave de API
// - Authorization: Bearer token
// - X-Auth-Token: token de autenticação

// Número de WhatsApp para suporte (formato internacional sem +)
if (!defined('SUPPORT_WHATSAPP_NUMBER')) {
    define('SUPPORT_WHATSAPP_NUMBER', '5547999999999'); // Ajustar para o número real
}

