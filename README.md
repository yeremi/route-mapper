# RouteMapper

**RouteMapper** is a lightweight PHP library designed to leverage PHP 8 attributes to map and resolve API routes effortlessly. Whether you’re building APIs or integrating with existing systems, RouteMapper simplifies the routing process, offering flexibility and customizability without the overhead of a full framework.

---

## Key Features

- **Attribute-Based Routing**: Define routes directly in your classes and methods using PHP 8 attributes.
- **Lightweight and Framework-Agnostic**: Perfect for standalone projects or as a complement to frameworks like Slim or Mezzio.
- **Simple Integration**: Easily integrate with tools like [Guzzle](https://docs.guzzlephp.org) or other HTTP clients.
- **Customizable and Flexible**: Full control over routing logic and resolution.
- **No Bloat**: Focused functionality without unnecessary dependencies.

---

## Why Use RouteMapper?

1. **Lightweight and Focused**
    - Unlike heavy frameworks, RouteMapper solves a specific problem: mapping routes with attributes. Ideal for small-to-medium projects or as a complement to other tools.

2. **Easy Integration**
    - RouteMapper can integrate seamlessly with HTTP clients (like Guzzle) or middleware-based frameworks, making it adaptable for various use cases.

3. **Customizability**
    - Developers retain full control of routing behavior while benefiting from the simplicity of attribute-based route definitions.

4. **Practicality for Modern PHP**
    - Embraces PHP 8 features, such as attributes and strict typing, ensuring modern, clean, and maintainable code.

---

## Installation

Install RouteMapper via Composer:

```bash
composer require yeremi/route-mapper
```

---

## Example Usage

### Setup

```php
use Yeremi\RouteMapper\Attribute\ApiRoute;
use Yeremi\RouteMapper\Registry\RouteRegistry;
use Yeremi\RouteMapper\Resolver\RouteResolver;
use GuzzleHttp\Client;

class UserRepository
{
    public function __construct(
        protected RouteRegistry $routeRegistry,
        protected RouteResolver $routeResolver,
        protected Client $httpClient
    ) {
        $this->routeRegistry->registerRoutes($this);
    }

    #[ApiRoute('/user/{id}')]
    public function fetchOne(): void
    {
        $parameters = ['id' => 123];
        $route = $this->resolveRoute(__FUNCTION__, $parameters);
        $response = $this->httpClient->get($route);

        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getBody()->getContents(), true);
            // Process the $data as needed.
        }
    }
    
    #[ApiRoute('/users')]
    public function fetchAll(): void
    {
        $route = $this->resolveRoute(__FUNCTION__, []);
        $response = $this->httpClient->get($route);
        echo "Fetched Users: " . $response->getBody()->getContents() . "\n";
    }

    #[ApiRoute('/user/create')]
    public function create(array $data): void
    {
        $route = $this->resolveRoute(__FUNCTION__, []);
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

    private function resolveRoute(string $methodName, array $parameters): string
    {
        $route = $this->routeRegistry->getRoute($this, $methodName);

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
$userRepository->fetchOne();
// Fetch all users
$userRepository->fetchAll();
// Create a user
$userRepository->create(['name' => 'John Doe', 'email' => 'john@example.com']);
// Update a user
$userRepository->update(123, ['name' => 'John Doe Updated']);
// Delete a user
$userRepository->delete(123);
```

---

## Comparison with Other Solutions

| Feature                        | RouteMapper             | Symfony Routing          | Laravel Routing          | Slim Framework           |
|--------------------------------|-------------------------|--------------------------|--------------------------|--------------------------|
| **Focus on Attributes**        | Yes                     | Yes                      | No                       | No                       |
| **Framework-Agnostic**         | Yes                     | No                       | No                       | Yes                      |
| **Lightweight**                | Yes                     | Moderate                 | No                       | Yes                      |
| **Customization**              | High                    | Moderate                 | Low                      | High                     |

## Acknowledgments

Inspired by the flexibility of modern PHP attributes and the simplicity of middleware-based frameworks.

---

## License

RouteMapper is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
