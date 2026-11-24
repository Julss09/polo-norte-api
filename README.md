# Polo Norte API — Escenario B

## Descripción

API REST para gestionar los renos, su asignación al vuelo navideño y los mantenimientos del terreno del Polo Norte. Desarrollada con Laravel 13, PHP 8.5 y MariaDB.

## Instalación y ejecución

1. Clonar el repositorio.
2. Ejecutar `composer install`.
3. Copiar `.env.example` como `.env`.
4. Ejecutar `php artisan key:generate`.
5. Crear la base de datos `polo_norte` en MariaDB.
6. Configurar las variables de entorno.
7. Ejecutar `php artisan migrate`.
8. Ejecutar `php artisan serve`.

## Variables de entorno

- DB_CONNECTION=mysql
- DB_HOST=127.0.0.1
- DB_PORT=3306
- DB_DATABASE=polo_norte
- DB_USERNAME: usuario local de MariaDB.
- DB_PASSWORD: contraseña local.
- API_KEY_AYUDANTE: token de prueba del ayudante.
- API_KEY_ELFO: token de prueba del elfo.
- METEO_SOAP_ENDPOINT: dirección del servicio meteorológico SOAP.

Para reproducir las pruebas, definir los tokens locales de prueba y configurar los mismos valores en las variables token_ayudante y token_elfo de Postman.

## Arquitectura

Postman → Rutas → Middleware → Controladores → Servicios → Repositorio → MariaDB.

Las reglas de negocio se encuentran en los servicios. MeteoAdapter realiza la comunicación SOAP con MeteoPoloNorteService y controla los fallos y tiempos de espera de tres segundos.

El manejador global de excepciones proporciona respuestas de error con una estructura JSON uniforme.

## Endpoints

| Método | Ruta |
|---|---|
| POST | /api/renos |
| GET | /api/renos |
| GET | /api/renos/{id} |
| PUT | /api/renos/{id} |
| DELETE | /api/renos/{id} |
| PATCH | /api/renos/{id}/asignacion |
| POST | /api/mantenimientos |
| GET | /api/mantenimientos |
| PATCH | /api/mantenimientos/{id}/estado |

## Seguridad

Las consultas son públicas. Las operaciones de escritura requieren autenticación mediante Bearer Token y el rol AYUDANTE. El rol ELFO permite comprobar el rechazo por permisos insuficientes.

## Resultados de las pruebas

Se ejecutaron 16 pruebas automatizadas en Postman: 16 aprobadas, 0 fallidas y 0 omitidas.

La prueba de ventisca devuelve 202 Accepted y guarda el mantenimiento como PENDIENTE_CLIMA, conforme al Escenario B.

Colección: PoloNorte.postman_collection.json.

## Microservicios

Separaría el sistema en un microservicio de renos, encargado de su energía, disponibilidad y asignación al vuelo, y otro de mantenimiento, responsable de las zonas, fechas, responsables y estados de las tareas. Cada servicio administraría su propia base de datos. El servicio de mantenimiento consumiría el servicio meteorológico mediante un adaptador SOAP. Si este último falla el 24 de diciembre a las 23:00, los trabajos exteriores quedarían en estado PENDIENTE_CLIMA y la API respondería 202 Accepted. Las operaciones de renos y los mantenimientos interiores continuarían funcionando independientemente. Los trabajos pendientes podrían confirmarse posteriormente mediante reintentos controlados.

## Diagrama de arquitectura

[Consultar diagrama](docs/arquitectura.png)
