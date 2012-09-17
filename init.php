<?php

/**
 * @author Roman Chvanikoff
 * @copyright 2011
 */

Route::set('generator', 'generate/<action>(/t(emplate)=<template>)(/<input>)', array('template' => '[\w\.]+','input' => '[\w;:,\._]+'))
		->defaults(array(
			'controller' => 'generator',
			'template' => 'default',
			'input' => null,
		));

?>