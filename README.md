Stg CAS Bundle 
==============

Bundle de autenticación CAS (Central Authentication Service) para **Symfony 7.4** y **PHP 8.2+**.

---

## Requisitos

| Componente | Versión mínima |
|---|---|
| PHP | 8.2 |
| Symfony | 7.4 |
| symfony/security-bundle | 7.4 |

---

Instalación
============

1). Instalación desde packagist.org: https://packagist.org/packages/stgbundle/cas-bundle

composer require stgbundle/cas-bundle:"v8.0"

Nota: Debe tener instalado previamente el SecurityBundle:
composer require symfony/security-bundle

En el `config/bundles.php`, el `SecurityBundle` se debe cargar **antes** que `CasBundle`:

```php
return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class   => ['all' => true],
    STG\DEIM\Security\Bundle\CasBundle\CasBundle::class  => ['all' => true],
    // ... otros bundles
];
```


2). Ajustar la configuración de seguridad  — `config/packages/security.yaml`

```yaml
security:
    providers:
        cas_users:
            entity:
                class: App\Entity\Usuario
                property: cuil

    firewalls:
        dev:
            pattern: ^/(_profiler|_wdt|assets|build|cas/proxy-callback)/
            security: false

        main:
            # lazy: false es obligatorio con CAS: el entry point debe dispararse
            # para requests anónimas sin esperar credenciales del usuario.
            lazy: false
            provider: cas_users
            cas:
                check_path: /cas/check      # Ruta donde CAS redirige con el ticket
                failure_path: /failure      # Ruta de error en caso de fallo
            entry_point: cas.authenticator.main
            logout:
                path: /secure/logout        # Ruta de logout de la aplicación

    access_control:
        - { path: ^/cas/check, roles: PUBLIC_ACCESS }
        - { path: ^/failure,   roles: PUBLIC_ACCESS }
        - { path: ^/,          roles: ROLE_USER }
```
Nota: entry_point varía según cómo se llame el firewall ($firewallName) puede ser que sea secure, entonces se define
entry_point: cas.authenticator.secure

3). Configuración del bundle

Crear el archivo `config\packages\cas.yaml`:

```yaml
cas:
    url: https://dsso.santafe.gov.ar/service-auth
    cert: false
    username_attribute: 'cuil' 
    proxy: false
```

4). La aplicación necesita al menos estas tres rutas:

```php
// src/Controller/DefaultController.php
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController
{
    #[Route('/failure', name: 'failure')]    
    public function failure(Request $request): Response
    {
        return new Response(
            'Error al autenticar - Usuario: ' . $request->get('user')
        );
    }

    #[Route('/secure/logout', name: 'logout')]
    public function logout()
    {
    }

    #[Route('/cas/check', name: 'cas_check')]
    public function casCheck(): Response
    {
        // El CasAuthenticator intercepta esta ruta cuando CAS redirige con ?ticket=
        // Si se llega aquí sin ticket, redirigir al inicio.
        return new RedirectResponse('/');
    }
```