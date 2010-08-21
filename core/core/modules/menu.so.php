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

		$this->alang->loadTranslation('menu');
		$this->tpl->assign('menu', $this->alang->menu);
	}
}
?>
