<?php

use Illuminate\Database\Migrations\Migration;

// This migration is intentionally a no-op.
// The submissions table ALREADY HAS all scoring columns from the original
// 2026_03_02_103227_create_submissions_table migration:
//   syntax_score, logic_score, structure_score, ai_total_score,
//   mentor_override_score, final_score, feedback, status
// Delete this file — or just run it, it does nothing.

return new class extends Migration
{
    public function up(): void {}
    public function down(): void {}
};