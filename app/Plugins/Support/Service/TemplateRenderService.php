<?php

declare(strict_types=1);

namespace App\Plugins\Support\Service;

final class TemplateRenderService
{
    private const TEMPLATE_ROOT = __DIR__ . '/../../../../UI Template';

    public function render(string $template): void
    {
        $file = self::TEMPLATE_ROOT . '/' . $template;

        if (!is_file($file)) {
            http_response_code(404);
            echo 'Template not found.';
            return;
        }

        $html = (string) file_get_contents($file);

        echo strtr($html, [
            'href="css/' => 'href="/assets/css/',
            'href="../css/' => 'href="/assets/css/',
            'src="js/' => 'src="/assets/js/',
            'src="../js/' => 'src="/assets/js/',
            'href="../pages/' => 'href="/',
            'href="index.html"' => 'href="/login"',
            'href="../index.html"' => 'href="/login"',
            'href="admin-dashboard.html"' => 'href="/admin/dashboard"',
            'href="exam-selection.html"' => 'href="/student/dashboard"',
            'href="active-exam.html"' => 'href="/student/exam"',
            'href="student-results.html"' => 'href="/student/results"',
            'href="exam-template.html"' => 'href="/admin/exams/create"',
            'href="question-repository.html"' => 'href="/teacher/questions"',
            'href="manual-grading.html"' => 'href="/teacher/grading"',
            'href="system-settings.html"' => 'href="/admin/settings"',
            'href="exam-analytics.html"' => 'href="/admin/analytics"',
            'href="roles-permissions.html"' => 'href="/admin/roles-permissions"',
            'href="bulk-upload.html"' => 'href="/teacher/questions/import"',
            'href="create-mcq.html"' => 'href="/teacher/questions/create-mcq"',
            'href="create-fill-blank.html"' => 'href="/teacher/questions/create-fill-blank"',
            'href="create-matching.html"' => 'href="/teacher/questions/create-matching"',
            'href="create-passage.html"' => 'href="/teacher/questions/create-passage"',
            'href="2fa.html"' => 'href="/login/2fa"',
        ]);
    }
}
