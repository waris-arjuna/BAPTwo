{{-- resources/views/components/common/flash-alert.blade.php --}}

@php
    $flashMessages = [];

    if (session('success')) {
        $flashMessages[] = [
            'variant' => 'success',
            'message' => session('success'),
        ];
    }

    if (session('profile_success')) {
        $flashMessages[] = [
            'variant' => 'success',
            'message' => session('profile_success'),
        ];
    }

    if (session('error')) {
        $flashMessages[] = [
            'variant' => 'error',
            'message' => session('error'),
        ];
    }

    if (session('warning')) {
        $flashMessages[] = [
            'variant' => 'warning',
            'message' => session('warning'),
        ];
    }

    if (session('status')) {
        $flashMessages[] = [
            'variant' => 'info',
            'message' => session('status'),
        ];
    }

    if (session('info')) {
        $flashMessages[] = [
            'variant' => 'info',
            'message' => session('info'),
        ];
    }
@endphp

@if (count($flashMessages) > 0)
    <div class="mb-6 space-y-3">
        @foreach ($flashMessages as $flash)
            <x-ui.alert
                :variant="$flash['variant']"
                :message="$flash['message']"
                :dismissible="true"
                :autoDismiss="true"
                :timeout="10000"
            />
        @endforeach
    </div>
@endif
