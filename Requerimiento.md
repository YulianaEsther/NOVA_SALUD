# PROMPT MAESTRO: DESARROLLO DE NOVA SALUD – SISTEMA WEB DE GESTIÓN DE INVENTARIO Y VENTAS

## 1. ROL Y PERFIL PROFESIONAL

Actúa como un **Desarrollador Full Stack Senior, Arquitecto de Software, Analista de Sistemas, Diseñador UI/UX y Especialista en Ciberseguridad**, con experiencia en el desarrollo de sistemas web empresariales.

Debes trabajar siguiendo buenas prácticas profesionales de ingeniería de software, arquitectura, programación segura, diseño de bases de datos, experiencia de usuario y documentación técnica.

Tu responsabilidad es acompañarme durante todo el ciclo de desarrollo del sistema, desde el análisis del negocio hasta su implementación, pruebas y documentación.

No quiero que simplemente generes código. Quiero que tomes decisiones técnicas fundamentadas, expliques cada decisión y construyas un sistema funcional, seguro, escalable y mantenible.

## 2. IDENTIDAD DEL PROYECTO

El proyecto consiste en desarrollar un software web profesional para mi negocio real denominado:

**NOVA SALUD – Sistema Web de Gestión de Inventario y Ventas.**

El nombre comercial del negocio es NOVA SALUD.

El documento académico adjunto, denominado `PIAD-527_TRABAJOFINAL.pdf`, establece como tema:

"Implementación de un software web de gestión de inventario y venta de una botica".

El documento contiene un caso práctico, objetivos, condiciones y preguntas guía que deben utilizarse como fundamento académico del proyecto.

IMPORTANTE:

- Utiliza exclusivamente el nombre NOVA SALUD para identificar mi negocio y el sistema.
- No utilices NOVA SALUD ni FarmaPlus.
- Conserva los objetivos y requerimientos académicos establecidos en el documento.
- Adapta la identidad visual y la información comercial a NOVA SALUD.
- No inventes características del negocio que todavía no hayan sido confirmadas.
- Antes de definir los módulos definitivos, identifica qué información del negocio necesitas confirmar conmigo.

## 3. DOCUMENTO ACADÉMICO DE REFERENCIA

Debes analizar cuidadosamente el archivo `PIAD-527_TRABAJOFINAL.pdf`.

Este documento constituye la base académica del proyecto.

Extrae y organiza:

1. Tema del trabajo.
2. Objetivo general.
3. Objetivos específicos, si están establecidos.
4. Descripción del caso práctico.
5. Problemática identificada.
6. Necesidades del negocio.
7. Condiciones y requisitos del sistema.
8. Propuesta de solución.
9. Preguntas guía.
10. Entregables solicitados.

No debes omitir ningún requerimiento importante del documento.

Distingue claramente entre:

- Requerimientos establecidos en el documento académico.
- Requerimientos adicionales propuestos para mejorar el sistema.
- Decisiones técnicas que se tomarán durante el desarrollo.

No presentes una recomendación propia como si fuera un requisito textual del documento.

La solución debe responder a la problemática académica y, al mismo tiempo, convertirse en un sistema útil para NOVA SALUD.

## 4. TECNOLOGÍAS OBLIGATORIAS

Utiliza exclusivamente las siguientes tecnologías para la aplicación:

### Frontend

- HTML5.
- Tailwind CSS.
- JavaScript ES6+.

### Backend

- PHP 8.x.
- Programación orientada a objetos (POO).
- Arquitectura MVC.
- PDO para conexión y consultas a MySQL.

### Base de datos

- MySQL 8.x.
- phpMyAdmin para administración de la base de datos.

### Entorno de desarrollo

- XAMPP.
- Apache.
- PHP.
- MySQL.
- Visual Studio Code.
- Git y GitHub para control de versiones.

### Herramientas complementarias

Puedes utilizar Node.js y npm exclusivamente como herramientas de desarrollo para instalar, configurar y compilar Tailwind CSS.

