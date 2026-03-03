<?php

declare(strict_types=1);

namespace App\Question\Type;

use App\Question\Exception\QuestionValidationException;

class DragDropType extends AbstractQuestionType
{
    public function getTypeName(): string
    {
        return 'drag_drop';
    }

    public function validate(array $payload): void
    {
        $this->validateRequiredKeys($payload, ['prompt', 'items', 'targets', 'correct_mapping']);

        if (!is_array($payload['items']) || empty($payload['items'])) {
            throw new QuestionValidationException("Field 'items' must be a non-empty array.");
        }

        if (!is_array($payload['targets']) || empty($payload['targets'])) {
            throw new QuestionValidationException("Field 'targets' must be a non-empty array.");
        }

        if (!is_array($payload['correct_mapping']) || empty($payload['correct_mapping'])) {
            throw new QuestionValidationException("Field 'correct_mapping' must be a non-empty object/array.");
        }
    }
}
