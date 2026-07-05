<?php

namespace App\Console\Commands;

use App\Services\CarrefourAlmacenImportService;
use Illuminate\Console\Command;
use Throwable;

class ImportCarrefourAlmacen extends Command
{
    protected $signature = 'products:import-carrefour-almacen
        {--department=almacen : Slug de la categoria de Carrefour a importar}
        {--user= : ID del usuario propietario de los productos importados}
        {--chunk=50 : Cantidad de productos por lote}
        {--max-pages= : Limita la cantidad de paginas a importar para pruebas}
        {--deactivate-missing : Desactiva productos importados que ya no existan en Carrefour}';

    protected $description = 'Importa productos de la categoria Carrefour Almacen a la tabla products';

    public function handle(CarrefourAlmacenImportService $service): int
    {
        try {
            $result = $service->import(
                departmentSlug: (string) $this->option('department'),
                userId: $this->option('user') !== null ? (int) $this->option('user') : null,
                chunkSize: max(1, (int) $this->option('chunk')),
                maxPages: $this->option('max-pages') !== null ? max(1, (int) $this->option('max-pages')) : null,
                deactivateMissing: (bool) $this->option('deactivate-missing'),
            );
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

        $this->info('Importacion Carrefour Almacen finalizada.');
        $this->line('Usuario propietario: ' . $result['owner_id']);
        $this->line('Registros remotos informados: ' . $result['records_filtered']);
        $this->line('Paginas procesadas: ' . $result['pages_processed']);
        $this->line('Productos creados: ' . $result['created']);
        $this->line('Productos actualizados: ' . $result['updated']);
        $this->line('Productos desactivados: ' . $result['deactivated']);

        return self::SUCCESS;
    }
}
