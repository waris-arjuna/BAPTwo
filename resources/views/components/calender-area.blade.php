<div>
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/3">
        <div class="custom-calendar relative">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Modal -->
    <div class="fixed inset-0 items-center justify-center hidden p-4 sm:p-5 overflow-y-auto modal z-99999" id="eventModal">
        <div class="modal-close-btn fixed inset-0 h-full w-full bg-gray-400/50 backdrop-blur-[32px]"></div>
        <div class="modal-dialog relative flex w-full max-w-175 flex-col overflow-y-auto rounded-3xl bg-white p-4 sm:p-6 lg:p-10 dark:bg-gray-900">

            <!-- Close Button -->
            <button class="modal-close-btn transition-colors absolute top-4 end-4 sm:top-5 sm:end-5 z-999 flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-gray-400 hover:bg-gray-200 hover:text-gray-600 sm:h-10 sm:w-10 dark:bg-white/5 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-gray-300">
                <i class="fi fi-rr-cross-small text-lg"></i>
            </button>

            <div class="flex flex-col px-1 sm:px-2 overflow-y-auto modal-content custom-scrollbar">

                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="mb-2 font-semibold text-gray-800 modal-title text-theme-xl lg:text-2xl dark:text-white/90" id="eventModalLabel">
                        Add Event
                    </h5>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Plan your next big moment: schedule or edit an event to stay on track
                    </p>
                </div>

                <!-- Modal Body -->
                <div class="mt-8 space-y-6 modal-body">

                    <!-- Event Title -->
                    <div>
                        <label for="event-title" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Event Title
                        </label>
                        <input id="event-title" type="text" class="h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800" placeholder="Enter event title" />
                    </div>

                    <!-- Event Color -->
                    <div>
                        <label class="mb-4 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Event Color
                        </label>
                        <div class="flex flex-wrap items-center gap-3 sm:gap-4">

                            <!-- Danger -->
                            <div class="n-chk">
                                <div class="form-check form-check-danger form-check-inline">
                                    <label class="flex items-center text-sm text-gray-700 form-check-label dark:text-gray-400 cursor-pointer" for="modalDanger">
                                        <span class="relative">
                                            <input class="sr-only form-check-input" type="radio" name="event-level" value="Danger" id="modalDanger" />
                                            <span class="box me-2 flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 dark:border-gray-700">
                                            </span>
                                        </span>
                                        Danger
                                    </label>
                                </div>
                            </div>

                            <!-- Success -->
                            <div class="n-chk">
                                <div class="form-check form-check-success form-check-inline">
                                    <label class="flex items-center text-sm text-gray-700 form-check-label dark:text-gray-400 cursor-pointer" for="modalSuccess">
                                        <span class="relative">
                                            <input class="sr-only form-check-input" type="radio" name="event-level" value="Success" id="modalSuccess" />
                                            <span class="box me-2 flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 dark:border-gray-700">
                                            </span>
                                        </span>
                                        Success
                                    </label>
                                </div>
                            </div>

                            <!-- Primary -->
                            <div class="n-chk">
                                <div class="form-check form-check-primary form-check-inline">
                                    <label class="flex items-center text-sm text-gray-700 form-check-label dark:text-gray-400 cursor-pointer" for="modalPrimary">
                                        <span class="relative">
                                            <input class="sr-only form-check-input" type="radio" name="event-level" value="Primary" id="modalPrimary" checked />
                                            <span class="box me-2 flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 dark:border-gray-700">
                                            </span>
                                        </span>
                                        Primary
                                    </label>
                                </div>
                            </div>

                            <!-- Warning -->
                            <div class="n-chk">
                                <div class="form-check form-check-warning form-check-inline">
                                    <label class="flex items-center text-sm text-gray-700 form-check-label dark:text-gray-400 cursor-pointer" for="modalWarning">
                                        <span class="relative">
                                            <input class="sr-only form-check-input" type="radio" name="event-level" value="Warning" id="modalWarning" />
                                            <span class="box me-2 flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 dark:border-gray-700">
                                            </span>
                                        </span>
                                        Warning
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label for="event-start-date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Enter Start Date
                        </label>
                        <div class="relative">
                            <input id="event-start-date" type="date" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2.5 ps-4 pe-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 cursor-pointer" onclick="this.showPicker?.()" />
                            <span class="absolute top-1/2 end-3.5 -translate-y-1/2 pointer-events-none text-gray-700 dark:text-gray-400">
                                <i class="fi fi-rr-calendar text-sm"></i>
                            </span>
                        </div>
                    </div>

                    <!-- End Date -->
                    <div>
                        <label for="event-end-date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                            Enter End Date
                        </label>
                        <div class="relative">
                            <input id="event-end-date" type="date" class="h-11 w-full appearance-none rounded-lg border border-gray-300 bg-transparent bg-none py-2.5 ps-4 pe-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800 cursor-pointer" onclick="this.showPicker?.()" />
                            <span class="absolute top-1/2 end-3.5 -translate-y-1/2 pointer-events-none text-gray-700 dark:text-gray-400">
                                <i class="fi fi-rr-calendar text-sm"></i>
                            </span>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="modal-footer mt-6 flex items-center gap-3 sm:justify-end">
                    <button type="button" class="modal-close-btn flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:w-auto dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/3">
                        Close
                    </button>
                    <button type="button" class="btn btn-update-event bg-brand-500 hover:bg-brand-600 flex w-full justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-white sm:w-auto" style="display: none;" data-fc-event-public-id="">
                        Update Changes
                    </button>
                    <button type="button" class="btn btn-add-event bg-brand-500 hover:bg-brand-600 flex w-full justify-center rounded-lg px-4 py-2.5 text-sm font-medium text-white sm:w-auto">
                        Add Event
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>
