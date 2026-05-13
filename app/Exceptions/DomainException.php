<?php

namespace App\Exceptions;

class DomainException extends \DomainException
{
    public function __construct(
        string $message = '',
        public readonly ?string $field = null,
        public readonly array $context = []
    ) {
        parent::__construct($message);
    }

    public function toArray(): array
    {
        $result = [
            'error' => 'planning_error',
            'message' => $this->getMessage(),
        ];

        if ($this->field !== null) {
            $result['field'] = $this->field;
        }

        if (!empty($this->context)) {
            $result['context'] = $this->context;
        }

        return $result;
    }
}
