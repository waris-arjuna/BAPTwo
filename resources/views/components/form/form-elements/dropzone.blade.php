<x-common.component-card title="Dropzone">
    <!-- Dropzone -->
    <div 
        x-data="{
            isDragging: false,
            files: [],
            handleDrop(e) {
                this.isDragging = false;
                const droppedFiles = Array.from(e.dataTransfer.files);
                this.handleFiles(droppedFiles);
            },
            handleFiles(selectedFiles) {
                const validTypes = ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'];
                const validFiles = selectedFiles.filter(file => validTypes.includes(file.type));
                
                if (validFiles.length > 0) {
                    this.files = [...this.files, ...validFiles];
                    console.log('Files uploaded:', validFiles);
                    
                    // Here you can add logic to upload files to server
                    this.uploadFiles(validFiles);
                }
            },
            uploadFiles(files) {
                // Implement your file upload logic here
                // Example: Use FormData and fetch/axios to upload
                console.log('Uploading files:', files);
            },
            removeFile(index) {
                this.files.splice(index, 1);
            }
        }"
        class="transition border border-gray-300 border-dashed cursor-pointer dark:hover:border-brand-500 dark:border-gray-700 rounded-xl hover:border-brand-500"
    >
        <div 
            @drop.prevent="handleDrop($event)"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @click="$refs.fileInput.click()"
            :class="isDragging 
                ? 'border-brand-500 bg-gray-100 dark:bg-gray-800' 
                : 'border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-900'"
            class="dropzone rounded-xl border-dashed border-gray-300 p-7 lg:p-10 transition-colors cursor-pointer"
            id="demo-upload"
        >
            <!-- Hidden File Input -->
            <input 
                x-ref="fileInput"
                type="file" 
                @change="handleFiles(Array.from($event.target.files)); $event.target.value = ''"
                accept="image/png,image/jpeg,image/webp,image/svg+xml"
                multiple
                class="hidden"
                @click.stop
            />

            <div class="flex flex-col items-center m-0">
                <!-- Icon Container -->
                <div class="mb-[22px] flex justify-center">
                    <div class="flex h-[68px] w-[68px] items-center justify-center rounded-full bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                        <i class="fi fi-rr-cloud-upload-alt text-2xl"></i>
                    </div>
                </div>

                <!-- Text Content -->
                <h4 class="mb-3 font-semibold text-gray-800 text-theme-xl dark:text-white/90">
                    <span x-show="!isDragging">Drag & Drop Files Here</span>
                    <span x-show="isDragging" x-cloak>Drop Files Here</span>
                </h4>

                <span class="text-center mb-5 block w-full max-w-[290px] text-sm text-gray-700 dark:text-gray-400">
                    Drag and drop your PNG, JPG, WebP, SVG images here or browse
                </span>

                <span class="font-medium underline text-theme-sm text-brand-500">
                    Browse File
                </span>
            </div>
        </div>

        <!-- File Preview List (Optional) -->
        <div x-show="files.length > 0" class="mt-4 p-4 border-t border-gray-200 dark:border-gray-700" x-cloak>
            <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Uploaded Files:</h5>
            <ul class="space-y-2">
                <template x-for="(file, index) in files" :key="index">
                    <li class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex items-center gap-3">
                            <i class="fi fi-rr-document text-base text-gray-500"></i>
                            <span class="text-sm text-gray-700 dark:text-gray-300" x-text="file.name"></span>
                        </div>
                        <button 
                            @click.stop="removeFile(index)"
                            type="button"
                            class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                        >
                            <i class="fi fi-rr-cross-small text-lg"></i>
                        </button>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</x-common.component-card>