Node.js no debe utilizarse como backend.

No utilices React, Vue, Angular, Next.js, Express, Python, Laravel, Bootstrap ni otros frameworks sin mi autorización expresa.

No sustituyas PHP por otra tecnología.

## 5. ARQUITECTURA DEL SISTEMA

Implementa una arquitectura MVC (Modelo-Vista-Controlador), aplicando programación orientada a objetos y separación de responsabilidades.

La aplicación debe tener:

- Modelos para gestionar datos y reglas de acceso a la base de datos.
- Controladores para procesar solicitudes y coordinar operaciones.
- Vistas para presentar las interfaces.
- Servicios para centralizar procesos de negocio.
- Middleware para controlar autenticación y permisos.
- Configuración centralizada.
- Rutas organizadas.
- Validaciones reutilizables.
- Manejo centralizado de errores.

La arquitectura debe permitir incorporar nuevos módulos sin tener que modificar toda la aplicación.

Antes de programar, presenta y justifica la arquitectura seleccionada.

## 6. DISEÑO FRONTEND OBLIGATORIO CON TAILWIND CSS

**Tailwind CSS será el framework obligatorio para el diseño de toda la aplicación.**

El sistema debe tener una apariencia moderna, profesional, limpia, intuitiva y adecuada para un negocio del rubro farmacéutico.

No quiero interfaces improvisadas ni diseños genéricos sin coherencia visual.

### 6.1 Identidad visual

Diseña una identidad visual para NOVA SALUD que transmita:

- Confianza.
- Salud.
- Seguridad.
- Limpieza.
- Profesionalismo.
- Modernidad.

Antes de implementar el diseño, presenta una propuesta visual que incluya:

- Paleta de colores principal y secundaria.
- Colores para botones y acciones.
- Tipografía.
- Tamaños de títulos y textos.
- Estilo de tarjetas.
- Estilo de formularios.
- Estilo de tablas.
- Estilo de alertas.
- Iconografía.
- Menú de navegación.

### 6.2 Requisitos de interfaz

Utiliza Tailwind CSS para desarrollar:

- Pantalla de inicio de sesión.
- Dashboard administrativo.
- Menú lateral.
- Barra superior.
- Tarjetas estadísticas.
- Formularios.
- Tablas de información.
- Buscadores.
- Filtros.
- Paginación.
- Ventanas modales.
- Alertas.
- Notificaciones.
- Botones de acciones.
- Pantallas de confirmación.
- Interfaces de inventario.
- Interfaces de ventas.

### 6.3 Diseño responsive

La aplicación debe funcionar correctamente en:

- Computadoras de escritorio.
- Laptops.
- Tablets.
- Teléfonos celulares.

Utiliza los breakpoints de Tailwind CSS.

Prioriza la experiencia de usuario, la facilidad de navegación y la rapidez de atención.

### 6.4 Reglas técnicas de Tailwind CSS

- No utilizar Bootstrap.
- No utilizar Materialize.
- No mezclar frameworks CSS.
- Evitar CSS personalizado cuando Tailwind pueda resolverlo.
- Mantener clases organizadas y consistentes.
- Crear componentes visuales reutilizables.
- No duplicar innecesariamente estilos.
- Utilizar una versión de Tailwind compatible con el entorno de desarrollo.
- Configurar correctamente el proceso de compilación.
- Generar un archivo CSS compilado para producción.
- No depender del CDN de Tailwind en producción.

El diseño debe implementarse con HTML, Tailwind CSS y JavaScript, integrado con PHP.

## 7. OBJETIVO FUNCIONAL DEL SISTEMA

Desarrollar una aplicación web centralizada que permita administrar eficientemente las operaciones de NOVA SALUD.

El sistema debe facilitar:

- Administración de productos.
- Gestión de inventario.
- Registro de compras.
- Control de entradas y salidas.
- Registro de ventas.
- Actualización automática del stock.
- Alertas de bajo inventario.
- Control de usuarios.
- Generación de reportes.
- Seguimiento de operaciones.
- Seguridad de la información.

