<<<<<<< ours
<<<<<<< ours
# AI Driven Intern Performance Management System

This project is developed using Laravel.

## Modules
- Authentication
- Intern Dashboard
- Team Lead Dashboard
- HR Dashboard

Developed by Team IPMS 🚀
=======
<h1 align="center">🤖 AI Driven Internship Performance System (AIPS)</h1>

<p align="center">
  <b>AI-assisted internship tracking, coding evaluation, and performance monitoring platform</b><br>
  Built with Laravel
</p>

---

## 📌 Project Title

**AI driven Internship Perfomance System**

---

## 🎯 Project Vision

The AI Driven Internship Performance System helps organizations manage the full internship lifecycle:

- onboarding interns and team leads,
- approving and activating accounts through HR,
- assigning mentors,
- generating coding exercises with AI,
- evaluating intern submissions,
- tracking attendance and work hours,
- and producing final internship performance reports.

---

## 👥 User Roles

- **Intern**
- **Team Lead**
- **HR**

Access and permissions are role-based after authentication.

---

## 🔁 End-to-End Workflow Modules

### 1) Registration Module
- Intern and Team Lead create accounts.
- Users submit basic profile details.

### 2) HR Approval
- HR reviews registered profiles.
- Only HR-approved users can log in.

### 3) Team Lead Assignment
- HR assigns one Team Lead to each approved Intern.
- Intern account is activated after assignment.

### 4) Login Process
- All roles log in with email and password.
- Role-based access:
  - **Intern** → solve exercises
  - **Team Lead** → create topics, review work
  - **HR** → monitor system-wide performance

### 5) Topic Creation
- Team Lead selects topics (e.g., Arrays, Laravel, API).
- Team Lead does not write questions manually.

### 6) AI Question Generation
- AI generates coding questions automatically.
- Questions range from basic to advanced.
- Generated questions form an exercise module.

### 7) Intern Solves Questions
- Intern views question list.
- On selecting a question:
  - code editor opens,
  - copy-paste is disabled,
  - intern writes and runs code,
  - each question is solved independently.

### 8) Submission
- Intern submits after finishing all questions.
- System stores all submitted answers.

### 9) AI Evaluation
- AI evaluates submitted code.
- AI provides score suggestion and feedback.

### 10) Team Lead Review
- Team Lead reviews:
  - intern code,
  - AI suggestion.
- Team Lead gives final score and feedback.

### 11) Time Tracking
- Timer starts on intern login and stops on logout.
- System calculates:
  - total work time,
  - time per topic,
  - time per question.

### 12) Work Outside System
- Intern can submit extra work hours (meetings, research, docs).
- Team Lead can approve/reject submitted extra hours.
- Approved hours are added to total time.

### 13) Attendance Calculation
- Attendance is calculated from:
  - system-tracked hours,
  - approved extra hours.
- Final attendance follows company policy.

### 14) Performance Calculation
- Final performance combines:
  - AI score,
  - Team Lead score,
  - time spent,
  - attendance.

### 15) Monitoring
- Team Lead monitors assigned interns and progress.
- HR monitors all interns, reports, and overall performance.

### 16) Internship Completion
- System generates a final performance report.
- Internship status is marked as completed.

---

## 🛠️ Suggested Core Tech Stack

- **Backend:** Laravel (PHP)
- **Frontend:** Blade or SPA frontend (as needed)
- **Database:** MySQL
- **Authentication:** Laravel Breeze / Sanctum (depending on architecture)
- **AI Integration:** LLM API for question generation and evaluation

---

## 🚀 Local Setup (Laravel)

=======
# AI driven Internship Perfomance System

## Overview
The **AI driven Internship Perfomance System** is a Laravel-based platform for managing the complete internship process inside a company. It helps **Interns, Team Leads, and HR** work in one system for registration, approval, task solving, evaluation, attendance tracking, and final performance reporting.

The main purpose of this project is to:
- manage intern onboarding,
- automate coding exercise generation using AI,
- track working hours and attendance,
- support code review and scoring,
- and generate a final internship performance report.

---

## Main Roles

### 1. Intern
An Intern can:
- log in after HR approval and Team Lead assignment,
- view assigned topics and AI-generated questions,
- solve coding exercises in the system,
- submit answers,
- view feedback and scores,
- submit extra work hours done outside the system.

### 2. Team Lead
A Team Lead can:
- register and get HR approval,
- create or select learning topics,
- trigger AI-based question generation,
- review intern submissions,
- compare AI evaluation with submitted code,
- provide final score and feedback,
- approve or reject extra work hour requests.

