# Check Git Branch Middleware

A Laravel middleware to check the current Git branch and prevent checkout of another one. This package ensures that your application only runs on the specified branch in production environments.

## Installation

You can install the package via Composer. Run the following command in your terminal:

```sh
composer require esameisa/check-git-branch-middleware
```

## Configuration

After installing the package, you need to set the desired branch in your `.env` file:

```sh
BRANCH=your-desired-branch-name
```

##Publishing the Configuration File

```sh
php artisan vendor:publish --provider="esameisa\CheckGitBranchMiddleware\CheckGitBranchMiddlewareServiceProvider" --tag="config"
```

and you can add those in .env file

```sh
BRANCH=master // branch name
BRANCH_MESSAGE=
BRANCH_PHONE_NUMBER=
```

## Usage

To use the middleware, you need to register it in your `app/Http/Kernel.php` file. Add it to the `$middleware` array:

```sh
\esameisa\CheckGitBranchMiddleware\CheckGitBranchMiddleware::class,
```

or if you want to put it only on some routes, Add it to the `$routeMiddleware` array:

```sh
protected $routeMiddleware = [
    // Other middleware...
    'check.git.branch' => \esameisa\CheckGitBranchMiddleware\CheckGitBranchMiddleware::class,
];
```

You can then apply this middleware to your routes or route groups:

```sh
Route::middleware(['check.git.branch'])->group(function () {
    Route::get('/your-route', 'YourController@yourMethod');
});
```

## How It Works

The middleware checks the current Git branch using the command:

```sh
git rev-parse --abbrev-ref HEAD
```

If the current branch does not match the one specified in the `.env` file and the application is running in a production environment, it will send an SMS notification and abort the request with a 404 status.

## SMS Notification

This package uses the `MoraSMS` service to send notifications. Ensure that you have this service set up and configured properly.

### Example of Sending SMS

You can customize the SMS message sent during branch mismatch by modifying the code in the `CheckGitBranchMiddleware`.

## Contributing

Contributions are welcome! Please feel free to submit issues or pull requests.

1. Fork the repository.
2. Create your feature branch (`git checkout -b feature/AmazingFeature`).
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`).
4. Push to the branch (`git push origin feature/AmazingFeature`).
5. Open a pull request.

## License

This package is licensed under the MIT License. See the [LICENSE](LICENSE) file for more information.

## Acknowledgments

- Thanks to [Laravel](https://laravel.com) for providing an excellent framework.
- Thanks to [Composer](https://getcomposer.org) for dependency management.
