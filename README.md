# Tails Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/devdojo/tails.svg?style=flat-square)](https://packagist.org/packages/devdojo/tails)
[![Total Downloads](https://img.shields.io/packagist/dt/devdojo/tails.svg?style=flat-square)](https://packagist.org/packages/devdojo/tails)

![Tails Package Image](https://cdn.devdojo.com/images/july2022/tails-package-image.jpeg)

This package will allow you to easily fetch designs that you have created in the [Tails](https://devdojo.com/tails) page builder. Follow the steps below to learn how to install, configure, and use this package.

## 1. Install The Package

You can install the package via composer:

```bash
composer require devdojo/tails
```

## 2. Publish the Config

Run the following command to publish the Tails config:

```bash
php artisan vendor:publish --tag=tails
```

## 3. Get Your API Key

Visit [https://devdojo.com/tails/app](https://devdojo.com/tails/app) and click the Developer API Key button from the menu.

![Developer API Key Menu Item](https://cdn.devdojo.com/images/july2022/dev-api-key-menu.png)

When you click this button it will open up a modal window that looks like the following:

![Generate New API Key](https://cdn.devdojo.com/images/july2022/generate-dev-api-key.png)

Simply, click the **Generate My API Key** button and you'll be presented with your new API Key.

![Tails API Key](https://cdn.devdojo.com/images/july2022/tails-api-key.png)

If you ever need to re-generate your key, you can click the **Re-generate My API Key** button and it will be refreshed.

## 5. Add API key to `.env`

You will need to add your API key that you got in the previous step to your `.env` file. It should look something like the following: 

```shell
TAILS_API_KEY=b6561d56fcf291ecd34627f95814da8c8771b32f31caccbb2e8578639518351e
```

## 6. Test the Connection

We've added a really simple artisan call for you to test out the functionality and confirm that your application is talking to the Tails API. Simply, run the following artisan command.

```shell
php artisan tails:ping
```

If you get a response that says **pong**, you have successfully authenticated and connected to the API.

### 7. Displaying Designs

We've made it really easy to display the designs you created in tails inside of your application. You may choose to display the page on a specific route or you may wish to add the design as an include inside of an existing blade file. Let's cover displaying the page on a route first.

#### Displaying the page via a Route

The simplest way is to add a Tails route is to add the following inside of your `routes/web.php` file:

```php
Tails::get('/', 'project-name');
```

This will load the design from the homepage of a project called `project-name`. You can find the project **slug** for your specific project by clicking on the settings bar at the top of a project.

![Tails Settings](https://cdn.devdojo.com/images/july2022/tails-settings.png)

This will show you the following modal:

![Tails Project Settings Modal](https://cdn.devdojo.com/images/july2022/tails-project-slug.png)

In the example from the image above, the slug you would use is in this case is`my-website`, so your route would look like the following:

```php
Tails::get('/', 'my-website');
```

This doesn't have to map to the homepage, it could map to any route:

```php
Tails::get('welcome', 'my-website');
```

Additionally, you can load any page from project by using dot notation, like so:

```php
Tails::get('about', 'my-website.about');
```

The code above 👆 will fetch the design from an `about` page inside the `my-website` project and load it up from your application at the `/about` route 🤙 Easy peasy, like Mac & Cheesy!

#### Display the Design in an Existing File

Next, you may want to include a specific design inside of an existing blade file. You can easily accomplish this with the following syntax:

```php
@tails('my-website');
```

Using dot notation, you can also load the design for a specific page:

```php
@tails('my-website.about')
```

By default these Blade helpers is only going to give you the content inside the `<body>` of the design. You may also need to load the specific minified styles for that HTML, you can easily do that with the following code:

```php
<style>
    @tails('my-website:page.styles')
</style>

@tails('my-website')
```

Using the `@tails` directive you can retrieve all the information about a specific page. Here is an example structure of the API response:

```javascript
{
    html: 'Full HTML of the page',
    header: 'Header info for the page',
    body: 'Body content for the page',
    footer: 'Footer content for the page',
    project: {
        title: 'Title',
        slug: 'slug',
        icon: 'icon',
        icon_type: 'Type os icon or link (may also be upload, but this is also just a link url)',
        settings: 'settings',
        custom_head: 'custom head content',
        custom_footer: 'custom footer content',
        cdn_css: 'Comma separated list of CSS CDN links',
        cdn_js: 'Comma separated list of JS CDN links',
        created_at: 'Date Created',
        updated_at: 'Last Updated'
    },
    page: {
        title: 'Title',
        slug: 'slug',
        settings: 'settings',
        custom_head: 'custom head content',
        custom_footer: 'custom footer content',
        styles: 'The TailwindCSS styles, you will need to wrap these in a <style> tag if you wish to apply the styles to a page',
        order: 'Really only relevant in the editor, but you can also use these orders to display them somewhere on your site',
        created_at: 'Date Created',
        updated_at: 'Last Updated',
        components: {
            [
                html: 'HTML of specific component',
                order: 'The order of the component on the page',
                created_at: 'Date Created',
                updated_at: 'Last Updated',
            ]
        }
    }
}
```

You can retrieve all this information using the @tails directive like so:

```php
@tails('my-website');
@tails('my-website:html');
@tails('my-website:header');
@tails('my-website:body');
@tails('my-website:footer');

@tails('my-website:project.title');
@tails('my-website:project.slug');
...

@tails('my-website:page.title');
@tails('my-website:page.slug');
...
```

### 8. Enable the Webhook

Be default all the requests that are made to fetch designs from the Tails API is cached on your applications end. This means that when a user visits your website or application it doesn't have to talk to the Tails API, resulting in faster page loads. Unfortunately since it's cached, you application needs to know when a new version of this page has been updated and show the updated version to your users. You can easiily solve this by enabling the webhook.

### Enabling Webhook and setting your Webhook URL

In the project settings modal there is a tab on the left called **Webhook**, on this tab you will need to turn on the webhook and enter in your webhook URL:

![Webhook](https://cdn.devdojo.com/images/july2022/tails-webhook.png)

Your webhook URL is going to be `your-website.com/tails/webhook`, this route `/tails/webhook`, has already been added in this package. All you have to do is enter the URL inside of the textarea.

### Adding the Webhook Key to your `.env`

In the Webhook modal you will also see another input labeled `Webhook Key`, you will need to copy this value and paste it into your `.env` file like so:

```
TAILS_WEBHOOK_KEY=DWpXWMHxAdePDleePCYyGgEs2VGe6j
```

This is a security measure, it will gaurantee that the Tails application is the only application sending requests to that endpoint. After you've added this key to your environment file and updated the correct URL in the Webhook modal, you should be all set.

Each time you are working in the Tails application and a page is saved, it will send a webhook to your application telling it which pages need to be cleared from the cache, this way the new version will be served up the next time someone visits that page.

### 9. That's it 🍻

That's the basics on how to setup this package and use with [Tails](https://devdojo.com/tails). 

Thanks for using Tails and feel free to reach out to [us on Twitter](https://twitter.com/thedevdojo) with any issues or recommendations and we'll try and get back to you in an appropriate time frame. Thanks again for using our products. You rock 🤘


### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email tony@devdojo.com instead of using the issue tracker.

## Credits

-   [Tony Lea](https://github.com/devdojo)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
