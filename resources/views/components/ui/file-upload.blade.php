@props([
    'type' => 'file',
    'accept' => '*',
    'multiple' => false,
    'label' => 'Upload a file',
    'hint' => 'or drag and drop',
    'formats' => null,
    'maxSize' => null,
])

<div 
    x-data="{
        files: [],
        isDragging: false,
        handleDrop(e) {
            this.isDragging = false;
            this.handleFiles(e.dataTransfer.files);
        },
        handleFiles(fileList) {
            this.files = Array.from(fileList);
            this.$refs.input.files = fileList;
        },
        removeFile(index) {
            this.files.splice(index, 1);
        },
        formatSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }"
    {{ $attributes->merge(['class' => 'w-full']) }}
>
    <!-- Drop Zone -->
    <div 
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop"
        :class="isDragging ? 'border-ink-900 bg-ink-50' : 'border-smoke-300 bg-white hover:bg-smoke-50'"
        class="relative flex flex-col items-center justify-center px-6 py-10 border-2 border-dashed rounded-xl transition-all duration-200 cursor-pointer"
    >
        <input 
            x-ref="input"
            type="file" 
            accept="{{ $accept }}"
            {{ $multiple ? 'multiple' : '' }}
            @change="handleFiles($event.target.files)"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
        >
        
        <div class="flex flex-col items-center">
            <div class="w-12 h-12 flex items-center justify-center rounded-full bg-smoke-100 text-smoke-500 mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
            </div>
            
            <div class="text-center">
                <span class="text-sm font-medium text-ink-900">{{ $label }}</span>
                <span class="text-sm text-smoke-500"> {{ $hint }}</span>
            </div>
            
            @if($formats || $maxSize)
                <p class="mt-2 text-xs text-smoke-500">
                    @if($formats){{ $formats }}@endif
                    @if($formats && $maxSize) · @endif
                    @if($maxSize)Max {{ $maxSize }}@endif
                </p>
            @endif
        </div>
    </div>
    
    <!-- File Preview -->
    <template x-if="files.length > 0">
        <ul class="mt-4 space-y-2">
            <template x-for="(file, index) in files" :key="index">
                <li class="flex items-center justify-between px-4 py-3 bg-smoke-50 rounded-lg">
                    <div class="flex items-center min-w-0">
                        <svg class="w-5 h-5 text-smoke-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink-900 truncate" x-text="file.name"></p>
                            <p class="text-xs text-smoke-500" x-text="formatSize(file.size)"></p>
                        </div>
                    </div>
                    <button 
                        @click="removeFile(index)"
                        type="button"
                        class="ml-4 p-1 text-smoke-500 hover:text-red-600 hover:bg-red-50 rounded transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </li>
            </template>
        </ul>
    </template>
</div>
