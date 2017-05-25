# Introducción

*Trabajo para la FIB-UPC sobre la colaboración a un proyecto open source.*

ostocket es un plugin para la plataforma osTicket que añade una opción de enlazar stocks registrados a los tickets creados.

Actualmente se puede instalar el plugin pero salta un error al navegar por la página luego de haberlo habilitado.

# Tiempo invertido:

4h en aprendizaje de la herramienta osticket + aprendizaje sobre la creación de un plugin para ésta

3h para preparar la presentación, contando retoques finales cambiando algunas cosas

29h de implementación del plugin. Desglose de horas invertidas por commits:
  - 3h hasta init BDD version 1
  - 6h configurando installer version 1
  - 11h core plugin
  - 7h update core plugin
  - 1h add twig
  - 1h fix install error
  
Total: ~36h

# Instalación:

Necesario instalar XAMPP o LAMP según sistema operativo y activar Apache y MySQL.

Para instalar la plataforma osTicket:

Clonar el repositorio de osticket o descargarlo como zip: https://github.com/osTicket/osTicket.git

Ubicar el contenido dentro de la carpeta /htdocs y visitar la página via navegador para instalar la herramienta.

Para instalar el plugin: 

Clonar este repositorio o descargarlo como zip y ubicar el contenido en el directorio osticket, dentro de la carpeta include/plugins.

Ir a localhost/"nombre_osticket"/scp para acceder al panel de control con las credenciales creadas previamente.

Acceder a Admin Panel -> Manage -> Plugin -> Add New Plugin y instalamos "Stock controller in tickets".

Antes de habilitarlo, seleccionar el nombre del plugin y activar Backend/Frontend.
