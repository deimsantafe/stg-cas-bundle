Stg CAS Bundle 
==============

Central Authentication Service para Symfony 4.4 

Instalación
============

1). Instalación desde packagist.org: https://packagist.org/packages/stgbundle/cas-bundle

composer require stgbundle/cas-bundle

2). Registrar el bundle en el archivo `config/bundles.php`:

```php
<?php

return [
    //...
    Stg\Bundle\CasGuardBundle\CasGuardBundle::class => ['all' => true],
];
```

3). Ajustar la configuración de seguridad (security.yml)

```yaml
security:
    # ...
    providers:
        app_user_provider:
            entity:
                class: App\Entity\Usuario
                property: cuil
    firewalls:
        secure:
            pattern: ^/secure
            provider: app_user_provider
            guard:
                authenticators:
                     - phpcasguard.cas_authenticator
            logout:
                path: /secure/logout
                success_handler: phpcasguard.cas_authenticator
        main:
            anonymous: lazy
    # ...

```

4). Configuración del bundle

Crear el archivo `config\packages\cas_guard.yaml`:

```yaml
cas_guard:
    hostname: dsso.santafe.gob.ar
    url: /service-auth # opcional
    port: 443 # opcional
    logout_redirect: home  # opcional
    login_failure: failure # opcional - Debe definirse en el área pública
    debug: true # opcional - Se recomienda false en producción
    version: "3.0" # opcional
```

5). Agregar las rutas vacias

```php
// src/Controller/DefaultController.php

    /**
     * @Route("/secure/logout")
    */
    public function logout()
    {
    }
```

6). Uso con Ajax

El bundle detecta cuando la llamada es ajax y en caso de necesitar autorización no lo
redirige a la pantalla del sso, sino que devuelve el código http 401 (Unauthorized).
Queda en el cliente detectar esta respuesta y redirigir la llamada.



