Stg CAS Bundle 
==============

Central Authentication Service para Symfony 5.4 

Instalación
============

1). Instalación desde packagist.org: https://packagist.org/packages/stgbundle/cas-bundle

composer require stgbundle/cas-bundle

2). Ajustar la configuración de seguridad (security.yml)

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
            custom_authenticators:
                - stg.cas_authenticator
            logout:
                path: /secure/logout
        main:
            anonymous: lazy
    # ...

```

3). Configuración del bundle

Crear el archivo `config\packages\cas.yaml`:

```yaml
cas:
    hostname: dsso.santafe.gob.ar
    url: /service-auth # opcional
    port: 443 # opcional
    user: cuil # Si se quiere acceder por cuil sino uid. Por defecto se utiliza cuil
    logout_redirect: home  # opcional
    login_failure: failure # opcional - Debe definirse en el área pública
    debug: true # opcional - Se recomienda false en producción
    version: "3.0" # opcional
```

4). Agregar las rutas vacias

```php
// src/Controller/DefaultController.php

    /**
     * @Route("/secure/logout")
    */
    public function logout()
    {
    }
```

5). Login failure

En caso de no existir el usuario en la base de datos de la aplicación, el bundle redirije la petición a la ruta definida en la
configuración del bundle y le envía como parámetro el cuil o uid ingresado, según corresponda

```php
    /**
    * @Route("/failure", name="failure")
    */    
    public function failure(Request $request): Response
    {
        return new Response(
            'Error al autenticar - Usuario: ' . $request->get('user')
        );
    }
```    

6). Uso con Ajax

El bundle detecta cuando la llamada es ajax y en caso de necesitar autorización no lo
redirige a la pantalla del sso, sino que devuelve el código http 401 (Unauthorized).
Queda en el cliente detectar esta respuesta y redirigir la llamada.



