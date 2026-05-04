Project Name: EduSkill

EduSkill is a web-based learning platform built around a microlearning concept. The system is designed so that users can learn step by step through courses, modules, materials, and quizzes, while admins can manage all learning content and monitor user activity.

==================================================
1. GENERAL OVERVIEW OF EDUSKILL
==================================================

EduSkill is an online learning platform focused on structured, gradual, and accessible learning. The system separates two main roles:

1. Admin
2. User / Learner

Main goals of EduSkill:
- help users find suitable courses
- provide a step-by-step learning flow through modules and materials
- assess user understanding through quizzes
- display learning progress
- provide certificates when a course is completed
- give admins the ability to manage and monitor learning activities

Core platform concept:
- course -> module -> content
- content can be text material or a quiz
- progress is calculated based on completed content
- quizzes can affect content/course completion
- the platform is Laravel Blade-based, not a full SPA

==================================================
2. TECH STACK AND ARCHITECTURAL PATTERN
==================================================

Main stack:
- Laravel
- Blade templates
- PHP
- MySQL
- CSS
- JavaScript / jQuery
- AJAX for several dynamic interactions
- DataTables on some admin pages
- Intervention Image for certificate generation

General project structure:
- `routes/web.php` -> main routes
- `app/Http/Controllers/...` -> controllers
- `app/Models/...` -> models
- `resources/views/...` -> Blade templates
- `public/assets/...` -> CSS, JS, media, logo
- `database/migrations/...` -> database schema

Application pattern:
- most pages are rendered server-side using Blade
- some actions such as quizzes, content loading, DataTable requests, and monitoring use AJAX
- admin and user UI are separated
- many interactions are written in inline JS in Blade or in page-specific JS

==================================================
3. ROLES IN THE SYSTEM
==================================================

A. Admin
Admin is responsible for:
- managing courses
- managing modules
- managing materials
- managing quizzes
- managing users
- viewing course participants
- viewing user progress
- monitoring quiz integrity violations
- handling certain utilities such as database backup

B. User
User is responsible for:
- login/register
- viewing the course list
- opening course details
- enrolling in courses
- studying materials
- taking quizzes
- viewing progress
- viewing/downloading certificates when a course is completed
- using the onboarding feature for category recommendations

==================================================
4. CORE FEATURES OF EDUSKILL
==================================================

Before the new features were added, EduSkill already had the following main features:

A. Authentication
- login
- register
- password reset
- OTP / certain verification mechanisms
- user profile

B. Dashboard
- admin dashboard with summary data
- user dashboard with course and progress information

C. Course listing and detail
- course list
- course detail
- module information
- enrollment status information

D. Learning structure
- a course consists of multiple modules
- a module consists of multiple content items
- content can be text material or a quiz

E. Quiz system
- admin creates quizzes per content item
- user takes quizzes
- the system calculates scores
- there is pass/fail logic
- if passed, progress is updated

F. Progress tracking
- progress per content item
- progress per course
- completion status

G. Certificate
- if the course is completed and certificate is enabled
- users can preview/download certificates

H. Admin management
- CRUD course
- CRUD module
- CRUD content / quiz
- user management
- participant monitoring
- backup / utilities

==================================================
5. IMPORTANT ENTITIES AND DATA MODEL
==================================================

Main entities:
- users
- kursuses
- modules
- contents
- quiz_questions
- quiz_options
- user_courses
- user_content_progress
- user_quiz_attempts
- user_quiz_answers

Important relationships:
- 1 course has many modules
- 1 module has many content items
- 1 content item can be text or a quiz
- 1 quiz content has many questions
- 1 question has many options
- a user has course enrollments
- a user has progress records
- a user has quiz attempts and answers

==================================================
6. NEW FEATURES THAT HAVE BEEN ADDED
==================================================

==================================================
6C. COURSE PREREQUISITES (PRASYARAT KURSUS)
==================================================

Purpose:
to allow admins to set learning progression — a user cannot enroll in a course until they
have completed all required prerequisite courses.

How it works:
- admins choose zero or more prerequisite courses when creating or editing a course
- prerequisites are stored in a many-to-many pivot table: `kursus_prerequisites`
- when a user visits a locked course detail page:
  - a "Prasyarat Kursus" section is shown listing each prerequisite with a green/red status badge
  - the enroll button is replaced by a grey "Kursus Terkunci" disabled button and a warning message
- the server also enforces the check on the enroll endpoint — the lock cannot be bypassed

Admin UI:
- a new "Prasyarat" tab in the course create/edit form
- uses a Select2 multi-select dropdown listing all other active courses
- saving syncs prerequisites automatically (adding/removing)

