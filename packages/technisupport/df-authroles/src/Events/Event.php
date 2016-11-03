<?php
namespace TechniSupport\DreamFactory\AuthRoles\Events;

/**
 * Class Events Clase que contiene los eventos manejados
 *
 * @package TechniSupport\DreamFactory\AuthRoles\Subject
 * @author TechniSupport SAS
 * @author Carlos Andraus <candraus@technisupport.com>
 * @copyright TechniSupport SAS 2016
 */
class Event
{
    const PRE_USER_CREATION = 0;
    const POST_USER_CREATION = 1;
    const PRE_ENTITY_GET=2;
    const POST_ENTITY_GET=3;
    const PRE_ENTITY_POST=4;
    const POST_ENTITY_POST=5;
    const PRE_ENTITY_PUT=6;
    const POST_ENTITY_PUT=7;
    const PRE_ENTITY_PATCH=8;
    const POST_ENTITY_PATCH=9;
    const PRE_ENTITY_DELETE=10;
    const POST_ENTITY_DELETE=11;
}