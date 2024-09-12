```
# Laravel Exception Notifications

An easy-to-use package for sending email notifications with stack traces whenever an exception occurs in your Laravel application.

The `exception-notifier` package is designed to handle and notify about exceptions in your application. It provides a convenient way to get notified whenever an exception occurs, making it easier to monitor and respond to issues in real-time.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Features](#features)
- [Contributing](#contributing)
- [License](#license)

## Installation

To install the `exception-notifier` package, you can use Composer. Run the following command:

```bash
composer require adaptit-darshan/exception-notifier
```

This command will add the package to your `composer.json` file and install it in your project.

## Configuration

After installing the package, you need to configure it to suit your needs. Here’s a basic example of how to configure the notifier.

1. **Publish the Configuration File**

Publish the ExceptionEmail configuration file by running the following Artisan command:

```bash
php artisan vendor:publish --provider="AdaptItDarshan\ExceptionNotifier\Providers\ExceptionNotifierServiceProvider"
```

This will create a configuration file at `config/exception-notifier.php`.

**Recipients**
Specify the email addresses that should receive the exception notifications by updating the `email` array:
you can use EXCEPTION_NOTIFIER_EMAIL in .env file

```php
'email' => [
    env('EXCEPTION_NOTIFIER_EMAIL', ['hello@example.com'])
],
```

**Customizing Emails**
To customize the subject and body of the error notification emails blade file are created inside : \resources\views\vendor\exception-notifier\emails\


**Capture Exceptions**
You can specify which types of exceptions should trigger email notifications. By default, the package includes `\Symfony\Component\ErrorHandler\Error\FatalError::class`.

```php
'capture' => [
    \Symfony\Component\ErrorHandler\Error\FatalError::class,
],
```

To capture all exceptions, you can use the wildcard `'*'`:

```php
'capture' => [
    '*'
],
```

**Ignored Exceptions**
You may define exceptions that should not trigger email notifications. This is done by adding them to the `ignored_exception` array.

```php
'ignored_exception' => [
    \Illuminate\Validation\ValidationException::class,
],
```

**Ignored Bots**
You can configure the package to ignore errors triggered by bots, like search engine crawlers. The default configuration includes common bots such as:

```php
'ignored_bots' => [
    'googlebot',
    'bingbot',
    'slurp', 
    'ia_archiver',
],
```

## Usage

Once you have configured the package, it will automatically handle and notify about exceptions based on the settings you provided. You can also manually notify about exceptions if needed:

```php
use ExceptionNotifier\ExceptionNotifier;

$notifier = new ExceptionNotifier(config('exception-notifier'));
$notifier->notify(new \Exception('Custom exception message'));
```

## Features

- **Email Notifications**: Send notifications via email to specified recipients.
- **Logging**: Optionally log exceptions to a file for future reference.
- **Customizable Configuration**: Easily customize the configuration to fit your needs.

## Contributing

Contributions are welcome! If you have any ideas for improvements or find a bug, please open an issue or submit a pull request on the [GitHub repository](https://github.com/damku999/exception-notifier).

## License

The `exception-notifier` package is open-source software licensed under the [MIT License](https://opensource.org/licenses/MIT).

---

For more details, visit the [Packagist page](https://packagist.org/packages/adaptit-darshan/exception-notifier) or check out the [GitHub repository](https://github.com/damku999/exception-notifier).
```
