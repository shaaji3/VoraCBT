# Feature Support Matrix (Question Types + Exam Controls)

This matrix maps currently implemented capabilities in the codebase to requested CBT features.

## Question types

- **MCQ**: **Supported** (question type exists + grading strategy).
- **True/False**: **Supported** (dedicated `true_false` type handler and grading strategy).
- **Fill-in-the-blank**: **Supported**.
- **Essay**: **Supported**.
- **Matching**: **Supported**.
- **Drag & drop**: **Supported** (dedicated `drag_drop` type handler and grading strategy).
- **Case study**: **Supported** (dedicated `case_study` type with nested sub-question validation).

## Randomization and exam flow

- **Question shuffling**: **Supported** (deterministic shuffle per seeded session generation).
- **Option shuffling**: **Supported** (stores per-session `options_order`).
- **Question bank pooling**: **Supported** (section selection rules can filter by type, difficulty, tags and sample counts).
- **Section-based exams**: **Supported** (`exam_sections`, section ordering, section session tracking).
- **Negative marking**: **Supported** (exam-level controls for negative marking and penalty per wrong answer wired into auto-grading config).
- **Time per section**: **Supported** (timer uses current section duration when present).
- **Auto-submit**: **Supported** (cron-ready script auto-submits expired in-progress sessions).
- **Resume capability**: **Supported** (checkpoint autosave + resume state endpoints/service).

## Bulk import and metadata

- **Bulk question import (CSV/Excel)**: **Supported** (teacher import now accepts CSV and `.xlsx` with preview/commit validation).
- **Question tagging (topic, difficulty, learning objective)**: **Supported** (create/import flows now include `learning_objective` metadata and randomization can filter by it).

## Recommended adjustments/additions (with education-focused rationale)

1. **Add dedicated `true_false` question type** (instead of modeling only as MCQ).
   - **Why it matters in education:** improves teacher authoring clarity, enables analytics by cognitive format (binary judgement vs multi-option), and reduces item-construction mistakes.

2. **Add dedicated `drag_drop` question type with structured scoring schema**.
   - **Why it matters in education:** supports competency-based assessment (sequencing, classification, labeling), especially for STEM, language, and vocational learning outcomes that are hard to measure with plain MCQ.

3. **Add dedicated `case_study` type (stem + sub-questions + shared resources)**.
   - **Why it matters in education:** better measures applied reasoning and scenario-based decision-making common in higher education and professional programs.

4. **Introduce exam-level negative marking policy controls** (global + per-section overrides).
   - **Why it matters in education:** preserves fairness and transparency by applying consistent anti-guessing rules across all objective question types, not just optional MCQ partial scoring.

5. **Implement server-side auto-submit worker for expired sessions**.
   - **Why it matters in education:** prevents unequal outcomes caused by network loss or client-side interruptions and guarantees assessment closure integrity at scale.

6. **Build bulk question import pipeline for CSV + Excel with template validation**.
   - **Why it matters in education:** drastically reduces setup time for teachers and curriculum teams migrating large legacy banks from spreadsheets.

7. **Promote `learning_objective` to a first-class metadata field** (alongside topic/difficulty) with validation.
   - **Why it matters in education:** enables outcome-based reporting, accreditation evidence, and targeted remediation plans tied to curriculum standards.

8. **Add tagging governance** (controlled vocabularies and optional taxonomy locks by tenant/school).
   - **Why it matters in education:** avoids inconsistent tags (e.g., `algebra-1` vs `alg1`) that degrade analytics quality and teacher collaboration.

9. **Add section-level resume policy and attempt-state UX constraints**.
   - **Why it matters in education:** supports exam policies like “no return to previous section,” which are common in standardized testing and helps preserve policy compliance.

10. **Add psychometric and blueprint analytics per tag/objective/type**.
    - **Why it matters in education:** helps schools evaluate assessment quality (difficulty balance, objective coverage, discrimination trends) and improve future tests.

## Primary evidence locations

- Question type registry: `app/Question/Type/QuestionTypeFactory.php`
- Grading strategies: `app/Domain/Grading/Service/GradingStrategy/*`
- Randomization: `app/Domain/Exam/Service/RandomizationService.php`
- Timer + resume/autosave: `app/Domain/Exam/Service/TimerService.php`, `app/Domain/Exam/Service/SessionRecoveryService.php`
- Submission behavior: `app/Domain/Exam/Service/SubmissionService.php`
- Question metadata/create flow: `public/assets/js/pages/question-create.js`, `app/Question/Service/QuestionService.php`
- Exam/section schema: `database/migrations/2023_10_27_000003_CreateExamsAndSessionsTable.php`
- Bulk import currently wired to identity: `resources/views/admin/questions/import.php`, `app/Plugins/Admin/Service/AdminApiService.php`, `app/Domain/Identity/StudentImportService.php`