### 3. HR
HR can:
- approve or reject registered users,
- assign a Team Lead to each Intern,
- monitor all interns and team leads,
- track attendance, working hours, and performance,
- view reports and internship completion status.

---

## Complete System Modules

### 1. Registration Module
- Intern and Team Lead create their accounts.
- They enter their basic details and submit the registration form.
- Newly registered users remain inactive until HR approval.

### 2. HR Approval Module
- HR reviews the submitted profile details.
- If the details are valid, HR approves the account.
- Only approved users are allowed to log in.

### 3. Team Lead Assignment Module
- HR assigns one Team Lead to each approved Intern.
- After assignment, the Intern account becomes active for task participation.

### 4. Login and Role-Based Access Module
- Intern, Team Lead, and HR log in using email and password.
- The system gives access according to the user role:
  - **Intern** → solve exercises and submit work.
  - **Team Lead** → manage topics and review intern work.
  - **HR** → monitor users, progress, and reports.

### 5. Topic Creation Module
- Team Lead selects a topic such as **Arrays**, **Laravel**, or **API**.
- Team Lead does not need to write questions manually.
- The topic becomes the base for AI-generated exercises.

### 6. AI Question Generation Module
- AI automatically generates coding questions for the selected topic.
- Questions can be generated from **basic**, **intermediate**, and **advanced** levels.
- These questions together form an exercise set for the Intern.

### 7. Exercise Solving Module
- Intern can view the list of assigned questions.
- When the Intern opens a question:
  - the code editor is displayed,
  - copy-paste is disabled,
  - the Intern writes code manually,
  - the Intern can run and test the code.
- Each question is solved separately.

### 8. Submission Module
- After completing all questions, the Intern submits the exercise.
- The system stores all answers securely for evaluation and review.

### 9. AI Evaluation Module
- AI checks the submitted code.
- AI provides:
  - suggested score,
  - technical feedback,
  - quality comments if needed.

### 10. Team Lead Review Module
- Team Lead reviews:
  - Intern code,
  - AI score suggestion,
  - AI feedback.
- Team Lead then gives the **final score** and **final feedback**.

### 11. Time Tracking Module
- Time starts when the Intern logs in.
- Time stops when the Intern logs out.
- The system calculates:
  - total working time,
  - time spent per topic,
  - time spent per question.

### 12. Extra Work Hours Module
- If the Intern works outside the system, such as in:
  - meetings,
  - research,
  - documentation,
  - discussion sessions,
  they can submit extra work hours with a description.
- Team Lead can approve or reject the request.
- Approved hours are added to total work hours.

### 13. Attendance Calculation Module
- Attendance is calculated using:
  - system tracked hours,
  - approved extra work hours.
- Final attendance is decided according to company policy.

### 14. Performance Calculation Module
- Final intern performance is calculated using:
  - AI score,
  - Team Lead score,
  - time spent,
  - attendance.
- This gives a more complete picture of intern performance.

### 15. Monitoring and Reporting Module
- Team Lead can monitor assigned Interns and their progress.
- HR can monitor all Interns, Team Leads, attendance, scores, and reports.
- Reports help management review internship progress at any time.

### 16. Internship Completion Module
- At the end of the internship period:
  - the system generates the final performance report,
  - the internship status is marked as **completed**.

---

## Suggested Features for Implementation
- Role-based authentication and authorization
- HR approval workflow
- Team Lead to Intern assignment
- AI-based coding question generation
- Secure code editor for Interns
- AI-based code evaluation
- Manual final review by Team Lead
- Time tracking and attendance calculation
- Extra hours approval workflow
- Final performance dashboard and report generation

---

## Suggested Technology Stack
- **Backend:** Laravel
- **Frontend:** Blade, Livewire, or Vue.js
- **Database:** MySQL
- **Authentication:** Laravel Breeze or Laravel Jetstream
- **AI Integration:** OpenAI or any coding-assistant model API
- **Code Execution:** Sandbox runner or secure execution environment

---

## Basic Laravel Setup
>>>>>>> theirs
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```
<<<<<<< ours

Open: `http://127.0.0.1:8000`

---

## 📈 Expected Outcomes

- Transparent and measurable intern performance
- Faster exercise generation and review cycle using AI
- Better mentor visibility into intern growth
- HR-level reporting for decisions and internship closure
>>>>>>> theirs
=======

Then open:
```text
http://127.0.0.1:8000
```

---

## Expected Benefits
- Better communication between Intern, Team Lead, and HR
- Faster task creation with AI support
- Transparent scoring and feedback process
- Accurate attendance and time tracking
- Easy monitoring of internship progress
- Final report generation for internship completion
>>>>>>> theirs
