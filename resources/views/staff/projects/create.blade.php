<x-staff>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Create Project') }}
            </h2>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('staff.projects.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Projects
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if ($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('staff.projects.store') }}" id="projectForm">
                @csrf
                
                <!-- Project Details -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Project Details</h3>
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="pre_generate" class="block text-sm font-medium text-gray-700 mb-2">Pre Generate QR Codes</label>
                            <input type="checkbox" name="pre_generate" id="pre_generate" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ old('pre_generate') ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Check this box if you want to make use of pre generated QR codes for this project</span>
                        </div>

                        <div class="mb-4">
                            <label for="allow_api" class="block text-sm font-medium text-gray-700 mb-2">Allow API Integration</label>
                            <input type="checkbox" name="allow_api" id="allow_api" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ old('allow_api') ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Check this box if you want to allow API integration for this project.</span>
                        </div>

                        <div class="mb-4">
                            <label for="is_published" class="block text-sm font-medium text-gray-700 mb-2">Self Enumeration</label>
                            <input type="checkbox" name="is_published" id="is_published" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ old('is_published') ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Check this box if you want enumeration to be open to all.</span>
                        </div>
                        
                        <div class="mb-4">
                            <label for="requires_verification" class="block text-sm font-medium text-gray-700 mb-2">Enumeration Verification Required</label>
                            <input type="checkbox" name="requires_verification" id="requires_verification" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ old('requires_verification') ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Check this box if enumerations for this project require manual verification.</span>
                        </div>
                    </div>
                </div>

                <!-- Project Fields -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Project Fields</h3>
                            <button type="button" onclick="addField()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Add Field
                            </button>
                        </div>
                        
                        <div id="fields-container">
                            <!-- Fields will be added here by JavaScript -->
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Create Project
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const preGenerate = document.getElementById('pre_generate');
        const allowApi = document.getElementById('allow_api');
        const isPublished = document.getElementById('is_published');
        const requiresVerification = document.getElementById('requires_verification');
        
        // When pre_generate is checked, turn off allow_api, requires_verification and is_published
        preGenerate.addEventListener('change', function() {
            console.log('Checked');
            
            if (this.checked) {
                allowApi.checked = false;
                isPublished.checked = false;
                requiresVerification.checked = false;
            }
        });
        
        // When is_published is checked, turn on requires_verification
        isPublished.addEventListener('change', function() {
            console.log('Checked');
            
            if (this.checked) {
                requiresVerification.checked = true;
            }
        });
        
        // When requires_verification is unchecked, turn off is_published
        requiresVerification.addEventListener('change', function() {
            if (!this.checked) {
                isPublished.checked = false;
            }
        });
        
        let fieldIndex = 0;
        
        function addField() {
            const container = document.getElementById('fields-container');
            const fieldHtml = `
                <div class="field-row border border-gray-200 rounded-lg p-4 mb-4" data-index="${fieldIndex}">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-md font-medium text-gray-900">Field ${fieldIndex + 1}</h4>
                        <button type="button" onclick="removeField(${fieldIndex})" class="text-red-600 hover:text-red-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Field Name</label>
                            <input type="text" name="fields[${fieldIndex}][name]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            <small class="text-gray-500">Use underscore_case (e.g., first_name)</small>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Field Label</label>
                            <input type="text" name="fields[${fieldIndex}][label]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Field Type</label>
                            <select name="fields[${fieldIndex}][type]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="toggleFieldOptions(${fieldIndex}, this.value)" required>
                                <option value="text">Text</option>
                                <option value="textarea">Textarea</option>
                                <option value="number">Number</option>
                                <option value="email">Email</option>
                                <option value="url">URL</option>
                                <option value="tel">Phone</option>
                                <option value="date">Date</option>
                                <option value="time">Time</option>
                                <option value="datetime-local">DateTime</option>
                                <option value="select">Select Dropdown</option>
                                <option value="radio">Radio Buttons</option>
                                <option value="checkbox">Single Checkbox</option>
                                <option value="checkboxes">Multiple Checkboxes</option>
                                <option value="file">File Upload</option>
                                
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                <input type="checkbox" name="fields[${fieldIndex}][required]" value="1" class="mr-1">
                                Required Field
                            </label>
                        </div>
                        
                        <div class="options-field" id="options-${fieldIndex}" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Options (comma-separated)</label>
                            <input type="text" name="fields[${fieldIndex}][options]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Option 1, Option 2, Option 3">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Placeholder Text</label>
                            <input type="text" name="fields[${fieldIndex}][placeholder]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Help Text</label>
                            <input type="text" name="fields[${fieldIndex}][help_text]" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', fieldHtml);
            fieldIndex++;
        }
        
        function removeField(index) {
            const fieldRow = document.querySelector(`[data-index="${index}"]`);
            if (fieldRow) {
                fieldRow.remove();
            }
        }
        
        function toggleFieldOptions(index, type) {
            const optionsField = document.getElementById(`options-${index}`);
            if (['select', 'radio', 'checkboxes'].includes(type)) {
                optionsField.style.display = 'block';
            } else {
                optionsField.style.display = 'none';
            }
        }
        
        // Add initial field
        document.addEventListener('DOMContentLoaded', function() {
            addField();
        });
    </script>
</x-staff>