User UI changes (course detail page):
- prerequisites list appears above the description if any exist
- each prerequisite shows: title, link, and a "Selesai" (green) or "Belum Selesai" (red) badge
- enroll button is conditionally replaced with locked state
- non-logged-in users also see the locked state if prerequisites exist

Database:
- new table: `kursus_prerequisites` (id, kursus_id, prerequisite_kursus_id, timestamps)
- unique constraint: (kursus_id, prerequisite_kursus_id)

Key files:
- `database/migrations/2026_05_04_000001_create_kursus_prerequisites_table.php`
- `app/Models/Kursus.php` — added `prerequisites()` belongsToMany relationship
- `app/Http/Controllers/Admin/KursusController.php` — create/edit pass `$allKursuses`, store/update sync prerequisites
- `app/Http/Controllers/User/ListKursusController.php` — show() checks prerequisites, enroll() enforces them
- `resources/views/admin/kursus/create.blade.php` — Prasyarat tab
- `resources/views/admin/kursus/edit.blade.php` — Prasyarat tab
- `resources/views/user/kursus/show.blade.php` — prerequisites list + locked state UI

==================================================
6D. AI QUIZ — GEMINI-POWERED AUTO-GENERATED QUESTIONS
==================================================

Purpose:
to allow admins to create AI-generated quizzes where every user receives a unique set of
questions automatically generated by Google Gemini, based on the module content text.

Problems it addresses:
- manual quiz creation is time-consuming for admins
- all users share the same fixed questions, making it easy to share answers
- admins want quiz diversity without maintaining large question banks

How it works (admin side):
1. admin goes to a module detail page
2. clicks "Tambah Materi", selects type "Kuis"
3. toggles the switch "Generate Soal dengan Gemini AI"
4. the manual question builder disappears
5. admin sets "Jumlah Soal per Pengguna" (1–20, default 5)
6. admin writes the module content as normal — this text is the context Gemini uses
7. saves — no manual questions needed at all

How it works (user side):
1. user opens an AI quiz content
2. the server calls Gemini 2.0 Flash API with the module text and question count
3. Gemini returns N unique multiple-choice questions (4 options each, 1 correct) in Bahasa Indonesia
4. questions are stored on the user's quiz attempt (`generated_questions` JSON)
5. the quiz UI is identical to a manual quiz — user sees and answers questions normally
6. on retry (after failing), a completely new set of questions is generated
7. after passing, the review shows the questions and answers from that specific attempt

Result:
- every user sees different questions
- every retry sees different questions
- no two users can share answers
- scoring, integrity mode, auto-submit, and review all work the same as manual quizzes

Gemini integration details:
- model: `gemini-2.0-flash`
- API key stored in `.env` as `GEMINI_API_KEY`
- config: `config/gemini.php`
- service class: `app/Services/GeminiService.php`
- the prompt instructs Gemini to output strict JSON with format:
  `{"questions":[{"question":"...","options":["A","B","C","D"],"correct_index":0}]}`
- `responseMimeType: application/json` is used for reliable structured output
- temperature 0.9 for variety between users

Storage:
- `generated_questions` JSON column on `user_quiz_attempts` — stores the AI-generated questions with correct answers (never sent to frontend)
- `ai_answers` JSON column on `user_quiz_attempts` — stores user's selected answers for AI quizzes
- no records written to `quiz_questions` or `quiz_options` tables for AI quizzes

Admin list:
- AI quiz content cards show a purple "AI Generated" badge instead of question count
- shows "N soal per pengguna" next to the badge

Database additions:
- `contents` table: `is_ai_generated` (boolean, default false), `ai_question_count` (tinyint, default 5)
- `user_quiz_attempts` table: `generated_questions` (JSON nullable), `ai_answers` (JSON nullable)

Key files:
- `database/migrations/2026_05_04_000002_add_ai_quiz_fields_to_contents_table.php`
- `database/migrations/2026_05_04_000003_add_ai_fields_to_user_quiz_attempts_table.php`
- `app/Services/GeminiService.php` — calls Gemini REST API, validates and parses response
- `config/gemini.php` — API key and model config
- `app/Models/Content.php` — added `is_ai_generated`, `ai_question_count` to fillable/casts
- `app/Models/UserQuizAttempt.php` — added `generated_questions`, `ai_answers` to fillable/casts
- `app/Http/Controllers/Admin/ContentController.php` — skips manual question validation for AI quizzes, saves AI fields
- `app/Http/Controllers/User/ListKursusController.php`:
  - `getContent()` — generates questions via Gemini if AI quiz, stores on attempt, returns to frontend without correct answers
  - `startQuizAttempt()` — finds existing pending AI attempt or creates one
  - `submitQuiz()` — scores against `generated_questions` JSON, saves `ai_answers`
  - `logIntegrityViolation()` — handles auto-submit for AI quizzes (score 0 since no partial answers)