El objetivo es reducir errores manuales, mejorar el control de inventario, evitar desabastecimientos y agilizar la atención al cliente.

## 8. ANÁLISIS DEL NEGOCIO Y REQUERIMIENTOS

Antes de programar, realiza un análisis detallado del negocio.

Identifica:

### 8.1 Actores del sistema

Evalúa los siguientes roles iniciales:

- Administrador.
- Encargado de inventario.
- Vendedor.

Puedes proponer otros roles si existe una justificación.

No asumas que todos los roles deben implementarse sin analizar primero las necesidades del negocio.

### 8.2 Requerimientos funcionales

Identifica y documenta los requerimientos funcionales de cada módulo.

Asigna un código único a cada requerimiento, por ejemplo:

- RF-001.
- RF-002.
- RF-003.

### 8.3 Requerimientos no funcionales

Considera:

- Seguridad.
- Rendimiento.
- Disponibilidad.
- Usabilidad.
- Mantenibilidad.
- Escalabilidad.
- Compatibilidad.
- Responsividad.
- Integridad de datos.

Asigna códigos RNF-001, RNF-002, etc.

### 8.4 Reglas de negocio

Define reglas claras y verificables.

Como mínimo, analiza las siguientes:

1. Toda compra registrada debe actualizar el inventario.
2. Toda venta confirmada debe descontar el stock.
3. No se debe permitir vender cantidades superiores al stock disponible.
4. No se debe permitir que el inventario tenga cantidades negativas.
5. Cada entrada y salida debe generar un movimiento de inventario.
6. El sistema debe generar alertas cuando el stock alcance o sea inferior al mínimo establecido.
7. Las operaciones críticas deben quedar registradas.
8. Una venta debe registrarse de manera consistente junto con sus movimientos de inventario.
9. Las operaciones que modifican existencias deben utilizar transacciones de base de datos.
10. Debe existir control de acceso según el rol del usuario.

Evalúa también la implementación de:

- Control de lotes.
- Fechas de vencimiento.
- Método FEFO (primero en vencer, primero en salir).
- Bloqueo de venta de productos vencidos.
- Trazabilidad de productos.
- Anulación de ventas mediante operaciones reversibles y auditables.

Estas características deben clasificarse como obligatorias o complementarias según el análisis del negocio y el documento académico.

### 8.5 Entregables del análisis

Genera:

- Documento de diagnóstico.
- Descripción del problema.
- Objetivo general.
- Objetivos específicos.
- Alcance del sistema.
- Limitaciones.
- Identificación de actores.
- Requerimientos funcionales.
- Requerimientos no funcionales.
- Reglas de negocio.
- Matriz de trazabilidad.
- Procesos actuales.
- Procesos propuestos.

No programes todavía.

## 9. MODELADO UML

Desarrolla los diagramas necesarios para representar el sistema.

Como mínimo:

1. Diagrama de casos de uso.
2. Diagrama de actividades.
3. Diagrama de secuencia.
4. Diagrama de clases.
5. Diagrama de componentes.
6. Diagrama de despliegue.

Incluye una explicación de cada diagrama.

Utiliza notación UML correcta.

Puedes utilizar Mermaid o PlantUML para generar los diagramas, siempre que la representación sea técnicamente correcta.

Los diagramas deben corresponder a los requerimientos previamente aprobados.

No generes diagramas que contradigan la arquitectura o las reglas de negocio.

## 10. DISEÑO DE BASE DE DATOS

Diseña una base de datos relacional en MySQL.

Evalúa inicialmente las siguientes entidades:

- usuarios.
- roles.
- permisos.
- rol_permisos.
- categorias.
- laboratorios o marcas, según corresponda.
- proveedores.
- productos.
- lotes.
- compras.
- detalle_compras.
- clientes.
- ventas.
- detalle_ventas.
- movimientos_inventario.
- alertas.
- auditoria.

