@php
    // Ensure answers is a map for easy lookup in Alpine
    $answersMap = $answers->toArray();
@endphp

<x-app-layout>
    <script src="https://unpkg.com/monaco-editor@latest/min/vs/loader.js"></script>
    <div x-data="workspace()" class="fixed inset-0 bg-slate-50 flex flex-col overflow-hidden z-[50]">
        
        <!-- HEADER -->
        @include('intern.tasks.components.header')

        <!-- MAIN LAYOUT -->
        <div class="flex-1 flex overflow-hidden">
            
            <!-- SIDEBAR -->
            @include('intern.tasks.components.sidebar')

            <!-- CONTENT AREA -->
            <main class="flex-1 bg-slate-50/50 overflow-y-auto overflow-x-hidden p-8">
                <div class="max-w-[850px] mx-auto pb-20">
                    @include('intern.tasks.components.question-renderer')
                </div>
            </main>

        </div>

        <!-- FOOTER -->
        @include('intern.tasks.components.footer')

    </div>

    <script>
    function workspace() {
        return {
            task: @json($task),
            submission: @json($submission),
            answers: @json($answersMap),
            currentIndex: 0,
            timer: '00:00:00',
            progress: 0,
            isSaving: false,
            isSubmitting: false,
            consoleOutput: '',
            isExecuting: false,
            editors: {},
            monacoLoader: null,
            templates: {
                javascript: "function solve() {\n\n}",
                python: "def solve():\n    pass",
                cpp: "#include <bits/stdc++.h>\nusing namespace std;\n\nint main() {\n\n    return 0;\n}",
                c: "#include <stdio.h>\n\nint main() {\n\n    return 0;\n}",
                java: "public class Main {\n    public static void main(String[] args) {\n\n    }\n}",
                php: "<?php\n\nfunction solve() {\n\n}"
            },

            init() {
                this.updateIcons();
                this.startTimer();
                this.calculateProgress();
                
                // Load Monaco
                this.loadMonaco();

                // Watch for current index changes to update icons and init/refresh editor
                this.$watch('currentIndex', () => {
                    this.updateIcons();
                    this.consoleOutput = '';
                    
                    if (this.currentQuestion.type === 'coding') {
                        this.$nextTick(() => {
                            const qId = this.currentQuestion.id;
                            const lang = this.currentQuestion.language || 'javascript';
                            this.initMonaco(qId, lang);
                        });
                    }
                });
            },

            loadMonaco() {
                require.config({ paths: { vs: 'https://unpkg.com/monaco-editor@latest/min/vs' } });
                require(['vs/editor/editor.main'], () => {
                    this.monacoLoader = monaco;
                    if (this.currentQuestion.type === 'coding') {
                        this.initMonaco(this.currentQuestion.id, this.currentQuestion.language || 'javascript');
                    }
                });
            },

            initMonaco(questionId, language) {
                const container = document.getElementById(`editor_${questionId}`);
                if (!container || this.editors[questionId]) return;

                const initialValue = this.getAnswer(questionId) || this.templates[language] || '';

                this.editors[questionId] = monaco.editor.create(container, {
                    value: initialValue,
                    language: language,
                    theme: 'vs-dark',
                    automaticLayout: true,
                    minimap: { enabled: false },
                    fontSize: 14,
                    lineNumbers: 'on',
                    roundedSelection: true,
                    scrollBeyondLastLine: false,
                    readOnly: false,
                    padding: { top: 16 }
                });

                // Sync with Alpine state
                this.editors[questionId].onDidChangeModelContent(() => {
                    const value = this.editors[questionId].getValue();
                    this.answers[questionId] = value;
                });
            },

            switchLanguage(questionId, language) {
                const editor = this.editors[questionId];
                if (!editor) return;

                const model = editor.getModel();
                monaco.editor.setModelLanguage(model, language);
                
                // If it's the first time or empty, load template
                if (!this.getAnswer(questionId) || this.getAnswer(questionId).trim() === '') {
                    editor.setValue(this.templates[language] || '');
                }
                
                // Update question language locally
                this.currentQuestion.language = language;
            },

            updateIcons() {
                this.$nextTick(() => {
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                });
            },

            get currentQuestion() {
                if (this.totalQuestions === 0) {
                    return {
                        id: 'generic',
                        type: this.task.type?.slug || 'file',
                        question: this.task.title,
                        description: this.task.description
                    };
                }
                return this.task.questions[this.currentIndex];
            },

            get totalQuestions() {
                return this.task.questions.length;
            },

            get answeredCount() {
                return this.task.questions.filter(q => this.isAnswered(q.id)).length;
            },

            get submissionStatus() {
                const status = this.submission.status?.slug || 'in_progress';
                const map = {
                    'in_progress': 'In Progress',
                    'submitted': 'Submitted',
                    'completed': 'Completed',
                    'ai_evaluated': 'AI Evaluated'
                };
                return map[status] || 'In Progress';
            },

            isAnswered(questionId) {
                const ans = this.answers[questionId];
                return ans !== undefined && ans !== null && ans.toString().trim() !== '';
            },

            getAnswer(questionId) {
                return this.answers[questionId] || '';
            },

            next() {
                if(this.currentIndex < this.totalQuestions - 1) {
                    this.currentIndex++;
                }
            },

            prev() {
                if(this.currentIndex > 0) {
                    this.currentIndex--;
                }
            },

            async saveProgress(questionId, value) {
                // Update local state first (Optimistic UI)
                this.answers[questionId] = value;
                this.calculateProgress();
                
                this.isSaving = true;
                
                try {
                    const response = await fetch(`/intern/tasks/${this.task.id}/save-answer`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            question_id: questionId,
                            answer: value
                        })
                    });

                    if (!response.successful && response.status !== 200) {
                        console.error('Save failed');
                    }
                } catch (error) {
                    console.error('Error saving progress:', error);
                } finally {
                    setTimeout(() => { this.isSaving = false; }, 500);
                }
            },

            async handleFileUpload(event, questionId) {
                const file = event.target.files[0];
                if (!file) return;

                this.isSaving = true;
                const formData = new FormData();
                formData.append('question_id', questionId);
                formData.append('file', file);

                try {
                    const response = await fetch(`/intern/tasks/${this.task.id}/save-answer`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    });

                    const result = await response.json();
                    if (result.status === 'success') {
                        this.answers[questionId] = 'File Uploaded'; // Placeholder for UI
                        this.calculateProgress();
                    }
                } catch (error) {
                    console.error('Upload failed:', error);
                } finally {
                    this.isSaving = false;
                }
            },

            uploadedFileName(questionId) {
                return this.isAnswered(questionId) ? 'File Uploaded' : null;
            },

            calculateProgress() {
                if (this.totalQuestions === 0) {
                    this.progress = this.isAnswered('generic') ? 100 : 0;
                    return;
                }
                this.progress = (this.answeredCount / this.totalQuestions) * 100;
            },

            startTimer() {
                // Future: fetch remaining time from backend if needed
                let seconds = 3600;  
                setInterval(() => {
                    if (seconds > 0) {
                        seconds--;
                        let h = Math.floor(seconds / 3600);
                        let m = Math.floor((seconds % 3600) / 60);
                        let s = seconds % 60;
                        this.timer = `${h.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
                    }
                }, 1000);
            },

            async submitTask() {
                if (!confirm('Are you sure you want to finish and submit this task? You cannot edit your answers after submission.')) {
                    return;
                }

                this.isSubmitting = true;
                
                try {
                    const response = await fetch(`/intern/tasks/${this.task.id}/submit`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });

                    const result = await response.json();
                    
                    if (result.status === 'success') {
                        window.location.href = `/intern/tasks/${this.task.id}/results`;
                    } else {
                        alert('Submission failed: ' + result.message);
                    }
                } catch (error) {
                    console.error('Error submitting task:', error);
                    alert('An error occurred during submission.');
                } finally {
                    this.isSubmitting = false;
                }
            },

            async runCode() {
                const qId = this.currentQuestion.id;
                const editor = this.editors[qId];
                if (!editor) return;

                const code = editor.getValue();
                const language = this.currentQuestion.language || 'javascript';

                this.isExecuting = true;
                this.consoleOutput = "Running...";

                try {
                    const response = await fetch('/run-code', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            code: code,
                            language: language
                        })
                    });

                    const result = await response.json();
                    this.consoleOutput = result.output;
                    
                    // Mark as error if status is not success (Judge0 status 3 is 'Accepted')
                    if (result.status && result.status.id !== 3) {
                        this.consoleError = true;
                    } else {
                        this.consoleError = false;
                    }

                } catch (error) {
                    this.consoleOutput = "Error: " + error.message;
                    this.consoleError = true;
                } finally {
                    this.isExecuting = false;
                    this.updateIcons();
                }
            },

            clearConsole() {
                this.consoleOutput = '';
                this.consoleError = false;
            },

            getInstruction(type) {
                const instructions = {
                    'mcq': 'Select the correct option from the list below.',
                    'true_false': 'Determine if the statement is True or False.',
                    'blank': 'Fill in the missing part of the statement.',
                    'descriptive': 'Provide a detailed explanation in the text area below.',
                    'coding': 'Write and test your code in the editor provided.',
                    'file': 'Upload the required project documents or source files.',
                    'github': 'Provide a link to your public repository.'
                };
                return instructions[type] || 'Follow the instructions to complete this question.';
            },

            parseBlankQuestion(text) {
                // If question has "___", highlight it
                return text.replace(/___/g, '<span class="text-blue-600 underline">_______</span>');
            }
        }
    }
    </script>
</x-app-layout>
