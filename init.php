<?php

/**
 * @author Roman Chvanikoff
 * @copyright 2011
 */

Route::set('generator', 'generate/<action>')
    ->defaults(array(
        'controller' => 'generator',
    ));

?>