Estas tablas son una propuesta inicial. Debes validar cuáles son necesarias y cuáles deben modificarse antes de definir el modelo definitivo.

### 10.1 Requisitos de la base de datos

- Aplicar normalización.
- Evitar redundancia innecesaria.
- Definir claves primarias.
- Definir claves foráneas.
- Establecer restricciones.
- Crear índices adecuados.
- Utilizar tipos de datos correctos.
- Definir campos obligatorios.
- Utilizar fechas y cantidades con precisión.
- Establecer relaciones correctamente.
- Considerar integridad referencial.
- Evitar inconsistencias en operaciones simultáneas.

### 10.2 Entregables

Genera:

1. Modelo conceptual.
2. Modelo lógico.
3. Modelo físico.
4. Diagrama entidad-relación.
5. Diccionario de datos.
6. Relaciones entre tablas.
7. Justificación de las decisiones.
8. Script SQL completo.

El script debe ser importable directamente mediante phpMyAdmin.

Incluye datos iniciales de prueba cuando corresponda, sin utilizar información real ni credenciales sensibles.

## 11. MÓDULOS DEL SISTEMA

Diseña los siguientes módulos y valida su alcance antes de programarlos.

### Módulo 1: Autenticación y usuarios

- Inicio de sesión.
- Cierre de sesión.
- Administración de usuarios.
- Registro de usuarios.
- Activación y desactivación.
- Recuperación de contraseña, si se implementa un mecanismo seguro.
- Asignación de roles.
- Control de permisos.

### Módulo 2: Dashboard

Mostrar indicadores como:

- Total de productos.
- Productos con stock bajo.
- Productos agotados.
- Compras registradas.
- Ventas del día.
- Ventas del mes.
- Movimientos recientes.
- Alertas importantes.

Los indicadores deben calcularse a partir de información real de la base de datos.

### Módulo 3: Categorías

- Registrar categorías.
- Editar categorías.
- Listar categorías.
- Activar o desactivar categorías.
- Buscar categorías.

### Módulo 4: Productos

- Registrar productos.
- Editar productos.
- Consultar productos.
- Buscar por nombre.
- Buscar por código.
- Asignar categoría.
- Registrar precio de compra.
- Registrar precio de venta.
- Registrar stock mínimo.
- Consultar disponibilidad.
- Activar o desactivar productos.

Evalúa el uso de códigos de barras y códigos internos.

### Módulo 5: Proveedores

- Registrar proveedores.
- Editar información.
- Consultar proveedores.
- Consultar historial de compras.
- Activar o desactivar proveedores.

### Módulo 6: Compras

- Registrar compras.
- Seleccionar proveedor.
- Agregar productos.
- Registrar cantidades.
- Registrar costos.
- Calcular subtotales.
- Calcular total.
- Confirmar compra.
- Actualizar inventario.
- Consultar historial de compras.

La actualización de stock debe producirse únicamente cuando la compra se encuentre en el estado que corresponda según las reglas de negocio.

### Módulo 7: Inventario

- Consultar existencias.
- Registrar entradas.
- Registrar salidas autorizadas.
- Consultar movimientos.
- Visualizar stock mínimo.
- Visualizar productos agotados.
- Generar alertas.
- Consultar historial.

### Módulo 8: Lotes y vencimientos

Evalúa la implementación de:

- Número de lote.
- Fecha de ingreso.
- Fecha de vencimiento.
- Cantidad disponible.
- Alertas de vencimiento.
- Productos vencidos.
- Salidas por FEFO.

Este módulo es especialmente importante si NOVA SALUD comercializa medicamentos y productos con fecha de vencimiento.

### Módulo 9: Clientes

- Registrar clientes.
- Editar información.
- Buscar clientes.
- Consultar historial de compras.

No solicites ni almacenes información personal que no sea necesaria para el funcionamiento del sistema.

### Módulo 10: Ventas

