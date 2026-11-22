<?php
/**
 * AI Kariyer Koçu — harici model ayarları (opsiyonel).
 *
 * Boş bırakıldığında koç yerel/kural tabanlı motorla çalışmaya devam eder.
 * OpenAI uyumlu herhangi bir API kullanılabilir.
 */
return [
    'enabled'  => false,
    'api_key'  => '', // örn: sk-...  (asla repoya/commit'e eklemeyin)
    'api_url'  => 'https://api.openai.com/v1/chat/completions',
    'model'    => 'gpt-4o-mini',
    'max_tokens' => 500,
];
