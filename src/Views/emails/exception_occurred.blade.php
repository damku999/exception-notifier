<!DOCTYPE html>
<html>
<head>
    <title>Exception Occurred</title>
</head>
<body>
    <h1>An Exception Occurred</h1>
    <p><strong>Message:</strong> {{ $exception->getMessage() }}</p>
    <p><strong>Code:</strong> {{ $exception->getCode() }}</p>
    <p><strong>File:</strong> {{ $exception->getFile() }}</p>
    <p><strong>Line:</strong> {{ $exception->getLine() }}</p>
    <p><strong>Trace:</strong></p>
    <pre>{{ $exception->getTraceAsString() }}</pre>
</body>
</html>