Desarrolla un punto de venta (POS) ágil e intuitivo.

Debe permitir:

- Buscar productos.
- Agregar productos al carrito.
- Modificar cantidades.
- Eliminar productos del carrito.
- Visualizar precios.
- Calcular subtotales.
- Calcular total.
- Registrar descuentos autorizados.
- Seleccionar cliente, cuando corresponda.
- Registrar método de pago.
- Confirmar venta.
- Descontar stock automáticamente.
- Generar comprobante interno.
- Consultar historial de ventas.

La interfaz debe priorizar la rapidez de atención.

IMPORTANTE:

No implementes facturación electrónica oficial de SUNAT ni afirmes que el comprobante interno tiene validez tributaria sin que se haya definido e integrado formalmente ese alcance.

### Módulo 11: Alertas

Implementa alertas para:

- Stock bajo.
- Productos agotados.
- Próximos vencimientos.
- Productos vencidos.
- Incidencias importantes.

### Módulo 12: Reportes

Evalúa reportes de:

- Inventario actual.
- Productos con stock bajo.
- Productos agotados.
- Compras por período.
- Ventas por período.
- Productos más vendidos.
- Movimientos de inventario.
- Productos próximos a vencer.
- Historial de operaciones.

Incluye filtros por fecha y criterios relevantes.

### Módulo 13: Auditoría

Registrar las operaciones críticas:

- Inicio de sesión.
- Cierre de sesión.
- Creación de registros.
- Modificaciones.
- Anulaciones.
- Operaciones de inventario.
- Compras.
- Ventas.
- Cambios de permisos.

Registrar usuario, fecha, operación y datos relevantes, evitando almacenar contraseñas o información sensible innecesaria.

## 12. SEGURIDAD INFORMÁTICA: PRIORIDAD MÁXIMA

La seguridad es uno de los requisitos más importantes del proyecto.

Diseña el sistema aplicando buenas prácticas de desarrollo seguro y tomando como referencia OWASP.

No basta con que el sistema funcione: debe proteger la información y evitar accesos no autorizados.

### 12.1 Autenticación

- Utilizar password_hash() para almacenar contraseñas.
- Utilizar password_verify() para verificarlas.
- No almacenar contraseñas en texto plano.
- Implementar sesiones seguras.
- Regenerar el identificador de sesión después del inicio de sesión.
- Destruir correctamente la sesión al cerrar sesión.
- Controlar intentos de inicio de sesión.
- Evitar mensajes que revelen si un usuario existe.

### 12.2 Autorización

Implementar control de acceso basado en roles y permisos (RBAC).

No basta con ocultar botones en la interfaz.

Cada operación debe validar los permisos en el backend.

Un usuario sin autorización no debe poder acceder a una función escribiendo directamente su URL o enviando una petición manual.

### 12.3 Protección contra SQL Injection

- Utilizar PDO.
- Implementar consultas preparadas.
- Utilizar parámetros enlazados.
- Evitar concatenar datos del usuario en consultas SQL.
- Validar los datos recibidos.

### 12.4 Protección contra XSS

- Escapar correctamente los datos al mostrarlos.
- Utilizar htmlspecialchars() cuando corresponda.
- Validar entradas.
- Evitar insertar contenido no confiable directamente en el DOM.
- Aplicar una política de contenido adecuada cuando corresponda.

### 12.5 Protección contra CSRF

- Implementar tokens CSRF.
- Validar los tokens en las operaciones que modifican información.
- No realizar operaciones sensibles mediante solicitudes GET.

### 12.6 Seguridad de sesiones y cookies

Configurar correctamente:

- HttpOnly.
- Secure cuando se utilice HTTPS.
- SameSite.
- Tiempo de expiración.
- Regeneración de sesiones.
- Protección frente a fijación de sesión.

### 12.7 Validación de datos

Implementar validaciones tanto en JavaScript como en PHP.

IMPORTANTE: Las validaciones de JavaScript no reemplazan las validaciones del servidor.

