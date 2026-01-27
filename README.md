# RouteMapper

![CI](https://github.com/yeremi/route-mapper/actions/workflows/php-qa.yml/badge.svg?branch=main)
![PHP Version](https://img.shields.io/badge/php-%3E%3D8.0-blue)
![License](https://img.shields.io/github/license/yeremi/route-mapper)
![Latest Version](https://img.shields.io/packagist/v/yeremi/route-mapper)
![Downloads](https://img.shields.io/packagist/dt/yeremi/route-mapper)

**RouteMapper** is a lightweight, framework-agnostic PHP library that uses **PHP 8 attributes** to map and resolve API route paths defined via PHP attributes in a simple and explicit way.

It focuses on one responsibility only: **mapping routes to methods**, without imposing an HTTP framework or execution model.

---

## Design Philosophy

- RouteMapper focuses on **one responsibility only**: mapping routes to methods.
- It does **not** handle HTTP requests, middleware, controllers, or responses.
- Integration is explicit by design — no magic containers or hidden behavior.
- The library favors **clarity over convenience**.
- If you need a full routing framework, RouteMapper is probably not the right tool.

---

## Key Features

- **Attribute-Based Routing** using PHP 8 attributes
- **Framework-Agnostic** (works with any HTTP client or framework)
- **Lightweight & Focused** — no unnecessary abstractions
- **Explicit Route Resolution** — no magic, full control
- **Modern PHP** — strict typing and PHP 8 features

---

## Requirements

- PHP 8.0 or higher

---

## Why Use RouteMapper?

Use RouteMapper when you want to keep routing metadata close to your code (via attributes) and still remain framework-agnostic.
It works well for SDKs, API clients, small services, and projects that want explicit, testable route resolution without adopting a full stack.

---

## Installation

Install RouteMapper via Composer:

```bash
composer require yeremi/route-mapper
```

---

## Example Usage

> Routes are resolved using the class name and method name.

### Defining routes with attributes

```php
use Yeremi\RouteMapper\Attribute\ApiRoute;
use Yeremi\RouteMapper\Registry\RouteRegistry;
use Yeremi\RouteMapper\Resolver\RouteResolver;

// (for this example): `composer require guzzlehttp/guzzle`
use GuzzleHttp\Client; // Example only

class UserRepository
{
    public function __construct(
        protected RouteRegistry $routeRegistry,
        protected RouteResolver $routeResolver,
        protected Client $httpClient
    ) {
        // Registers all #[ApiRoute] attributes in this class
        $this->routeRegistry->registerRoutes($this);
    }

    #[ApiRoute('/users')] // Defines only the route path
    public function fetchAll(): void
    {
        $route = $this->resolveRoute(__FUNCTION__);
        $response = $this->httpClient->get($route);
        echo $response->getBody()->getContents();
    }
    
    #[ApiRoute('/user/{id}')]
    public function fetchOne(int $id): void
    {
        $route = $this->resolveRoute(__FUNCTION__, ['id' => $id]);
        $response = $this->httpClient->get($route);

        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getBody()->getContents(), true);
            // Process the $data as needed.
        }
    }

    #[ApiRoute('/user/create')]
    public function create(array $data): void
    {
        $route = $this->resolveRoute(__FUNCTION__);
        $response = $this->httpClient->post($route, [
            'json' => $data,
        ]);
        echo "User Created: " . $response->getBody()->getContents() . "\n";
    }

    #[ApiRoute('/user/{id}/update')]
    public function update(int $id, array $data): void
    {
        $parameters = ['id' => $id];
        $route = $this->resolveRoute(__FUNCTION__, $parameters);

        $response = $this->httpClient->put($route, [
            'json' => $data,
        ]);
        echo "User Updated: " . $response->getBody()->getContents() . "\n";
    }

    #[ApiRoute('/user/{id}/delete')]
    public function delete(int $id): void
    {
        $parameters = ['id' => $id];
        $route = $this->resolveRoute(__FUNCTION__, $parameters);

        $response = $this->httpClient->delete($route);
        echo "User Deleted: " . $response->getBody()->getContents() . "\n";
    }

    private function resolveRoute(string $methodName, array $parameters = []): string
    {
        $route = $this->routeRegistry->getRoute(static::class, $methodName);

        if (!$route) {
            throw new \RuntimeException("Route not found for method: $methodName");
        }

        return $this->routeResolver->resolve($route, $parameters);
    }
}

// Usage example:
$routeRegistry = new RouteRegistry();
$routeResolver = new RouteResolver();
$httpClient = new Client([
    'base_uri' => 'https://api.example.com',
    'timeout'  => 5.0,
]);

$userRepository = new UserRepository($routeRegistry, $routeResolver, $httpClient);
// Fetch one user
$userRepository->fetchOne(123);
// Fetch all users
$userRepository->fetchAll();
// Create a user
$userRepository->create(['name' => 'John Doe', 'email' => 'john@example.com']);
// Update a user
$userRepository->update(123, ['name' => 'John Doe Updated']);
// Delete a user
$userRepository->delete(123);
```

> Note: RouteMapper does not perform HTTP requests. Any HTTP client (Guzzle, Symfony HttpClient, etc.) can be used.

---

## Comparison with Other Solutions

| Feature                 | RouteMapper | Symfony Routing | Laravel Routing | Slim Framework |
|-------------------------|-------------|-----------------|-----------------|----------------|
| **Attribute-based**     | ✅           | ✅               | ❌               | ❌              |
| **Framework-Agnostic**  | ✅           | ❌               | ❌               | ✅              |
| **Lightweight**         | ✅           | ⚠️              | ❌               | ✅              |
| **Explicit resolution** | ✅           | ❌               | ❌               | ⚠️             |

## Acknowledgments

Inspired by the flexibility of modern PHP attributes and the simplicity of middleware-based frameworks.

---

## License

RouteMapper is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
