<?php
namespace TechniSupport\DreamFactory\AuthRoles\Observer;

use TechniSupport\DreamFactory\AuthRoles\Subject\BaseSubject;

/**
 * Class BaseObserver clase base observer para implementar patron observador
 *
 * @package TechniSupport\DreamFactory\AuthRoles\Observer
 * @author TechniSupport SAS
 * @author Carlos Andraus <candraus@technisupport.com>
 * @copyright TechniSupport SAS 2016
 */
abstract class BaseObserver {
    /**
     * Obtiene la información de ún sujeto
     *
     * @param BaseSubject $subject_in
     * @return mixed
     */
    abstract function update(BaseSubject &$subject_in);

    /**
     * Retorna el id del observador
     * @return string
     */
    abstract function id();

}