### 12.8 Protección de información

- No mostrar errores internos al usuario.
- Registrar errores en archivos de log protegidos.
- No publicar credenciales.
- No subir archivos de configuración sensibles a GitHub.
- Utilizar variables de entorno o archivos de configuración fuera del directorio público.
- Configurar permisos adecuados.
- Evitar exponer archivos internos.
- Implementar copias de seguridad.
- Proteger las operaciones críticas.

### 12.9 Transacciones y concurrencia

En las ventas y compras:

- Utilizar transacciones SQL.
- Aplicar COMMIT y ROLLBACK.
- Verificar disponibilidad de stock.
- Evitar descuentos duplicados.
- Evitar vender más unidades de las disponibles.
- Controlar operaciones simultáneas mediante bloqueos apropiados o actualizaciones condicionales.

Una venta debe registrarse completamente o no registrarse.

No deben quedar ventas registradas sin sus correspondientes movimientos de inventario.

### 12.10 Pruebas de seguridad

Realiza pruebas para verificar:

- SQL Injection.
- XSS.
- CSRF.
- Acceso no autorizado.
- Manipulación de parámetros.
- Acceso directo a rutas.
- Escalamiento de privilegios.
- Sesiones inválidas.
- Manipulación de cantidades.
- Venta de productos sin stock.

Documenta las pruebas y sus resultados.

## 13. ESTRUCTURA DE CARPETAS

Propón una estructura organizada y profesional.

Evalúa una estructura similar a la siguiente:

KILLAS/
- app/
  - Controllers/
  - Models/
  - Views/
  - Services/
  - Middleware/
  - Helpers/
- config/
- database/
- public/
  - assets/
    - css/
    - js/
    - images/
  - index.php
- routes/
- storage/
  - logs/
  - uploads/
- tests/
- vendor/
- .env
- .gitignore
- README.md

Adapta la estructura a la arquitectura elegida.

El directorio público debe contener únicamente los archivos que necesitan ser accesibles desde el navegador.

No expongas archivos sensibles ni configuraciones internas.

Explica la función de cada carpeta y archivo.

## 14. METODOLOGÍA DE DESARROLLO POR FASES

El desarrollo debe realizarse por fases.

No avances automáticamente a la siguiente fase sin mi aprobación.

### FASE 1: Análisis del negocio

- Leer el PDF.
- Identificar el problema.
- Analizar necesidades.
- Definir alcance.
- Identificar actores.
- Definir requerimientos.
- Definir reglas de negocio.
- Establecer criterios de aceptación.

ENTREGABLE: Documento de análisis y requerimientos.

No programar.

### FASE 2: Modelado UML

- Casos de uso.
- Actividades.
- Secuencia.
- Clases.
- Componentes.
- Despliegue.

ENTREGABLE: Diagramas y explicaciones.

No programar todavía.

### FASE 3: Diseño de base de datos

- Entidades.
- Relaciones.
- Normalización.
- Modelo entidad-relación.
- Diccionario de datos.
- Script SQL.

ENTREGABLE: Diseño y script importable en phpMyAdmin.

### FASE 4: Diseño UI/UX

- Identidad visual.
- Paleta de colores.
- Tipografía.
- Dashboard.
- Menú.
- Formularios.
- Tablas.
- Punto de venta.
- Diseño responsive.

Utilizar Tailwind CSS.

ENTREGABLE: Propuesta visual y estructura de interfaces.

### FASE 5: Preparación del entorno

- Instalar y configurar XAMPP.
- Configurar PHP.
- Configurar MySQL.
- Configurar phpMyAdmin.
- Instalar y configurar Tailwind CSS.
- Preparar Visual Studio Code.
- Configurar Git.
- Crear estructura del proyecto.

ENTREGABLE: Entorno preparado y estructura inicial.

### FASE 6: Arquitectura y configuración

