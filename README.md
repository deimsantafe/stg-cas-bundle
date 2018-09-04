STG CAS Bundle
==================
Central Authentication Service en Symfony 2 - Basado en Rizeway Bundle


Installation
---------------

1). Instalación desde packagist.org: https://packagist.org/packages/stgbundle/cas-bundle

    composer require stgbundle/cas-bundle

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
        url: https://dsso.santafe.gob.ar/service-auth # Para testing https://tsso.santafe.gov.ar/service-auth
        server: https://dsso.santafe.gob.ar/service-auth # Para testing https://tsso.santafe.gov.ar/service-auth
        cert: false # false to bypass (opcional)
        username_attribute: cuil
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
