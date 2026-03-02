<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

final class PageController
{
    private const TEMPLATE_ROOT = __DIR__ . '/../../../../UI Template';

    public function home(): void
    {
        $this->renderTemplate('index.html');
    }

    public function login(): void
    {
        $this->renderTemplate('index.html');
    }

    public function adminDashboard(): void
    {
        $this->renderTemplate('pages/admin-dashboard.html');
    }

    public function studentDashboard(): void
    {
        $this->renderTemplate('pages/exam-selection.html');
    }

    public function studentExam(): void
    {
        $this->renderTemplate('pages/active-exam.html');
    }

    public function adminExamCreate(): void
    {
        $this->renderTemplate('pages/exam-template.html');
    }

    public function studentResults(): void
    {
        $this->renderTemplate('pages/student-results.html');
    }

    private function renderTemplate(string $template): void
    {
        $file = self::TEMPLATE_ROOT . '/' . $template;

        if (!is_file($file)) {
            http_response_code(404);
            echo 'Template not found.';
            return;
        }

        $html = (string) file_get_contents($file);

        $replacements = [
            'href="css/' => 'href="/assets/css/',
            'href="../css/' => 'href="/assets/css/',
            'src="js/' => 'src="/assets/js/',
            'src="../js/' => 'src="/assets/js/',
            'href="index.html"' => 'href="/login"',
            'href="../index.html"' => 'href="/login"',
            'href="admin-dashboard.html"' => 'href="/admin/dashboard"',
            'href="exam-selection.html"' => 'href="/student/dashboard"',
            'href="active-exam.html"' => 'href="/student/exam"',
            'href="student-results.html"' => 'href="/student/results"',
            'href="exam-template.html"' => 'href="/admin/exams/create"',
        ];

        echo strtr($html, $replacements);
    }
}
