<?php

declare(strict_types=1);

namespace App\Question\Type;

use App\Question\Exception\QuestionValidationException;

class CaseStudyType extends AbstractQuestionType
{
    public function getTypeName(): string
    {
        return 'case_study';
    }

    public function validate(array $payload): void
    {
        $this->validateRequiredKeys($payload, ['case_text', 'questions']);

        if (!is_array($payload['questions']) || empty($payload['questions'])) {
            throw new QuestionValidationException("Field 'questions' must be a non-empty array.");
        }

        foreach ($payload['questions'] as $index => $question) {
            if (!isset($question['type'], $question['content']) || !is_array($question['content'])) {
                throw new QuestionValidationException("Case study question at index {$index} must include 'type' and array 'content'.");
            }

            if ($question['type'] === 'case_study') {
                throw new QuestionValidationException('Nested case_study is not allowed.');
            }

            QuestionTypeFactory::create((string) $question['type'])->validate($question['content']);
        }
    }
}
