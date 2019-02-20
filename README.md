# Linkedin Share v2 API Integration for Laravel
This package allows you to share content in Linkedin with the v2 API

## Installation on Laravel

### Install with Composer

```bash
composer require lightit/linkedin-share
```

## Sharing content

You will need the user authentication code ($code) to be able to share content in behalf of the user, you will probabily get this code from the fornt-end of your application.

### Sharing text
```
LinkedinShare::shareNone($code, $text)
```

### Sharing images
```
LinkedinShare::shareImage($code, $image, $text)
```

### Sharing links
```
LinkedinShare::shareArticle($code, $url, $text)
```

## Extra functionality
### Getting access token from authentication code
```
LinkedinShare::getAccessToken($code)
```

## About Lightit
[Light-it](https://lightit.io) is a software development company with offices in Uruguay and Paraguay. 

## License
This project and the Laravel framework are open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
