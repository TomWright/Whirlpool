Whirlpool Framework
===================

Introduction
------------
Whirlpool framework is a lightweight MVC framework based on a few different composer packages.

Installation
---------------
### Installing the framework
The best way to install the framework is through composer. The following command will install the framework to /new/project/location

    composer create-project whirlpool/framework /new/project/location

### Setting up the environment
By default, whirlpool expects all web requests to go to the index.php file in the public directory. I suggest setting
the "public" directory as your web/document root.

Getting Started
---------------
### Routing
Routes are located at `application/routes.php`. You can set up routes in here that map to a specific controller and method.
If no method is specified the default will be used which can be found in `config/routing.php`.
For more help with creating routes please see [Aura.Router](https://github.com/auraphp/Aura.Router)

### Controllers
Controllers are located at `application/controllers`. Each method that can be an action should be followed by "Action".
Each controller should end with the text "Controller" and the file name must match the controller name. A BaseController
is provided with Whirlpool and it is recommended that your controllers extend `Whirlpool\BaseController`.

### Models
The models are located at `application/models`. The `Whirlpool\BaseModel` extends Eloquent so extending from it will
give you access to all the wonderful things that Eloquent provides. For help with Eloquent see the [documentation](http://laravel.com/docs/4.2/eloquent)

### Views
Views are handled with [Twig](http://twig.sensiolabs.org/). When your controller extends from `Whirlpool\BaseController` you
will have access to the twig object via `$this->twig`. A shortcut to display views has also been provided in the form of
`$this->displayView($viewName [, array $data = array()]);`.

Subdomains
----------
Subdomains can be a useful way to separate logic for different sections of your applications.
You can use `Request::subdomain()` to get the active subdomain. Please note that there is a list of subdomains to ignore
in `config/general.php`.

### Get going with subdomains
All you have to do to start using a subdomain is to create a folder in `application/subdomains`. Inside this new folder
you will need to create the folders `controllers`, `models` and `views`. This folder will be used as the main application
folder when the specified subdomain is active, and the `application/models` folder will act as a fallback.