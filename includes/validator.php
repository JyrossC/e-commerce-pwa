<?php
/**
 * NovaMart — Input Validation & Sanitization
 * All user input is validated here before use.
 */

class Validator
{
    private array $errors = [];
    private array $data   = [];

    public function __construct(array $input)
    {
        $this->data = $input;
    }

    // ─── Factory ─────────────────────────────────────────────────────────────
    public static function make(array $input, array $rules): self
    {
        $v = new self($input);
        foreach ($rules as $field => $ruleSet) {
            $v->apply($field, $ruleSet);
        }
        return $v;
    }

    // ─── Public API ──────────────────────────────────────────────────────────
    public function fails(): bool  { return !empty($this->errors); }
    public function passes(): bool { return empty($this->errors); }
    public function errors(): array { return $this->errors; }

    /** Get sanitized string value */
    public function get(string $field, mixed $default = null): mixed
    {
        return $this->data[$field] ?? $default;
    }

    /** Get all sanitized data */
    public function validated(): array { return $this->data; }

    // ─── Rule Engine ─────────────────────────────────────────────────────────
    private function apply(string $field, string $ruleSet): void
    {
        $rules = explode('|', $ruleSet);
        $value = $this->data[$field] ?? null;

        // Sanitize string values
        if (is_string($value)) {
            $value = trim($value);
            $this->data[$field] = $value;
        }

        foreach ($rules as $rule) {
            [$ruleName, $param] = array_pad(explode(':', $rule, 2), 2, null);

            switch ($ruleName) {
                case 'required':
                    if ($value === null || $value === '') {
                        $this->addError($field, ucfirst(str_replace('_', ' ', $field)) . ' is required.');
                        return; // Stop further checks for this field
                    }
                    break;

                case 'optional':
                    if ($value === null || $value === '') return; // Skip remaining rules
                    break;

                case 'string':
                    if (!is_string($value)) $this->addError($field, ucfirst($field) . ' must be a string.');
                    break;

                case 'numeric':
                    if (!is_numeric($value)) $this->addError($field, ucfirst($field) . ' must be numeric.');
                    break;

                case 'integer':
                    if (!filter_var($value, FILTER_VALIDATE_INT)) $this->addError($field, ucfirst($field) . ' must be an integer.');
                    break;

                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) $this->addError($field, 'Invalid email address.');
                    break;

                case 'min':
                    if (is_string($value) && mb_strlen($value) < (int)$param)
                        $this->addError($field, ucfirst($field) . " must be at least {$param} characters.");
                    elseif (is_numeric($value) && $value < (float)$param)
                        $this->addError($field, ucfirst($field) . " must be at least {$param}.");
                    break;

                case 'max':
                    if (is_string($value) && mb_strlen($value) > (int)$param)
                        $this->addError($field, ucfirst($field) . " must not exceed {$param} characters.");
                    elseif (is_numeric($value) && $value > (float)$param)
                        $this->addError($field, ucfirst($field) . " must not exceed {$param}.");
                    break;

                case 'in':
                    $allowed = explode(',', $param);
                    if (!in_array($value, $allowed, true))
                        $this->addError($field, ucfirst($field) . ' is not a valid value.');
                    break;

                case 'regex':
                    if (!preg_match($param, $value))
                        $this->addError($field, ucfirst($field) . ' format is invalid.');
                    break;

                case 'password':
                    // At least 8 chars, 1 uppercase, 1 lowercase, 1 digit, 1 special char
                    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $value))
                        $this->addError($field, 'Password must be at least 8 characters with uppercase, lowercase, number, and special character.');
                    break;

                case 'confirmed':
                    $confirmField = $field . '_confirmation';
                    if ($value !== ($this->data[$confirmField] ?? ''))
                        $this->addError($field, ucfirst($field) . ' confirmation does not match.');
                    break;

                case 'url':
                    if (!filter_var($value, FILTER_VALIDATE_URL)) $this->addError($field, ucfirst($field) . ' must be a valid URL.');
                    break;

                case 'positive':
                    if (!is_numeric($value) || $value <= 0) $this->addError($field, ucfirst($field) . ' must be a positive number.');
                    break;
            }
        }
    }

    private function addError(string $field, string $message): void
    {
        $this->errors[$field][] = $message;
    }

    // ─── Static Sanitizers ───────────────────────────────────────────────────

    /** Escape output for HTML context (prevent XSS) */
    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /** Strip any tags and sanitize for safe display */
    public static function sanitizeText(string $value): string
    {
        return strip_tags(trim($value));
    }

    /** Sanitize an integer */
    public static function sanitizeInt(mixed $value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /** Sanitize a float (for prices) */
    public static function sanitizeFloat(mixed $value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /** Generate a URL-safe slug */
    public static function slug(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^\w\s-]/', '', $text);
        $text = preg_replace('/[\s_-]+/', '-', $text);
        return trim($text, '-');
    }
}
