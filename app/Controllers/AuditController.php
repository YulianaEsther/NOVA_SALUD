<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Models\Audit;

final class AuditController
{
    public function index(): void
    {
        $filters=['q'=>mb_substr(trim((string)($_GET['q']??'')),0,100),'accion'=>mb_substr(trim((string)($_GET['accion']??'')),0,100),'desde'=>trim((string)($_GET['desde']??'')),'hasta'=>trim((string)($_GET['hasta']??''))];
        foreach (['desde','hasta'] as $key) { $date=\DateTimeImmutable::createFromFormat('!Y-m-d',$filters[$key]); if ($filters[$key]!=='' && (!$date || $date->format('Y-m-d')!==$filters[$key])) $filters[$key]=''; }
        if ($filters['desde']!=='' && $filters['hasta']!=='' && $filters['desde']>$filters['hasta']) { $filters['desde']=''; $filters['hasta']=''; }
        $this->render(['title'=>'Auditoría','filters'=>$filters,'rows'=>(new Audit())->search($filters)]);
    }
    private function render(array $data): void { $app=require dirname(__DIR__,2).'/config/app.php'; $viewPath=dirname(__DIR__).'/Views/audit/index.php'; extract($data,EXTR_SKIP); require dirname(__DIR__).'/Views/layout.php'; }
}
