<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace servicios\categorias;

use dao\categorias\CategoriasDAO;

class CategoriasService {
    
    public function getCategorias(){
        $dao = new CategoriasDAO();
        return $dao->getCategoriaDAO();
    }
    public function inserCategoria($categoria){
        $dao = new CategoriasDAO();
        return $dao->insertCategoriaDAO($categoria);
    }
    public function updateCategoria($categoria,$idcategoria,$old_category){
        $dao = new CategoriasDAO();
        return $dao->updateCategoriaDAO($categoria,$idcategoria,$old_category);
    }
    public function deleteCategoria($id,$categoria){
        $dao = new CategoriasDAO();
        return $dao->deleteCategoriaDAO($id,$categoria);
    }
    
  
    
    
}