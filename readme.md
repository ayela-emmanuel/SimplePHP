### Simple PHP Framework Documentation

---

### **Table of Contents**
1. [Introduction](#introduction)
2. [Installation](#installation)
3. [Core Components](#core-components)
    - [Routing](#routing)
    - [HTTP Context](#http-context)
    - [Middleware](#middleware)
4. [Features](#features)
    - [Routing](#routing-feature)
    - [Request Handling](#request-handling)
    - [File Upload](#file-upload)
    - [Response Handling](#response-handling)
    - [Middleware Support](#middleware-support)
    - [CORS Middleware](#cors-middleware)
    - [Auto Documentation(API Explorer)](#auto-doc)
5. [Configuration](#configuration)
6. [Debugging](#debugging)
7. [DataTransformer](#data-transformer)
8. [Example Usage](#example-usage)


---

### **1. Introduction** <a id="introduction"></a>

The simplePHP framework provides essential tools to create modern web applications. It includes routing, request and response handling, middleware support, and features for handling file uploads with validation. The framework is designed with flexibility in mind, and uses PHP 8’s attributes for route-specific middleware and annotations for lower versions of PHP.

---

### **2. Installation** <a id="installation"></a>

#### **Requirements**:
- PHP 7.4+ (PHP 8+ recommended for attributes support)

#### **Installation Steps**:
1. Clone the repository or use composer to create project via:
   ```
   composer create-project simple-php-web/simple-php-web
   ```
3. Run `composer install` to set up dependencies.
4. Configure the `public/index.php` file as your entry point for your application.  
```php 
<?php 
include_once __DIR__."/../index.php";
?>
```



---

### **3. Core Components** <a id="core-components"></a>

The framework is composed of several core components:

- **Routing**: Defines how incoming HTTP requests are matched to controller actions.
- **HTTP Context**: Handles the request and response objects to encapsulate HTTP data.
- **Middleware**: Allows you to intercept requests and responses for additional processing.

---

### **4. Features** <a id="features"></a>

#### **4.1. Routing** <a id="routing-feature"></a>

The framework includes a simple routing mechanism that maps URLs to controller actions. It supports defining routes using PHP attributes (in PHP 8+) and annotations (for PHP 7.4+).

**Features**:
- **Route Attributes**: Easily define routes in your controllers.
- **Route-Specific Middleware**: Apply middleware to specific routes using attributes or annotations.
- **Support for Route Groups**: You can define common prefixes or middleware for multiple routes.
  
**Example**:  
Recommended Folder Structure for Src
```bash
src/
│
├── Controllers/
│   ├── API/
│   │   ├── BaseApiController.php
│   │   ├── UserController.php
│   │   └── ProductController.php
│   │   └── # Other API controllers files
│   ├── WEB/
│   │   ├── BaseWebController.php
│   │   ├── HomeController.php
│   │   ├── AuthController.php
│   │   └── # Other Other Web controllers
│
├── Middleware/
│   ├── AuthMiddleware.php
│   └── # Other Middleware files
│
├── Models/
│   ├── Data/
│   │   └── UserLoginModel.php
│   ├── Database/
│   │   └── AppUser.php
│
├── Templates/
│   ├── Layouts/
│   │   ├── BaseLayout.latte
│   │   └── # Other layout templates
│   ├── HomePage.latte
│   └── # Other page templates
│
└── # Other core directories and files as needed
```
*it is recommended to follow psr-4 standard for namespaces*


```php
namespace App\Controllers\WEB;
class FooController
{
    #[Route('/users', 'GET')]
    public function listUsers(Request $request, Response $response) {
        // Logic to list users
    }

}
```

For older versions of PHP, use annotation-based comments to define routes:
```php
namespace App\Controllers\WEB;
class FooController
{
    /**
     * @Route("/users", methods={"GET"})
     */
    public function listUsers(Request $request, Response $response) {
        // Logic to list users
    }
}
```
after creating your controller you register them in `index.php`:

```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Internal\Router\Router;
use Internal\Http\Request;
use Internal\Http\Response;

//Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


// Initialize Router
$router = new Router();

// Add controllers
//API
$router->addRoute(new App\Controllers\API\IndexController(),"api"); // You can group controllers by adding a prefix
//WEB
$router->addRoute(new App\Controllers\WEB\MainController());

// Add Global Middlewares 
// Look at the Middleware section to creat additional middleware
$globalMiddlewares = [
    Internal\Middleware\LogRequestMiddleware::class,
    Internal\Middleware\CorsMiddleware::class,
];

// Handle Request
/**
 * You can extend from the request 
 * and response classes to add and 
 * override methods and have your 
 * own thing going
*/
$request = new Request();
$response = new Response();


foreach ($globalMiddlewares as $middlewareClass) {
    $middleware = new $middlewareClass();
    $middleware->handle($request, $response, fn() => null); // Global middleware
}



// Route handling and response middleware
$router->handle($request, $response);


```
---

#### **4.2. Request Handling** <a id="request-handling"></a>

The `Request` class provides a structured way to access HTTP request data, including query parameters, request body, and file uploads.

**Features**:
- **Query Parameters**: Get `$_GET` parameters in a structured way.
- **Request Body**: Get `$_POST` or JSON request bodies.
- **Strongly Typed Body**: Optionally deserialize request data into specific PHP models.

**Example**:

```php
$queryParams = $request->getQueryParams();
$postData = $request->getBody(true); // For Form requests
$postData = $request->getBody(false); // For JSON requests
```

You can also automatically map query parameters or body data to PHP objects using the `getBody()` and `getQueryParams()` methods:

```php
$deserializedData = $request->getBody(false, MyModelClass::class);
```

---

#### **4.3. File Upload** <a id="file-upload"></a>

The framework provides robust file upload handling via the `FileModel` class. It includes features for validating file type, size, and moving uploaded files to a designated directory.

**Features**:
- **Get Uploaded Files**: Retrieve uploaded files via `getFiles()` or `getFile()`.
- **Validate File Type and Size**: Ensure that only specific file types and sizes are allowed.
- **Save Uploaded Files**: Save files to a specified directory.

**Example**:

```php
$file = $request->getFile('profile_picture');
$file->validateType(['image/jpeg', 'image/png']);
$file->validateSize(2 * 1024 * 1024); // 2MB
$file->save('/path/to/uploads');
```

---

#### **4.4. Response Handling** <a id="response-handling"></a>

The `Response` class provides methods to send HTTP responses back to the client. It supports sending JSON responses, setting headers, and HTTP status codes.

**Example**:

```php
$response->json(['message' => 'Data saved successfully.'], 200);
```
or
```php
$response->json(new MyResponseClass(true,"Data saved successfully."), 200);
```
---

#### **4.5. Middleware Support** <a id="middleware-support"></a>

Middleware is central to this framework, allowing you to process requests and responses at different stages of the lifecycle.

**Features**:
- **Global Middleware**: Register middleware globally in the application index.
- **Route-Specific Middleware**: Apply middleware to specific routes using attributes or annotations.
- **Response Middleware**: Modify the response after the controller has executed.

**Example**:

Creating Your Middleware:
```php
declare(strict_types=1);

namespace App\Middleware;

use Internal\Http\Request;
use Internal\Http\Response;
use Internal\Middleware\Middleware;


class AuthMiddleware implements Middleware
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        //logic
        $next();  // Call the next middleware or controller action
        //logic after
    }
}
```
Applying Your Middleware:  
Register middleware globally in `index.php`
```php
$middlewareStack->register(new AuthMiddleware());
```
Apply middleware to a specific route
```php
#[Middleware(AuthMiddleware::class)]
public function updateProfile(Request $request, Response $response) {
    // Controller logic
}
```
or apply to whole controller
```php
namespace App\Controllers\WEB;
#[Middleware(AuthMiddleware::class)]
class FooController
{
    #[Route('/users', 'GET')]
    public function listUsers(Request $request, Response $response) {
        // Logic to list users
    }

}
```
---

#### **4.6. CORS Middleware** <a id="cors-middleware"></a>

The framework includes a customizable Cross-Origin Resource Sharing (CORS) middleware. This middleware helps control which domains can access your API or web application.

**Example**:

```php
class CorsMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        return $response;
    }
}
```

To register it globally:

```php
$middlewareStack->register(new CorsMiddleware());
```

---

---

#### **4.7. Auto Documentation** <a id="auto-doc"></a>
## **1. `AutoDocAttribute`**

The `AutoDocAttribute` is used to automatically generate API documentation for controllers and routes. It signals that the annotated class or method should be included in the API documentation generation process.

To access the api documentation go to `/debug/docs/api`.

### **Usage**  
You apply the attribute to a class or controller that contains routes:

```php
#[AutoDocAttribute]
class IndexController
{
    #[Route("GET", "/")]
    public function home(Request $request, Response $response)
    {
        $response->setStatusCode(200);
        $response->json(new ApiResponseModel(true, "Welcome to simple php"));
    }
}
```

### **Effects**  
- Automatically scans and includes the annotated class or method in the API documentation tool.
- Collects metadata like route paths, HTTP methods, and responses for documentation generation.


## **2. `AutoDocComment`**

### **Purpose**  
The `AutoDocComment` attribute allows you to add custom comments or descriptions for individual API routes. These comments are included in the auto-generated documentation.

### **Usage**  
You apply the attribute to specific methods where you want to add descriptive comments:

```php
#[AutoDocComment("This endpoint returns a test response.")]
#[Route("POST", "/test")]
public function test(Request $request, Response $response)
{
    $response->setStatusCode(200);
    $response->json(new ApiResponseModel(true, "Welcome to simple php"));
}
```

### **Effects**  
- Adds a human-readable description to the API documentation for the method or route.
- Provides clarity on the purpose of the endpoint to developers or API consumers.
---


## **3. `RequestModelAttribute`**

### **Purpose**  
The `RequestModelAttribute` is used to bind and validate incoming request data against a specified model class. It ensures the request payload adheres to a defined structure.

### **Usage**  
You annotate a method with the attribute, passing a model class that handles the request data validation:

```php
#[Route("GET", "/")]
#[RequestModelAttribute(ApiResponseModel::class)]
public function home(Request $request, Response $response)
{
    $response->setStatusCode(200);
    $response->json(new ApiResponseModel(true, "Welcome to simple php"));
}
```

### **Effects**  
- Simply for Documentation Generation(may be used for validation in the near future)


### **5. Configuration** <a id="configuration"></a>

To configure the framework for your environment:

- **Routing**: Configure the routes in `routes.php` or use the `Route` attribute directly in controllers.
- **Middleware**: Register middleware globally during the application index or apply them to specific routes.

In **CPanel/Apache environments**, ensure that `.htaccess` is set up to direct all requests to `index.php`:

```bash
# Enable URL rewriting
RewriteEngine On

# Set the base directory
RewriteBase /

# Prevent directory listing
Options -Indexes

# Exclude requests for actual files in public/www or public/upload directories
RewriteCond %{REQUEST_URI} ^/public/www/ [OR]
RewriteCond %{REQUEST_URI} ^/public/upload/
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^ - [L]

# Avoid redirecting requests already aimed at index.php
RewriteCond %{REQUEST_URI} !^/index\.php
RewriteRule ^(.*)$ index.php [L]
```

---

### **6. Debugging** <a id="debugging"></a>

When the application is running we can find logs at:

**Log Route**:  `/dev/logs`

this will automatically log in the event of a 500 error  
also the logs can be found in `/logs/.log`

---
### **7. DataTransformer** <a id="data-transformer"></a>

## Overview
The `DataTransformer` class provides utility methods for transforming data between arrays and objects, validating data, and mapping data formats based on specified mappings.

---

## Methods

### 1. `deserialize`
#### Description:
Deserializes an associative array into an object of the specified type.

#### Template:
- `@template T`

#### Parameters:
- `array<string, mixed> $data`: The array to deserialize.
- `class-string<T> $modelType`: The fully qualified class name of the target object.

#### Returns:
- `T`: The deserialized object.

#### Throws:
- `InvalidArgumentException`: If the specified class does not exist or if there are unexpected keys in the input data.

#### Example:
```php
$data = ['name' => 'John', 'age' => 30];
$person = DataTransformer::deserialize($data, Person::class);
```

---

### 2. `serialize`
#### Description:
Serializes an object into an associative array.

#### Parameters:
- `object $object`: The object to serialize.

#### Returns:
- `array<string, mixed>`: The serialized array.

#### Example:
```php
$person = new Person();
$person->name = 'John';
$person->age = 30;
$data = DataTransformer::serialize($person);
```

---

### 3. `validateData`
#### Description:
Validates the input data against the required properties of a given model class.

#### Template:
- `@template T`

#### Parameters:
- `array<string, mixed> $data`: The data to validate.
- `class-string<T> $modelType`: The fully qualified class name of the target model.

#### Returns:
- `void`

#### Throws:
- `InvalidArgumentException`: If the specified class does not exist or if required properties are missing from the data.

#### Example:
```php
$data = ['name' => 'John'];
DataTransformer::validateData($data, Person::class);
```

---

### 4. `mapData`
#### Description:
Transforms an associative array into a new format based on a mapping configuration.

#### Parameters:
- `array<string, mixed> $data`: The source data.
- `array<string, string> $mapping`: An associative array mapping source keys to target keys.

#### Returns:
- `array<string, mixed>`: The transformed array.

#### Example:
```php
$data = ['firstName' => 'John', 'lastName' => 'Doe'];
$mapping = ['firstName' => 'first_name', 'lastName' => 'last_name'];
$transformed = DataTransformer::mapData($data, $mapping);
```

---

## Usage Examples

### Deserialization and Serialization:
```php
// Define a class
class Person {
    public $name;
    public $age;

    public function validate() {
        if (empty($this->name) || $this->age < 0) {
            throw new InvalidArgumentException("Invalid data");
        }
    }
}

// Deserialize
$data = ['name' => 'John', 'age' => 30];
$person = DataTransformer::deserialize($data, Person::class);

// Serialize
$serializedData = DataTransformer::serialize($person);
```

### Validation:
```php
$data = ['name' => 'Jane'];
DataTransformer::validateData($data, Person::class);
```

### Data Mapping:
```php
$data = ['firstName' => 'John', 'lastName' => 'Smith'];
$mapping = ['firstName' => 'first_name', 'lastName' => 'last_name'];
$transformedData = DataTransformer::mapData($data, $mapping);
```



---
### **7. Example Usage** <a id="example-usage"></a>

Here's an example of a controller with a simple route, file upload, and response:

```php
<?php

use Internal\Http\Request;
use Internal\Http\Response;

class UserController
{
    #[Route('/users', 'POST')]
    public function createUser(Request $request, Response $response): void
    {
        // Handle file upload
        $file = $request->getFile('profile_picture');
        $file->validateType(['image/jpeg', 'image/png']);
        $file->validateSize(2 * 1024 * 1024); // 2MB max size
        $filePath = $file->save('/path/to/uploads');
        
        // Respond with success message
        $response->json(['message' => 'User created successfully', 'profile_picture' => $filePath]);
    }
}
```

