<?php

namespace Com\Daw2\Models;

use \PDO;

class ProveedoresModel extends \Com\Daw2\Core\BaseModel{
    
    private const ORDER = [ 'alias', 'nombre_completo', 'nombre_tipo_proveedor', 'nombre_continente', 'anho_fundacion'];
    //const ORDER = [ 'proveedor.alias', 'proveedor.nombre_completo', 'aux_tipo_proveedor.nombre_tipo_proveedor', 'aux_continente.nombre_continente, proveedor.anho_fundación'];
    private const SELECT_FROM = 'SELECT proveedor.alias, proveedor.nombre_completo, aux_tipo_proveedor.nombre_tipo_proveedor, aux_continente.nombre_continente, proveedor.anho_fundacion, aux_continente.continente_avisar, proveedor.website, proveedor.email, proveedor.telefono FROM proveedor LEFT JOIN aux_tipo_proveedor ON aux_tipo_proveedor.id_tipo_proveedor = proveedor.id_tipo_proveedor LEFT JOIN aux_continente ON proveedor.id_continente = aux_continente.id_continente';
    
    public function obtenerTodo(){
        $stmt = $this->pdo->query(self::SELECT_FROM);
        return $stmt;
    }
    
    public function obtenerFiltrado(array $filtros, int $order = 1, int $pagina) : array{
        $datos = $this->generarWhere($filtros);
        $where = implode(' AND ', $datos[0]);
        if(empty($where)){
            $stmt = $this->pdo->query(self::SELECT_FROM.' ORDER BY '.self::ORDER[$order - 1].' LIMIT '.$this->getNumPag($pagina).', 20'); 
        }else{
            $stmt = $this->pdo->prepare(self::SELECT_FROM.' WHERE '.$where.' ORDER BY '.self::ORDER[$order - 1].' LIMIT '.$this->getNumPag($pagina).', 20'); 
        }
        $stmt->execute($datos[1]);
        return $stmt->fetchAll();
    }
    
    private function generarWhere(array $filtros) : array{
        $where = [];
        $var = [];
        
        if(isset($filtros['alias']) && strlen($filtros['alias']) > 0){
            $where[] = 'alias LIKE :alias';
            $expresion = "%$filtros[alias]%";
            $var['alias'] = $expresion;
        }
        if(isset($filtros['nombre_completo']) && strlen($filtros['nombre_completo']) > 0){
            $where[] = 'nombre_completo LIKE :nombre';
            $expresion = "%$filtros[nombre_completo]%";
            $var['nombre'] = $expresion;     
        }
        if(isset($filtros['id_continente']) && filter_var($filtros['id_continente'], FILTER_VALIDATE_INT)&& $filtros['id_continente'] > 0){
            $where[] = 'proveedor.id_continente = :continente';
            $var['continente'] = $filtros['id_continente'];
        }
        if(isset($filtros['id_tipo']) && filter_var_array($filtros['id_tipo'], FILTER_VALIDATE_INT)&& $filtros['id_tipo'] > 0){
            $i=0;
            $sentencia = '';
            foreach($filtros['id_tipo'] as $tipo){
                $var[':id_tipo'.$i] = $tipo;
                $sentencia .= ':id_tipo'.$i.',';
                $i++;
            }
            $sentencia = substr($sentencia, 0, -1);
            $where[] = 'proveedor.id_tipo_proveedor IN ('.$sentencia.')';
        }
        if(isset($filtros['min_anho']) && filter_var($filtros['min_anho'], FILTER_VALIDATE_INT)&& $filtros['min_anho'] > 0){
            $where[]= 'anho_fundacion >= :min';
            $var['min'] = $filtros['min_anho'];
        }
        if(isset($filtros['max_anho']) && filter_var($filtros['max_anho'], FILTER_VALIDATE_INT)&& $filtros['max_anho'] > 0){
            $where[]= 'anho_fundacion <= :max';
            $var['max'] = $filtros['max_anho'];
        }
        return [$where, $var];
    }
    
    public function getTipo(){
        $stmt = $this->pdo->query('SELECT * FROM aux_tipo_proveedor');
        return $stmt;
    }
    
    public function getContinentes(){
        $stmt = $this->pdo->query('SELECT * FROM aux_continente');
        return $stmt;
    }
    
    private function getNumPag(int $pagina) : int{
        return $registro = ($pagina -1) * 20;
    }
    
    public function count(array $filtros) : int{
        $datos = $this->generarWhere($filtros);
        $where = implode(' AND ', $datos[0]);
        if(empty($where)){
            $stmt = $this->pdo->query('SELECT COUNT(*) as numero FROM proveedor');
        }else{
            $stmt = $this->pdo->prepare('SELECT COUNT(*) as numero FROM proveedor LEFT JOIN aux_tipo_proveedor ON aux_tipo_proveedor.id_tipo_proveedor = proveedor.id_tipo_proveedor LEFT JOIN aux_continente ON proveedor.id_continente = aux_continente.id_continente WHERE '.$where);
        }
        $stmt->execute($datos[1]);
        $numeroA = $stmt->fetchAll();
        $numero = $this->getLastNumPag($numeroA[0]['numero']);
        return $numero;
        
    }


    private function getLastNumPag(int $numero) : int{
        if($numero%20 == 0){
            return $numero/20;
        }else{
            return (int)floor($numero/20) + 1;
        }
    }
}