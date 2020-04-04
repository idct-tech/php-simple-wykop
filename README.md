# php-simple-wykop
Simple PHP client for the Wykop.pl API v2

# installation

The best way to install the library in your project is by using Composer:

```
composer require idct/php-simple-wykop
```

of course you can still manually include all the required files in your project using using statements yet Composer and autoloading is more than suggested.

# TODOs and contribution

The main area which requires a lot of work and contribution is the coverage of dedicated methods. Apart from that any updates on tests (mostly missing) or code-style is always more than welcome! Please create Pull Requests or file Issues with your input.

# Usage

First, create an instance:
```php
use IDCT\Wykop\SimpleWykop();
$wykop = new SimpleWykop($appKey, $appSecret);
```

## Where to get `$appKey`, `$appSecret` from?

You need to register an __API v2__ app in the "For programmers" section of Wykop.pl; usually it is here:
https://www.wykop.pl/dla-programistow/

If you are creating an application for yourself you can already pass the `$connectionKey`:
```php
use IDCT\Wykop\SimpleWykop();
$wykop = new SimpleWykop($appKey, $appSecret, $connectionKey);
```

## How to obtain the `$connectionKey`?

There are two ways to do so: 

### for applications made for yourself

Simply click the connect button ("Połącz aplikację") on the list of your apps in Wykop's panel:
https://www.wykop.pl/dla-programistow/twoje-aplikacje/

### for multiple users who authenticate using their browsers

Yet if you want to handle multiple users you need to first make them to connect with your app using Wykop's auth methods:

1. Create an instance of the app and generate the connection url:
```php
$wykop = new SimpleWykop($appKey, $appSecret);
$returnUrl = 'https://mydomain.pl/myscript.php';
$connectUrl = $wykop->getConnectUrl($returnUrl);
```

`$returnUrl` should be a script on your server which will handle the response data, which includes user's connection key.

2. Redirect user to the connection url using your own frontend somehow or by calling:
```php
header('Location' . $connectUrl);
exit();
```

3. In the script which handles your return url parse the response by calling:
```php
$wykopConnect = $wykop->parseWykopConnectData();
```

By default it expects the returned information to be in `connectData` GET parameter, but if you handled that somehow differently you can simply pass the base64-encoded array of returned contents as the first argument.

In case of a failure (missing parameters) it will throw an exception.

4. Now set the `connectionKey` in the instance of your `SimpleWykop`:
```php
$wykopConnect->setConnectionKey($connectionKey);
```

And done.

# Making requests

It is highly recommended to use dedicated methods, yet if one is not implemented (at most likely it is not) then you can use a generic one:

```php
$response = $this->execute($function, $arguments, $filesInfo, $page);
```

Arguments:
- `$function` should be replaced with the name of a method available in Wykop's API. For example "notifications/index".
- `$arguments` should be an array of parameters required by the function.
- `filesData` array of files' data posted with the input arguments.
- `$page` if function can return multiple pages then here you enter page number.

The response - if not a failure - will be an instance of `GenericResponse` class which holds the whole response as an array accessible using the `getData` method. If there is a next or previous page then `getPaginationNext` `getPaginationNext` will hold values instead of null (urls, ignore the urls when using the library just handle the fact that there is a value).

# Dedicated methods

## Login 

```php
$wykopConnect->login($username);
```

Will attempt to sign in as the user identified by username (that user must be already linked to your app using the `$connectionKey`).

On success will return an instance of the `Profile` entity which holds basic information about the user. `loginKey` and `login` attributes of `SimpleWykop` instance will be also set. 

Other option is to sign in manually using:

```php
$wykopConnect->loginManually($username, $loginKey);
```

Such login attempt will assume that you already know the login key (for example from previous signing in and retrieved it using `$wykop->getLoginKey()`). __Warning:__ no verification is executed, it assumes that you know what you are doing.

**warning:** login key is temporary, but you should always try to re-use it as Wykop's API has a very low limit of actions per hour and login attempt is also considered an action.

Dedicated methods will always attempt to re-login in case of a `InvalidWykopCredentials` exception so be sure to check `getLoginKey()` in case you store it somewhere after execution of all actions.

## Logout 

```php
$wykopConnect->logout();
```

Clears the `login` and `loginKey` attributes, but performs no remote actions as simply Wykop's API does not offer such methods.

## retrieveNotifications

As the name suggests it retrieves user's notifications, this includes PMs info. Does not include __hashtags__ notifications, for that there is a special method.

It creates an `Iterator` of class `Notifications` over `GenericResponse` which creates __new__ instances of __Notification__ on every iteration therefore be aware of potential memory implications if you iterate the results set over and over. 

Sample usage:

```php
$notifications = $wykop->retrieveNotifications();

foreach ($notifications as $notification) {
    var_dump( $notification->getUrl() );
}
```    

Since `Notifications` extends `GenericResponse` you can still access raw array of data using `getData` or informationa about next/prev pages.

## retrieveHashtagNotifications

Very similar to the `retrieveNotifications` method is simply retrieves user's hashtags' notifications.

It creates an `Iterator` of class `Notifications` over `GenericResponse` which creates __new__ instances of __Notification__ on every iteration therefore be aware of potential memory implications if you iterate the results set over and over. 

Sample usage:

```php
$notifications = $wykop->retrieveHashtagNotifications();

foreach ($notifications as $notification) {
    var_dump( $notification->getUrl() );
}
```    

Since `Notifications` extends `GenericResponse` you can still access raw array of data using `getData` or informationa about next/prev pages.





