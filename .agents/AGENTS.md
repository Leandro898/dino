# Reglas del Proyecto

## Estructura de Vistas de Blade
- **Uso de Layouts**: Todas las vistas de Blade deben extender o utilizar un componente de Layout (por ejemplo, `<x-app-layout>`, `<x-guest-layout>` o un layout personalizado).
- **Prohibición de HTML Duplicado**: Ninguna vista individual de Blade debe definir la estructura base de un documento HTML (`<!DOCTYPE html>`, `<html>`, `<head>`, `<body>`), a menos que sea un archivo de Layout raíz.
- **Estilos Desacoplados**: Evitar el uso de bloques `<style>` extensos dentro de las vistas individuales. En su lugar:
  - Utilizar clases de Tailwind CSS directamente en el marcado HTML.
  - Colocar los estilos personalizados en los archivos CSS globales o en los layouts compartidos si es estrictamente necesario.
- **Uso de Componentes Reutilizables**: Priorizar el uso de componentes de Blade del proyecto (como `<x-text-input>`, `<x-input-label>`, o `<x-input-error>`) para formularios y elementos recurrentes en lugar de escribir código HTML crudo y repetitivo.

## Uso de Seeders y Base de Datos
- **Prohibición de Seeders**: Bajo ninguna circunstancia el asistente de IA debe proponer o ejecutar comandos de seeders (`php artisan db:seed`, `migrate:fresh --seed`, etc.) en el entorno de desarrollo o producción, para evitar la pérdida o sobrescritura de datos reales. Cualquier población de base de datos mediante seeders debe ser realizada exclusivamente de forma manual por el usuario.

\n## Idioma\n- **Español Requerido**: Todas las comunicaciones, resúmenes y descripciones de las herramientas deben estar estrictamente en español para que el usuario pueda comprender las acciones realizadas por el asistente.
