@if (isset($exception))
    Exception: [{{ $exception->getMessage() }}] | {{ get_class($exception) }} | Server -
    {{ request()->server('SERVER_NAME') }} | Environment - {{ config('app.env') }}
@else
    Default Exception Subject
@endif
