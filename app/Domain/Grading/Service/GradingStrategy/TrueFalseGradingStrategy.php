<?php

declare(strict_types=1);

namespace App\Domain\Grading\Service\GradingStrategy;

use App\Domain\Grading\DTO\GradingResult;

class TrueFalseGradingStrategy implements GradingStrategyInterface
{
    public function canGrade(string $questionType): bool
    {
        return $questionType === 'true_false';
    }

    public function grade(array $questionContent, array $answerPayload, float $maxMarks, array $config = []): GradingResult
    {
        $correct = $questionContent['correct_answer'] ?? null;
        $answer = $answerPayload['answer'] ?? null;

        if (!is_bool($correct) || !is_bool($answer)) {
            return new GradingResult(0.0, false, 'Invalid true/false payload.');
        }

        $isCorrect = $correct === $answer;
        return new GradingResult($isCorrect ? $maxMarks : 0.0, $isCorrect);
    }
}
