STG CAS Bundle
==================
Central Authentication Service en Symfony 2 - Basado en Rizeway Bundle


Installation
---------------

1). Agregar el repositorio en donde se encuentra el bundle a instalar. Si no 
    existe la clave "repositories" debe ser creada en el primer nivel del 
    archivo composer.json.

    {
    ...

        "repositories": [
            {
              "type": "composer",
              "url": "https://github.com/deimsantafe/stg-cas-bundle"
            }
         ],

    ...
    }

2). Agregar en el archivo composer.json el nombre y la versión del bundle:

    {
        ...

        "require": {
            ...

            "stg/cas-bundle": "4.0"

            ...
        }

        ...
    }

    Versiones:
        - dev-trunk (rama principal de desarrollo, no utilizar en producción)
        - 1.0
        - 1.1
        - 1.2
        - 2.0
        - 2.0.1
        - 3.0 - Se agrega soporte para CAS 3.0
        - 3.1 - Se agrega envio de parámetros al failure_path
        - 3.2 - Se quitan parámetros de usuario y exception a failure_path
        - 4.0 - Se agrega soporte para Symfony 3.4

3). Registrar el bundle en el archivo AppKernel.php:

    public function registerBundles()
    {
        ...

        $bundles = array(
            ...

            new STG\DEIM\Security\Bundle\CasBundle\CasBundle(),

            ...
        );

        ...
    }


4). Agregar en el archivo config.yml la configuración del endpoint a utilizar:

    cas:
        # desarrollo/testing
        url: https://dsso.santafe.gov.ar/service-auth
        server: https://dsso.santafe.gov.ar/service-auth # (only if different from the url, for server to server requests)
        cert: false # false to bypass (opcional)
        username_attribute: user  # (opcional)
        proxy: false # if you want to active the proxy cas mode (opcional)


5). Instalar y/o actualizar el bundle:

    $ composer install
    $ composer update


6). Ajustar la configuración de seguridad (security.yml)

    providers:
	in_memory:
            memory:
               users:
                   tuNetIDenCAS:  { password: noSeUsa, roles: [ 'ROLE_USER' ] }

# Para trabajar con entidades, definir el siguiente provider en lugar del anterior
        #usuarios:
        #   entity: { class: STG\DEIM\UsuarioBundle\Entity\Usuario, property: nombre }


# Definir los firewalls para las áreas seguras y públicas. 
# Tener en cuenta que el failure_path se define en el área pública

        firewalls:
            dev:
                pattern:  ^/(_(profiler|wdt)|css|images|js)/
                security: false

            public:
                pattern: ^/failure
                security: false

            secured_area:
                pattern:  ^/demo/secured/

	# Si define su propio provider, especifiquelo de la siguiente forma:
                #provider: usuarios

        # Indicar que se va a usar la autenticación por cas
                cas:
                    check_path: _demo_login
                    failure_path: _demo_failure
                logout:
                    path:   _demo_logout
                    target: _demo
                    invalidate_session: false

7). Agregar las rutas vacias para login check y logout en app/config/routing.yml
   
    _demo_login:
        path: /demo/secured/loginCAS 
        defaults:

    _demo_logout:
        path: /demo/secured/logoutCAS
        defaults: 
    
8). Agregar la ruta del failure_path en app/config/routing.yml

    _demo_failure:
        path: /failure
        defaults: { _controller: AppBundle:MyController:failure }
        

o en el controlador

    @Route("/failure", name="failure_path")