- `resources/views/admin/module/detail.blade.php` — AI toggle in create/edit modals, AI badge in content list



==================================================
6A. QUIZ INTEGRITY MODE
==================================================

This is the most important additional feature and the main focus of the thesis.

Purpose:
to monitor user integrity while taking online quizzes and give admins visibility into violations.

Problems it addresses:
- users can switch tabs during a quiz
- users can exit fullscreen
- users can lose window focus
- admins previously had no clear quiz violation monitoring system

Features that have been added:

1. Integrity settings per quiz/content
Admins can configure:
- `integrity_mode_enabled`
- `require_fullscreen`
- `max_violations`

2. Rules panel before the quiz starts
If integrity mode is active, before the quiz the user will see rules such as:
- tab switching is monitored
- fullscreen may be required
- there is a violation limit
- the quiz may auto-submit

3. Monitoring while the quiz is running
The system monitors events such as:
- `visibilitychange` / tab switching
- `blur` / losing window focus
- `fullscreenchange` / exiting fullscreen

4. Warning system
If a violation occurs:
- the system increments the violation count
- a warning is shown to the user
- the event is saved to the database

5. Auto-submit
If the number of violations reaches the limit:
- the quiz is automatically submitted
- the attempt is marked as auto-submitted due to integrity violations

6. Admin monitoring
Admins can see:
- which user violated the rules
- the related quiz/content
- the number of violations
- event history
- whether the attempt was auto-submitted

Important note:
- this is not absolute anti-cheat protection
- this is a browser-based monitoring / deterrence system
- the focus is on monitoring, logging, warning, and auto-submit

==================================================
6B. EXPLORE YOUR PATH
==================================================

This is a public onboarding feature before login.

Purpose:
to help visitors discover the most suitable learning category before they log in or register.

Categories used:
- Programming
- Design
- Marketing
- Business
- Cybersecurity

Feature flow:
1. visitor enters `/`
2. a public landing page is shown
3. there are 3 buttons:
   - Explore Your Path
   - Login
   - Register
4. if the visitor chooses Explore Your Path:
   - they enter a 5-question questionnaire
5. the system calculates the result using rule-based scoring
6. a recommended category result is displayed
7. the visitor can continue to login or register

Scoring rule:
- option 1 = Programming
- option 2 = Design
- option 3 = Marketing
- option 4 = Business
- option 5 = Cybersecurity

Correct mapping examples:
- [1,1,1,1,1] -> Programming
- [2,2,2,2,2] -> Design
- [3,3,3,3,3] -> Marketing
- [4,4,4,4,4] -> Business
- [5,5,5,5,5] -> Cybersecurity

Main implementation:
- new public landing page
- questionnaire page
- result page
- ExplorePathController
- session persistence for the result

==================================================
7. UI ENHANCEMENTS THAT HAVE BEEN DONE
==================================================

A. Explore Your Path landing page
It has been made more branded and attractive:
- uses the EduSkill logo
- hero section has been strengthened
- includes a badge such as “Personalized Learning Start”
- Explore Your Path is the primary CTA
- login/register are secondary CTAs
- includes or plans to include dark mode / light mode aligned with the system theme

B. Explore Your Path questionnaire
It has been made more modern:
- cleaner question cards
- progress text/bar
- more interactive selected-answer styling
- there were issues with radio selection / selected state / progress sync

==================================================
8. ISSUES AND BUGS THAT HAVE APPEARED OR ARE STILL PRESENT
==================================================

A. Learn page quiz syntax bug
Problem:
- the start learning / loadContent / startFirstContent buttons did not work
Root cause:
- JavaScript syntax error in `learn.blade.php`
Impact:
- global functions were not defined
Status:
- this has been fixed before by correcting quote concatenation

B. Explore Your Path selected answer bug
Problem:
- the selected answer is not clearly visible
- sometimes it is not actually selected
- the progress bar does not update correctly
Fix direction:
- simplify to a native radio pattern
- use label-wrapped input
- calculate progress from checked inputs
Status:
- it has been fixed multiple times, but still needs stability verification

C. Explore Your Path scoring bug
Problem:
- choosing all option number 1 answers once produced the wrong category
Example of incorrect behavior:
- [1,1,1,1,1] -> Cybersecurity
When it should be:
- [1,1,1,1,1] -> Programming
Fix direction:
- check form value mapping
- check score accumulation
- check result selection

