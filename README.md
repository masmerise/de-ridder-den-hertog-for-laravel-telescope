<p align="center">
<img src="https://media-01.imu.nl/storage/ridderenhertog.nl/2163/renh-kassasystemen-weegschalen.jpg" alt="RenH PHP SDK" height="100">
<img width="391" height="83" src="https://raw.githubusercontent.com/laravel/telescope/refs/heads/5.x/art/logo.svg" alt="Logo Laravel Telescope">
</p>

<p align="center">
<a href="https://github.com/masmerise/de-ridder-den-hertog-for-laravel/actions"><img src="https://github.com/masmerise/de-ridder-den-hertog-for-laravel/actions/workflows/test.yml/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/masmerise/de-ridder-den-hertog-for-laravel"><img src="https://img.shields.io/packagist/dt/masmerise/de-ridder-den-hertog-for-laravel" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/masmerise/de-ridder-den-hertog-for-laravel"><img src="https://img.shields.io/packagist/v/masmerise/de-ridder-den-hertog-for-laravel" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/masmerise/de-ridder-den-hertog-for-laravel"><img src="https://img.shields.io/packagist/l/masmerise/de-ridder-den-hertog-for-laravel" alt="License"></a>
</p>

## Telescope transformer for De Ridder & Den Hertog SDK

This package provides readable JSON payloads within the Telescope dashboard and tags the requests by individual actions.

### Before

```json
[
]
```

### After

```json
{
	"APIGuid": "********",
	"Action": "SetCustomer",
	"Customer": {
		"City": "redacted",
		"HouseNumber": "redacted",
		"LandCodeISO2": "BE",
		"Street": "redacted",
		"Zipcode": "9000",
		"Geboortedatum": "redacted",
		"Emailadress": "redacted",
		"Name": "Muhammed Sarı",
		"Phone": "redacted",
		"Company": 0
	}
}
```

## Installation

You can install the package via [composer](https://getcomposer.org):

```bash
composer require masmerise/de-ridder-den-hertog-for-laravel-telescope
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security

If you discover any security related issues, please email support@masmerise.be instead of using the issue tracker.

## Credits

- [Muhammed Sari](https://github.com/mabdullahsari)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.