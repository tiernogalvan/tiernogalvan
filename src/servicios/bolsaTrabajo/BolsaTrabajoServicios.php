<?php
/**
 * Created by PhpStorm.
 * User: Gato
 * Date: 01/05/2018
 * Time: 17:12
 */

namespace servicios\bolsaTrabajo;


use Carbon\Carbon;
use config\ConfigBolsaTrabajo;
use dao\bolsaTrabajo\BolsaTrabajoDAO;
use Respect\Validation\Validator as v;
use utils\bolsaTrabajo\ConstantesBolsaTrabajo;
use utils\bolsaTrabajo\MensajesBT;

class BolsaTrabajoServicios
{
    public function insertNuevaOferta($oferta)
    {
        $dao = new BolsaTrabajoDAO();
        return $dao->insertOfertaDB($oferta);
    }

    public function verOferta($idOferta)
    {
        $dao = new BolsaTrabajoDAO();
        return $dao->verOfertaDB($idOferta);
    }

    public function getOfertaFpTitulo($idOferta)
    {
        $dao = new BolsaTrabajoDAO();
        return $dao->getFpTitulosByIdOferta($idOferta);
    }

    public function getOfertasByFpIdAndTime($limit, $offset, $fpId, $orden)
    {
        if ($fpId == 0) {//Cuando queremos todas las ofertas del centro

            return $this->getAllOfertas($limit, $offset, $orden);
        }
        return $this->getAllOfertasFilter($limit, $offset, $fpId, $orden);
    }


    public function tratarParametrosOferta($json)
    {
        return $this->validarOferta($json);
    }

    public function getEstudiosCentro()
    {
        $dao = new BolsaTrabajoDAO();
        return $dao->getEstudiosCentroDB();
    }

    public function validarOferta($ofertaNueva)
    {
        $isValid = false;
        $validador = v::attribute(ConstantesBolsaTrabajo::TITULO_OFERTA, v::stringType()->length(10, 200))
            ->attribute(ConstantesBolsaTrabajo::DESCRIPCION_OFERTA, v::stringType()->length(10, 1000))
            ->attribute(ConstantesBolsaTrabajo::REQUISITOS_OFERTA, v::stringType()->length(10, 800))
            ->attribute(ConstantesBolsaTrabajo::EMAIL_OFERTA, v::optional(v::stringType()->length(4, 80)))
            ->attribute(ConstantesBolsaTrabajo::EMPRESA_OFERTA, v::optional(v::stringType()->length(3, 60)))
            ->attribute(ConstantesBolsaTrabajo::WEB_OFERTA, v::optional(v::stringType()->length(4, 80)))
            ->attribute(ConstantesBolsaTrabajo::LOCALIZACION_OFERTA, v::optional(v::stringType()->length(3, 85)))
            ->attribute(ConstantesBolsaTrabajo::TELEFONO_OFERTA, v::optional(v::stringType()->length(8, 15)))
            ->attribute(ConstantesBolsaTrabajo::VACANTE_OFERTA, v::optional(v::numeric()))
            ->attribute(ConstantesBolsaTrabajo::SALARIO_OFERTA, v::optional(v::floatVal()))//no funciona con Coma
            ->attribute(ConstantesBolsaTrabajo::CADUCIDAD_OFERTA, v::date('Y-m-d')->between(Carbon::now(), Carbon::now()->addMonth(3)));
        if ($validador->validate($ofertaNueva)) {
            $fp_array_oferta = $ofertaNueva->fp_oferta;

            $isValid = $this->validarFpCodes($fp_array_oferta);
        }
        return $isValid;
    }

    public function validarFpCodes($fp_array_oferta)
    {
        $isValid = false;
        if (is_array($fp_array_oferta)) {
            foreach ($fp_array_oferta as $item) {
                if (v::stringType()->length(1, 100)->validate($item)) {
                    $isValid = true;
                }
            }
        }
        return $isValid;
    }

    public function misOfertas($idOwner)
    {
        $dao = new BolsaTrabajoDAO();
        return $dao->getOfertasByIdOwner($idOwner);
    }

    public function miPerfil($idPerfil)
    {
        return true;
    }

    public function actualizarOferta($datos)
    {
        $dao = new BolsaTrabajoDAO();
        return $dao->updateOfertaDB($datos);
    }

    public function borrarOferta($idOferta, $idOwner)
    {
        $dao = new BolsaTrabajoDAO();
        return $dao->deleteOfertaDB($idOferta, $idOwner);
    }

    public function getAllOfertas($limit, $offset, $orden)
    {
        $dao = new BolsaTrabajoDAO();
        $ofertasDB = $dao->getAllOfertasDB($limit, $offset, $orden);
        if (is_array($ofertasDB)) {
            foreach ($ofertasDB as $item) {
                $item = $this->formatOferta($item);
            }
        }
        return $ofertasDB;
    }

    public function getAllOfertasFilter($limit, $offset, $fpId, $orden)
    {
        $dao = new BolsaTrabajoDAO();
        $ofertasDB = $dao->getOfertasByFpCodeAndTimeDB($limit, $offset, $fpId, $orden);
        if (is_array($ofertasDB)) {
            foreach ($ofertasDB as $item) {
                $item = $this->formatOferta($item);
            }
        }
        return $ofertasDB;
    }

    public function formatOferta($oferta)
    {
        $oferta->CREACION = $this->formatCreacion($oferta->CREACION);
        $oferta->DESCRIPCION = $this->formatTexto($oferta->DESCRIPCION, ConfigBolsaTrabajo::LONGITUD_TEXTO_DESCRIPCION);
        return $oferta;
    }

    public function formatCreacion($creacion)
    {
        $caducidad = Carbon::createFromTimeString($creacion);
        $diferencia = Carbon::now()->diffInDays($caducidad);
        return sprintf(MensajesBT::TIEMPO_TRANSCURRIDO, $diferencia);
    }

    public function formatTexto($texto, $limit)
    {
        if (strlen($texto) > $limit)
            $texto = substr($texto, 0, strrpos(substr($texto, 0, $limit), ' ')) . '...';
        return $texto;
    }
}//fin clase