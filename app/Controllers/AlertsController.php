<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\AuditLogger;
use App\Core\Database;
use App\Models\Alert;

final class AlertsController
{
    private Alert $alerts;
    public function __construct() { $this->alerts = new Alert(); }
    public function index(): void
    {
        $this->alerts->refresh();
        $status = (string) ($_GET['estado'] ?? '');
        if (!in_array($status, ['', 'activa', 'atendida', 'descartada'], true)) { $status=''; }
        $this->render('alerts/index.php', ['title'=>'Alertas de inventario', 'rows'=>$this->alerts->all($status), 'status'=>$status, 'success'=>\flash('success'), 'error'=>\flash('error')]);
    }
    public function attend(): void { $this->change('atendida', 'atender'); }
    public function dismiss(): void { $this->change('descartada', 'descartar'); }
    private function change(string $status, string $action): void
    {
        if (!\verify_csrf_token($_POST['_csrf'] ?? null)) { http_response_code(419); echo 'La sesión del formulario venció.'; return; }
        $id=filter_var($_POST['id'] ?? null,FILTER_VALIDATE_INT); $user=(int)($_SESSION['auth_user_id'] ?? 0);
        if (!$id || !$this->alerts->setStatus((int)$id,$status,$user)) { \flash('error','La alerta ya no está activa o no existe.'); }
        else { AuditLogger::record(Database::connection(),$user,$action,'alerta',(int)$id,['estado'=>$status]); \flash('success',$status==='atendida'?'Alerta marcada como atendida.':'Alerta descartada.'); }
        $this->redirect('/alertas');
    }
    private function render(string $template,array $data): void { $app=require dirname(__DIR__,2).'/config/app.php'; $viewPath=dirname(__DIR__).'/Views/'.$template; extract($data,EXTR_SKIP); require dirname(__DIR__).'/Views/layout.php'; }
    private function redirect(string $path): never { $app=require dirname(__DIR__,2).'/config/app.php'; header('Location: '.$app['url'].$path,true,303); exit; }
}
