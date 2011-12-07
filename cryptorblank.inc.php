<?php
class Cryptor
{
    public function cipher()
    {
        return $this->cipher;
    }
    
    public function decrypt($data)
    {
    	if(!$this->generatedtime)
    	{
            return false;
    	}
    	
    	return rtrim(mcrypt_decrypt($this->cipher, base64_decode($this->base64key), $data, $this->mode, base64_decode($this->base64iv)), "\0\4");
    }
    
    public function encrypt($data)
    {
        if(!$this->generatedtime)
        {
            return false;
        }
        
        return mcrypt_encrypt($this->cipher, base64_decode($this->base64key), $data, $this->mode, base64_decode($this->base64iv));
    }
    
    public function generatedtime()
    {
        return $this->generatedtime;
    }
    
    public function mode()
    {
        return $this->mode;
    }

    
    /**
     * @var string
     */
    private $cipher = NULL;

    /**
     * @var integer
     */
    private $generatedtime = 0;
    
    /**
     * @var string
     */
    private $base64iv = NULL;
    
    /**
     * @var string
     */
    private $base64key = NULL;
    
    /**
     * @var string
     */
    private $mode = NULL;
}
?>