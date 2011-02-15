<?php
### Simple page loader FOR tuxKernel by WebNuLL
### Licensed under AGPLv3 ( Affero GPLv3 )
# http://wiki.github.com/webnull/OpenWikiBlog/

$EXT_INF = array ( 'classname' => 'libmenu');

class libmenu extends KernelModule
{
	# this is a plugin current state, kernel must check if the module is ready to use
	public $state='module not ready';
	private $tpl, $alang;

	public function __construct ( $Params, &$Kernel )
	{
		$this -> Kernel = $Kernel;
		$this->state = 'ready';
		$this->tpl = &$Kernel->tpl;
		$this->alang = &$Kernel->alang;
		$TranslationName = 'menu';

		// if another plugin wants to change the menu, this is the option
		$Kernel -> hooks -> startHook ( 'menu_translation', $TranslationName );

		//# ==== IF TRANSLATION MODULE IS NOT LOADED, WE WILL TRY TO USE DEFAULT MENU FROM SERIALIZED ARRAY IN FILE
		if ( !is_object ( $Kernel->alang ) )
		{
			if ( is_file ( 'data/core/menu.conf.php' ) )
			{
				$MenuList = unserialize(file_get_contents ( 'data/modules/libmenu/user-menu.php' ));

				// binding to change menu by other plugins
				$Kernel -> hooks -> startHook ( 'menu_array', $MenuList );
			} else {
				$MenuList = array ( 0 => array ( 'title' => 'No Menu file found', 'link' => '#' ) );
				$this->Debug->logString ( 'menu.so.php::E_ERROR::init: No Menu file found in \'data/modules/libmenu/menu.conf.php\'');
			}
		} else {

			//# ===== GET THE MENU FROM TRANSLATION
			$this->alang->loadTranslation($TranslationName);
			$MenuList = $this->alang->$TranslationName;	
		}
		
		$this->tpl->assign('menu', $MenuList);
	}
}
?>
