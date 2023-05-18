<?php

namespace Com\Daw2\Controllers;

class listadoController extends \Com\Daw2\Core\BaseController {
    
    public function datos(){
         $data = array(
            'titulo' => 'Listado',
            'breadcrumb' => ['Inicio', 'Listado'],
            'seccion' => 'listado'
        );
        
        $data['input'] = filter_var_array($_GET, FILTER_SANITIZE_SPECIAL_CHARS);
        
        $modelProveedor = new \Com\Daw2\Models\ProveedoresModel();
        
        if(isset($_GET['order']) && filter_var($_GET['order'], FILTER_VALIDATE_INT)){
            $order = $_GET['order'];
        }else{
            $order = 1;
        }
        
        $pagina = 1;
        if(isset($_GET['page']) && filter_var($_GET['page'], FILTER_VALIDATE_INT) && $_GET['page'] > 1){
            $pagina = $_GET['page'];
        }
        $data['pagina'] = $pagina;
        $data['data'] = $modelProveedor->obtenerFiltrado($_GET, $order, $pagina);
        $data['continentes'] = $modelProveedor->getContinentes();
        $data['tipos'] = $modelProveedor->getTipo();
        $data['final'] = $modelProveedor->count($_GET);
        
        $copia = $_GET;
        unset($copia['page']);
        $data['urlPaginado'] = count($copia) > 0 ? '&'.http_build_query($copia) : '';
        unset($copia['order']);
        $data['url'] = count($copia) > 0 ? '&'.http_build_query($copia) : '';
        
        $this->view->showViews(array('templates/header.view.php', 'proveedores.view.php', 'templates/footer.view.php'), $data);
    }
}