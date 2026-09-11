<?php
/**
 * Request Validation Helper
 */

class Validator {
    private array $data;
    private array $errors = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    public static function make(array $data): self {
        return new self($data);
    }

    public function required(string $field, string $message = null): self {
        if (!isset($this->data[$field]) || trim((string)$this->data[$field]) === '') {
            $this->errors[$field][] = $message ?? ucfirst(str_replace('_', ' ', $field)) . " is required";
        }
        return $this;
    }

    public function email(string $field, string $message = null): self {
        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field][] = $message ?? "A valid email address is required";
        }
        return $this;
    }

    public function numeric(string $field, string $message = null): self {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = $message ?? ucfirst(str_replace('_', ' ', $field)) . " must be numeric";
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $message = null): self {
        if (isset($this->data[$field]) && strlen((string)$this->data[$field]) < $min) {
            $this->errors[$field][] = $message ?? ucfirst(str_replace('_', ' ', $field)) . " must be at least {$min} characters";
        }
        return $this;
    }

    public function in(string $field, array $allowedValues, string $message = null): self {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $allowedValues)) {
            $this->errors[$field][] = $message ?? ucfirst(str_replace('_', ' ', $field)) . " must be one of: " . implode(', ', $allowedValues);
        }
        return $this;
    }

    public function passes(): bool {
        return empty($this->errors);
    }

    public function fails(): bool {
        return !empty($this->errors);
    }

    public function errors(): array {
        return $this->errors;
    }
}
