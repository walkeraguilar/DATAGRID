<?php

class DataGrid{
    
    private $_id;
    private $_columns = array();        /*almacena las columnas*/
    private $_data;                     /*almacena la data*/
    public  $totalRegistros;           /*almacena el total de registros*/
    private $_paginate;
    private $_reg_x_pag;
    private $_ajax;
    private $_criterio;
    private $_itemPaginas;
    private $_info;
    private $_infoText;
    private $_changeNum = array(10,20,50,100);

    private function createHead(){
        $header  = '<thead>';
        $header .=      '<tr>';
        foreach ($this->_columns as $value) {
            $title = isset($value['title'])?$value['title']:'Title';
            
            $header .= '<th>'.$title.'</th>';
        }
        $header .=      '</tr>';
        $header .= '</thead>';
        
        return $header;
    }

    private function createRows(){
        $tbody  = '<tbody>';
        
        if(count($this->_data)){
            foreach ($this->_data as $row) {
                $tbody .= '<tr>';
                foreach ($this->_columns as $col){
                    $campo = isset($col['campo'])?$col['campo']:'';
                    
                    if(empty($campo)){
                        $f = 'Campo indefinido';
                    }else{
                        $f = $row[$campo];
                    }
                    
                    $tbody .= '<td>'.$f.'</td>';
                    
                }
                $tbody .= '</tr>';
            }
        }else{
            $tbody .= '<tr>';
            $tbody .=   '<td colspan="'.count($this->_columns).'" style="text-align:center">No se encontraron registros.</td>';
            $tbody .= '</tr>';
        }
        
        $tbody .= '</tbody>';
        return $tbody;
    }

    private function createPaginator(){
        $total = $this->totalRegistros;
        $paginaActual = $this->_paginate['page'];
        $length = $this->_paginate['reg_x_pag'];
        
        $numPaginas = ceil($total / $length);
        $itemPag = ceil($this->_itemPaginas / 2);
        
        $pagInicio = $paginaActual - $itemPag;
        $pagInicio = ($pagInicio <= 0)?1:$pagInicio;
        $pagFinal  = ($pagInicio + ($this->_itemPaginas - 1));
        
        $trIni = (($paginaActual * $length) - $length) + 1;
        $trFin = ($paginaActual * $length);

        $cantRreg = $trFin - ($trFin - $length);
        $trFinOk = ($cantRreg < $length) ? ($cantRreg === $total) ? $cantRreg : ($trFin - ($length - $cantRreg)) : $trFin;
            
        $this->_infoText = $trIni . ' al ' . $trFinOk . ' de ' . $total;
        
        $ul = '<ul class="pagination pull-right">';
        /*botones primeros*/
        if($paginaActual > 1){
            $disableFirst = '';
            $clickFirst = $this->_paginate['ajax'].'(\''.$this->_criterio.'\',1,\''.$this->_paginate['reg_x_pag'].'\')';
            $clickPrev  = $this->_paginate['ajax'].'(\''.$this->_criterio.'\',\''.($paginaActual - 1).'\',\''.$this->_paginate['reg_x_pag'].'\')';
        }else{
            $disableFirst = 'disabled';
            $clickFirst = '';
            $clickPrev = '';
        }
        $ul .= '<li class="'.$disableFirst.'"><a href="javascript:;" onclick="'.$clickFirst.'"><span class="glyphicon glyphicon-fast-backward"></span></a></li>';
        $ul .= '<li class="'.$disableFirst.'"><a href="javascript:;" onclick="'.$clickPrev.'"><span class="glyphicon glyphicon-backward"></span></a></li>';
        /*fin botones primeros*/
        
        /*botones medios*/
        for($i = $pagInicio; $i <= $pagFinal; $i++){
            if($i <= $numPaginas){
                if($i == $paginaActual){
                    $active = 'active';
                    $click = '';
                }else{
                    $active = '';
                    $click = $this->_paginate['ajax'].'(\''.$this->_criterio.'\',\''.$i.'\',\''.$this->_paginate['reg_x_pag'].'\')';
                }
                $ul .= '<li class="'.$active.'"><a href="javascript:;" onclick="'.$click.'">'.$i.'<span class="sr-only">(página actual)</span></a></li>';
            }else{
                break;
            }
        }
        /*fin botones medios*/
        if($numPaginas > 1 && $paginaActual != $numPaginas){
            $disableLast = '';
            $clickNext = $this->_paginate['ajax'].'(\''.$this->_criterio.'\',\''.($paginaActual + 1).'\',\''.$this->_paginate['reg_x_pag'].'\')';
            $clickLlast = $this->_paginate['ajax'].'(\''.$this->_criterio.'\',\''.$numPaginas.'\',\''.$this->_paginate['reg_x_pag'].'\')';
        }else{
            $disableLast = 'disabled';
            $clickNext = '';
            $clickLlast = '';
        }
        $ul .= '<li class="'.$disableLast.'"><a href="javascript:;" onclick="'.$clickNext.'"><span class="glyphicon glyphicon-forward"></span></a></li>';
        $ul .= '<li class="'.$disableLast.'"><a href="javascript:;" onclick="'.$clickLlast.'"><span class="glyphicon glyphicon-fast-forward"></span></a></li>';
        /*botones ultimos*/
        
        /*fin botones ultimos*/
        $ul .= '</ul>';
        
        return $ul;
    }

