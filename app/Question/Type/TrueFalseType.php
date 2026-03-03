<?php

declare(strict_types=1);

namespace App\Question\Type;

use App\Question\Exception\QuestionValidationException;

class TrueFalseType extends AbstractQuestionType
{
    public function getTypeName(): string
    {
        return 'true_false';
    }

    public function validate(array $payload): void
    {
        $this->validateRequiredKeys($payload, ['prompt', 'correct_answer']);

        $value = $payload['correct_answer'];
        if (!is_bool($value)) {
            throw new QuestionValidationException("Field 'correct_answer' must be a boolean.");
        }
    }

    public function normalize(array $payload): array
    {
        $payload['options'] = [
            ['id' => 'true', 'text' => 'True'],
            ['id' => 'false', 'text' => 'False'],
        ];

        return $payload;
    }
}