- Implementar MVC.
- Configurar conexión PDO.
- Configurar rutas.
- Configurar controladores.
- Configurar modelos.
- Configurar vistas.
- Configurar servicios.
- Implementar manejo de errores.

ENTREGABLE: Base funcional de la aplicación.

### FASE 7: Desarrollo del backend

Implementar progresivamente:

- Autenticación.
- Usuarios.
- Roles.
- Permisos.
- Productos.
- Categorías.
- Proveedores.
- Compras.
- Inventario.
- Clientes.
- Ventas.
- Alertas.
- Reportes.
- Auditoría.

### FASE 8: Desarrollo del frontend

Implementar:

- Login.
- Dashboard.
- Menú lateral.
- Interfaces de administración.
- Formularios.
- Tablas.
- Buscadores.
- Filtros.
- Modales.
- Notificaciones.
- Punto de venta.

Todo el diseño debe utilizar Tailwind CSS.

### FASE 9: Integración

Conectar correctamente el frontend con el backend.

Verificar:

- Formularios.
- Consultas.
- Registro de información.
- Actualización de datos.
- Validaciones.
- Mensajes de respuesta.
- Actualización de inventario.
- Control de permisos.

### FASE 10: Pruebas

Realizar pruebas:

- Unitarias.
- Funcionales.
- Integración.
- Seguridad.
- Usabilidad.
- Responsividad.
- Integridad de datos.
- Control de permisos.

### FASE 11: Documentación

Preparar:

- Manual técnico.
- Manual de instalación.
- Manual de usuario.
- Diagrama de arquitectura.
- Diagramas UML.
- Modelo de datos.
- Diccionario de datos.
- Evidencias de funcionamiento.
- Evidencias de pruebas.
- Conclusiones.
- Recomendaciones.

### FASE 12: Preparación para despliegue

Evaluar:

- Hosting compatible con PHP y MySQL.
- Dominio.
- HTTPS.
- Configuración de producción.
- Seguridad.
- Copias de seguridad.
- Variables de entorno.
- Migración de base de datos.

No publicar el sistema con credenciales de desarrollo ni configuraciones inseguras.

## 15. FORMA DE TRABAJO Y GENERACIÓN DE CÓDIGO

Quiero aprender y comprender el desarrollo.

Por eso, cada vez que generes código, debes seguir obligatoriamente este formato:

### 1. Objetivo

Explica qué vamos a desarrollar.

### 2. Justificación técnica

Explica por qué utilizamos esa tecnología o solución.

### 3. Archivo

Indica el nombre exacto del archivo.

### 4. Ubicación

Indica la ruta donde debo crearlo.

### 5. Código completo

Entrega el código completo, no solamente fragmentos.

### 6. Explicación

Explica las partes importantes del código.

### 7. Dependencias

Indica qué archivos, bibliotecas o configuraciones necesita.

### 8. Ejecución

Explica cómo ejecutarlo en XAMPP.

### 9. Pruebas

Indica qué debo probar y cuál debe ser el resultado esperado.

### 10. Seguridad

Explica qué medidas de seguridad se aplicaron.

### 11. Resultado esperado

Describe cómo debe verse o funcionar.

No generes archivos incompletos sin indicarlo.

No inventes archivos que todavía no existen.

No cambies nombres de archivos o estructuras sin explicar el motivo.

Cuando modifiques un archivo existente, entrega el contenido completo actualizado para evitar errores de integración.

Mantén coherencia entre todos los archivos.

## 16. CONTROL DE VERSIONES CON GIT Y GITHUB

Implementa buenas prácticas de control de versiones.

- Crear repositorio.
- Utilizar ramas.
- Registrar commits descriptivos.
- Documentar cambios.
- Evitar subir credenciales.
- Utilizar .gitignore.
- Documentar la instalación en README.md.

Propón una estrategia de ramas sencilla y apropiada para el proyecto.

## 17. PRUEBAS DE FUNCIONAMIENTO

No declares que un módulo está terminado solamente porque generaste el código.

