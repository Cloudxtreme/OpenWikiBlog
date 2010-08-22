<?php
# libmenu, no dependencies yet :-)
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
		$this->Site = addslashes($_GET['site']);

		//# ==== IF TRANSLATION MODULE IS NOT LOADED, WE WILL TRY TO USE DEFAULT MENU FROM SERIALIZED ARRAY IN FILE
		if ( !is_object ( $Kernel->alang ) )
		{
			if ( is_file ( 'websites/' .$this->Site. '/core/menu.conf.php' ) )
			{
				$MenuList = unserialize(file_get_contents ( 'websites/' .$this->Site. '/core/menu.conf.php' ));
			} else {
				$MenuList = array ( 0 => array ( 'title' => 'No Menu file found', 'link' => '#' ) );
				$this->Debug->logString ( 'menu.so.php::E_ERROR::init: No Menu file found in \'websites/' .$this->Site. '/core/menu.conf.php\'');
			}
		} else {

			//# ===== GET THE MENU FROM TRANSLATION
			$this->alang->loadTranslation('menu');
			$MenuList = $this->alang->menu;	
		}
		
		$this->tpl->assign('menu', $MenuList);
	}
}
?>
