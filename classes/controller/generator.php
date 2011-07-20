<?php

/**
 * @author Roman Chvanikoff
 * @copyright 2011
 */

class Controller_Generator extends Kohana_Controller
{

	protected $_stdin = NULL;
	protected $_cli_name = NULL;

	protected $templates = array(
		'model' => 'generator/model/jelly',
		'controller' => 'generator/controller'
	);

	/**
	 * @const End Of Input - use this string to finish work in CLI-mode
	 * @usage self::EOI
	 */
	const EOI = 'commit';

	public function before()
	{
		parent::before();

		if(!Kohana::$is_cli) {
			$this->request->redirect(Route::url('default', NULL, TRUE));
		}
		ob_end_clean();
		$this->_cli_name .= 'CLI-generator';
	}

	public function action_model($template = 'default', $input = null)
	{
		$this->_cli_name .= '>Model';
		$models = array();
		$in = null;

		if($template == 'default') {
			$template = $this->templates['model'];
		}
		if(isset($_SERVER['argc']) && $_SERVER['argc'] > 2) {
			if($_SERVER['argc'] == 3) {
				$template = strstr($_SERVER['argv'][2], '=');
				if(!$template) $input = $_SERVER['argv'][2];
				else $template = substr($template, 1, strlen($template) - 1);
			} else {
				$template = strstr($_SERVER['argv'][2], '=');
				$template = ($template == false) ? $_SERVER['argv'][2] : substr($template, 1, strlen($template) - 1);
				$input = $_SERVER['argv'][3];
			}
		}
		if(strcmp(strtolower($this->templates['model']), strtolower($template))) {
			$this->templates['model'] = preg_replace('/./', '/', $template);
		}

		if(!empty($input)) {
			$params = preg_split('/;/', $input);
			foreach($params as $param) {
				if(!empty($param)) {
					$modelParam = preg_split('/:/', $param);
					$model = array();
					if(count($modelParam) == 2) {
						$model['model'] = $modelParam[0];
						$model['table'] = empty($modelParam[1]) ? '' : '->table(\'' . $modelParam[1] . '\')';
					} else {
						$model['model'] = $modelParam[0];
						$model['table'] = '';
					}
					unset($modelParam);
					print $this->generate_model($model) ? 'Generated model: ' . $model['model'] . "\n"
							: 'Failed to generate model: ' . $model['model'] . "\n";
				}
			}
			unset($params);
			return;
		}
		$this->_stdin = fopen('php://stdin', 'r');
		while($in != self::EOI)
		{
			if(!$in) {
				echo 'Please enter model name and table name (if not satisfy ORM standards)' . "\n";
				echo 'syntax: model_name([|table_name])' . "\n";
				echo 'When done enter "' . self::EOI . '" to finish models generation' . "\n";
			}

			echo $this->_cli_name . '> ';
			$in = strtolower(trim(fgets($this->_stdin)));
			if($in == self::EOI) {
				echo 'Good bye';
				exit(1);
			}
			$key = count($models);
			$models[$key] = array();

			if(($pos = strpos($in, '|')) !== FALSE) {
				$temp = explode('|', $in);
				$models[$key] = array(
					'model' => $temp[0],
					'table' => '->table(\'' . $temp[1] . '\')'
				);
				$table = $temp[1];
			}
			else
			{
				$models[$key] = array(
					'model' => $in,
					'table' => ''
				);
				$table = 'not used but assumed ' . Inflector::plural($in);
			}

			print $this->generate_model($models[$key])
					? 'model ' . $models[$key]['model'] . ' (table - ' . $table . ') was generated.'
					: 'oops... Something went wrong, model was not generated.\n';
		}
	}

