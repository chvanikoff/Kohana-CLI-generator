<?php defined('SYSPATH') or die('No direct script access.');

class {$controller} extends Controller_Template_Twig
{
	
	/* Set the template: $this->template->set_filename('master/base'); */
	
	/**
	 * Called at the beginning of a request.
	 * @return  void
	 */
	public function before()
	{
		parent::before();
		
	}{$actions}

	/**
	 * Called at the end of a request.
	 * @return  void
	 */
	public function after()
	{

		parent::after();
	}
}
