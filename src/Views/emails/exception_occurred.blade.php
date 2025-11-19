<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exception Occurred</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f5f5f5;
            line-height: 1.6;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header .alert-icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .email-body {
            padding: 30px 20px;
        }
        .alert-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-box strong {
            color: #856404;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            color: #667eea;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #667eea;
        }
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            padding: 8px 12px;
            background-color: #f8f9fa;
            font-weight: 600;
            width: 30%;
            color: #495057;
            border-bottom: 1px solid #dee2e6;
        }
        .info-value {
            display: table-cell;
            padding: 8px 12px;
            border-bottom: 1px solid #dee2e6;
            word-break: break-all;
        }
        .code-block {
            background-color: #282c34;
            color: #abb2bf;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 13px;
            line-height: 1.5;
            margin-top: 10px;
        }
        .code-block pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
        }
        .badge-error {
            background-color: #dc3545;
            color: #ffffff;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000000;
        }
        .badge-info {
            background-color: #17a2b8;
            color: #ffffff;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #dee2e6;
        }
        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }
        .timestamp {
            color: #6c757d;
            font-size: 13px;
            margin-top: 10px;
        }
        @media only screen and (max-width: 600px) {
            .email-container {
                margin: 10px;
            }
            .info-label, .info-value {
                display: block;
                width: 100%;
            }
            .info-label {
                border-bottom: none;
                padding-bottom: 4px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="alert-icon">⚠️</div>
            <h1>Exception Occurred</h1>
            <p style="margin: 5px 0 0 0; opacity: 0.9;">Your application encountered an error</p>
        </div>

        <!-- Body -->
        <div class="email-body">
            <!-- Alert Box -->
            <div class="alert-box">
                <strong>⚡ Action Required:</strong> An exception was thrown in your Laravel application. Please review the details below and take appropriate action.
            </div>

            <!-- Exception Details -->
            <div class="section">
                <h2 class="section-title">Exception Details</h2>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Exception Type</div>
                        <div class="info-value">
                            <strong>{{ class_basename($exception) }}</strong>
                            <span class="badge badge-error">ERROR</span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Error Message</div>
                        <div class="info-value">{{ $exception->getMessage() }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Error Code</div>
                        <div class="info-value">{{ $exception->getCode() ?: 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">File Location</div>
                        <div class="info-value">{{ $exception->getFile() }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Line Number</div>
                        <div class="info-value">{{ $exception->getLine() }}</div>
                    </div>
                </div>
            </div>

            <!-- Stack Trace -->
            <div class="section">
                <h2 class="section-title">Stack Trace</h2>
                <div class="code-block">
                    <pre>{{ $exception->getTraceAsString() }}</pre>
                </div>
            </div>

            <!-- Environment Information -->
            <div class="section">
                <h2 class="section-title">Environment Information</h2>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">Environment</div>
                        <div class="info-value">
                            {{ app()->environment() }}
                            @if(app()->environment('production'))
                                <span class="badge badge-error">PRODUCTION</span>
                            @elseif(app()->environment('staging'))
                                <span class="badge badge-warning">STAGING</span>
                            @else
                                <span class="badge badge-info">{{ strtoupper(app()->environment()) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Laravel Version</div>
                        <div class="info-value">{{ app()->version() }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">PHP Version</div>
                        <div class="info-value">{{ PHP_VERSION }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Timestamp</div>
                        <div class="info-value">{{ now()->format('Y-m-d H:i:s T') }}</div>
                    </div>
                </div>
            </div>

            <!-- Request Information (if available) -->
            @if(request()->getMethod())
            <div class="section">
                <h2 class="section-title">Request Information</h2>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">URL</div>
                        <div class="info-value">{{ request()->fullUrl() }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Method</div>
                        <div class="info-value">{{ request()->method() }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">IP Address</div>
                        <div class="info-value">{{ request()->ip() }}</div>
                    </div>
                    @if(request()->userAgent())
                    <div class="info-row">
                        <div class="info-label">User Agent</div>
                        <div class="info-value">{{ request()->userAgent() }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- User Information (if authenticated) -->
            @auth
            <div class="section">
                <h2 class="section-title">User Information</h2>
                <div class="info-grid">
                    <div class="info-row">
                        <div class="info-label">User ID</div>
                        <div class="info-value">{{ auth()->id() }}</div>
                    </div>
                    @if(auth()->user()->email)
                    <div class="info-row">
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ auth()->user()->email }}</div>
                    </div>
                    @endif
                    @if(auth()->user()->name)
                    <div class="info-row">
                        <div class="info-label">Name</div>
                        <div class="info-value">{{ auth()->user()->name }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endauth

            <!-- Timestamp -->
            <div class="timestamp">
                <strong>Generated:</strong> {{ now()->format('l, F j, Y \a\t g:i A T') }}
            </div>
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p style="margin: 0 0 10px 0;">
                <strong>Exception Notifier</strong> v1.2.0
            </p>
            <p style="margin: 0; font-size: 12px;">
                This is an automated notification from your Laravel application.<br>
                Powered by <a href="https://github.com/damku999/exception-notifier">adaptit-darshan/exception-notifier</a>
            </p>
        </div>
    </div>
</body>
</html>