D. Certificate issue
Problem:
- certificate preview is not ideal yet
- downloading certificates fails if the PHP GD extension is not enabled
Root cause:
- Intervention Image requires the GD driver
Status:
- still needs attention

E. Crisp chat issue
Problem:
- the previous user’s conversation is carried over when another user logs in on the same browser
Likely root cause:
- Crisp session/token is not reset on logout
- there is no proper per-user session continuity
Status:
- needs a deep audit of the Crisp integration

==================================================
9. FEATURES CURRENTLY BEING CONSIDERED / ON HOLD
==================================================

==================================================
9A. TEMPORARY SUSPENSION
==================================================

This is a follow-up feature that fits very well with Quiz Integrity Mode.

Purpose:
after admins see violations in monitoring, they can give users a temporary sanction.

Concept:
- not a permanent ban
- temporary suspension with minute-based duration
- options:
  - 1 minute
  - 5 minutes
  - 10 minutes
  - 30 minutes
  - 60 minutes
  - custom minutes

Desired behavior:
- suspension status is saved to the user account
- if the user is still logged in, the user is forced to log out on the next request
- if the user tries to log in while suspension is still active:
  - login is blocked
  - a remaining-time message is shown
- when the suspension expires:
  - the user can log in again normally

Academic value:
this feature completes the flow:
violation monitoring -> admin decision -> temporary sanction -> access restriction

Status:
- still on hold
- not yet fully implemented

==================================================
10. MAIN SYSTEM FLOWS
==================================================

A. User flow
1. user logs in / registers
2. sees dashboard
3. selects a course
4. enrolls if necessary
5. studies each module/content
6. if the content is a quiz:
   - the user takes the quiz
   - if integrity mode is active, monitoring runs
7. progress is updated
8. if the course is completed:
   - the user can view / download the certificate

B. Admin flow
1. admin logs in
2. enters admin dashboard
3. manages courses
4. manages modules
5. manages content / quizzes
6. manages users
7. views course participants
8. monitors quiz integrity
9. (optional in the future) applies temporary suspension

==================================================
11. IMPORTANT FILES AND AREAS TO UNDERSTAND
==================================================

Important files/areas:
- `routes/web.php`
- `app/Http/Controllers/User/ListKursusController.php`
- `app/Http/Controllers/Admin/ContentController.php`
- `app/Http/Controllers/Admin/QuizIntegrityController.php`
- `app/Http/Controllers/Guest/ExplorePathController.php`
- `app/Models/Content.php`
- `app/Models/UserQuizAttempt.php`
- `app/Models/UserQuizIntegrityEvent.php`
- `resources/views/user/kursus/learn.blade.php`
- `resources/views/admin/module/detail.blade.php`
- `resources/views/admin/kursus/integrity.blade.php`
- `resources/views/public/landing.blade.php`
- `resources/views/public/explore-path.blade.php`
- `resources/views/public/explore-result.blade.php`
- `resources/views/layout/sidenav.blade.php`

For Crisp integration, additional areas to inspect:
- shared layouts
- auth flow
- logout flow
- inline scripts that load Crisp
- env/config related to Crisp

==================================================
12. CURRENT PRIORITIES
==================================================

Current main project priorities:
1. keep Quiz Integrity Mode stable
2. ensure admin monitoring works correctly
3. resolve Explore Your Path issues:
   - selected answer
   - progress bar
   - scoring logic
4. fix Crisp integration so user conversations do not get mixed
5. if needed, continue Temporary Suspension as a complement to Quiz Integrity Mode

==================================================
13. THESIS FOCUS
==================================================

The main thesis focus is currently closest to:
**Quiz Integrity Mode**

Thesis direction:
- quiz integrity monitoring
- detecting violations during quizzes
- warning and auto-submit
- admin visibility into violations
- possible extension to temporary sanctions

This means:
Explore Your Path is an interesting additional feature,
but the feature most relevant to the thesis is Quiz Integrity Mode.

==================================================
14. IMPORTANT NOTES FOR CLAUDE
==================================================

When analyzing or fixing EduSkill:
- do not drastically change the basic Laravel/Blade architecture
- do not change the main application flow without a strong reason
- prioritize reliability over overly fancy UI
- if there is a frontend bug, check:
  - markup
  - CSS selectors
  - JS event binding
  - Blade cache
  - browser cache
- if there is a login/chat bug, trace:
  - login
  - logout
  - session
  - browser persistence
  - reset state
- for Quiz Integrity Mode, treat it as a browser monitoring system, not an absolute anti-cheat system
- when fixing bugs, focus on the root cause, not just the visual symptoms