	public function action_controller($template = 'default', $input = null)
	{
		$this->_cli_name .= '>Controller';
		$in = NULL;
		$controllers = array();

		if($template == 'default') {
			$template = $this->templates['controller'];
		}

		if(isset($_SERVER['argc']) && $_SERVER['argc'] > 2) {
			if($_SERVER['argc'] == 3) {
				$template = strstr($_SERVER['argv'][2], '=');
				if(!$template) $input = $_SERVER['argv'][2];
				else $template = substr($template, 1, strlen($template) - 1);
			} else {
				$template = strstr($_SERVER['argv'][2], '=');
				$template = ($template == false) ? $_SERVER['argv'][2] : substr($template, 1, strlen($template) - 1);
				$input = $_SERVER['argv'][3];
			}
		}
		if(strcmp(strtolower($this->templates['controller']), strtolower($template))) {
			$this->templates['controller'] = preg_replace('/\./', '/', $template);
		}

		if(!empty($input)) {
			$params = preg_split('/;/', $input);
			foreach($params as $param) {
				if(preg_match('/^(?P<controller>[^:.=;,]+)(?:(?:\.(?P<parent>[^.=;,:]+))?)(?::(?P<actions>(?:(?:[^;=,:]+)(?:,|$))+))?$/', $param, $controller)) {
					$hasContr = strstr(strtolower($controller['controller']), 'controller_');
					if($hasContr == false) {
						$controller['controller'] = 'Controller_' . ucfirst($controller['controller']);
					}
					$controller['parent'] = empty($controller['parent']) ? 'Controller'
							: 'Controller_' . ucfirst($controller['parent']);
					$controller['actions'] = empty($controller['actions']) ? array()
							: preg_split('/,/', $controller['actions']);
				} else {
					print "Error reading a controller.\n";
				}
				print $this->generate_controller($controller)
						? 'Generated controller: ' . $controller['controller'] . "\n"
						: 'Failed to generate controller: ' . $controller['controller'] . "\n";
			}
			unset($params);
			return;
		}
		$this->_stdin = fopen('php://stdin', 'r');
		while($in != self::EOI)
		{
			if(!$in) {
				echo 'Please enter Controller name and it\'s actions' . "\n";
				echo 'syntax: controller_name([.parent_controller])([|action1[|action2[|action...]]])' . "\n";
				echo 'When done enter "' . self::EOI . '" to finish controllers generation' . "\n";
			}

			echo $this->_cli_name . '> ';
			$in = strtolower(trim(fgets($this->_stdin)));
			if($in == self::EOI) {
				echo 'Good bye';
				exit(1);
			}
			$key = count($controllers);
			$controllers[$key] = array(
				'controller' => 'Controller',
				'parent' => 'Controller',
				'actions' => array()
			);

			$parent_defined = strstr($in, '.');
			$actions_defined = strstr($in, '|');

			if($actions_defined) {
				$temp = explode('|', $in);
				$controllers[$key]['actions'] = array_slice($temp, 1);
				$in = $temp[0];
			}

			if($parent_defined) {
				$temp = explode('.', $in);

				// Pass by reference (PBR) here is forbidden but allowed. I dont know how to make this part of code w/out PBR
				@array_walk(explode('_', $temp[1]), function($el) use (& $controllers, $key)
					{
						$controllers[$key]['parent'] .= '_' . ucfirst($el);
					});
				$in = $temp[0];
			}
			// Pass by reference (PBR) here is forbidden but allowed. I dont know how to make this part of code w/out PBR
			@array_walk(explode('_', $in), function($el) use (& $controllers, $key)
				{
					$controllers[$key]['controller'] .= '_' . ucfirst($el);
				});

			$actions = empty($controllers[$key]['actions']) ? 'no actions'
					: 'actions ' . implode(', ', $controllers[$key]['actions']);

			echo $this->generate_controller($controllers[$key])
					? 'Controller ' . $controllers[$key]['controller'] . ' that extends ' . $controllers[$key]['parent'] . ' (with ' . $actions . ') was generated'
					: 'oops... Something went wrong, controller was not generated.';
			echo "\n";
		}
	}

	private function generate_model(array $data)
	{
		$path = APPPATH . 'classes' . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR;

		$model_path = explode('_', $data['model']);
		$model_name = '';
		foreach($model_path as $_path)
		{
			$path .= $_path . DIRECTORY_SEPARATOR;
			if(!is_dir($path)) {
				mkdir($path);
			}
			$model_name .= '_' . ucfirst($_path);
		}
		rmdir($path);
		$path = substr($path, 0, (strlen($path) - 1));
		$path .= EXT;
		$empty_model = Kohana::find_file('views', $this->templates['model']);
		$content = file_get_contents($empty_model);
		$table = $data['table'];
		$new_content = preg_replace_callback('#\{[$]{1}([a-z]+)\}#', function($matches) use ($model_name, $table)
			{
				$name = $model_name;
				return $$matches[1];
			}, $content);

		try
		{
			$file_h = fopen($path, 'w');
			fwrite($file_h, $new_content);
			fclose($file_h);
		}
		catch(exception $ex)
		{
			return FALSE;
		}
		return TRUE;
	}

	private function generate_controller(array $data)
	{
		$path = APPPATH . 'classes' . DIRECTORY_SEPARATOR;

		$controller_path = explode('_', $data['controller']);

		foreach($controller_path as $_path)
		{
			$_path = strtolower($_path);
			$path .= $_path . DIRECTORY_SEPARATOR;
			if(!is_dir($path)) {
				mkdir($path);
			}
		}
		rmdir($path);
		$path = substr($path, 0, (strlen($path) - 1));
		$path .= EXT;
		$empty_controller = Kohana::find_file('views', $this->templates['controller']);
		$content = file_get_contents($empty_controller);
		$actions_string = '';
		foreach($data['actions'] as $action)
		{
			$actions_string .= '

	public function action_' . $action . '()
	{

	}';
		}
		$new_content = preg_replace_callback('#\{[$]{1}([a-z]+)\}#', function($matches) use ($data, $actions_string)
			{
				switch($matches[1])
				{
					case 'controller' :
						return $data['controller'];
						break;
					case 'parent' :
						return $data['parent'];
						break;
					case 'actions' :
						return $actions_string;
						break;
				}
			}, $content);
		try
		{
			$file_h = fopen(strtolower($path), 'w');
			fwrite($file_h, $new_content);
			fclose($file_h);
		}
		catch(exception $ex)
		{
			return FALSE;
		}
		return TRUE;
	}
}

?>