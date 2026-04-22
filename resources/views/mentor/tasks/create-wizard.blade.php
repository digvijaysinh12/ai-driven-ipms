<x-app-layout>
    <div x-data="taskWizard()" class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Progress Bar -->
        <div class="mb-12">
            <div class="flex items-center justify-between mb-4">
                <template x-for="step in steps" :key="step.number">
                    <div class="flex-1">
                        <div class="relative flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300"
                                 :class="currentStep >= step.number ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-slate-200 text-slate-400'">
                                <i x-bind:data-lucide="step.icon" class="w-5 h-5"></i>
                            </div>
                            <span class="mt-2 text-xs font-semibold tracking-tight"
                                  :class="currentStep >= step.number ? 'text-indigo-600' : 'text-slate-400'"
                                  x-text="step.title"></span>
                        </div>
                    </div>
                </template>
            </div>
            <div class="relative h-1 bg-slate-100 rounded-full overflow-hidden">
                <div class="absolute h-full bg-indigo-600 transition-all duration-500"
                     :style="'width: ' + ((currentStep - 1) / (steps.length - 1) * 100) + '%'"></div>
            </div>
        </div>

        <!-- Step content -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            
            <!-- Step 1: Basic Info -->
            <div x-show="currentStep === 1" x-cloak class="p-8 space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Basic Information</h2>
                    <p class="text-sm text-slate-500 mt-1">Start by defining the core details of your task.</p>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Task Title</label>
                        <input type="text" x-model="formData.title" class="input-field" placeholder="e.g. Advanced Laravel Architecture">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Task Type</label>
                        <select x-model="formData.task_type_id" class="input-field">
                            <option value="">Select a type...</option>
                            @foreach($taskTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Detailed Description</label>
                        <textarea x-model="formData.description" rows="4" class="input-field" placeholder="What should the intern learn or achieve?"></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 2: AI Config -->
            <div x-show="currentStep === 2" x-cloak class="p-8 space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">AI Configuration</h2>
                    <p class="text-sm text-slate-500 mt-1">Configure parameters for AI question generation.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Topic / Subject</label>
                        <input type="text" x-model="formData.topic" class="input-field" placeholder="e.g. Service Providers and Contracts">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Difficulty Level</label>
                            <select x-model="formData.difficulty" class="input-field">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                                <option value="expert">Expert</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1">Question Count</label>
                            <input type="number" x-model="formData.question_count" min="1" max="15" class="input-field">
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button @click="generateQuestions" 
                            class="w-full btn btn-accent py-3 font-bold group"
                            :disabled="isGenerating">
                        <template x-if="!isGenerating">
                            <div class="flex items-center justify-center gap-2">
                                <i data-lucide="sparkles" class="w-5 h-5"></i>
                                Generate Questions with AI
                            </div>
                        </template>
                        <template x-if="isGenerating">
                            <div class="flex items-center justify-center gap-2">
                                <i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i>
                                Generating magic...
                            </div>
                        </template>
                    </button>
                </div>
            </div>

            <!-- Step 3: Question Review -->
            <div x-show="currentStep === 3" x-cloak class="p-8 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">Question Review</h2>
                        <p class="text-sm text-slate-500 mt-1">Review and refine AI-generated questions.</p>
                    </div>
                    <button @click="addQuestion" class="btn btn-secondary py-1 text-xs gap-1">
                        <i data-lucide="plus" class="w-3 h-3"></i> Add Manually
                    </button>
                </div>

                <div class="space-y-4 max-h-[400px] overflow-y-auto pr-2 custom-scrollbar">
                    <template x-for="(q, index) in questions" :key="index">
                        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl relative group">
                            <button @click="removeQuestion(index)" class="absolute top-2 right-2 text-slate-300 hover:text-red-500 transition-colors">
                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                            </button>
                            
                            <div class="space-y-3">
                                <input type="text" x-model="q.question_text" class="w-full bg-transparent border-none font-bold text-slate-800 p-0 focus:ring-0" placeholder="Question Title">
                                <textarea x-model="q.description" rows="2" class="w-full bg-white border border-slate-100 rounded-lg text-xs p-2 focus:ring-1 focus:ring-indigo-100" placeholder="Question instructions/context..."></textarea>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Step 4: Assignment -->
            <div x-show="currentStep === 4" x-cloak class="p-8 space-y-6">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Assign Task</h2>
                    <p class="text-sm text-slate-500 mt-1">Select the interns who should receive this task.</p>
                </div>

                <div class="space-y-6">
                    <div class="relative">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Select Interns</label>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <template x-for="id in selectedInterns" :key="id">
                                <span class="badge bg-indigo-50 border-indigo-200 text-indigo-700 py-1 pl-3 pr-1 gap-1">
                                    <span x-text="getInternName(id)"></span>
                                    <button @click="toggleIntern(id)" class="hover:bg-indigo-200 rounded-full p-0.5">
                                        <i data-lucide="x" class="w-3 h-3"></i>
                                    </button>
                                </span>
                            </template>
                        </div>
                        <input type="text" x-model="internSearch" @input="searchInterns" 
                               class="input-field" placeholder="Search interns by name...">
                        
                        <!-- Search Dropdown -->
                        <div x-show="internSearch.length > 0 && filteredInterns.length > 0" 
                             class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="intern in filteredInterns" :key="intern.id">
                                <button @click="toggleIntern(intern.id)" 
                                        class="w-full text-left px-4 py-3 hover:bg-slate-50 flex items-center justify-between"
                                        :class="selectedInterns.includes(intern.id) ? 'bg-indigo-50' : ''">
                                    <span x-text="intern.name" class="text-sm font-medium"></span>
                                    <i x-show="selectedInterns.includes(intern.id)" data-lucide="check" class="w-4 h-4 text-indigo-600"></i>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1">Due Date (Optional)</label>
                        <input type="datetime-local" x-model="formData.due_at" class="input-field">
                    </div>
                </div>
            </div>

            <!-- Footer Navigation -->
            <div class="bg-slate-50 p-6 flex items-center justify-between border-t border-slate-200">
                <button @click="prevStep" 
                        x-show="currentStep > 1" 
                        class="btn btn-secondary gap-2 px-6">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
                </button>
                <div x-show="currentStep === 1"></div> <!-- Spacer -->

                <button @click="nextStep" 
                        v-if="currentStep < 4" 
                        class="btn btn-primary gap-2 px-8 shadow-indigo-500/20"
                        :disabled="!isStepValid()">
                    <span x-text="currentStep === 4 ? 'Complete & Launch' : 'Continue' "></span>
                    <i x-bind:data-lucide="currentStep === 4 ? 'rocket' : 'arrow-right'" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
        function taskWizard() {
            return {
                currentStep: 1,
                isGenerating: false,
                isSaving: false,
                steps: [
                    { number: 1, title: 'Basics', icon: 'info' },
                    { number: 2, title: 'AI Config', icon: 'sparkles' },
                    { number: 3, title: 'Review', icon: 'list-checks' },
                    { number: 4, title: 'Assign', icon: 'users' },
                ],
                formData: {
                    title: '',
                    task_type_id: '',
                    description: '',
                    topic: '',
                    difficulty: 'intermediate',
                    question_count: 5,
                    due_at: '',
                },
                questions: [],
                interns: @json($interns),
                selectedInterns: [],
                internSearch: '',
                filteredInterns: [],

                init() {
                    lucide.createIcons();
                },

                isStepValid() {
                    if (this.currentStep === 1) return this.formData.title && this.formData.task_type_id;
                    if (this.currentStep === 2) return this.formData.topic;
                    if (this.currentStep === 3) return this.questions.length > 0;
                    if (this.currentStep === 4) return this.selectedInterns.length > 0;
                    return true;
                },

                nextStep() {
                    if (this.currentStep < 4) {
                        this.currentStep++;
                        this.$nextTick(() => lucide.createIcons());
                    } else {
                        this.finish();
                    }
                },

                prevStep() {
                    if (this.currentStep > 1) {
                        this.currentStep--;
                        this.$nextTick(() => lucide.createIcons());
                    }
                },

                addQuestion() {
                    this.questions.push({ question_text: 'New Question', description: '' });
                },

                removeQuestion(index) {
                    this.questions.splice(index, 1);
                },

                searchInterns() {
                    if (this.internSearch.length < 1) {
                        this.filteredInterns = [];
                        return;
                    }
                    this.filteredInterns = this.interns.filter(i => 
                        i.name.toLowerCase().includes(this.internSearch.toLowerCase())
                    );
                },

                toggleIntern(id) {
                    if (this.selectedInterns.includes(id)) {
                        this.selectedInterns = this.selectedInterns.filter(i => i !== id);
                    } else {
                        this.selectedInterns.push(id);
                    }
                },

                getInternName(id) {
                    return this.interns.find(i => i.id === id)?.name || 'Unknown';
                },

                async generateQuestions() {
                    this.isGenerating = true;
                    try {
                        // In a real app, this calls the API endpoint we built
                        // For now, simulating the API flow
                        const response = await fetch(`/api/v1/mentor/tasks/generate-questions-preview`, {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.formData)
                        });
                        
                        const result = await response.json();
                        this.questions = result.data.questions;
                        this.nextStep();
                    } catch (e) {
                        alert('Generation failed. Please try again.');
                    } finally {
                        this.isGenerating = false;
                    }
                },

                async finish() {
                    this.isSaving = true;
                    try {
                        // Submit Task -> Store Questions -> Sync Interns
                        // This uses the nested service logic on the backend
                        const response = await fetch(`/api/v1/mentor/tasks/store-full`, {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                ...this.formData,
                                questions: this.questions,
                                intern_ids: this.selectedInterns
                            })
                        });

                        if (response.ok) {
                            window.location.href = "{{ route('user.mentor.dashboard') }}";
                        }
                    } catch (e) {
                        alert('Launch failed. Please try again.');
                    } finally {
                        this.isSaving = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>
