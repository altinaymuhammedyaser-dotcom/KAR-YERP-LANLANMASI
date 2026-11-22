<?php
/**
 * Akilli Kariyer - Form dogrulama
 *
 * Sunucu tarafi dogrulama kurallari; hem klasik POST formlari hem de AJAX API'leri icin kullanilir.
 */

namespace App\Core;

final class Validator
{
    private array $data;
    private array $errors = [];
    private array $rules = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data): self
    {
        return new self($data);
    }

    /**
     * Kural tanimlar.
     *
     * @param array<string,string> $rules  alan => "required|email|min:3|max:50"
     */
    public function rules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * Dogrulamayi calistirir.
     */
    public function validate(): bool
    {
        foreach ($this->rules as $field => $ruleString) {
            $value = $this->value($field);
            $rules = array_filter(explode('|', $ruleString));

            $nullable = in_array('nullable', $rules, true);
            $isEmpty  = ($value === null || $value === '' || (is_array($value) && count($value) === 0));

            if ($isEmpty) {
                if (in_array('required', $rules, true)) {
                    $this->addError($field, $this->label($field) . ' alanı zorunludur.');
                } elseif ($nullable) {
                    continue;
                }
                // Bos degerler icin format kontrolu yapilmaz
                if (!in_array('required', $rules, true)) {
                    continue;
                }
            }

            foreach ($rules as $rule) {
                if (in_array($rule, ['required', 'nullable'], true)) {
                    continue;
                }
                $this->applyRule($field, $value, $rule);
            }
        }
        return $this->errors === [];
    }

    private function applyRule(string $field, mixed $value, string $rule): void
    {
        [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

        switch ($name) {
            case 'email':
                if (!filter_var((string) $value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, 'Geçerli bir e-posta adresi girin.');
                }
                break;
            case 'min':
                $len = is_array($value) ? count($value) : mb_strlen((string) $value);
                if ($len < (int) $param) {
                    $this->addError($field, $this->label($field) . ' en az ' . (int) $param . ' karakter olmalıdır.');
                }
                break;
            case 'max':
                $len = is_array($value) ? count($value) : mb_strlen((string) $value);
                if ($len > (int) $param) {
                    $this->addError($field, $this->label($field) . ' en fazla ' . (int) $param . ' karakter olabilir.');
                }
                break;
            case 'numeric':
                if (!is_numeric($value)) {
                    $this->addError($field, $this->label($field) . ' sayısal olmalıdır.');
                }
                break;
            case 'integer':
                if (filter_var($value, FILTER_VALIDATE_INT) === false) {
                    $this->addError($field, $this->label($field) . ' tam sayı olmalıdır.');
                }
                break;
            case 'in':
                $allowed = explode(',', (string) $param);
                if (!in_array((string) $value, $allowed, true)) {
                    $this->addError($field, $this->label($field) . ' geçersiz bir seçim.');
                }
                break;
            case 'confirmed':
                $other = $this->value($param);
                if ($value !== $other) {
                    $this->addError($field, $this->label($field) . ' alanları eşleşmiyor.');
                }
                break;
            case 'password':
                $errors = self::passwordErrors((string) $value);
                foreach ($errors as $msg) {
                    $this->addError($field, $msg);
                }
                break;
            case 'regex':
                if (!preg_match($param, (string) $value)) {
                    $this->addError($field, $this->label($field) . ' formatı geçersiz.');
                }
                break;
            case 'date':
                $d = \DateTime::createFromFormat('Y-m-d', (string) $value);
                if (!$d || $d->format('Y-m-d') !== $value) {
                    $this->addError($field, $this->label($field) . ' geçerli bir tarih değil.');
                }
                break;
            case 'phone':
                if (!preg_match('/^[0-9+\s\-()]{10,20}$/', (string) $value)) {
                    $this->addError($field, 'Geçerli bir telefon numarası girin.');
                }
                break;
            case 'slug':
                if (!preg_match('/^[a-z0-9\-]+$/', (string) $value)) {
                    $this->addError($field, $this->label($field) . ' sadece küçük harf, rakam ve tire içerebilir.');
                }
                break;
        }
    }

    /**
     * Sifre guc kontrolu (minimum 8 karakter, harf + rakam).
     */
    public static function passwordErrors(string $password, int $minLength = 8): array
    {
        $errors = [];
        if (mb_strlen($password) < $minLength) {
            $errors[] = 'Şifre en az ' . $minLength . ' karakter olmalıdır.';
        }
        if (!preg_match('/[A-Za-zÇĞİÖŞÜçğıöşü]/u', $password)) {
            $errors[] = 'Şifre en az bir harf içermelidir.';
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Şifre en az bir rakam içermelidir.';
        }
        return $errors;
    }

    public function value(string $field): mixed
    {
        return $this->data[$field] ?? null;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): ?string
    {
        return $this->errors ? reset($this->errors) : null;
    }

    public function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }

    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = $message;
        }
    }

    private function label(string $field): string
    {
        $labels = [
            'first_name' => 'Ad', 'last_name' => 'Soyad', 'email' => 'E-posta',
            'password' => 'Şifre', 'password_confirmation' => 'Şifre tekrarı',
            'education_level' => 'Eğitim seviyesi', 'kvkk' => 'KVKK onayı',
            'phone' => 'Telefon', 'city' => 'Şehir', 'message' => 'Mesaj',
            'name' => 'Ad Soyad', 'subject' => 'Konu', 'current_password' => 'Mevcut şifre',
            'new_password' => 'Yeni şifre', 'title' => 'Başlık', 'slug' => 'Bağlantı',
            'question_text' => 'Soru metni', 'category_id' => 'Kategori',
            'education_level_id' => 'Eğitim seviyesi',
        ];
        return $labels[$field] ?? ucfirst(str_replace('_', ' ', $field));
    }
}