    private function changeLength(){
        $on = $this->_paginate['ajax'].'(\''.$this->_criterio.'\',\''.$this->_paginate['page'].'\',this.value)';
        
        $cb  = '<select id="changeLength_'.$this->_id.'" name="changeLength_'.$this->_id.'" onchange="'.$on.'">';
        foreach ($this->_changeNum as $value) {
            $sel = '';
            if($this->_paginate['reg_x_pag'] == $value){
                $sel = 'selected="selected"';
            }
            $cb .= '<option value="'.$value.'" '.$sel.'>'.$value.'</option>';
        }
        $cb .= '</select>';
        return $cb;
    }
    
    public function __construct($id) {
        $this->_id = $id;
    }

    public function addColumn($obj){
        $this->_columns[] = $obj;
    }
    
    public function selectData($obj){
        $this->_criterio = isset($obj['criterio'])?$obj['criterio']:'';
        $this->_info = isset($obj['info'])?$obj['info']:false;
        $class    = isset($obj['class'])?$obj['class']:'';
        $method   = isset($obj['method'])?$obj['method']:'';
        
        $this->_paginate = isset($obj['paginate'])?$obj['paginate']:false;
        $this->_itemPaginas = isset($this->_paginate['itemPaginas'])?$this->_paginate['itemPaginas']:5;
        $pag = isset($this->_paginate['page'])?$this->_paginate['page']:'';
        $regxpag = isset($this->_paginate['reg_x_pag'])?$this->_paginate['reg_x_pag']:'';
        
        $Obj = new $class();
        $result = $Obj->$method('P',  $this->_criterio,$pag,$regxpag);
        
        $this->_data = $result;
        $this->totalRegistros = $this->_data[0]['total'];
    }

    public function render(){
        $table  = '<table border="1" width="100%" class="table table-striped table-bordered table-hover">';
        $table .= $this->createHead();
        $table .= $this->createRows();
        $table .= '</table>';
        
        if(count($this->_paginate) && is_array($this->_paginate)){
           $p = $this->createPaginator();
           
           $table .= '<div class="container_pagin">'; 
           
           $table .= '<div class="pagin_inline pull-left pagin_info">';
           if($this->_info){
               $table .= $this->_infoText;
               $table .= $this->changeLength();
           }
           $table .= '</div>';
           
           $table .= '<div class="pagin_inline pull-right">';
           $table .=    $p;
           $table .= '</div>';
           $table .= '<div class="clearfix"></div>';
           $table .= '</div>'; 
        }
        
        return $table;
    }
    
}