CMS: El Financiero
===
#Backend API Privada

##Instalación
```
git clone https://dsolis_ef@bitbucket.org/dsolis_ef/api-elfinanciero.git [FOLDER]
```

```
composer update
```
```
sudo chmod -R 777 log
```

Dentro de la carpeta web:
```
ln -s /mnt/nfs/var/nfs/uploads2/ uploads
```

Después dar los permisos correspondientes a var/logs, y var/sessions

##Settings

### Symfony

#### Project creation

The project is generated in 2 ways:

* With Symfony command.
```
symfony new my_project_name
```
* By composer.
```
composer create-project symfony/framework-standard-edition my_project_name
```

To run we use:
```
php bin/console server:run
```

### Swagger

Swagger is the world’s largest framework of API developer tools for the OpenAPI Specification(OAS).
For this project based in PHP/Symfony, the tool to use is NelmioApiDocBundle (https://symfony.com/doc/current/bundles/NelmioApiDocBundle/index.html).

#### Installation
Is made by composer downloading the bundle:

```
composer require nelmio/api-doc-bundle
```
#### Enable the Bundle
Then, enable the bundle by adding it to the list of registered bundles in the app/AppKernel.php file of your project:
```
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
        );

        // ...
    }

    // ...
}
```
###Register the Routes
Import the routing definition in routing.yml:
```
# app/config/routing.yml
NelmioApiDocBundle:
    resource: "@NelmioApiDocBundle/Resources/config/routing.yml"
    prefix:   /api/doc
```
###Configure the Bundle
Enable the bundle's configuration in app/config/config.yml:
```
# app/config/config.yml
nelmio_api_doc: ~
```
The NelmioApiDocBundle requires Twig as a template engine so do not forget to enable it:
```
# app/config/config.yml
framework:
    templating:
        engines: ['twig']
```
###Usage
- The ApiDoc Annotation()

The bundle provides an ApiDoc() annotation for your controllers:
```
namespace Your\Namespace;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class YourController extends Controller
{
    /**
     * This is the documentation description of your method, it will appear
     * on a specific pane. It will read all the text until the first
     * annotation.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="This is a description of your API method",
     *  filters={
     *      {"name"="a-filter", "dataType"="integer"},
     *      {"name"="another-filter", "dataType"="string", "pattern"="(foo|bar) ASC|DESC"}
     *  }
     * )
     */
    public function getAction()
    {
    }

    /**
     * @ApiDoc(
     *  description="Create a new Object",
     *  input="Your\Namespace\Form\Type\YourType",
     *  output="Your\Namespace\Class"
     * )
     */
    public function postAction()
    {
    }

    /**
     * @ApiDoc(
     *  description="Returns a collection of Object",
     *  requirements={
     *      {
     *          "name"="limit",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="how many objects to return"
     *      }
     *  },
     *  parameters={
     *      {"name"="categoryId", "dataType"="integer", "required"=true, "description"="category id"}
     *  }
     * )
     */
    public function cgetAction($limit)
    {
    }
}
```
The following properties are available:

    
 - section: allow to group resources
 - resource: whether the method describes a main resource or not (default: false);
 - description: a description of the API method;
 - https: whether the method described requires the https protocol (default: false);
 - deprecated: allow to set method as deprecated (default: false);
 - tags: allow to tag a method (e.g. beta or in-development). Either a single tag or an array of tags. Each tag can have an optional hex colorcode attached.
 - filters: an array of filters;
 - requirements: an array of requirements;
 - parameters: an array of parameters;
 - headers: an array of headers; available properties are: name, description, required, default.
  
  Example:
  
```
class YourController
{
    /**
     * @ApiDoc(
     *     headers={
     *         {
     *             "name"="X-AUTHORIZE-KEY",
     *             "description"="Authorization key"
     *         }
     *     }
     * )
     */
    public function myFunction()
    {
        // ...
    }
}
```

 - input: the input type associated to the method (currently this supports Form Types, classes with JMS Serializer metadata, classes with Validation component metadata and classes that implement JsonSerializable) useful for POST|PUT methods, either as FQCN or as form type (if it is registered in the form factory in the container).
 - output: the output type associated with the response. Specified and parsed the same way as input.
 - statusCodes: an array of HTTP status codes and a description of when that status is returned; 
 
 Example:
 
 ```
 class YourController
 {
     /**
      * @ApiDoc(
      *     statusCodes={
      *         200="Returned when successful",
      *         403="Returned when the user is not authorized to say hello",
      *         404={
      *           "Returned when the user is not found",
      *           "Returned when something else is not found"
      *         }
      *     }
      * )
      */
     public function myFunction()
     {
         // ...
     }
 }
 ```
 
 - views: the view(s) under which this resource will be shown. Leave empty to specify the default view. Either a single view, or an array of views.
 
 ###Configuration Reference
 
 ```
 nelmio_api_doc:
     name:                 'API documentation'
     exclude_sections:     []
     default_sections_opened:  true
     motd:
         template:             'NelmioApiDocBundle::Components/motd.html.twig'
     request_listener:
         enabled:              true
         parameter:            _doc
     sandbox:
         enabled:              true
         endpoint:             null
         accept_type:          null
         body_format:
             formats:
 
                 # Defaults:
                 - form
                 - json
             default_format:       ~ # One of "form"; "json"
         request_format:
             formats:
 
                 # Defaults:
                 json:                application/json
                 xml:                 application/xml
             method:               ~ # One of "format_param"; "accept_header"
             default_format:       json
         authentication:
             delivery:             ~ # Required
             name:                 ~ # Required
 
             # Required if http delivery is selected.
             type:                 ~ # One of "basic"; "bearer"
             custom_endpoint:      false
         entity_to_choice:         true
     swagger:
         api_base_path:        /api
         swagger_version:      '1.2'
         api_version:          '0.1'
         info:
             title:                Symfony2
             description:          'My awesome Symfony2 app!'
             TermsOfServiceUrl:    null
             contact:              null
             license:              null
             licenseUrl:           null
     cache:
         enabled:              false
         file:                 '%kernel.cache_dir%/api-doc.cache'
 ```
 
 ### JWT
 
 JSON Web Tokens are an open, industry standard RFC 7519 method for representing claims securely between two parties.
 
 #### When should you use JSON Web Tokens? 
 
 Here are some scenarios where JSON Web Tokens are useful:
 
 - Authentication
 - Information Exchange
 
 #### What is the JSON Web Token structure?

- Header
- Payload
- Signature

#### How do JSON Web Tokens work?

In authentication, when the user successfully logs in using their credentials, a JSON Web Token will be returned and must be saved locally (typically in local storage, but cookies can be also used), instead of the traditional approach of creating a session in the server and returning a cookie. 

###@Settings

Add in composer.json the require:

```
"firebase/php-jwt":"^3.0.0",
"knplabs/knp-paginator-bundle":"2.5.*"
```

Define this version of Symfony:
```
"symfony/symfony": "3.0.7",
```
Update:
```
composer update
```

### User's rolls
#####Settings

User's rolls settings are a defined in mix between:

- All rolls skeleton is detailed in:
```
/Users/javiermorquecho/Sites/api financiero/app/config/security.yml
```
In sections of role_hierarchy and "access_control". And...

- In DB in table "wf_users" in column "roles".

#####Login
In order to get all user's capabilities starting a session in CMS. This step request these fields:
1. Username: string
2. Password: string
3. IsHash: boolean

If you isHash field has a false value you will see user's data.

#####Roles

After login is mandatory get roles in order to continue with the normal flow of the system.

The end points related are:
- /api/users/roles/ - Get all roles in system.
- /api/users/paths/ - Get enabled actions based in all roles enabled for current user.

### Categories

An category is a box where you can save many pages (articles or similar information products). All categories are formed in a tree, that implicates a root category and a single category for every section. 

This is the site's backbone for articles and notes.

With this, you have a tree in this basic form:

```
| Home |          |          |
|------|----------|----------|
|  |-> | Economía |          |
|      | Empresas |          |
|      | Mercados |          |
|      |      |-> | Acciones |
|      |          | IPC      |
|      |          | Divisas  |
|      |          | Divisas  |
```

In the tree, you can see a segment of categories where you can see: 

- A root category called "Home".
- Categories of the first level like "Economía", "Empresas" and "Mercados".
- Children of "Mercados": "Acciones", "IPC" and two more.

###Documentation
 
* This file:
api-elfinanciero\readme.md

* Symfony:
https://symfony.com/doc/current/bundles/NelmioApiDocBundle/index.html

* Swagger:
http://swagger.io/

* JWT:
https://jwt.io/