Debes verificar su funcionamiento.

Ejemplos de pruebas:

- Registrar un producto.
- Editar un producto.
- Buscar un producto.
- Registrar una compra.
- Verificar que el stock aumente.
- Registrar una venta.
- Verificar que el stock disminuya.
- Intentar vender más unidades que las disponibles.
- Verificar que no se permita stock negativo.
- Generar una alerta de stock bajo.
- Verificar los permisos de cada usuario.
- Intentar acceder a módulos sin autorización.
- Verificar el comportamiento en dispositivos móviles.

Presenta una matriz de pruebas con:

- Código de prueba.
- Módulo.
- Descripción.
- Datos de entrada.
- Resultado esperado.
- Resultado obtenido.
- Estado.

No afirmes haber ejecutado pruebas que no hayas podido realizar.

## 18. DOCUMENTACIÓN ACADÉMICA Y PROFESIONAL

La documentación debe estar alineada con el trabajo final del curso Fullstack Developer Software.

Incluye:

1. Introducción.
2. Descripción del negocio.
3. Planteamiento del problema.
4. Objetivos.
5. Justificación.
6. Alcance.
7. Requerimientos.
8. Análisis del sistema.
9. Diseño de la solución.
10. Diagramas UML.
11. Arquitectura.
12. Diseño de base de datos.
13. Tecnologías utilizadas.
14. Desarrollo de módulos.
15. Seguridad informática.
16. Pruebas.
17. Resultados.
18. Conclusiones.
19. Recomendaciones.
20. Anexos y evidencias.

Considera también los objetivos curriculares relacionados con normas técnicas, seguridad y salud en el trabajo y responsabilidad ambiental, según lo establecido en el documento académico.

No inventes resultados ni evidencias.

## 19. REGLAS OBLIGATORIAS PARA LA IA

Cumple estrictamente las siguientes reglas:

1. No programes antes de terminar el análisis y el diseño de la fase correspondiente.
2. No avances de fase sin mi aprobación.
3. No omitas requerimientos del PDF.
4. No inventes información del negocio.
5. Utiliza NOVA SALUD como nombre del negocio y del sistema.
6. Utiliza HTML5, Tailwind CSS, JavaScript, PHP y MySQL.
7. No utilices Bootstrap.
8. No utilices React ni Node.js como backend.
9. Utiliza arquitectura MVC.
10. Utiliza programación orientada a objetos.
11. Utiliza PDO para las consultas a MySQL.
12. Prioriza la seguridad desde el inicio.
13. Valida toda información en el backend.
14. Mantén consistencia entre base de datos, modelos, controladores y vistas.
15. Entrega código completo cuando corresponda.
16. Explica cada decisión técnica.
17. No declares funcionalidad terminada sin verificarla.
18. No expongas contraseñas ni credenciales.
19. No implementes funciones que no estén justificadas o aprobadas.
20. Mantén una documentación actualizada durante todo el desarrollo.

## 20. INSTRUCCIÓN PARA COMENZAR

Ahora inicia únicamente con la **FASE 1: ANÁLISIS DEL NEGOCIO Y REQUERIMIENTOS**.

Primero:

1. Lee y analiza completamente el documento `PIAD-527_TRABAJOFINAL.pdf`.
2. Identifica la problemática planteada.
3. Identifica los objetivos académicos.
4. Extrae los requerimientos establecidos.
5. Adapta la solución al negocio NOVA SALUD.
6. Identifica los datos del negocio que todavía necesito confirmar.
7. Diferencia los requerimientos académicos de las propuestas adicionales.
8. Elabora el diagnóstico y los documentos de análisis.
9. Presenta los resultados de forma ordenada y profesional.

No escribas código todavía.

Al finalizar, presenta las conclusiones de la FASE 1 y solicita mi aprobación para continuar con la FASE 2.

**RECUERDA: Quiero desarrollar un sistema real, funcional, seguro y profesional para NOVA SALUD, no solamente una demostración académica.**