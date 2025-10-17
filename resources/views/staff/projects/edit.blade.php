<x-staff>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Project: ') . $project->name }}
            </h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('staff.projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back
                </a>
                @if($project->enumerations()->count() == 0)
                    <form method="POST" action="{{ route('staff.projects.destroy', $project) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this project?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                            Delete Project
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Project Details Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Project Details</h3>
                    
                    <form method="POST" action="{{ route('staff.projects.update', $project) }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Project Name</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $project->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $project->description) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label for="is_published" class="block text-sm font-medium text-gray-700 mb-2">Self Enumeration</label>
                            <input type="checkbox" name="is_published" id="is_published" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ old('is_published', $project->is_published) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Check this box if you want enumeration to be open to all.</span>
                        </div>
                        
                        <div class="mb-4">
                            <label for="requires_verification" class="block text-sm font-medium text-gray-700 mb-2">Enumeration Verification Required</label>
                            <input type="checkbox" name="requires_verification" id="requires_verification" value="1" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" {{ old('requires_verification', $project->requires_verification) ? 'checked' : '' }}>
                            <span class="ml-2 text-sm text-gray-700">Check this box if enumerations for this project require manual verification.</span>
                        </div>
                        
                        <div class="flex space-x-2">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Project
                            </button>
                            @if($project->is_active)
                                <a href="{{ route('staff.projects.deactivate', $project) }}" onclick="event.preventDefault(); document.getElementById('deactivate-form').submit();" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                                    Deactivate Project
                                </a>
                            @else
                                <a href="{{ route('staff.projects.activate', $project) }}" onclick="event.preventDefault(); document.getElementById('activate-form').submit();" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Activate Project
                                </a>
                            @endif
                        </div>
                    </form>
                    
                    @if($project->is_active)
                        <form id="deactivate-form" action="{{ route('staff.projects.deactivate', $project) }}" method="POST" style="display: none;">
                            @csrf
                            @method('PATCH')
                        </form>
                    @else
                        <form id="activate-form" action="{{ route('staff.projects.activate', $project) }}" method="POST" style="display: none;">
                            @csrf
                            @method('PATCH')
                        </form>
                    @endif
                </div>
            </div>

            <!-- Project Fields Management -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Project Fields</h3>
                        <button type="button" onclick="toggleAddFieldForm()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Add New Field
                        </button>
                    </div>

                    <!-- Add New Field Form -->
                    <div id="add-field-form" class="hidden border border-gray-200 rounded-lg p-4 mb-6">
                        <form method="POST" action="{{ route('staff.projects.addField', $project) }}">
                            @csrf
                            <h4 class="text-md font-medium text-gray-900 mb-4">Add New Field</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Field Name</label>
                                    <input type="text" name="name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <small class="text-gray-500">Use underscore_case (e.g., first_name)</small>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Field Label</label>
                                    <input type="text" name="label" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Field Type</label>
                                    <select name="type" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="toggleNewFieldOptions(this.value)" required>
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
                                        <input type="checkbox" name="required" value="1" class="mr-1">
                                        Required Field
                                    </label>
                                </div>
                                
                                <div class="new-options-field" id="new-options" style="display: none;">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Options (comma-separated)</label>
                                    <input type="text" name="options" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Option 1, Option 2, Option 3">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Placeholder Text</label>
                                    <input type="text" name="placeholder" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Help Text</label>
                                    <input type="text" name="help_text" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                            
                            <div class="mt-4 flex space-x-2">
                                <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Add Field
                                </button>
                                <button type="button" onclick="toggleAddFieldForm()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Existing Fields List -->
                    @if($project->projectFields->count() > 0)
                        <div id="fields-list" class="space-y-4">
                            @foreach($project->projectFields as $field)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-4">
                                                <h4 class="text-md font-medium text-gray-900">{{ $field->label }}</h4>
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">{{ ucfirst($field->type) }}</span>
                                                @if($field->required)
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Required</span>
                                                @endif
                                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $field->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $field->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">Field name: {{ $field->name }}</p>
                                            @if($field->help_text)
                                                <p class="text-sm text-gray-500 mt-1">{{ $field->help_text }}</p>
                                            @endif
                                            @if($field->options)
                                                <p class="text-sm text-gray-500 mt-1">Options: {{ implode(', ', $field->options) }}</p>
                                            @endif
                                        </div>
                                        <div class="flex space-x-2">
                                            <form method="POST" action="{{ route('staff.fields.toggle', $field) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-sm {{ $field->is_active ? 'text-yellow-600 hover:text-yellow-800' : 'text-green-600 hover:text-green-800' }}">
                                                    {{ $field->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                            @if($field->enumerationData()->count() == 0)
                                                <form method="POST" action="{{ route('staff.fields.delete', $field) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this field?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600">No fields defined for this project.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        const isPublished = document.getElementById('is_published');
        const requiresVerification = document.getElementById('requires_verification');
        
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

        function toggleAddFieldForm() {
            const form = document.getElementById('add-field-form');
            form.classList.toggle('hidden');
        }
        
        function toggleNewFieldOptions(type) {
            const optionsField = document.getElementById('new-options');
            if (['select', 'radio', 'checkboxes'].includes(type)) {
                optionsField.style.display = 'block';
            } else {
                optionsField.style.display = 'none';
            }
        }
    </script>
</x-staff>