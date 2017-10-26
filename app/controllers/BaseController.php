
<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

	protected function NOW()
	{		
		return gmdate("Y-m-d H:i:s", time());
	}
	protected function getSid()
	{
		mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$uuid = substr($charid, 0, 8).substr($charid, 8, 4).substr($charid,12, 4).substr($charid,16, 4).substr($charid,20,12);
		return $uuid;
	}

	protected function encrypt($data)
	{
		global $KEY;
		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($KEY), $data, MCRYPT_MODE_CBC, md5(md5($KEY))));
	}

}
