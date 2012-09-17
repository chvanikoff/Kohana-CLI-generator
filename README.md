# Kohana Cli-Generator #
## About the Module
I know many things are not perfect here, the code in some places possibly awful... But:

1.	It works
2.	I will work on the module if someone else will be interested in it.

All bugfixes, comments, advices etc. (any response) are appreciated.

## Installation
Just add a link to this module in _Kohana::modules()_ in bootstrap.php

## Usage
Navigate to your **DOCROOT** *(where index.php is located)* via command-line, and try one of the following commands:

#### Interactive Model-Generator
> php index.php --uri=generate/model


#### Interactive Controller-Generator
> php index.php --uri=generate/controller

#### Static Model-Generator

> **Argument Syntax**

> php index.php --uri=generate/model *template=name* **model***:table*


> **URL Syntax**

> php index.php --uri=generate/model/*template=name*/**model***:table*


#### Static Controller-Generator

> **Argument Syntax**

> *Single Controller*

> php index.php --uri=generate/controller _template=name_ **controller**_.parent:action1,action2,action3_

> *Multiple Controllers*

> php index.php --uri=generate/controller _template=name_ **controller**_.parent:action1,action2,action3_;**controller2**_.parent:action1.._


> **URL Syntax**

> *Single Controller*

> php index.php --uri=generate/controller/_template=name/_**controller**_.parent:action1,action2,action3

> *Multiple Controllers*

> php index.php --uri=generate/controller/_template=name/_**controller**_.parent:action1,action2,action3;_**controller2**_.parent:action1.._


## Model-Generator

The model generator is used to generate [Jelly](https://github.com/creatoro/kohana-jelly-for-Kohana-3.1 "kohana-jelly-for-Kohana-3.1") models. It can also generate other models, depending on the templates you have available in your views folder. By default, it will use the _generator/model/jelly_

### Syntax

In the interactive console, the syntax of this command is **MODEL_NAME|TABLE_NAME**. Because the model name is optional, you don't need to specify it.

In a static call, the syntax for the arguments are **MODEL_NAME:TABLE_NAME**. For template argument syntax, see the Templates section. You can generate multiple models at once by separating them with a semicolon. See the Usage Syntax above for details.

### Arguments

**MODEL\_NAME**: The name of the intended model, without the "Model\_" prefix.

**TABLE\_NAME**: (_Optional_) An argument specifying the table containing the target object.

**TEMPLATE\_NAME**: (_Optional_) Specifying what template to use. See the Templates section below for details. (_Note: I have not yet implemented this on the interactive console._)

### Examples

#### Interactive Console
> **php** index.php --uri=generate/model
> _CLI\-generator_**\>**_Model_**\>** user\_metadata|my\_users\_meta

#### Static Console  
> **php** index.php --uri=generate/model/user\_metadata:my\_user\_metadata
> *or*
> **php** index.php --uri=generate/model/user\_metadata:my\_user\_metadata


_Generates:_

	<?php defined('SYSPATH') or die('No direct script access.');

	class Model_User_Metadata extends Jelly_Model {

		public static function initialize(Jelly_Meta $meta)
		{
			$meta->fields(array(

			))->table('my_users_meta');
		}
	}


The file location will be _"application/classes/model/user/metadata.php"_. If no table is specified (in case it's name satisfies ORM standards) - it will not add call to table() method of ORM.

#### Interactive Console
> **php** index.php --uri=generate/model
> _CLI\-generator_**\>**_Model_**\>** seller

#### Static Console
> **php** index.php --uri=generate/model user\_metadata:my\_user\_metadata
> *or*
> **php** index.php --uri=generate/model/user\_metadata:my\_user\_metadata

_Generates:_

	<?php defined('SYSPATH') or die('No direct script access.');

	class Model_Seller extends Jelly_Model {

		public static function initialize(Jelly_Meta $meta)
		{
			$meta->fields(array(

			));
		}
	}

The file location will be _"application/classes/model/seller.php"_.

## Controller-Generator

The controller generator generates Kohana-style controllers. If no parent is set, it uses the standard _Controller_ class as a parent. It can also generate other models, depending on the templates you have available in your views folder. By default, it will use the _generator/controller_ file.

### Syntax

In the interactive console, the syntax of this command is **CONTROLLER_NAME.PARENT_CONTROLLER|ACTION1|ACTION2|ACTION...**. Parent controller, and all actions are optional.

In a static call, the syntax for the arguments are **CONTROLLER_NAME.PARENT_CONTROLLER:ACTION1,ACTION2,ACTION...**. For template argument syntax, see the Templates section. As with models, you can generate multiple controllers by separating them with semicolons.

### Arguments

**CONTROLLER\_NAME**: The name of the intended controller. If you do not specify the "Controller_" prefix, it will.

**PARENT\_NAME**: (_Optional_) An argument specifying the previous controller to inherit. Defaults to "Controller".

**ACTION**: (_Optional_) The names of any actions you want present in the controller when generated.

**TEMPLATE\_NAME**: (_Optional_) Specifying what template to use. See the Templates section below for details. (_Note: I have not yet implemented this on the interactive console._)

### Examples

#### Interactive Console
> **php** index.php --uri=generate/controller
> _CLI\-generator_**\>**_Controller_**\>** user.template|create|read|update|delete

#### Static Console
> **php** index.php --uri=generate/controller user.template:create,read,update,delete
> *or*
> **php** index.php --uri=generate/controller/user.template:create,read,update,delete

_Generates:_

	<?php defined('SYSPATH') or die('No direct script access.');

	class Controller_User extends Controller_Template {

		public function action_create() {

		}

		public function action_read() {

		}

		public function action_update() {

		}

		public function action_delete() {

		}
	}


The file location will be _"application/classes/controller/user.php"_.


## Templates
You can now specify the template you'd like to use in static calls to the generator. To do so, you simple specify the
template in the first argument of your command. When specifying the template, you can either use **template=** or **t=**.
Either will generate the same results.

### Syntax#

When generating a model file (_Model\_Users in this case_), the following lines will generate the same results:

<pre>
	php index.php --uri=generate/model template=orm user:cv_users

	php index.php --uri=generate/model t=orm user:cv_users

	php index.php --uri=generate/model/template=orm/user:cv_users

	php index.php --uri=generate/model/t=orm/user:cv_users
</pre>

Controller generation is no different (_We'll pretend that the twig template has a parent field._):

<pre>
	php index.php --uri=generate/controller template=twig users.User_Admin:index,edit,delete,profile

	php index.php --uri=generate/controller t=twig users.User_Admin:index,edit,delete,profile

	php index.php --uri=generate/controller/template=twig/users.User_Admin:index,edit,delete,profile

	php index.php --uri=generate/controller/t=twig/users.User_Admin:index,edit,delete,profile
</pre>

You may notice, however, that you cannot specify child directories for the template when using the ***URL Syntax***. To account for this, I've had it replace any "."s in the template's name with a directory separator. Therefore, to use a template located at _"views/admin/controller/twig.php"_, you'd specify your template as _"admin.controller.twig"_. Hopefully this should work out as intended.


