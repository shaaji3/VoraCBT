<?php

declare(strict_types=1);

namespace App\Domain\Grading\Service\GradingStrategy;

use App\Domain\Grading\DTO\GradingResult;

class DragDropGradingStrategy implements GradingStrategyInterface
{
    public function canGrade(string $questionType): bool
    {
        return $questionType === 'drag_drop';
    }

    public function grade(array $questionContent, array $answerPayload, float $maxMarks, array $config = []): GradingResult
    {
        $correctMapping = $questionContent['correct_mapping'] ?? [];
        $studentMapping = $answerPayload['mapping'] ?? [];

        if (!is_array($correctMapping) || empty($correctMapping) || !is_array($studentMapping)) {
            return new GradingResult(0.0, false, 'Invalid drag_drop payload.');
        }

        $total = count($correctMapping);
        $matched = 0;
        foreach ($correctMapping as $itemId => $targetId) {
            if (($studentMapping[$itemId] ?? null) === $targetId) {
                $matched++;
            }
        }

        $score = round(($matched / max(1, $total)) * $maxMarks, 2);
        return new GradingResult($score, $matched === $total);
    }
}
