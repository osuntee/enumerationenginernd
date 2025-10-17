<x-staff>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Add Enumeration Data') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">{{ $project->name }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('staff.projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Back to Project
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('staff.projects.enumeration.store', $project) }}">
                @csrf
                
                <!-- Enumeration Metadata -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Enumeration Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea name="notes" id="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Project Fields Data -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Field Data</h3>
                        
                        <div class="space-y-6">
                            @foreach($project->projectFields as $field)
                                <div class="border-b border-gray-200 pb-4 last:border-b-0">
                                    <label for="data_{{ $field->name }}" class="block text-sm font-medium text-gray-700 mb-2">
                                        {{ $field->label }}
                                        @if($field->required)
                                            <span class="text-red-500">*</span>
                                        @endif
                                    </label>
                                    
                                    @if($field->help_text)
                                        <p class="text-sm text-gray-500 mb-2">{{ $field->help_text }}</p>
                                    @endif
                                    
                                    @switch($field->type)
                                        @case('text')
                                        @case('email')
                                        @case('url')
                                        @case('tel')
                                            <input type="{{ $field->type }}" 
                                                   name="data[{{ $field->name }}]" 
                                                   id="data_{{ $field->name }}" 
                                                   value="{{ old('data.' . $field->name) }}" 
                                                   placeholder="{{ $field->placeholder }}"
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                   {{ $field->required ? 'required' : '' }}
                                                   @if($field->attributes)
                                                       @foreach($field->attributes as $attr => $value)
                                                           {{ $attr }}="{{ $value }}"
                                                       @endforeach
                                                   @endif>
                                            @break
                                            
                                        @case('textarea')
                                            <textarea name="data[{{ $field->name }}]" 
                                                      id="data_{{ $field->name }}" 
                                                      rows="3" 
                                                      placeholder="{{ $field->placeholder }}"
                                                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                      {{ $field->required ? 'required' : '' }}
                                                      @if($field->attributes)
                                                          @foreach($field->attributes as $attr => $value)
                                                              {{ $attr }}="{{ $value }}"
                                                          @endforeach
                                                      @endif>{{ old('data.' . $field->name) }}</textarea>
                                            @break
                                            
                                        @case('number')
                                        @case('range')
                                            <input type="{{ $field->type }}" 
                                                   name="data[{{ $field->name }}]" 
                                                   id="data_{{ $field->name }}" 
                                                   value="{{ old('data.' . $field->name) }}" 
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                   {{ $field->required ? 'required' : '' }}
                                                   @if($field->attributes)
                                                       @foreach($field->attributes as $attr => $value)
                                                           {{ $attr }}="{{ $value }}"
                                                       @endforeach
                                                   @endif>
                                            @break
                                            
                                        @case('date')
                                        @case('time')
                                        @case('datetime-local')
                                        @case('color')
                                            <input type="{{ $field->type }}" 
                                                   name="data[{{ $field->name }}]" 
                                                   id="data_{{ $field->name }}" 
                                                   value="{{ old('data.' . $field->name, $field->default_value) }}" 
                                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                   {{ $field->required ? 'required' : '' }}>
                                            @break
                                            
                                        @case('select')
                                            <select name="data[{{ $field->name }}]" 
                                                    id="data_{{ $field->name }}" 
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    {{ $field->required ? 'required' : '' }}>
                                                @if(!$field->required)
                                                    <option value="">-- Select Option --</option>
                                                @endif
                                                @if($field->options)
                                                    @foreach($field->options as $option)
                                                        <option value="{{ $option }}" {{ old('data.' . $field->name) == $option ? 'selected' : '' }}>
                                                            {{ $option }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @break
                                            
                                        @case('radio')
                                            @if($field->options)
                                                <div class="mt-2 space-y-2">
                                                    @foreach($field->options as $option)
                                                        <div class="flex items-center">
                                                            <input type="radio" 
                                                                   name="data[{{ $field->name }}]" 
                                                                   id="data_{{ $field->name }}_{{ $loop->index }}" 
                                                                   value="{{ $option }}"
                                                                   {{ old('data.' . $field->name) == $option ? 'checked' : '' }}
                                                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                                                   {{ $field->required ? 'required' : '' }}>
                                                            <label for="data_{{ $field->name }}_{{ $loop->index }}" class="ml-3 block text-sm font-medium text-gray-700">
                                                                {{ $option }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @break
                                            
                                        @case('checkbox')
                                            <div class="mt-2">
                                                <div class="flex items-center">
                                                    <input type="checkbox" 
                                                           name="data[{{ $field->name }}]" 
                                                           id="data_{{ $field->name }}" 
                                                           value="1"
                                                           {{ old('data.' . $field->name) ? 'checked' : '' }}
                                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                    <label for="data_{{ $field->name }}" class="ml-3 block text-sm font-medium text-gray-700">
                                                        {{ $field->label }}
                                                    </label>
                                                </div>
                                            </div>
                                            @break
                                            
                                        @case('checkboxes')
                                            @if($field->options)
                                                <div class="mt-2 space-y-2">
                                                    @foreach($field->options as $option)
                                                        <div class="flex items-center">
                                                            <input type="checkbox" 
                                                                   name="data[{{ $field->name }}][]" 
                                                                   id="data_{{ $field->name }}_{{ $loop->index }}" 
                                                                   value="{{ $option }}"
                                                                   {{ in_array($option, old('data.' . $field->name, [])) ? 'checked' : '' }}
                                                                   class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                                            <label for="data_{{ $field->name }}_{{ $loop->index }}" class="ml-3 block text-sm font-medium text-gray-700">
                                                                {{ $option }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @break
                                            
                                        @case('file')
                                            <input type="file" 
                                                   name="data[{{ $field->name }}]" 
                                                   id="data_{{ $field->name }}" 
                                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                                                   {{ $field->required ? 'required' : '' }}
                                                   @if($field->attributes)
                                                       @foreach($field->attributes as $attr => $value)
                                                           {{ $attr }}="{{ $value }}"
                                                       @endforeach
                                                   @endif>
                                            @break
                                    @endswitch
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6 flex space-x-2">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Save Enumeration Data
                            </button>
                            <a href="{{ route('staff.projects.show', $project) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-staff>